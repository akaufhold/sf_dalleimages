<?php

declare(strict_types=1);

namespace Stackfactory\SfDalleimages\Tca;

use TYPO3\CMS\Backend\Form\AbstractNode;
use TYPO3\CMS\Backend\Form\NodeInterface;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Backend\Form\InlineStackProcessor;
use TYPO3\CMS\Backend\Form\Container\FileReferenceContainer;
use TYPO3\CMS\Backend\Form\Event\ModifyFileReferenceControlsEvent;
use TYPO3\CMS\Backend\Form\Event\ModifyFileReferenceEnabledControlsEvent;
use TYPO3\CMS\Core\Localization\LanguageService;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;

use TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariantCollection;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Http\ServerRequestFactory;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Configuration\BackendConfigurationManager;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Core\Configuration\AjaxConfiguration;

use Psr\EventDispatcher\EventDispatcherInterface;

use Stackfactory\SfDalleimages\Services\ImageService;
use Stackfactory\SfDalleimages\Domain\Repository\AssetRepository;


class PreviewImages extends AbstractNode implements NodeInterface
{
    private const TEMPLATE_FILE ='PreviewImages.html';
    private const ELEMENTS_PER_ROW = 5;
    private const FILE_REFERENCE_TABLE = 'sys_file_reference';
    private const FOREIGN_SELECTOR = 'uid_local';

    protected $isSliding = false;
    protected $view;

    private EventDispatcherInterface $eventDispatcher;
    protected IconFactory $iconFactory;
    protected InlineStackProcessor $inlineStackProcessor;

    public function __construct(
        NodeFactory $nodeFactory, 
        array $data, 
        EventDispatcherInterface $eventDispatcher = null
    ) {
        parent::__construct($nodeFactory, $data);
        $this->eventDispatcher = $eventDispatcher ?? GeneralUtility::makeInstance(EventDispatcherInterface::class);
        $this->iconFactory = GeneralUtility::makeInstance(IconFactory::class);
        $this->inlineStackProcessor = GeneralUtility::makeInstance(InlineStackProcessor::class);
    }

    /**
     * Renders the custom tca preview images
     * reusing sys_file_reference rendering from typo3/cms-backend/Classes/Form/Container/FileReferenceContainer.php
     * 
     * @return array
     */
    public function render(): array
    {
        // Initialize StandaloneView
        $this->view = GeneralUtility::makeInstance(StandaloneView::class);

        // Configure template path
        $configurationManager = GeneralUtility::makeInstance(BackendConfigurationManager::class);

        // Get template root path from extension config
        $typoscriptSetup = $configurationManager->getTypoScriptSetup();
        $templatePath = $typoscriptSetup['module.']['sf_dalleimages.']['view.']['templateRootPaths.'][0];
        $fluidTemplateFile = $templatePath . self::TEMPLATE_FILE;
        $this->view->setTemplatePathAndFilename($fluidTemplateFile);

        // Fetch data and prepare variables for the template
        $record = $this->data['databaseRow'];
        $currentContentUid = $record['uid'];
        $imageService = GeneralUtility::makeInstance(ImageService::class);
        $assetData = getType($currentContentUid) === 'integer' ? $imageService->getAssetsForContentElement('tt_content', $currentContentUid, 'crdate') : null;
        $referenceUids = $assetData ? array_column($assetData, 'file_uid') : null;

        foreach ($assetData as $key => $value) {
            $fileUidMapping[$value['file_uid']] = $value;
        }
        
        // Process data and assign to the Fluid template
        if (is_array($assetData)) {
            $assetRepository = GeneralUtility::makeInstance(AssetRepository::class);
            $fileReferences = $assetRepository->findByUidList($referenceUids);
            $this->data['inlineStructure'] = [
                'stable' => [
                    '0' => [
                        'table' => 'tt_content',
                        'uid' => $record['uid'],
                        'field' => 'assets'
                    ],
                ],
            ];
            //DebugUtility::debug($fileReferences);
            if ($fileReferences) {
                $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
                foreach($fileReferences as $key => $fileReference) {
           
                    $uid = $fileReference['uid'];
                    try {
                        /** @var File $file */
                        $file = $resourceFactory->getFileObject($uid);
                        $imageUrl = $file->getPublicUrl();
                        $fileReferences[$key]['publicUrl'] = $imageUrl; 
                        $fileReferences[$key]['controlHtml'] = $this->getPanelWrapperHtml($fileUidMapping[$uid]['reference_uid'], $fileUidMapping[$uid]['file_uid']);
                        $fileReferences[$key]['hidden'] = $fileUidMapping[$uid]['hidden'];
                        
                        //DebugUtility::debug($fileReferences);
                    } catch (FileDoesNotExistException $e) {
                        throw new \RuntimeException('Could not find files with the uid "' . $uid, 1314354065);
                    }
                }
            }

            $this->view->assign('fileReferences', $fileReferences);
            $this->view->assign('data', $this->data);
            $this->isSliding = count($fileReferences) > self::ELEMENTS_PER_ROW;
        }
        $this->view->assign('isSliding', $this->isSliding);
        $result['html'] = $this->view->render();

        return $result;
    }

    /**
     * Renders the HTML header for the panel wrapper
     * @param int $uidReference 
     * @param int $uidFile 
     * @return string
     */
    protected function getPanelWrapperHtml($uidReference, $uidFile):string {
        $inlineStackProcessor = $this->inlineStackProcessor;
        $inlineStackProcessor->initializeByGivenStructure($this->data['inlineStructure']);

        // Send a mapping information to the browser via JSON:
        // e.g. data[<curTable>][<curId>][<curField>] => data-<pid>-<parentTable>-<parentId>-<parentField>-<curTable>-<curId>-<curField>
        $formPrefix = $inlineStackProcessor->getCurrentStructureFormPrefix();
        $domObjectId = $inlineStackProcessor->getCurrentStructureDomObjectIdPrefix($this->data['inlineFirstPid']);

        //DebugUtility::debug($domObjectId);
        //DebugUtility::debug($domObjectId);
        $this->fileReferenceData = $this->data['inlineData'];
        $this->fileReferenceData['map'][$formPrefix] = $domObjectId;

        $resultArray = $this->initializeResultArray();
        $resultArray['inlineData'] = $this->fileReferenceData;

        $html = '';
        $classes = [];
        $combinationHtml = '';
        $record = $this->data['databaseRow'];
        $uid = $record['uid'] ?? 0;
        $appendFormFieldNames = '[' . self::FILE_REFERENCE_TABLE . '][' . $uidReference . ']';
        $objectId = $domObjectId . '-' . self::FILE_REFERENCE_TABLE . '-' . $uidReference;
        $isNewRecord = $this->data['command'] === 'new';
        $hiddenFieldName = (string)($this->data['processedTca']['ctrl']['enablecolumns']['disabled'] ?? '');
        if (!$this->data['isInlineDefaultLanguageRecordInLocalizedParentContext']) {
            if ($isNewRecord || $this->data['isInlineChildExpanded']) {
                $fileReferenceData = $this->renderFileReference($this->data);
                $html = $fileReferenceData['html'];
                $resultArray = $this->mergeChildReturnIntoExistingResult($resultArray, $fileReferenceData, false);
            } else {
                // This class is the marker for the JS-function to check if the full content has already been loaded
                $classes[] = 't3js-not-loaded';
            }
            if ($isNewRecord) {
                // Add pid of file reference as hidden field
                $html .= '<input type="hidden" name="data' . htmlspecialchars($appendFormFieldNames)
                    . '[pid]" value="' . (int)$record['pid'] . '"/>';
                // Tell DataHandler this file reference is expanded
                $ucFieldName = 'uc[inlineView]'
                    . '[' . $this->data['inlineTopMostParentTableName'] . ']'
                    . '[' . $this->data['inlineTopMostParentUid'] . ']'
                    . htmlspecialchars($appendFormFieldNames);
                $html .= '<input type="hidden" name="' . htmlspecialchars($ucFieldName)
                    . '" value="' . (int)$this->data['isInlineChildExpanded'] . '" />';
            } else {
                // Set additional field for processing for saving
                $html .= '<input type="hidden" name="cmd' . htmlspecialchars($appendFormFieldNames)
                    . '[delete]" value="1" disabled="disabled" />';
                if ($hiddenFieldName !== ''
                    && (!$this->data['isInlineChildExpanded']
                        || !in_array($hiddenFieldName, $this->data['columnsToProcess'], true))
                ) {
                    $isHidden = (bool)($record[$hiddenFieldName] ?? false);
                    $html .= '<input type="checkbox" class="d-none" data-formengine-input-name="data'
                        . htmlspecialchars($appendFormFieldNames)
                        . '[' . htmlspecialchars($hiddenFieldName) . ']" value="1"'
                        . ($isHidden ? ' checked="checked"' : '') . ' />';
                }
            }
            // If this file reference should be shown collapsed
            $classes[] = $this->data['isInlineChildExpanded'] ? 'panel-visible' : 'panel-collapsed';
        }
        $hiddenFieldHtml = implode("\n", $resultArray['additionalHiddenFields'] ?? []);

        if ($this->data['inlineParentConfig']['renderFieldsOnly'] ?? false) {
            // Render "body" part only
            $resultArray['html'] = $html . $hiddenFieldHtml . $combinationHtml;
            return $resultArray;
        }

        // Render header row and content (if expanded)
        if ($this->data['isInlineDefaultLanguageRecordInLocalizedParentContext']) {
            $classes[] = 't3-form-field-container-inline-placeHolder';
        }
        if ($record[$hiddenFieldName] ?? false) {
            $classes[] = 't3-form-field-container-inline-hidden';
        }
        if ($isNewRecord) {
            $classes[] = 'isNewFileReference';
        }
        
        $hashedObjectId = 'hash-' . md5($objectId);
        $containerAttributes = [
            'id' => $objectId . '_div',
            'class' => 'form-irre-object panel panel-default panel-condensed ' . trim(implode(' ', $classes)),
            'data-object-uid' => $uidReference ?? 0,
            'data-object-id' => $objectId,
            'data-object-id-hash' => $hashedObjectId,
            'data-object-parent-group' => $domObjectId . '-' . self::FILE_REFERENCE_TABLE,
            'data-field-name' => $appendFormFieldNames,
            'data-topmost-parent-table' => $this->data['inlineTopMostParentTableName'],
            'data-topmost-parent-uid' => $this->data['inlineTopMostParentUid'],
            'data-placeholder-record' => $this->data['isInlineDefaultLanguageRecordInLocalizedParentContext'] ? '1' : '0',
        ];

        $languageService = $this->getLanguageService();

        $databaseRow = $this->data['databaseRow'];

        $objectId = $this->inlineStackProcessor->getCurrentStructureDomObjectIdPrefix($this->data['inlineFirstPid'])
            . '-' . self::FILE_REFERENCE_TABLE
            . '-' . ($uidReference ?? 0);

        $altText = BackendUtility::getRecordIconAltText($databaseRow, self::FILE_REFERENCE_TABLE, false);

        // Renders a thumbnail for the header
        $thumbnail = '';
        if ($GLOBALS['TYPO3_CONF_VARS']['GFX']['thumbnails'] ?? false) {
            if (!empty($uidFile)) {
                try {
                    $fileObject = GeneralUtility::makeInstance(ResourceFactory::class)->getFileObject($uidFile);
                    if ($fileObject->isMissing()) {
                        $thumbnail = '
                            <span class="badge badge-danger">'
                                . htmlspecialchars($languageService->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:warning.file_missing')) . '
                            </span>&nbsp;
                            ' . htmlspecialchars($fileObject->getName()) . '
                            <br />';
                    } elseif ($fileObject->isImage() || $fileObject->isMediaFile()) {
                        $imageSetup = $this->data['inlineParentConfig']['appearance']['headerThumbnail'] ?? [];
                        $cropVariantCollection = CropVariantCollection::create($databaseRow['crop'] ?? '');
                        if (!$cropVariantCollection->getCropArea()->isEmpty()) {
                            $imageSetup['crop'] = $cropVariantCollection->getCropArea()->makeAbsoluteBasedOnFile($fileObject);
                        }
                        $processedImage = $fileObject->process(
                            ProcessedFile::CONTEXT_IMAGECROPSCALEMASK,
                            array_merge(['maxWidth' => '210', 'maxHeight' => '210'], $imageSetup)
                        );
                        // Only use a thumbnail if the processing process was successful by checking if image width is set
                        if ($processedImage->getProperty('width')) {
                            $imageUrl = $processedImage->getPublicUrl() ?? '';
                            $thumbnail = '<img src="' . htmlspecialchars($imageUrl) . '" ' .
                                'width="' . $processedImage->getProperty('width') . '" ' .
                                'height="' . $processedImage->getProperty('height') . '" ' .
                                'alt="" ' .
                                'title="' . htmlspecialchars($altText) . '" ' .
                                'loading="lazy">';
                        }
                    }
                } catch (\InvalidArgumentException $e) {
                    $fileObject = null;
                }
            }
        }

        if ($thumbnail !== '') {
            $headerImage = '
                <div class="form-irre-header-thumbnail" id="' . $objectId . '_thumbnailcontainer">
                    ' . $thumbnail . '
                </div>';
        } else {
            $headerImage = '
                <div class="form-irre-header-icon" id="' . $objectId . '_iconcontainer">
                    ' . $this->iconFactory
                        ->getIconForRecord(self::FILE_REFERENCE_TABLE, $databaseRow, Icon::SIZE_SMALL)
                        ->setTitle($altText)
                        ->render() . '
                </div>';
        }

        $ariaControls = htmlspecialchars($objectId . '_fields', ENT_QUOTES | ENT_HTML5);
        return'
            <div ' . GeneralUtility::implodeAttributes($containerAttributes, true) . '>
                <div class="panel-heading" data-bs-toggle="formengine-file" id="' . htmlspecialchars($hashedObjectId, ENT_QUOTES | ENT_HTML5) . '_header" data-expandSingle="' . (($this->data['inlineParentConfig']['appearance']['expandSingle'] ?? false) ? 1 : 0) . '">
                    <div class="form-irre-header">
                        ' . $headerImage . '
                        ' . $this->getControlHtml($uidReference) . '
                    </div>
                </div>
                <div class="panel-collapse" id="' . $ariaControls . '">' . $html . $hiddenFieldHtml . $combinationHtml . '</div>
            </div>';
    
    }

     /**
     * Renders the icon toolbar for dalle image tab
     * @return string
     */
    protected function getControlHtml($uidReference):string {
        $this->data['tableName'] = 'sys_file_reference';
        $fileReferenceData = array_merge($this->data, [
            'vanillaUid' => $uidReference,
            'databaseRow' => [
                'uid_local' => [
                    ['uid' => $uidReference ?? 0,
                    'title' => 'sys_file_reference']
                ],
            ],
            'inlineParentConfig' => [
                'readOnly' => false,
                'appearance' => [
                    'enabledControls' => [
                        'delete' => TRUE,
                    ]
                ],
            ],
            'inlineParentUid' => $this->data['databaseRow']['uid'],
            'inlineParentTableName' => 'tt_content',
            'inlineParentFieldName' => 'assets',
            'isInlineChild' => true,
            'isInlineChildExpanded' => true,
        ]);

        $controlEvent = new ModifyFileReferenceEnabledControlsEvent($fileReferenceData, $fileReferenceData['databaseRow']);
        $this->eventDispatcher->dispatch($controlEvent);

        $fileReferenceContainer = GeneralUtility::makeInstance(CustomFileReferenceContainer::class, $this->nodeFactory, $fileReferenceData);
        
        $fileControls = $fileReferenceContainer->getRenderFileReferenceHeaderControl();

        return '
            <div class="form-irre-header-cell form-irre-header-control t3js-formengine-file-header-control">
                ' . $fileControls . '
            </div>';
    }

    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}




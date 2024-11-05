<?php

declare(strict_types=1);

namespace Stackfactory\SfDalleimages\Tca;

use TYPO3\CMS\Backend\Form\AbstractNode;
use TYPO3\CMS\Backend\Form\NodeInterface;
use TYPO3\CMS\Backend\Form\NodeFactory;
use Stackfactory\SfDalleimages\Services\ImageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

use TYPO3\CMS\Core\Utility\DebugUtility;

use Stackfactory\SfDalleimages\Domain\Repository\AssetRepository;

use TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Http\ServerRequestFactory;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Configuration\BackendConfigurationManager;

use TYPO3\CMS\Backend\Form\Container\FileReferenceContainer;
use TYPO3\CMS\Backend\Form\Event\ModifyFileReferenceControlsEvent;
use TYPO3\CMS\Backend\Form\Event\ModifyFileReferenceEnabledControlsEvent;
use Psr\EventDispatcher\EventDispatcherInterface;

class PreviewImages extends AbstractNode implements NodeInterface
{
    protected $templateFile ='PreviewImages.html';
    protected $elementsPerRow = 5;
    protected $view;
    protected $isSliding = false;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        NodeFactory $nodeFactory, 
        array $data, 
        EventDispatcherInterface $eventDispatcher = null
    ) {
        parent::__construct($nodeFactory, $data);
        $this->eventDispatcher = $eventDispatcher ?? GeneralUtility::makeInstance(EventDispatcherInterface::class);
    }
    /**
     * Renders the custom tca preview images
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
        $fluidTemplateFile = $templatePath . $this->templateFile;
        $this->view->setTemplatePathAndFilename($fluidTemplateFile);

        // Fetch data and prepare variables for the template
        $record = $this->data['databaseRow'];
        $currentContentUid = $record['uid'];
        $imageService = GeneralUtility::makeInstance(ImageService::class);
        $assetUids = getType($currentContentUid) === 'integer' ? $imageService->getAssetsForContentElement('tt_content', $currentContentUid, 'crdate') : null;

        // Process data and assign to the Fluid template
        if (is_array($assetUids)) {
            $assetRepository = GeneralUtility::makeInstance(AssetRepository::class);
            $fileReferences = $assetRepository->findByUidList($assetUids);

            if ($fileReferences) {
                $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
                foreach($fileReferences as $key => $fileReference) {
                    $uid = $fileReference['uid'];
                    try {
                        /** @var File $file */
                        $file = $resourceFactory->getFileObject($uid);
                        $imageUrl = $file->getPublicUrl();
                        $fileReferences[$key]['publicUrl'] = $imageUrl; 

                        $fileReferenceData = array_merge($this->data, [
                            'databaseRow' => [
                                'uid_local' => [['uid' => $uid]],
                            ],
                            'inlineParentConfig' => [
                                'readOnly' => false,
                                'appearance' => [
                                    'useSortable' => true,
                                    'enabledControls' => [
                                        'delete' => 1,
                                        'sort' => 1,
                                        'info' => 1
                                    ]
                                ],
                            ],
                            'isInlineChildExpanded' => true,
                        ]);
                    
                        $controlEvent = new ModifyFileReferenceEnabledControlsEvent($fileReferenceData, $fileReferenceData['databaseRow']);
                        $controlEvent->enableControl('delete');
                        //$controlEvent->enableControl('sort');
                        $controlEvent->enableControl('info');
                        $this->eventDispatcher->dispatch($controlEvent);

                        $fileReferenceContainer = GeneralUtility::makeInstance(CustomFileReferenceContainer::class, $this->nodeFactory, $fileReferenceData);
                        
                        $fileControls = $fileReferenceContainer->getRenderFileReferenceHeaderControl();
                        $fileReferences[$key]['controlHtml'] = '
                            <div class="form-irre-header-cell form-irre-header-control t3js-formengine-file-header-control">
                                ' . $fileControls . '
                            </div>';
                        //DebugUtility::debug($fileReferences);
                    } catch (FileDoesNotExistException $e) {
                        throw new \RuntimeException('Could not find files with the uid "' . $uid, 1314354065);
                    }
                }
            }

            $this->view->assign('fileReferences', $fileReferences);
            $this->isSliding = count($fileReferences) > $this->elementsPerRow;
        }
        $this->view->assign('isSliding', $this->isSliding);
        $result['html'] = $this->view->render();

        return $result;
    }
}
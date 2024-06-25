<?php
declare(strict_types=1);

namespace Stackfactory\SfDalleimages\Tca;

use TYPO3\CMS\Backend\Form\AbstractNode;
use TYPO3\CMS\Backend\Form\NodeInterface;
use TYPO3\CMS\Backend\Form\NodeFactory;
use Stackfactory\SfDalleimages\Services\ImageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

use Stackfactory\SfDalleimages\Domain\Repository\AssetRepository;

use TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Http\ServerRequestFactory;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Configuration\BackendConfigurationManager;

class PreviewImages extends AbstractNode implements NodeInterface
{
    protected $templateFile ='PreviewImages.html';
    protected $elementsPerRow = 5;
    protected $view;

    public function __construct(NodeFactory $nodeFactory, array $data)
    {
        parent::__construct($nodeFactory, $data);
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
        $extbaseFrameworkConfiguration = $configurationManager->getTypoScriptSetup();
        $templatePath = $extbaseFrameworkConfiguration['module.']['sf_dalleimages.']['view.']['templateRootPaths.'][0];
        $fluidTemplateFile = $templatePath . $this->templateFile;
        $this->view->setTemplatePathAndFilename($fluidTemplateFile);

        // Fetch data and prepare variables for the template
        $record = $this->data['databaseRow'];
        $currentContentUid = $record['uid'];
        $this->imageService = GeneralUtility::makeInstance(ImageService::class);
        $assetUids = getType($currentContentUid) === 'integer' ? $this->imageService->getAssetsForContentElement('tt_content', $currentContentUid, 'crdate') : null;

        // Process data and assign to the Fluid template
        if (is_array($assetUids)) {
            $assetRepository = GeneralUtility::makeInstance(AssetRepository::class);
            $fileReferences = $assetRepository->findByUidList($assetUids);

            if ($fileReferences) {
                $this->resourceFactory = GeneralUtility::makeInstance(ResourceFactory ::class);
                foreach($fileReferences as $key => $fileReference) {
                    $uid = $fileReference['uid'];
                    try {
                        /** @var File $file */
                        $file = $this->resourceFactory->getFileObject($uid);
                        $imageUrl = $file->getPublicUrl();
                        $fileReferences[$key]['publicUrl'] = $imageUrl; 
                    } catch (FileDoesNotExistException $e) {
                        throw new \RuntimeException('Could not find files with the uid "' . $uid, 1314354065);
                    }
                }
            }

            $this->view->assign('fileReferences', $fileReferences);
            $isSliding = count($fileReferences) > $this->elementsPerRow;
        }
        $this->view->assign('isSliding', $isSliding);
        $return['html'] = $this->view->render();

        return $return;
    }
}
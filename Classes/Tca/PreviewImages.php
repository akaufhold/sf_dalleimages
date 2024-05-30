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

class PreviewImages extends AbstractNode implements NodeInterface
{
    /**
     * Instance of the node factory to create sub elements, container and single element expansions.
     *
     * @var NodeFactory
     */
    protected $nodeFactory;

    /**
     * Renders the custom TCA field.
     *
     * @return array
     */
    public function render(): array
    {
        // Retrieve the record data
        $record = $this->data['databaseRow'];

        // Retrieve the assets field data
        $currentContentUid = $record['uid'];
        // $currentAssets = explode(',',$record['assets']);

        $this->imageService = GeneralUtility::makeInstance(ImageService::class);
        $assetUids = $this->imageService->getAssetsForContentElement('tt_content', $currentContentUid, 'crdate');

        // If there are no assets, return an empty string
        if (!is_array($assetUids)) {
            $html = '<div>No image found</div>';
        } else {
            // Get the file repository to fetch the file reference
            /** @var \Stackfactory\SfDalleimages\Domain\Repository\AssetRepository $assetRepository */
            $assetRepository = GeneralUtility::makeInstance(AssetRepository::class);
            /** @var \TYPO3\CMS\Core\Resource\FileReference $fileReference */
            $fileReferences = $assetRepository->findByUidList($assetUids);

            // If the file reference is not found, return an empty string
            if (!$fileReferences) {
                $html = '<div>No image found</div>';
            } else {
                $this->resourceFactory = GeneralUtility::makeInstance(ResourceFactory ::class);
                $html .= sprintf('<div id="carouselPreview" class="carousel dalle-preview slide" data-ride="carousel"><div class="carousel-inner row">');
                $elementsPerRow = 5;
                foreach($fileReferences as $key => $fileReference) {
                    ($key%$elementsPerRow === 0) && $html .= sprintf('<div class="dalle-preview-item carousel-item'.($key === 0 ?' active': '').'">');
    
                    $uid = $fileReference['uid'];
                    try {
                        /** @var File $file */
                        $file = $this->resourceFactory->getFileObject($uid);
                        $imageUrl = $file->getPublicUrl();
                        $html .= sprintf('<img class="dalle-preview-item-image py-4" src="%s" alt="">', htmlspecialchars($imageUrl));
                    } catch (FileDoesNotExistException $e) {
                        throw new \RuntimeException('Could not find files with the uid "' . $uid, 1314354065);
                    }
                    (($key%$elementsPerRow === 4) || (count($fileReferences) === ($key-1))) && $html .= sprintf('</div>');
                }
                $html .= sprintf('</div>');
                if (count($fileReferences) > $elementsPerRow) {
                    $html .= sprintf('
                        <a class="carousel-control-prev" data-bs-target="#carouselPreview" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="sr-only">Previous</span>
                        </a>
                        <a class="carousel-control-next" data-bs-target="#carouselPreview" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="sr-only">Next</span>
                        </a></div></div>'
                    );
                } else {
                    $html .= sprintf('</div>');
                }
            }
        }

        $result['html'] .= $html;

		return $result;
    }
}
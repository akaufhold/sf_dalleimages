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
        $record = $this->data['databaseRow'];
        $currentContentUid = $record['uid'];

        $this->imageService = GeneralUtility::makeInstance(ImageService::class);
        getType($currentContentUid) === 'integer' && $assetUids = $this->imageService->getAssetsForContentElement('tt_content', $currentContentUid, 'crdate');

        // If there are no assets, return an empty string
        if (!is_array($assetUids)) {
            $html = '<div>No image found</div>';
        } else {
            /** @var \Stackfactory\SfDalleimages\Domain\Repository\AssetRepository $assetRepository */
            $assetRepository = GeneralUtility::makeInstance(AssetRepository::class);
            /** @var \TYPO3\CMS\Core\Resource\FileReference $fileReference */
            $fileReferences = $assetRepository->findByUidList($assetUids);

            $elementsPerRow = 5;
            $isSliding = (count($fileReferences) > $elementsPerRow) ? true : false;

            // If the file reference is not found, return an empty string
            if (!$fileReferences) {
                $html = '<div>No image found</div>';
            } else {
                $this->resourceFactory = GeneralUtility::makeInstance(ResourceFactory ::class);
                $html .= sprintf('<div id="carouselPreview" class="carousel carousel-dark dalle-preview slide '.($isSliding ? "px-5" : "").'" data-bs-ride="carousel" data-bs-wrap="false"><div class="carousel-inner row">');

                foreach($fileReferences as $key => $fileReference) {
                    ($key%$elementsPerRow === 0) && $html .= sprintf('<div class="dalle-preview-item carousel-item'.($key === 0 ?' active': '').($isSliding ? " py-4" : "").'">');
    
                    $uid = $fileReference['uid'];
                    try {
                        /** @var File $file */
                        $file = $this->resourceFactory->getFileObject($uid);
                        $imageUrl = $file->getPublicUrl();
                        $html .= sprintf('<img class="dalle-preview-item-image'.($isSliding ? " zoom p-2" : "").'" src="%s" alt="">', htmlspecialchars($imageUrl));
                    } catch (FileDoesNotExistException $e) {
                        throw new \RuntimeException('Could not find files with the uid "' . $uid, 1314354065);
                    }
                    ((($key)%$elementsPerRow === 4) || (count($fileReferences) === ($key+1))) && $html .= sprintf('</div>');
                }
                $html .= sprintf('</div>');

                if ($isSliding) {
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
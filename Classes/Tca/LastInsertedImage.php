<?php
declare(strict_types=1);

namespace Stackfactory\SfDalleimages\Tca;

use TYPO3\CMS\Backend\Form\AbstractNode;
use TYPO3\CMS\Backend\Form\NodeInterface;
use TYPO3\CMS\Backend\Form\NodeFactory;
use Stackfactory\SfDalleimages\Services\ImageService;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\DebugUtility;

use \TYPO3\CMS\Core\Resource\FileRepository;

class LastInsertedImage extends AbstractNode implements NodeInterface
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
        $this->imageService = GeneralUtility::makeInstance(ImageService::class);
        $lastAssetUid = $this->imageService->getLastAssetForContentElement('tt_content', $currentContentUid);

        // If there are no assets, return an empty string
        if (empty($lastAssetUid)) {
            $html = '<div>No image found</div>';
        } else {
            
            // Get the file repository to fetch the file reference
            /** @var \TYPO3\CMS\Core\Resource\FileRepository $fileRepository */
            $fileRepository = GeneralUtility::makeInstance(FileRepository::class);
            /** @var \TYPO3\CMS\Core\Resource\FileReference $fileReference */
            $fileReference = $fileRepository->findByUid($lastAssetUid);

            // If the file reference is not found, return an empty string
            if (!$fileReference) {
                $html = '<div>No image found</div>';
            }

            // Generate the HTML for the image
            $imageUrl = $fileReference->getPublicUrl();
            $html = sprintf('<div class="w-100"><img src="%s" alt="" style="max-width: 300px; height: auto;"></div>', htmlspecialchars($imageUrl));
        }

        $result['html'] .= $html;

		return $result;
    }
}

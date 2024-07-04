<?php
declare(strict_types=1);

namespace Stackfactory\SfDalleimages\Tca;

use TYPO3\CMS\Core\Page\PageRenderer;
use Stackfactory\SfDalleimages\Utility\BackendLanguageUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Information\Typo3Version;

class ImageSizeOptions
{
    protected PageRenderer $pageRenderer;
    /**
     * Rendering item option for image size field
     * 
     * @param array $config
     * @return array
     */
    public function getSizeOptions(array &$config): void
    {	
        // Load JavaScript via PageRenderer	
		$typo3Version = new Typo3Version();
		if ($typo3Version->getMajorVersion() > 11) {
			$this->pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
			$this->pageRenderer->loadJavaScriptModule('@vendor/sf_dalleimages/sizeOptions.js');
		} else {
			$this->pageRenderer = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Page\PageRenderer::class);
			$this->pageRenderer->loadRequireJsModule('TYPO3/CMS/SfDalleimages/sizeOptions');
		}

        $selectedModel = $config['row']['tx_dalleimage_model'];
        $config['row']['tx_dalleimage_size'] = 0;
        $items = [];
        
        $itemOptionsLL = 'LLL:EXT:sf_dalleimages/Resources/Private/Language/locallang_db.xlf:tt_content.dalleimage.options.size';

        switch ($selectedModel) {
            case 'dall-e-2':
                $items = [
                    ['256x256', '256x256'],
                    ['512x512', '512x512'],
                    ['1024x1024', '1024x1024'],
                ];
                break;
            case 'dall-e-3':
                $items = [
                    ['1024x1024', '1024x1024'],
                    ['1024x1792', '1024x1792'],
                    ['1792x1024', '1792x1024'],
                ];
                break;
            default:
                $items = [
                    ['256x256', '256x256'],
                    ['512x512', '512x512'],
                    ['1024x1024', '1024x1024'],
                ];
                break;
        }
        $config['items'] = $items;
    }
}

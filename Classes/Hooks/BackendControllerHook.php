<?php

namespace Stackfactory\SfDalleimages\Hooks;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\DebugUtility;

class BackendControllerHook
   {
       public function registerClientSideEventHandler(): void
       {
            $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);

            $typo3Version = new Typo3Version();
            if ($typo3Version->getMajorVersion() > 11) {
                $this->pageRenderer->loadJavaScriptModule(
                    '@vendor/sf_dalleimages/backend.js',
                );
            } else {
                // keep RequireJs for TYPO3 below v12.0
                $pageRenderer->loadRequireJsModule(
                    'TYPO3/CMS/SfDalleimages/Backend',
                    'function() { console.log("Loaded own module."); }'
                    );
            }
       }
    }
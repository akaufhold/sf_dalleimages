<?php
defined('TYPO3_MODE') || die();

use TYPO3\CMS\Core\Information\Typo3Version;

call_user_func(static function() {

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('sf_dalleimages', 'Configuration/TypoScript', 'Dalle Images');

    if (TYPO3_MODE === 'BE') {
        $typo3Version = new Typo3Version();
        if ($typo3Version->getMajorVersion() > 11) {
            $GLOBALS['TYPO3_CONF_VARS']['BE']['stylesheets']['sf_dalleimages'] = 'EXT:sf_dalleimages/Resources/Public/StyleSheets/';
        } else {
            $GLOBALS['TBE_STYLES']['skins']['sf_dalleimages'] = array();
            $GLOBALS['TBE_STYLES']['skins']['sf_dalleimages']['name'] = 'Dalle Image Backend CSS';
            $GLOBALS['TBE_STYLES']['skins']['sf_dalleimages']['stylesheetDirectories'][] = 'EXT:sf_dalleimages/Resources/Public/StyleSheets/';
        }
    }
});

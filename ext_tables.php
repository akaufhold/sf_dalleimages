<?php
defined('TYPO3') || die();

use TYPO3\CMS\Core\Information\Typo3Version;

$typo3Version = new Typo3Version();
if ($typo3Version->getMajorVersion() > 11) {
    $GLOBALS['TYPO3_CONF_VARS']['BE']['stylesheets']['sf_dalleimages'] = 'EXT:sf_dalleimages/Resources/Public/StyleSheets/';
} else {
    $GLOBALS['TBE_STYLES']['skins']['sf_dalleimages'] = array();
    $GLOBALS['TBE_STYLES']['skins']['sf_dalleimages']['name'] = 'Dalle Image Backend CSS';
    $GLOBALS['TBE_STYLES']['skins']['sf_dalleimages']['stylesheetDirectories'][] = 'EXT:sf_dalleimages/Resources/Public/StyleSheets/';
}
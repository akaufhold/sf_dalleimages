<?php
defined('TYPO3_MODE') || die();

call_user_func(static function() {

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('sf_dalleimages', 'Configuration/TypoScript', 'Dalle Images');

});

<?php
defined('TYPO3') || die();

use TYPO3\CMS\Core\Information\Typo3Version;

call_user_func(function(){

	$iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);

	$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1716995129] = [
		'nodeName' => 'previewImages',
		'priority' => 40,
		'class' => Stackfactory\SfDalleimages\Tca\PreviewImages::class,
	];

	$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1717157527] = [
		'nodeName' => 'buttonsAndProgress',
		'priority' => 40,
		'class' => \Stackfactory\SfDalleimages\Tca\ButtonsAndProgress::class,
	];
});
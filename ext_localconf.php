<?php

defined('TYPO3') || die();

call_user_func(function(){
	$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1716995129] = [
		'nodeName' => 'previewImages',
		'priority' => 40,
		'class' => Stackfactory\SfDalleimages\Tca\PreviewImages::class,
	];

	$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1717157527] = [
		'nodeName' => 'buttonsDalle',
		'priority' => 40,
		'class' => \Stackfactory\SfDalleimages\Tca\ButtonsDalle::class,
	];
});

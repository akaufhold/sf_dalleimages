<?php

defined('TYPO3') || die();

call_user_func(function(){
	//$iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);

	$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1716995129] = [
		'nodeName' => 'previewImages',
		'priority' => 40,
		'class' => Stackfactory\SfDalleimages\Tca\PreviewImages::class,
	];

	$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1717157527] = [
		'nodeName' => 'generatePrompt',
		'priority' => 40,
		'class' => \Stackfactory\SfDalleimages\Tca\GeneratePrompt::class,
	];

	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/backend.php']['constructPostProcess'][]
		= Stackfactory\SfDalleimages\Hooks\BackendControllerHook::class . '->registerClientSideEventHandler';


	/*$GLOBALS['TYPO3_CONF_VARS']['BE']['AJAX']['sf_dalleimage::handleFormSubmission'] = [
		'callbackMethod' => \Stackfactory\SfDalleimages\Controller\AjaxController::class . '->formSubmitAction',
		'csrfTokenCheck' => false,
	];*/
});

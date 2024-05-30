<?php
defined('TYPO3_MODE') || die();

call_user_func(function(){

	$iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
	
	/*$iconRegistry->registerIcon(
		'sf_seolighthouse-plugin-showlighthouse',
		\TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
		['source' => 'EXT:sf_seolighthouse/Resources/Public/Icons/user_plugin_showlighthouse.svg']
	);*/

	$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1716642329] = [
		'nodeName' => 'sendToDalleButton',
		'priority' => 40,
		'class' => Stackfactory\SfDalleimages\Tca\SendToDalleButton::class,
	];

	$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1716995129] = [
		'nodeName' => 'PreviewImages',
		'priority' => 40,
		'class' => Stackfactory\SfDalleimages\Tca\PreviewImages::class,
	];

	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/backend.php']['constructPostProcess'][]
		= Stackfactory\SfDalleimages\Hooks\BackendControllerHook::class . '->registerClientSideEventHandler';


	/*$GLOBALS['TYPO3_CONF_VARS']['BE']['AJAX']['sf_dalleimage::handleFormSubmission'] = [
		'callbackMethod' => \Stackfactory\SfDalleimages\Controller\AjaxController::class . '->formSubmitAction',
		'csrfTokenCheck' => false,
	];*/
});

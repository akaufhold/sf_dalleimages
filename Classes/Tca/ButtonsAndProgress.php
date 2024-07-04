<?php

declare(strict_types=1);

namespace Stackfactory\SfDalleimages\Tca;

use TYPO3\CMS\Backend\Form\Element\InputTextElement;
use TYPO3\CMS\Core\Page\PageRenderer;

use TYPO3\CMS\Extbase\Configuration\BackendConfigurationManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Core\Information\Typo3Version;

use TYPO3\CMS\Core\Utility\DebugUtility;

class ButtonsAndProgress extends InputTextElement{
	protected PageRenderer $pageRenderer;
	protected $templateFile ='ButtonsAndProgress.html';
	protected $view;
	/**
	 * Render Function for customized TCA Field
	 *
	 * @return array
	 */
	public function render(): array
	{
		$result = parent::render();

		// Initialize StandaloneView
		$this->view = GeneralUtility::makeInstance(StandaloneView::class);
		
		$typo3Version = new Typo3Version();
		if ($typo3Version->getMajorVersion() > 11) {
			// Load JavaScript via PageRenderer
			$this->pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
			$this->pageRenderer->loadJavaScriptModule('@vendor/sf_dalleimages/backend.js');
		} else {
			$this->pageRenderer = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Page\PageRenderer::class);
			$this->pageRenderer->loadRequireJsModule('TYPO3/CMS/SfDalleimages/Backend');
		}


		// Configure template path
		$configurationManager = GeneralUtility::makeInstance(BackendConfigurationManager::class);
		// Get template root path from extension config
		$typoscriptSetup = $configurationManager->getTypoScriptSetup();
		$templatePath = $typoscriptSetup['module.']['sf_dalleimages.']['view.']['templateRootPaths.'][0];
		$fluidTemplateFile = $templatePath . $this->templateFile;
		$this->view->setTemplatePathAndFilename($fluidTemplateFile);

		// Append the button HTML to the existing HTML
		$result['html'] .= $this->view->render();

		return $result;
	}
}

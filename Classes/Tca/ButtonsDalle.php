<?php

declare(strict_types=1);

namespace Stackfactory\SfDalleimages\Tca;

use TYPO3\CMS\Backend\Form\Element\InputTextElement;
use TYPO3\CMS\Core\Page\PageRenderer;

use TYPO3\CMS\Extbase\Configuration\BackendConfigurationManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

use TYPO3\CMS\Core\Utility\DebugUtility;

class ButtonsDalle extends InputTextElement{
	protected $templateFile ='ButtonsDalle.html';
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

		// DebugUtility::debug($result);
		// Load JavaScript via PageRenderer
		$this->pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
		$this->pageRenderer->loadJavaScriptModule('@vendor/sf_dalleimages/Backend.js');

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

<?php

declare(strict_types=1);

namespace Stackfactory\SfDalleimages\Tca;

use TYPO3\CMS\Backend\Form\Element\InputTextElement;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Backend\Routing\UriBuilder;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use Stackfactory\SfDalleimages\Utility\DalleIntegration;

use TYPO3\CMS\Core\Page\JavaScriptModuleInstruction;

use TYPO3\CMS\Core\Http\RequestFactory;

class SendToDalleButton extends InputTextElement{
	/**
	 * Render Function for customized TCA Field
	 *
	 * @return array
	 */
	public function render()
	{
		$result = parent::render();
		// Get the field configuration
		//$fieldConfig = $this->data['parameterArray']['fieldTSConfig'];
		
		// Retrieve custom parameters, e.g., text and action
		$buttonPromptText = 'Generate Prompt';
		$buttonDalleText = 'Get Image from Dalle';
		//$buttonHtml = '<button href="#" class="btn btn-primary sendToDalle" name="_savedok" form="EditDocumentController">' . htmlspecialchars($buttonText) . '</button>';
		$buttonHtml = '<a href="#" class="btn btn-secondary generatePrompt me-2">' . htmlspecialchars($buttonPromptText) . '</a>' 
								. '<a href="#" class="btn btn-primary sendToDalle">' . htmlspecialchars($buttonDalleText) . '</a>';

		// Load JavaScript via PageRenderer
			
		$this->pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
		$this->pageRenderer->loadRequireJsModule('TYPO3/CMS/SfDalleimages/Backend');

		// Append the button HTML to the existing HTML
		$result['html'] .= $buttonHtml;

		return $result;
	}
}

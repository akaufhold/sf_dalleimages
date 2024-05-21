<?php

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
		$buttonText = 'Send Image to Dalle';
		$buttonHtml = '<a href="#" class="btn btn-primary sendToDalle">' . htmlspecialchars($buttonText) . '</a>';

		// Load JavaScript via PageRenderer
			
		$this->pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
		$this->pageRenderer->loadRequireJsModule('TYPO3/CMS/SfDalleimages/Backend');

		// Append the button HTML to the existing HTML
		$result['html'] .= $buttonHtml;

		return $result;
	}
}

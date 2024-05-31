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

class ProgressBar extends InputTextElement{
	/**
	 * Render Function for customized TCA Field
	 *
	 * @return array
	 */
	public function render()
	{
		$progressStatus = 'Status';
		$progressProgress = 'Progress';
		$progressError = 'Error';
		$progressSuccess = 'Success';

		$buttonHtml = '<div class="progressBar progress-bar-animated">
											<span class="counterContainer">
													<span class="counterAmount" data-width="0%"></span>
													<span class="counterTitle">
													<span class="statusMessage">
														'.$progressStatus.'
													</span>
													<span class="progressMessage">
														'.$progressProgress.'
													</span>
													<span class="errorMessage">
														'.$progressError.'
													</span>
													<span class="successMessage">
														'.$progressSuccess.'
													</span>
													</span>
											</span>
									</div>';

		// Load JavaScript via PageRenderer
		$this->pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
		$this->pageRenderer->loadRequireJsModule('TYPO3/CMS/SfDalleimages/Backend');

		// Append the button HTML to the existing HTML
		$result['html'] .= $buttonHtml;

		return $result;
	}
}

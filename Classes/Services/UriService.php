<?php

declare(strict_types=1);

namespace Stackfactory\SfDalleimages\Services;

use TYPO3\CMS\Core\Utility\GeneralUtility;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

use TYPO3\CMS\Backend\Routing\UriBuilder;

class UriService {
		/**
     * @var UriBuilder
     */
    protected $uriBuilder;
		
		public function __construct()
		{
				$this->uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
		}

	/** Get url for certain tab when editing content elements
	  * @param string $backendFormUrl
		* @return string
		*/
		public function getEditFormUrl($backendFormUrl, int $contentUid, string $targetTab = 'media') {
				$urlParameters = [
						'edit' => [
								'tt_content' => [
										$contentUid => 'edit',
								],
						],
				];
				return (string) $this->uriBuilder->buildUriFromRoute('record_edit', $urlParameters). '#tab-' .  $targetTab;
		}
}
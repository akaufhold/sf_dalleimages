<?php

declare(strict_types=1);

namespace Stackfactory\SfDalleimages\Controller;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\DebugUtility;

use Stackfactory\SfDalleimages\Utility\DalleIntegration;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AjaxController extends InputTextElement
{ 
	public function __construct(
		private ResponseFactoryInterface $responseFactory
	) {
		
	}

	/**
	 * 
	 */
	public function getDalleImageAction(ServerRequestInterface $request): ResponseInterface
	{
			$input = $request->getQueryParams()['input']
				?? throw new \InvalidArgumentException(
					'Please provide a text prompt',
					1580585107,
			);
			$textPrompt = $submittedData['text_prompt'];
			$dalleUtility = GeneralUtility::makeInstance(DalleUtility::class);
			$dalleimage = $dalleUtility->processDalleImage($data, $table);

			$response = $this->responseFactory->createResponse()
				->withHeader('Content-Type', 'application/json; charset=utf-8');
				
			$response->getBody()->write(
					json_encode(['result' => $result], JSON_THROW_ON_ERROR),
			);

			return $response;
	}
}
<?php

declare(strict_types=1);

namespace Stackfactory\SfDalleimages\Controller;

use TYPO3\CMS\Core\Utility\DebugUtility;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

use GuzzleHttp\Client;
use Stackfactory\SfDalleimages\Services\ImageService;

class AjaxController {
		private ServerRequestInterface $request;
		private ImageService $imageService;
		private ResponseFactoryInterface $responseFactory;

    public function __construct(
			ImageService $imageService,
			ResponseFactoryInterface $responseFactory,
		)
		{
				$this->imageService = $imageService;
				$this->responseFactory = $responseFactory;
		}

		/**
     * Get image from Dalle Api and save to db afterwards
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function processAjaxRequest(ServerRequestInterface $request): ResponseInterface 
    {
        $this->request = $request;

        $textPrompt = $this->request->getQueryParams()['input'] ?? throw new \InvalidArgumentException(
            'Please provide a text prompt for dalle image generation',
            1580585107,
        );
        
        if ($textPrompt != '') {
            $fileUid = $this->imageService->saveImageAsAsset($this->imageService->getDalleImageUrl($textPrompt));

            $contentID = $this->request->getQueryParams('uid');
            $fileReferenceUid = $this->imageService->addUserImageReference('tt_content', $fileUid, $contentID, 'assets', substr($textPrompt, 0, 254), $textPrompt);
            $this->imageService->enableTableField('tt_content', 'assets', $contentUid, 1);
    
            $response = $this->responseFactory->createResponse()
                ->withHeader('Content-Type', 'application/json; charset=utf-8');
            $response->getBody()->write(
                json_encode(['result' => $fileReferenceUid], JSON_THROW_ON_ERROR),
            );
            return $response;
        }
    }
}
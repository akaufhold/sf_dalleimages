<?php

declare(strict_types=1);

namespace Stackfactory\SfDalleimages\Controller;

use TYPO3\CMS\Core\Utility\DebugUtility;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

use Stackfactory\SfDalleimages\Services\ImageService;
use Stackfactory\SfDalleimages\Services\UriService;

class AjaxController {
    private ImageService $imageService;
    private UriService $uriService;
    private ResponseFactoryInterface $responseFactory;

    public function __construct(
        ImageService $imageService,
        UriService $uriService,
        ResponseFactoryInterface $responseFactory,
    )
    {
        $this->imageService = $imageService;
        $this->uriService = $uriService;
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

        /* Get parameters from ajax call */
        $textPrompt = $this->request->getQueryParams()['input'] ?? throw new \InvalidArgumentException(
            'Please provide a text prompt for dalle image generation',
            1580585107,
        );

        $contentID = (int) $this->request->getQueryParams()['uid'];

        $backendFormUrl = $this->request->getQueryParams()['backendFormUrl'] ?? throw new \InvalidArgumentException(
            'Please provide a backend from url to ajax request',
            1580585107,
        );

        
        if ($textPrompt != '') {
            $mediaTabUrl = $this->uriService->getEditFormUrl($backendFormUrl, $contentID, 'media');

            $fileUid = $this->imageService->saveImageAsAsset($this->imageService->getDalleImageUrl($textPrompt));

            $fileReferenceUid = $this->imageService->addUserImageReference('tt_content', $fileUid, $contentID, substr($textPrompt, 0, 254), $textPrompt, 'assets');
            $this->imageService->enableTableField('tt_content', 'assets', $contentID, 1);

            /* create ajax response */
            $response = $this->responseFactory->createResponse()->withHeader('Content-Type', 'application/json; charset=utf-8');
            $response->getBody()->write(json_encode(['result' => $fileReferenceUid], JSON_THROW_ON_ERROR));
            return $response;
        }
    }
}
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
    private ServerRequestInterface $request;

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
     * Process ajax request to get image from dalle api and save to db afterwards
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function processAjaxRequest(ServerRequestInterface $request): ResponseInterface 
    {
        $this->request = $request;

        /* Get parameters from ajax call and error handling */
        $textPrompt = (string) $this->request->getQueryParams()['input'] ?? throw new \InvalidArgumentException(
            'Please provide a text prompt for dalle image generation',
            1580585107,
        );
        $model = (string) $this->request->getQueryParams()['model'] ?? throw new \InvalidArgumentException(
            'Please provide a model for dalle image generation',
            1580585107,
        );
        $size = (string) $this->request->getQueryParams()['size'] ?? throw new \InvalidArgumentException(
            'Please provide a image size for dalle image generation',
            1580585107,
        );
        $quality = (string) $this->request->getQueryParams()['quality'] ?? throw new \InvalidArgumentException(
            'Please provide a image quality for dalle image generation',
            1580585107,
        );
        $amount = (int) $this->request->getQueryParams()['amount'] ?? throw new \InvalidArgumentException(
            'Please provide a image amount for dalle image generation',
            1580585107,
        );
        $contentID = (int) $this->request->getQueryParams()['uid'];
        
        if ($textPrompt != '') {
            /* save image in fileadmin and add sys_file entry */
            $imageUrl = $this->imageService->getDalleImageUrl($textPrompt, $model, $size, $quality, $amount);
            $fileUid = $this->imageService->saveImageAsAsset($imageUrl);
            
            /* add image to sys_file_reference and enable assets in related tt_content element */
            $fileReferenceUid = $this->imageService->addUserImageReference('tt_content', $fileUid, $contentID, substr($textPrompt, 0, 254), $textPrompt, 'assets');
            $this->imageService->enableTableField('tt_content', 'assets', $contentID, 1);

            /* create ajax response */
            $response = $this->responseFactory->createResponse()->withHeader('Content-Type', 'application/json; charset=utf-8');
            $response->getBody()->write(json_encode(['result' => $fileReferenceUid], JSON_THROW_ON_ERROR));
            return $response;
        }
    }
}
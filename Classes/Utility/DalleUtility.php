<?php

declare(strict_types=1);

namespace Stackfactory\SfDalleimages\Utility;

use GuzzleHttp\Client;

use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Resource\Storage\StorageRepository as ResourceStorageRepository;
use TYPO3\CMS\Core\Resource\Index\FileIndexRepository;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Resource\ResourceStorage;

use TYPO3\CMS\Core\Resource\Index\Indexer;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\DebugUtility;

use Psr\Http\Message\ServerRequestInterface;

final class DalleUtility
{
    private StorageRepository $storageRepository;

    public function __construct(
        StorageRepository $storageRepository
    )
    {
        $this->storageRepository = $storageRepository;
    }

    public function processDalleImage(ServerRequestInterface $request)
    {
        $textPrompt = $request->getQueryParams()['input']
        ?? throw new \InvalidArgumentException(
            'Please provide a number',
            1580585107,
        );

        if ($textPrompt!='') {
            // Fetch image from Dalle
            $imageUrl = $this->fetchImageFromDalle($textPrompt);
            //$imageUrl = 'fileadmin/user_upload/apartment.jpg';
            var_dump($imageUrl);

            if ($imageUrl) {
                // Save image as asset in TYPO3
                $assetIdentifier = $this->saveImageAsAsset($imageUrl);

                // Store the asset identifier in the "assets" field
                $data['assets'] = $assetIdentifier;
            } else {
                // Handle error
            }
        }
    }

    
    public function fetchImageFromDalle($textPrompt): string
    {
        // Define the Dalle API endpoint
        $dalleEndpoint = 'https://api.openai.com/v1/images/generations';
		$apiKey = 'sk-proj-S00mJYo8iMjEagJHh3oGT3BlbkFJQRk7AwpVIhTw3RgjP4u0';

        // Define the Dalle API headers (replace "YOUR_API_KEY" with your actual API key)
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '.$apiKey,
        ];

        // Define the request body with the text prompt
        $body = [
            'prompt' => $textPrompt,
            'model' => 'dall-e-2', // Specify the DALL-E model
            'n' => 1, // Number of images to generate
            'size' => '1024x1024', // Size of the image
        ];

        // Send request to Dalle API
        $client = new Client();
        $response = $client->post($dalleEndpoint, [
            'headers' => $headers,
            'json' => $body,
        ]);

        // Check if request was successful
        if ($response->getStatusCode() === 200) {
            $responseData = json_decode($response->getBody(), true);
            // Check if image URL is present in the response
            if (isset($responseData['data'][0]['url'])) {
                    return $responseData['data'][0]['url'];
            } else {
                    // Handle error - Image URL not found in response
                    return false;
            }
        } else {
                // Handle error - Request failed
                return false;
        }
    }

    public function saveImageAsAsset($imageUrl)
    {
        $storage = $this->storageRepository->getDefaultStorage();
        $fileName = basename($imageUrl);
        $tempFilePath = GeneralUtility::tempnam('imported_image');

        // Download the image
        $imageContent = GeneralUtility::getUrl($imageUrl);
        if ($imageContent === false) {
            throw new \RuntimeException('Could not download image from URL: ' . $imageUrl);
        }

        // Save the image content to a temporary file
        GeneralUtility::writeFile($tempFilePath, $imageContent);

        // Move the file to TYPO3 storage
        $folder = $storage->getRootLevelFolder();
        $file = $storage->addFile($tempFilePath, $folder, $fileName);

        // Clean up the temporary file
        GeneralUtility::unlink_tempfile($tempFilePath);

        // Index the new file
        $indexer = GeneralUtility::makeInstance(Indexer::class, $storage);
        $indexer->indexFile($file);

        // Create a file reference if needed
        $fileReference = $this->createFileReference($file);

        return $fileReference;
    }

    protected function createFileReference($file)
    {
        $fileIndexRepository = GeneralUtility::makeInstance(FileIndexRepository::class);
        $fileReference = $fileIndexRepository->createFileReferenceObject($file->getUid());

        // You may need to persist the file reference in a database table if you intend to use it in a specific context

        return $fileReference;
    }
}
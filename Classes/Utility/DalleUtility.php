<?php
namespace Stackfactory\SfDalleimages\Utility;

use GuzzleHttp\Client;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DalleUtility
{
    
    public function fetchImageFromDalle($textPrompt)
    {
        // Define the Dalle API endpoint
        $dalleEndpoint = 'https://api.openai.com/v1/images/generate';
		$apiKey = 'sk-proj-S00mJYo8iMjEagJHh3oGT3BlbkFJQRk7AwpVIhTw3RgjP4u0';

        // Define the Dalle API headers (replace "YOUR_API_KEY" with your actual API key)
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '.$apiKey,
        ];

        // Define the request body with the text prompt
        $body = [
            'prompt' => $textPrompt,
            'model' => 'dall-e-3', // Specify the DALL-E model
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

    public function processDalleImage(&$data, $table, $id, &$fieldArray, &$pObj)
    {
        if ($table === 'tt_content' && isset($fieldArray['dalle_text_prompt'])) {
            $textPrompt = $fieldArray['dalle_text_prompt'];

            // Fetch image from Dalle
            $imageUrl = $this->fetchImageFromDalle($textPrompt);

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

    public function saveImageAsAsset($imageUrl)
    {
        $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
        $storage = $resourceFactory->getDefaultStorage();

        // Download the image from the URL
        $imageContent = file_get_contents($imageUrl);

        // Save the image as an asset in TYPO3
        $newFile = $storage->addFile(
            'path/to/save/image.jpg', // Specify the destination path
            $imageContent
        );

        // Return the file reference
        return $newFile->getIdentifier();
    }
}
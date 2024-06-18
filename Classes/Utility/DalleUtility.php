<?php

declare(strict_types=1);

namespace Stackfactory\SfDalleimages\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\BackendConfigurationManager;

use GuzzleHttp\Client;

final class DalleUtility
{
    protected $apiKey;

    public function __construct(

    )
    {
        $backendConfigurationManager = GeneralUtility::makeInstance(BackendConfigurationManager::class);
        $typoscript = $backendConfigurationManager->getTypoScriptSetup();
        $this->apiKey = $typoscript['plugin.']["sf_dalleimages."]["settings."]["dalleApiKey"];
    }

    /**
     * Generate dalle image with text prompt and return url 
     *
     * @param string $textPrompt
     * @param string $model
     * @param string $size
     * @param string $quality
     * @param integer $amount
     * @return string
     */
    public function fetchImageFromDalle($textPrompt, $model, $size, $quality, $amount): string
    {
        // Define the Dalle API endpoint
        $dalleEndpoint = 'https://api.openai.com/v1/images/generations';

        // Define the Dalle API headers (replace "YOUR_API_KEY" with your actual API key)
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '.$this->apiKey,
        ];

        // Define the request body with the text prompt
        $body = [
            'prompt' => $textPrompt,
            'model' => $model, // Specify the DALL-E model
            'n' => $amount, // Number of images to generate
            'size' => $size, // Size of the image
            'quality' => $quality // Quality of the image
        ];

        // Send request to Dalle API
        $client = new Client();
        $response = $client->post($dalleEndpoint, [
            'headers' => $headers,
            'json' => $body,
        ]);

        // Check if request was successful
        if ($response->getStatusCode() === 200) {
            $responseData = json_decode($response->getBody()->getContents(), true);
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
}
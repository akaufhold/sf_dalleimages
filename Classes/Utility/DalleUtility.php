<?php

declare(strict_types=1);

namespace Stackfactory\SfDalleimages\Utility;

use GuzzleHttp\Client;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\DebugUtility;


final class DalleUtility
{
    /**
     * Generate dalle image with text prompt and return url 
     *
     * @param string $textPrompt
     * @return string
     */
    public function fetchImageFromDalle($textPrompt): string
    {
        // Define the Dalle API endpoint
        $dalleEndpoint = 'https://api.openai.com/v1/images/generations';
		$apiKey = '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';

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
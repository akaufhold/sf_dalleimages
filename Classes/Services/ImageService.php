<?php

declare(strict_types=1);

namespace Stackfactory\SfDalleimages\Services;

use TYPO3\CMS\Core\Context\Context;

use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Resource\Storage\StorageRepository as ResourceStorageRepository;
use TYPO3\CMS\Core\Resource\Index\FileIndexRepository;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Resource\Storage;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileRepository;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;

use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

use Doctrine\DBAL\Exception;
use Stackfactory\SfDalleimages\Utility\DalleUtility;

use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

use GuzzleHttp\Client;

class ImageService
{
    private DataHandler $dataHandler;
    private ServerRequestInterface $request;
    private StorageRepository $storageRepository;
    private ResponseFactoryInterface $responseFactory;

    /**
     * @var ConfigurationManagerInterface
     */
    protected $configurationManager;

    public function __construct(
        DataHandler $dataHandler,
        StorageRepository $storageRepository,
        ResponseFactoryInterface $responseFactory
    )
    {
        $this->dataHandler = $dataHandler;
        $this->storageRepository = $storageRepository;
        $this->responseFactory = $responseFactory;
        $this->client = new Client();
    }

    /**
     * @param ConfigurationManagerInterface $configurationManager
     */
    public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager): void
    {
        $this->configurationManager = $configurationManager;
    }

    /**
     * Process temporary image and save to db
     *
     * @param ServerRequestInterface $request
     * @param string $imageUrl
     * @param integer $contentElementUid
     * @return ResponseInterface
     */
    public function processImage(ServerRequestInterface $request): ResponseInterface 
    {
        $this->request = $request;

        $fieldname = 'assets';
        $fileUid = $this->saveImageAsAsset($this->getDalleImageUrl($request));

        $contentID = $this->request->getQueryParams();

        var_dump($contentID);


        $fileReferenceUid = $this->addUserImageReference('tt_content', $fileUid, $contentElementUid, $fieldname);
        $this->enableTableField('tt_content', $fieldname, $contentUid, 1);

        $response = $this->responseFactory->createResponse()
            ->withHeader('Content-Type', 'application/json; charset=utf-8');
        $response->getBody()->write(
            json_encode(['result' => $fileReferenceUid], JSON_THROW_ON_ERROR),
        );
        return $response;
    }

    /**
     * Add new image to file system
     *
     * @param string $imageUrl
     * @param integer $contentElementUid
     * @return string
     */
    public function getDalleImageUrl(): string
    {
        $textPrompt = $this->request->getQueryParams()['input']
        ?? throw new \InvalidArgumentException(
            'Please provide a number',
            1580585107,
        );

        if ($textPrompt!='') {
            // Fetch image from Dalle
            $this->dalleUtility = GeneralUtility::makeInstance(DalleUtility::class);
            //$imageUrl = $this->dalleUtility->fetchImageFromDalle($textPrompt);
            $imageUrl = 'https://picsum.photos/200/300';
            return $imageUrl;
        }
    }

    /**
     * Save the image from the URL in sys_file and file system
     *
     * @param string $imageUrl
     * @return integer
     */
    public function saveImageAsAsset(string $imageUrl): int
    {
        // Define the local file path
        $tempFilePath = GeneralUtility::tempnam('dalle_image_') . '.jpg';
        $response = $this->client ->get($imageUrl, ['sink' => $tempFilePath]);

        // Check if the download was successful
        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException('Failed to download image');
        }

        // Get the default storage
        $storage = $this->storageRepository ->getDefaultStorage();
        // Define the target folder and file name
        $targetPath = '/user_upload';

        if ($storage->hasFolder($targetPath)) {
            $folder = $storage->getFolder($targetPath);
        } else {
            throw new \Exception ($targetPath . " path not found");
        }
    
        $fileName = basename($tempFilePath);

        /** @var File $file */
        $file = $storage->addFile($tempFilePath, $folder, $fileName);

        // Optionally, delete the temporary local file
        unlink($tempFilePath);
        return $file->getUid();
    }

    /**
     * Add user image to sys_file_reference
     *
     * @param string $table
     * @param Context $context
     * @param integer $uid
     * @param integer $curUserId
     * @throws Exception
     * @throws AspectNotFoundException
     * @return integer
     */
    public function addUserImageReference($table, $uid, $curUserId, $fieldname='image'): int
    {
        $context = GeneralUtility::makeInstance(Context::class);
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($table);
        try {
            $query = $connection
                ->insert('sys_file_reference',
                [
                    'uid_local' => (int)$uid,
                    'uid_foreign' => (int)$curUserId,
                    'tablenames' => $table,
                    'fieldname' => $fieldname,
                    'table_local' => 'sys_file',
                    'tstamp' => $context->getPropertyFromAspect('date', 'timestamp'),
                    'crdate' => $context->getPropertyFromAspect('date', 'timestamp'),
                    'title' => $firstName . ' ' . $lastName
                ],
                [
                    Connection::PARAM_INT,
                    Connection::PARAM_INT,
                    Connection::PARAM_STR,
                    Connection::PARAM_STR,
                    Connection::PARAM_STR,
                    Connection::PARAM_INT,
                    Connection::PARAM_INT,
                    Connection::PARAM_STR
                ]);
            return (int)$connection->lastInsertId();
        } catch (Exception $exception) {
            $connection->rollBack();
            throw $exception;
        }
    }

    /**
     * Enable table fields for using assets like  
     *
     * @param string $table
     * @param integer $curUserId
     * @param int imgEnabled
     * @throws Exception
     * @return void
     */
    public function enableTableField($table, $fieldname, $uid, $enabled): void
    {
        try {
            $query = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable($table)
            ->update(
                $table,
                [
                    $fieldname => $imgEnabled,
                ],
                [
                    'uid' => $uid
                ],
                [
                    Connection::PARAM_INT,
                ]
            );
            //$query = $query->getSQL();
        } catch (Exception $exception) {
            throw $exception;
        }
    }
}
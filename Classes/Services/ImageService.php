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
use TYPO3\CMS\Core\Utility\GeneralUtility;

use Doctrine\DBAL\Exception;
use Stackfactory\SfDalleimages\Utility\DalleUtility;

use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class ImageService
{
    protected DataHandler $dataHandler;
    protected ServerRequestInterface $request;
    protected StorageRepository $storageRepository;
    protected ResponseFactoryInterface $responseFactory;
    protected Client $client;

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
     * Return new image url
     *
     * @param string $imageUrl
     * @param string $model
     * @param string $size
     * @param string $quality
     * @param integer $amount
     * @param integer $contentElementUid
     * @return string
     */
    public function getDalleImageUrl($textPrompt, $model='dall-e-3', $size='1024x1024', $quality='Standard', $amount=1): string
    {
        $dalleUtility = GeneralUtility::makeInstance(DalleUtility::class);
        $imageUrl = $dalleUtility->fetchImageFromDalle($textPrompt, $model, $size, $quality, $amount);
        
        // TEST DATA
        //$imageUrl = 'https://picsum.photos/200/300';
        return $imageUrl;
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
        try {
            $response = $this->client->get($imageUrl, ['sink' => $tempFilePath]);

            // Check if the download was successful
            if ($response->getStatusCode() !== 200) {
                echo "Failed to download image. Status code: " . $response->getStatusCode();
            }
        } catch (RequestException $e) {
            echo "An error occurred: " . $e->getMessage();
            if ($e->hasResponse()) {
                echo "Response: " . $e->getResponse()->getBody();
            }
        }

        $storage = $this->storageRepository ->getDefaultStorage();
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
        //unlink($tempFilePath);
        return $file->getUid();
    }

    /**
     * Add user image to sys_file_reference
     *
     * @param string $table
     * @param integer $uid
     * @param integer $contentUid
     * @param string $fieldname
     * @throws Exception
     * @throws AspectNotFoundException
     * @return integer
     */
    public function addUserImageReference($table, $uid, $contentUid, $alternative, $prompt, $fieldname='image'): int
    {
        $context = GeneralUtility::makeInstance(Context::class);
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($table);
        // Begin transaction
        // $connection->beginTransaction();
        try {
            $query = $connection
                ->insert('sys_file_reference',
                [
                    'uid_local' => (int)$uid,
                    'uid_foreign' => (int)$contentUid,
                    'tablenames' => $table,
                    'fieldname' => $fieldname,
                    'table_local' => 'sys_file',
                    'tstamp' => $context->getPropertyFromAspect('date', 'timestamp'),
                    'crdate' => $context->getPropertyFromAspect('date', 'timestamp'),
                    'alternative' => $alternative,
                    'tx_dalleimage_prompt' => $prompt
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
     * Enable table fields for using assets  
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
                    $fieldname => $enabled,
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

    /**
     * Enable table fields for using assets  
     *
     * @param string $table
     * @param integer $contentElementUid
     * @param string sortingField
     * @throws Exception
     * @return array
     */
    public function getAssetsForContentElement($table, int $contentElementUid, $sortingField): ?array
    {
        $databaseConnection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('sys_file_reference');

        $queryBuilder = $databaseConnection->createQueryBuilder();
        $queryBuilder
            ->select('uid_local')
            ->from('sys_file_reference')
            ->where(
                $queryBuilder->expr()->eq('tablenames', $queryBuilder->createNamedParameter($table)),
                $queryBuilder->expr()->eq('uid_foreign', $queryBuilder->createNamedParameter($contentElementUid)),
                $queryBuilder->expr()->eq('deleted', '0'),
                $queryBuilder->expr()->isNotNull('uid_local')
            )
            ->orderBy($sortingField, 'DESC');

        $statement = $queryBuilder->execute();
        $assetUids = $statement->fetchAll();

        $assetUidsFlat = array_map( function($n) {
            return $n['uid_local'];
        }, $assetUids);

        return $assetUidsFlat ? $assetUidsFlat : null;
    }
}
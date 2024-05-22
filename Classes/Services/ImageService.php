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

class ImageService
{
    private ServerRequestInterface $request;
    private StorageRepository $storageRepository;
    private DataHandler $dataHandler;

    /**
     * @var ConfigurationManagerInterface
     */
    protected $configurationManager;

    public function __construct(
        DataHandler $dataHandler,
        StorageRepository $storageRepository
    )
    {
        $this->dataHandler = $dataHandler;
        $this->storageRepository = $storageRepository;
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
     * @return integer
     */
    public function processImage(ServerRequestInterface $request) {
        // Download the image
        $imageUrl = $this->getDalleImageUrl($request);
        // Define the path to save the downloaded image
        $tempPath = PATH_site . 'typo3temp/' . basename($imageUrl);
        // Save the image to TYPO3 storage
        $file = $this->saveImageToStorage($localFilePath);
        // Create a file reference for the content element
        $fileReferenceUid = $this->addUserImageReference($file, $contentElementUid);
        return $fileReferenceUid;
    }

    /**
     * Add new image to file system
     *
     * @param ServerRequestInterface $request
     * @param string $imageUrl
     * @param integer $contentElementUid
     * @return string
     */
    public function getDalleImageUrl(ServerRequestInterface $request): string
    {
        $this->request = $request;
        $textPrompt = $this->request->getQueryParams()['input']
        ?? throw new \InvalidArgumentException(
            'Please provide a number',
            1580585107,
        );

        if ($textPrompt!='') {
            // Fetch image from Dalle
            var_dump($textPrompt);
            $this->dalleUtility = GeneralUtility::makeInstance(DalleUtility::class);
            //$imageUrl = $this->dalleUtility->fetchImageFromDalle($textPrompt);
            $imageUrl = 'https://webpacktest.ddev.site/fileadmin/user_upload/apartment.jpg';
            var_dump($imageUrl);
            return $imageUrl;
        }
    }
    

    function downloadImage($url, $targetPath) {
        $client = new Client();
        $response = $client->get($url, ['sink' => $targetPath]);
    
        if ($response->getStatusCode() === 200) {
            return $targetPath;
        } else {
            throw new \Exception('Failed to download image');
        }
    }

    /**
     * Add new image to file system
     *
     * @param string $localFilePath
     * @param integer $storageUid
     * @param string $folderIdentifier
     * @return void
     */
    function saveImageToStorage($localFilePath, $storageUid = 1, $folderIdentifier = '/user_upload/') {
        $storageRepository = GeneralUtility::makeInstance(StorageRepository::class);
        $storage = $storageRepository->findByUid($storageUid);
        $folder = $storage->getFolder($folderIdentifier);
        $fileName = basename($localFilePath);
    
        // Move the file into TYPO3 storage
        $file = $storage->addFile($localFilePath, $folder, $fileName);
        return $file;
    }

    /**
     * Add user image to sys_file_reference
     *
     * @param string $table
     * @param Context $context
     * @param integer $uid
     * @param integer $curUserId
     * @param string $firstName
     * @param string $lastName
     * @throws Exception
     * @throws AspectNotFoundException
     * @return void
     */
    public function addUserImageReference($table, $context, $uid, $curUserId, $fieldname='image'): void
    {
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
        } catch (Exception $exception) {
            $connection->rollBack();
            throw $exception;
        }
    }
}
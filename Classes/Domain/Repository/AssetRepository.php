<?php
declare(strict_types=1);

namespace Stackfactory\SfDalleimages\Domain\Repository;

// use TYPO3\CMS\Extbase\Persistence\FileRepository;
use TYPO3\CMS\Core\Resource\FileRepository;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

class AssetRepository extends FileRepository
{
    /**
     * Finds assets from tt_content on a given list of uids
     *
     * @param array $uidList
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @return array
     */
    public function findByUidList(array $uidList): array
    {
      if (!is_array($uidList)) {
          throw new \InvalidArgumentException('The UID list has to be an array. UID list given: "' . $uidList . '"', 1316779798);
      }
      $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($this->table);
      if ($this->getEnvironmentMode() === 'FE') {
          $queryBuilder->setRestrictions(GeneralUtility::makeInstance(FrontendRestrictionContainer::class));
      }
      $results = $queryBuilder
          ->select('*')
          ->from($this->table)
          ->where(
              $queryBuilder->expr()->in('uid', $queryBuilder->createNamedParameter($uidList, Connection::PARAM_INT_ARRAY))
          )
          ->orderBy('creation_date','DESC')
          ->executeQuery()
          ->fetchAll();
      if (!is_array($results)) {
          throw new \RuntimeException('Could not find rows with one of the UIDs "' . $uid . '" in table "' . $this->table . '"', 1314354065);
      }
      return $results;
    }
}
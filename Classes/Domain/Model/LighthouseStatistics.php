<?php

declare(strict_types=1);

namespace Stackfactory\SfSeolighthouse\Domain\Model;

/**
 *
 * This file is part of the "SEO Lighthouse Score" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2021 Andreas Kauhold <info@stackfactory.de>, Stackfactory
 */

/**
 * LighthouseStatistics
 */

class LighthouseStatistics extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    /**
    * @var int
    */
    protected $crdate;
    
    /**
     * Returns the Creation Date
     * 
     * @return int $crdate
     */
    public function getCrdate()
    {
        return $this->crdate;
    }

    /**
     * Returns the target
     * 
     * @return int $target
     */
    public function getTarget()
    {
        return $this->target;
    }
}

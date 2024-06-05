<?php

declare(strict_types=1);

namespace Stackfactory\SfDalleimages\Utility;

use TYPO3\CMS\Core\Localization\LanguageService;

final class BackendLanguageUtility
{
		public function getLanguageService(): LanguageService
		{
			return $GLOBALS['LANG'];
		}
}
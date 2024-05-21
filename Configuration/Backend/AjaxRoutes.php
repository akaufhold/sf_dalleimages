<?php

use Stackfactory\SfDalleimages\Utility\DalleUtility;

return [
    'sf_dalleimages_getDalleImage' => [
        'path' => '/sf_dalleimages/getDalleImage/',
        'target' => DalleUtility::class . '::processDalleImage',
    ],
];
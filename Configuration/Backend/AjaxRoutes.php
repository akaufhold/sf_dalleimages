<?php

use Stackfactory\SfDalleimages\Services\ImageService;

return [
    'sf_dalleimages_getDalleImage' => [
        'path' => '/sf_dalleimages/getDalleImage/',
        'target' => ImageService::class . '::processImage',
    ],
];
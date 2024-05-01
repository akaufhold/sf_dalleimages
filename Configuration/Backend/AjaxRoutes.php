<?php

use Stackfactory\SfDalleimages\Controller\AjaxController;

return [
    'sf_dalleimages_getDalleImage' => [
        'path' => '/sf_dalleimages/Ajax/getDalleImage/',
        'target' => AjaxController::class . '::getDalleImageAction',
    ],
];
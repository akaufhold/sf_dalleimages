<?php

use Stackfactory\SfDalleimages\Controller\AjaxController;

return [
    'sf_dalleimages_getDalleImage' => [
        'path' => '/sf_dalleimages/getDalleImage/',
        'target' => AjaxController::class . '::processAjaxRequest',
    ],
];
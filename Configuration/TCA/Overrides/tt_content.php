<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3_MODE') or die();

$lll = 'LLL:EXT:sf_dalleimages/Resources/Private/Language/locallang_db.xlf:';

$dalleImage = [
    'tx_dalleimage_prompt' => [
        'label' => $lll . 'tt_content.dalleimage.prompt',
        'exclude' => 1,
        'config' => [
            'type' => 'input',
            'renderType' => 'sendToDalleButton',
            'size' => 30,
            'max' => 255,
            'eval' => 'trim',
        ],
    ],
];

ExtensionManagementUtility::addTCAcolumns(
    'tt_content',
    $dalleImage
);

// Add the field to the palette
ExtensionManagementUtility::addToAllTCAtypes(
	'tt_content',
	'tx_dalleimage_prompt',
	'textmedia,image',
	'after:assets'
);
<?php

return [
    // required import configurations of other extensions,
    // in case a module imports from another package
    'dependencies' => ['core'],
    'imports' => [
        // recursive definiton, all *.js files in this folder are import-mapped
        // trailing slash is required per importmap-specification
        '@vendor/sf_dalleimages/' => 'EXT:sf_dalleimages/Resources/Public/JavaScript/',
    ]
];
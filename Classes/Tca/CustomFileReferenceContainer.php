<?php

namespace Stackfactory\SfDalleimages\Tca;

use TYPO3\CMS\Backend\Form\Container\FileReferenceContainer;
use TYPO3\CMS\Core\Utility\DebugUtility;

class CustomFileReferenceContainer extends FileReferenceContainer
{
    public function getRenderFileReferenceHeaderControl(): string
    {   
        //DebugUtility::debug($this->data);
        return $this->renderFileReferenceHeaderControl();
    }
}

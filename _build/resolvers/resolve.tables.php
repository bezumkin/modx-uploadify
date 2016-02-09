<?php

if ($object->xpdo) {
    /** @var array $options */
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
            /** @var modX $modx */
            $modx =& $object->xpdo;
            $modelPath = $modx->getOption('uploadify.core_path', null,
                    $modx->getOption('core_path') . 'components/uploadify/') . 'model/';

            $modx->addPackage('uploadify', $modelPath);
            $manager = $modx->getManager();
            $manager->createObjectContainer('uFile');
            break;

        case xPDOTransport::ACTION_UPGRADE:
            break;
    }
}
return true;
<?php
/**
 * Resolve creating db tables
 *
 * @var modX $modx
  */
if ($object->xpdo) {
	switch ($options[xPDOTransport::PACKAGE_ACTION]) {
		case xPDOTransport::ACTION_INSTALL:
			$modx =& $object->xpdo;
			$modelPath = $modx->getOption('uploadify.core_path',null,$modx->getOption('core_path').'components/uploadify/').'model/';

			$modx->addPackage('uploadify',$modelPath);
			$manager = $modx->getManager();
			$manager->createObjectContainer('uFile');
		break;

		case xPDOTransport::ACTION_UPGRADE:
		break;
	}
}
return true;
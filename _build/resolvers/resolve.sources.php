<?php
/**
 * Resolve creating media sources
 *
 * @package minishop2
 * @subpackage build
 */
if ($object->xpdo) {
	switch ($options[xPDOTransport::PACKAGE_ACTION]) {
		case xPDOTransport::ACTION_INSTALL:
		case xPDOTransport::ACTION_UPGRADE:
			/* @var modX $modx */
			$modx =& $object->xpdo;

			$tmp = explode('/', MODX_ASSETS_URL);
			$assets = $tmp[count($tmp) - 2];

			$properties = array(
				'name' => 'Uploadify Images'
				,'description' => 'Default media source for images of Uploadify component'
				,'class_key' => 'sources.modFileMediaSource'
				,'properties' => array(
					'basePath' => array(
						'name' => 'basePath','desc' => 'prop_file.basePath_desc','type' => 'textfield','lexicon' => 'core:source'
						,'value' => $assets . '/uploadify/'
					)
					,'baseUrl' => array(
						'name' => 'baseUrl','desc' => 'prop_file.baseUrl_desc','type' => 'textfield','lexicon' => 'core:source'
						,'value' => $assets . '/uploadify/'
					)
					,'baseUrlRelative' => array(
						'name' => 'baseUrlRelative','desc' => 'prop_file.baseUrlRelative_desc','type' => 'combo-boolean','lexicon' => 'core:source'
						,'value' => false
					)
					,'imageExtensions' => array(
						'name' => 'imageExtensions','desc' => 'prop_file.imageExtensions_desc','type' => 'textfield','lexicon' => 'core:source'
						,'value' => 'jpg,jpeg,png'
					)
					,'thumbnailType' => array(
						'name' => 'thumbnailType','desc' => 'prop_file.thumbnailType_desc','type' => 'list','lexicon' => 'core:source'
						,'options' => array(array('value' => 'png','text' => 'png'), array('value' => 'jpg','text' => 'jpg'))
						,'value' => 'jpg'
					)
				)
				,'is_stream' => 1
			);
			if (!$source = $modx->getObject('sources.modMediaSource', array('name' => $properties['name']))) {
				$source = $modx->newObject('sources.modMediaSource', $properties);
				$source->save();
			}
			if ($setting = $modx->getObject('modSystemSetting', array('key' => 'uf_source_default'))) {
				$setting->set('value', $source->get('id'));
				$setting->save();
			}

			@mkdir(MODX_ASSETS_PATH . 'uploadify/');
		break;

		case xPDOTransport::ACTION_UNINSTALL: break;
	}
}
return true;
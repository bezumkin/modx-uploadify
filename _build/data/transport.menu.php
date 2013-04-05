<?php
/**
 * Adds modActions and modMenus into package
 *
 * */

$menu = array();

/*
$action= $modx->newObject('modAction');
$action->fromArray(array(
	'id' => 1,
	'namespace' => 'modextra',
	'parent' => 0,
	'controller' => 'index',
	'haslayout' => 1,
	'lang_topics' => 'modextra:default',
	'assets' => '',
),'',true,true);

$menu= $modx->newObject('modMenu');
$menu->fromArray(array(
	'text' => 'modextra',
	'parent' => 'components',
	'description' => 'modextra.menu_desc',
	'icon' => 'images/icons/plugin.gif',
	'menuindex' => 0,
	'params' => '',
	'handler' => '',
),'',true,true);
$menu->addOne($action);
unset($action);
*/

return $menu;
<?php
require dirname(dirname(dirname(dirname(__FILE__)))).'/config.core.php';
require MODX_CORE_PATH . '/config/config.inc.php';

define('MODX_API_MODE', true);
require MODX_BASE_PATH . '/index.php';

$modx->getService('error','error.modError');
$modx->setLogLevel(modX::LOG_LEVEL_ERROR);
$modx->setLogTarget('FILE');
$modx->error->message = null;

if (empty($_REQUEST['action']) || (empty($_SESSION['uid']) && $modx->user->id == 0)) {
	exit('Access denied');
}
else {
	$action = !empty($_FILES['Filedata']) ? 'form/upload' : $_REQUEST['action'];
}

if (!empty($_REQUEST['pageId']) && $resource = $modx->getObject('modResource', (int) $_REQUEST['pageId'])) {
	if ($resource->get('context_key') != 'web') {
		$modx->switchContext($resource->context_key);
	}
}

/* @var Uploadify $Uploadify */
$Uploadify = $modx->getService('uploadify','Uploadify',$modx->getOption('uploadify.core_path',null,$modx->getOption('core_path').'components/uploadify/').'model/uploadify/', array());

switch ($action) {
	case 'form/upload':
		$response = $Uploadify->uploadFile($_FILES['Filedata']);
		$maxIterations= (integer) $modx->getOption('parser_max_iterations', null, 10);
		$modx->getParser()->processElementTags('', $response, false, false, '[[', ']]', array(), $maxIterations);
		$modx->getParser()->processElementTags('', $response, true, true, '[[', ']]', array(), $maxIterations);
	break;
	case 'form/option':
		$response = $Uploadify->setOption($_POST['key'], $_POST['value']);
	break;
	default: $response = json_encode(array('success' => false, 'message' => 'no action'));
}

exit($response);



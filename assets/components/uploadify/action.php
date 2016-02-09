<?php

if (empty($_REQUEST['action'])) {
    exit('Access denied');
}
$action = !empty($_FILES['Filedata'])
    ? 'form/upload'
    : $_REQUEST['action'];

define('MODX_API_MODE', true);
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/index.php';

$modx->getService('error', 'error.modError');
$modx->setLogLevel(modX::LOG_LEVEL_ERROR);
$modx->setLogTarget('FILE');
$modx->error->message = null;

if (!empty($_REQUEST['pageId']) && $resource = $modx->getObject('modResource', (int)$_REQUEST['pageId'])) {
    if ($resource->get('context_key') != 'web') {
        $modx->switchContext($resource->context_key);
    }
}

/** @var Uploadify $Uploadify */
$Uploadify = $modx->getService('uploadify', 'Uploadify', $modx->getOption('uploadify.core_path', null,
        $modx->getOption('core_path') . 'components/uploadify/') . 'model/uploadify/', array());

switch ($action) {
    case 'form/upload':
        $response = $Uploadify->uploadFile($_FILES['Filedata']);
        $maxIterations = (int)$modx->getOption('parser_max_iterations', null, 10);
        $modx->getParser()->processElementTags('', $response, false, false, '[[', ']]', array(), $maxIterations);
        $modx->getParser()->processElementTags('', $response, true, true, '[[', ']]', array(), $maxIterations);
        break;
    case 'form/option':
        $response = $Uploadify->setOption($_POST['key'], $_POST['value']);
        break;
    default:
        $response = $modx->toJSON(array(
            'success' => false,
            'message' => 'no action',
        ));
}

exit($response);
<?php
require_once dirname(dirname(dirname(dirname(__FILE__)))).'/config.core.php';

define('MODX_API_MODE', true);
require dirname(MODX_CORE_PATH).'/index.php';

$modx->getService('error','error.modError');
$modx->setLogLevel(modX::LOG_LEVEL_ERROR);
$modx->setLogTarget('FILE');
$modx->error->message = null;

if (!isset($_POST['action']) || $_POST['action'] != 'uploadFile' || (empty($_SESSION['uid']) && $modx->user->id == 0) || empty($_FILES['Filedata'])) {
	exit('Access denied');
}

$Uploadify = $modx->getService('uploadify','Uploadify',$modx->getOption('uploadify.core_path',null,$modx->getOption('core_path').'components/uploadify/').'model/uploadify/', array());

$output = $Uploadify->uploadFile($_FILES['Filedata']);

$maxIterations= (integer) $modx->getOption('parser_max_iterations', null, 10);
$modx->getParser()->processElementTags('', $output, false, false, '[[', ']]', array(), $maxIterations);
$modx->getParser()->processElementTags('', $output, true, true, '[[', ']]', array(), $maxIterations);

exit($output);



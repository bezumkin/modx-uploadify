<?php
/*
 * Add chunks to build
 *
 * */

$chunks = array();

$tmp = array(
	'tpl.Uploadify.form' => 'uploadify_form'
	,'tpl.Uploadify.formfive' => 'uploadifive_form'
	,'tpl.Uploadify.auth' => 'uploadify_auth'
	,'tpl.Uploadify.image' => 'uploadify_image'
	,'tpl.Uploadify.file' => 'uploadify_file'
	,'tpl.Uploadify.option' => 'uploadify_option'
);

foreach ($tmp as $k => $v) {
	/* @var modChunk $chunk */
	$chunk = $modx->newObject('modChunk');
	$chunk->fromArray(array(
		'id' => 0
		,'name' => $k
		,'description' => ''
		,'snippet' => file_get_contents($sources['source_core'].'/elements/chunks/chunk.'.$v.'.tpl')
		//,'static' => 1
		//,'static_file' => 'uploadify/elements/chunks/chunk.'.$v.'.tpl'
	),'',true,true);

	$chunks[$k] = $chunk;
}

return $chunks;
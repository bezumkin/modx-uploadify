<?php
/**
 * Add snippets to build
 *
 * */

$snippets = array();

$tmp = array(
	'Uploadify' => 'uploadify'
);

foreach ($tmp as $k => $v) {
	/* @var modSnippet $snippet*/
	$snippet = $modx->newObject('modSnippet');
	$snippet->fromArray(array(
		'id' => 0
		,'name' => $k
		,'description' => ''
		,'snippet' => getSnippetContent($sources['source_core'].'/elements/snippets/snippet.'.$v.'.php')
		,'static' => 1
		,'static_file' => 'uploadify/elements/snippets/snippet.'.$v.'.php'
	),'',true,true);

	$properties = require dirname(dirname(__FILE__)) . '/properties/properties.'.$v.'.php';
	$snippet->setProperties($properties);

	$snippets[$k] = $snippet;
}

return $snippets;
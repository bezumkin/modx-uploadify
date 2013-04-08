<?php
if (empty($scriptProperties['tplForm'])) {
	$scriptProperties['tplForm'] = !empty($uploadiFive) ? 'tpl.Uploadify.formfive' : 'tpl.Uploadify.form';
}
if (empty($scriptProperties['host'])) {
	$scriptProperties['host'] = $modx->getOption('http_host');
}
if (empty($scriptProperties['source'])) {
	$scriptProperties['source'] = $modx->getOption('uf_source_default', null, 1, true);
}

/* @var Uploadify $Uploadify */
$Uploadify = $modx->getService('uploadify','Uploadify',$modx->getOption('uploadify.core_path',null,$modx->getOption('core_path').'components/uploadify/').'model/uploadify/',$scriptProperties);
return $Uploadify->getForm();
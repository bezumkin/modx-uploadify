<?php
$Uploadify = $modx->getService('uploadify','Uploadify',$modx->getOption('uploadify.core_path',null,$modx->getOption('core_path').'components/uploadify/').'model/uploadify/',$scriptProperties);

$Uploadify->config = array_merge($Uploadify->config, $scriptProperties);

return $Uploadify->getForm();
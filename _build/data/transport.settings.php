<?php
/**
 * Loads system settings into build
 *
 * */

$settings = array();

$tmp = array(
    'uf_frontend_css' => array(
        'value' => '[[+cssUrl]]web/default.css',
        'xtype' => 'textfield',
    ),
    'uf_frontend_js' => array(
        'value' => '[[+jsUrl]]web/default.js',
        'xtype' => 'textfield',
    ),
    'uf_source_default' => array(
        'value' => 1,
        'xtype' => 'modx-combo-source',
    ),
);

foreach ($tmp as $k => $v) {
    /** @var modSystemSetting $setting */
    $setting = $modx->newObject('modSystemSetting');
    $setting->fromArray(array_merge(
        array(
            'key' => $k,
            'namespace' => 'uploadify',
            'area' => 'uf_frontend',
        ), $v
    ), '', true, true);
    $settings[] = $setting;
}

return $settings;
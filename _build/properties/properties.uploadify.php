<?php

$properties = array();

$tmp = array(
	'tplForm' => array(
		'type' => 'textfield'
		,'value' => ''
	)
	,'tplAuth' => array(
		'type' => 'textfield'
		,'value' => 'tpl.Uploadify.auth'
	)
	,'tplImage' => array(
		'type' => 'textfield'
		,'value' => 'tpl.Uploadify.image'
	)
	,'tplFile' => array(
		'type' => 'textfield'
		,'value' => 'tpl.Uploadify.file'
	)
	,'tplOption' => array(
		'type' => 'textfield'
		,'value' => 'tpl.Uploadify.option'
	)
	,'uploadiFive' => array(
		'type' => 'combo-boolean'
		,'value' => true
	)
	,'fileExtensions' => array(
		'type' => 'textfield'
		,'value' => 'jpg,jpeg,png'
	)
	,'maxFilesize' => array(
		'type' => 'numberfiel'
		,'value' => 1048576
	)
	,'imageExtensions' => array(
		'type' => 'textfield'
		,'value' => 'jpg,jpeg,png'
	)
	,'imageMaxWidth' => array(
		'type' => 'numberfield'
		,'value' => 1920

	)
	,'imageMaxHeight' => array(
		'type' => 'numberfield'
		,'value' => 1200
	)
/*
	,'thumbMinWidth' => array(
		'type' => 'numberfield'
		,'value' => 640
	)
	,'thumbMinHeight' => array(
		'type' => 'numberfield'
		,'value' => 480
	)
*/
	,'thumbWidth' => array(
		'type' => 'numberfield'
		,'value' => 320
	)
	,'thumbHeight' => array(
		'type' => 'numberfield'
		,'value' => 240
	)
	,'thumbZC' =>  array(
		'type' => 'list'
		,'options' => array(
			array('text' => 'None', 'value' => '0')
			,array('text' => 'Center', 'value' => 'C')
			,array('text' => 'Top', 'value' => 'T')
			,array('text' => 'Bottom', 'value' => 'B')
			,array('text' => 'Right', 'value' => 'R')
			,array('text' => 'Left', 'value' => 'L')
			,array('text' => 'Top-Right', 'value' => 'TR')
			,array('text' => 'Top-Left', 'value' => 'TL')
			,array('text' => 'Bottom-Right', 'value' => 'BR')
			,array('text' => 'Bottom-Left', 'value' => 'BL')
		)
		,'value' => 'C'
	)
	,'thumbBG' => array(
		'type' => 'textfield'
		,'value' => 'ffffff'
	)
	,'thumbQuality' => array(
		'type' => 'numberfield'
		,'value' => 90
	)
	,'thumbFormat' =>  array(
		'type' => 'list'
		,'options' => array(
			array('text' => 'Jpg', 'value' => 'jpg')
			,array('text' => 'Png', 'value' => 'png')
		)
		,'value' => 'jpg'
	)
	,'source' => array(
		'type' => 'numberfield'
		,'value' => 0
	)
	,'imageQuality' => array(
		'type' => 'numberfield'
		,'value' => 99
	)
	,'listThumbSize' => array(
		'type' => 'textfield'
		,'value' => '320x240,640x480'
	)
	,'listThumbZC' => array(
		'type' => 'textfield'
		,'value' => '0,C'
	)
	,'listThumbBG' => array(
		'type' => 'textfield'
		,'value' => 'ffffff,000000'
	)
);


foreach ($tmp as $k => $v) {
	$properties[] = array_merge(
		array(
			'name' => $k
			,'desc' => 'uf_prop_'.$k
			,'lexicon' => 'modextra:properties'
		), $v
	);
}

return $properties;
<?php

include_once ROOT_FOLDER."inc/entity.class.php";

function test_back_inc_entity_class_fctlst(){
	$testFileList[]	='test_back_inc_entity_class_create';
	$testFileList[]	='test_back_inc_entity_class_get';
	return $testFileList;
}

function test_back_inc_entity_class_create(&$args){
	$ey = new Entity;
	$minorCols = array('Firstname'=>'my test Firstname', 'Address1'=>'Here!');
	$val = $ey->create('1', 'I am Entity', $minorCols);
	testNotEmpty($args, $val);
}

function test_back_inc_entity_class_get(&$args){
	$ey = new Entity;
	$res = $ey->get($ey->last());
	$val = $res[0]['Name'];
	testNotEmpty($args,$val);
}


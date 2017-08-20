<?php
include_once ROOT_FOLDER."inc/form.class.php";

function test_back_inc_form_class_fctlst(){
	$testFileList[]	='test_back_inc_form_class_addField';
	return $testFileList;
}

function test_back_inc_form_class_addField(&$args){
	$f = new Form;
	$f->addField('MyField',	'My field test',	'text');
	$f->addField('MyPass',	'My field password','password');
	$f->addField('MyPass',	'My field password','range',		'',				'min="0" max="10"' );
	$f->addField('MySubmit','',					'submit',		'My submit');
	$val = $f->get();
	testNotEmpty($args, $val);
}
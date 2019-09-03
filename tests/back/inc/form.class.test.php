<?php
session_start();
include_once ROOT_FOLDER."inc/form.class.php";

function test_back_inc_form_class_fctlst(){
	$testFileList[]	='test_back_inc_form_class_addField';
	return $testFileList;
}

function test_back_inc_form_class_addField(&$args){
	$f = new Form('testForm');
	echo INCLUDE_JQUERY;
	echo $f->getCss();
	$f->posInline(TRUE);
	$f->addField('MyField',		'My field test',	'text',		TRUE);
	$f->addFieldAjax('MyFieldX','Ajax Country');
	$f->addField('MyField2',	'My field test',	'text',		TRUE);
	$f->addField('MyPass',		'My field password','password'	);
	$f->addField('MyPass2',		'My field range ',	'range',	'',				'min="0" max="10"' );
	$f->posInline(FALSE, '');
	$f->addField('MySubmit',	'',					'submit',	TRUE, 'Submit');
	$val = $f->get();
	testNotEmpty($args, $val);
}
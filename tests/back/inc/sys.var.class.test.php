<?php
include_once ROOT_FOLDER."inc/sys.var.class.php";

function test_back_inc_sys_var_class_fctlst(){
	$testFileList[]	='test_back_inc_sys_var_class_getGroups';
	return $testFileList;
}

function test_back_inc_sys_var_class_getGroups(&$args){
	$sv = new SYS_Var();
	$rows = $sv->getGroups();
	testNotEmpty($args, $rows, FALSE);
}
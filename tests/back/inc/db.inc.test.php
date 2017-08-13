<?php

function test_back_inc_db_inc_fctlst(){
	$testFileList[]	=	'testlive_db_query';
	$testFileList[]	=	'test_back_inc_db_inc';
	return $testFileList;
}

function testlive_db_query(&$args){
	$connect = new PDO(DB_PDO_CONNECT, ''.DB_USER, DB_PASSWORD);
	$stmt = $connect->prepare('SELECT max(ID_Entity) as max_id FROM GBM_Entity');
	$execr= $stmt->execute($pdoArray);
	$res = $stmt->fetchAll();
	testNotEmpty($args, $res[0][0]);
}


function test_back_inc_db_inc(&$args){
	$sql = "SELECT * FROM GBM_SYS_Var WHERE ID_Var=?";
	$res = db($sql, array('1'));
	$val = (int)$res[0]['ID_Var'];
	testEqual($args, $val, 1);
}


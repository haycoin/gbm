<?php
$NO_CSS = TRUE;
define('DEBUG_MODE', TRUE);
$_SESSION["ID_User"] = 'Guest';
require_once 'inc.php';
require_once ROOT_FOLDER.'inc/entity.class.php';

/*
		$sql = "SELECT * FROM GBM_SYS_Var WHERE ID_Var=?";
		$res = db($sql, array('1'));
		

pr($res);
*/

$ey = new Entity;
$res = $ey->getEntity(1);



pr($res);


echo 'end test';
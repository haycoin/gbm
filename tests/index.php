<?php
$NO_CSS = TRUE;
define('DEBUG_MODE', TRUE);
$_SESSION["ID_User"] = 'Guest';
include_once '../back/inc/inc.php';
require_once ROOT_FOLDER.'inc/test.inc.php';

$args = array();
testSetFilePath($args, 'back/inc/db.inc');
testSetFilePath($args, 'back/inc/entity.class');

pr(testResult($args));


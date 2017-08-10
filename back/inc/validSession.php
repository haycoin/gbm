<?php
/**
 * Check if user if user is allowed to use ressource
 * 
 * If session is not started, add this code at top of the page
 * sessions_start();
 * $_SESSION["ID_User"]=='Guest';
 *  
 * @author Alec Avedisyan
 * @copyright 2017 Sparkou.com
 * @package GBM
 * @version 0.9.1
 * @access private
 */


if(eregi(".*/back/.*",$_SERVER['REQUEST_URI'])){
// If Joomla actived but login trough GBM interface
	session_start();
}elseif(defined("ROOT_JOOMLA_PATH")){
	if (ROOT_JOOMLA_PATH <> ""){
		include_once("joomla.inc.php");
		init_joomla_session();
	}else{
		session_start();
	}
}else{
	session_start();
}



// Variable de Session
// Variable de Session
if(!isset($_SESSION["ID_User"]) || $_SESSION["ID_User"]=="") {

	if($_COOKIE["startup_url"]<>""){

		header("Location: ".$_COOKIE["startup_url"]."");
	}elseif(defined("LOGIN_MSG")){
		if (LOGIN_MSG==""){
			echo "<BR><BR>Please Login !";
		}else{
			echo LOGIN_MSG;
		}
	}else{
		header("Location: ".ROOT_URI_PORT."");
	}
	exit();
}


if (!(isset($_SESSION["FO_VALID_USER"]))&& $_SESSION["ID_User"] <> "Guest"){

	//include_once("/home/svn/public_html/gbm/back_FO/inc/inc.php");
	//include_once("/home/svn/public_html/gbm_ob/back_FO/inc/inc.php");
	if (file_exists("../../../gbm.conf.php")){
		require_once("../../../gbm.conf.php");	
	}elseif(file_exists("../../gbm.conf.php")){
		require_once("../../gbm.conf.php");	
	}elseif(file_exists("../../../../gbm.conf.php")){
		require_once("../../../../gbm.conf.php");	
	}	
	
	include_once(ROOT_FOLDER."inc/db.inc.php");
	$_SESSION["FO_VALID_USER"] = check_row_exist_count('"ID_User"', '"SecureLevel"=0 AND "ID_User"='.$_SESSION["ID_User"], '"FO_SYS_User"');
	if (defined(DEBUG_MODE)){
		if (DEBUG_MODE){
			echo "Current ID_User ".$_SESSION["ID_User"]." Right permision into ".$_SESSION["FO_VALID_USER"]. " modules<BR>";
		}
	}
}

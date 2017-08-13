<?php
/*@FIXIT*/


// COLORS Settings
define("FORM_COLOR_ALTER_BG_0", "FFFFFF");
define("FORM_COLOR_ALTER_BG_1", "111144");
define("FORM_COLOR_ALTER_FN_0", "000000");
define("FORM_COLOR_ALTER_FN_1", "FFFFFF");

define("FORM_COLOR_SELECTED", "FF9900");
define("FORM_COLOR_SELECTED_FONT", "FFFFFF");
define("FORM_COLOR_UNSELECTED", "EEEEEE");
define("FORM_COLOR_UNSELECTED_FONT", "AAAAAA");

mb_eregi('^(.*)(/back_(FO|FO_UI)/)(.*)$',$_SERVER['PHP_SELF'],$arr); /*@FIXIT*/
define("ROOT_FOLDER_UI", "http://".$_SERVER['HTTP_HOST']."".$arr[1]."/back_FO_UI/");

/*	define("CSS_FORM_headerTitle_color", "001544");
	define("CSS_FORM_majorTable_background_color", "EEEEEE");
	define("CSS_AJAX_LIST_BACK", CSS_FORM_headerTitle_color);*/
	
if ($_POST["CSS_PROFIL_NAME"]<>""){
	$_SESSION["CSS_PROFIL_NAME"] = $_GET["CSS_PROFIL_NAME"];
}

switch ($_SESSION["CSS_PROFIL_NAME"]) {
case "Red":
	define("CSS_FORM_headerTitle_color", "FF0000");
	define("CSS_FORM_majorTable_background_color", "EEEEEE");
	define("CSS_AJAX_LIST_BACK", CSS_FORM_headerTitle_color);
	break;
case "Black":
	define("CSS_FORM_headerTitle_color", "001544");
	define("CSS_FORM_majorTable_background_color", "EEEEEE");
	define("CSS_AJAX_LIST_BACK", CSS_FORM_headerTitle_color);
	break;
case "Grey":
	define("CSS_FORM_headerTitle_color", "DDDDDD");
	define("CSS_FORM_majorTable_background_color", "EEEEEE");
	define("CSS_AJAX_LIST_BACK", CSS_FORM_headerTitle_color);
	break;	
case "Default":
default:
	define("CSS_FORM_headerTitle_color", "001544");
	define("CSS_FORM_majorTable_background_color", "EEEEEE");
	define("CSS_AJAX_LIST_BACK", CSS_FORM_headerTitle_color);	
}


<?php
/**
 * Main include file, should stand into every file
 *
 * @author Alec Avedisyan
 * @copyright 2017 Sparkou.com
 * @package GBM
 * @version 0.9.1
 */

/* This file MUST be strongly refactored ! */

// Search the gbm.conf.php file
if (file_exists("../../../gbm.conf.php")){
	require_once("../../../gbm.conf.php");	
}elseif(file_exists("../../gbm.conf.php")){
	require_once("../../gbm.conf.php");	
}elseif(file_exists("../../../../gbm.conf.php")){
	require_once("../../../../gbm.conf.php");	
}elseif(file_exists("../../../../../gbm.conf.php")){
	require_once("../../../../../gbm.conf.php");	
}

define("block_vertical", ROOT_URI."inc/block_list_vertical.inc.php?ID_Block=");
define("block_horizontal", ROOT_URI."inc/block_list_horizontal.inc.php?ID_Block=");	
define("block_file_browse", ROOT_URI."inc/block_list_file_browse.inc.php?ID_Block=");	


/*
// Pour les langues en gnral voir plus bas (Maximum ces 3 langues (DB))
define("LANGUAGE", "E"); // E = English, F = French, D = German // LANGUE POUR LES ELEMENT SYSTEM ONLY 
define("LANGUAGE_N", "0"); // 0 = English, 1 = French, 2 = German  // LANGUE POUR LES ELEMENT SYSTEM ONLY (relation avec les liste deroulante)
*/

function getLangCol($L='900'){
	if		($L == '901'){ $r = "F";}
	elseif	($L == '902'){ $r = "D";}	
	else	{$r = "E";}
	return $r;
}

/** 
 * DB Related constant
 * 
 * @param string SQL_TABLENAME		Table name
 * @param string SQL_WHERE			Where condition, not used in Insert case
 * @package GBM
 * @subpackage DB
 */
define("TYPESQL_ADDS",		"_TypeSQL");
define("TYPESQL_NO_ADDS",	"_Copy");
define("SQL_TABLENAME",		"SQL_TABLENAME");		// Table name
define("SQL_WHERE",			"SQL_WHERE");			//
define("SQL_ID",			"SQL_ID");				//
define("SQL_ID_START_VAL",	"SQL_ID_START_VAL");	//
define("SQL_IGNORE_EMPTY",	"SQL_IGNORE_EMPTY");	//	Ignore if the field is empty
define("SQL_UNIQUE", 		"SQL_UNIQUE");  		//

/* Is that still necessary ? 
define("CONS_WEEKDAY","Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday;Lundi,Mardi,Mercredi,Jeudi,Vendredi,Samedi,Dimanche");
define("CONS_WEEKDAY_SHORT","MON,TUE,WED,THU,FRI,SAT,SUN;LUN,MAR,MER,JEU,VEN,SAM,DIM");
 */


// Variables comes from FO_SYS_Var
define("SYS_SECUREKEY",100000);

define("JS_TAG_START","<script language=\"javascript\" >\n");
define("JS_TAG_END","</script>\n");

define("F_REMAKE",60*60*24);
define(CROP_MARK,"<div @@@CROP@@@></div>");
define("BOX_MESSAGE_USER", "BOX_MESSAGE_USER");
define("JS_CONFIRM","return confirm('Etes-vous sûr ?');");
define("ERR_DB_CONN", "Please correct the datas ! (query not accepeted)");

// Guarentee that the page will be reloaded
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

// Valid the session
include_once (ROOT_FOLDER."inc/std_function.inc.php");

// Valid the session
include_once (ROOT_FOLDER."inc/validSession.php");

// include pour la verfication de la session et des autorisations
include_once (ROOT_FOLDER."inc/db.inc.php");

// Each day the file db_var.inc.php will be regenerated 
include_once(ROOT_FOLDER."inc/db_var.inc");
// CSS Standard
if ($NO_CSS == TRUE){}else{
	echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">'."\n";
	echo "\n<style type=\"text/css\">\n<!--\n";
	include_once ROOT_FOLDER."css/std.inc.css";
	echo "\n-->\n</style>\n";
	echo "<style type=\"text/css\">\n<!--\n";
	include_once ROOT_FOLDER."css/list_std.inc.css";
	echo "\n-->\n</style>\n";
}
include_once(ROOT_FOLDER."css/dynamic.inc.css.php");

/**
 * Manipulate Variables comming form several sources. 
 *
 * @package GBM
 */
class getVar{
	var $VarArrayVal;
	var $VarArrayType;
	
	function getPosted(){
		unset ($this->VarArrayVal);
		unset ($this->VarArrayType);
		
		reset ($_POST);
		// Spare les variables des types de variables
		while (list($key, $val) = each($_POST)) {
			if (mb_ereg(TYPESQL_ADDS.'$',$key)==FALSE){
				if (mb_eregi('^(@S[I|U]?@)([0-9I]*)$',$val,$sessVal)){
					$this->VarArrayVal[$key] = $_SESSION[decryptString(decryptAscii($sessVal[2]))];
				}else{
					$this->VarArrayVal[$key] = $val;				
				}
			}else{
				$this->VarArrayType[$key] = $val;
			}
		}
	}
		
	function makeGetString(){
	$temp = "";
		while (list($key, $val) = each($_POST)) {
			$temp .= "&".$key."=".$val;
		}
		while (list($key, $val) = each($_GET)) {
			$temp .= "&".$key."=".$val;
		}
		return $temp;
	}
	
	function extractVar($externalKey){
	$temp = "";
		while (list($key, $val) = each($_POST)) {
			if (trim($key) == trim($externalKey)){
				$temp .= "&".$key."=".$val;
			}
		}
		while (list($key, $val) = each($_GET)) {
			if (trim($key) == trim($externalKey)){
				$temp .= "&".$key."=".$val;
			}
		}
		return $temp;
	}
	
	function echoVar(){
		echo "Values : <BR><BR>\n";
		print_r($this->VarArrayVal);		
		echo "Type : <BR><BR>\n";
		print_r($this->VarArrayType);
	}

}


function no_valid_field($field_name){
		write_error("- ".DICO_ERROR_FIELD." :".$field_name);
}

function no_valid_field2($field_name,$show){
	if ($show){
		write_error("- ".DICO_ERROR_FIELD." :".$field_name);
		}
}



////////////////////////////
// Make an array with constant value
////////////////////////////
function make_cons_array($const_name){
// Only for array having maximum 2D (dim1 = ';'  dim2 = ',')
	//$const_name = CONS_WEEKDAY;	
	$sub_array = explode(";",$const_name);
	//if (is_array($sub_array[$i])){
		for ($i = 0; $i < count($sub_array); $i++){
				$final_array[$i] = explode(",",$sub_array[$i]);
		}
	//}
return $final_array;
}

////////////////////////////
// Make an array with constant value
////////////////////////////
function make_select_field($datas, $values, $name, $style_css,$selected_val, $jscript, $output = TRUE){
		$o = "<select name='".$name."' style='".$style_css."' ".$jscript.">";
			for ($i = 0; $i < sizeof($values); $i++){
				$o .=  "<option value='".$values[$i]."'";
					if ($values[$i] == $selected_val){$o .= " selected ";}
				$o .= ">".$datas[$i]."</option>";
			}
		$o .= "</select>";
		
		if($output){
			echo $o;
		}else{
			return $o;
		}
		
}

////////////////////////////
// Timeout to redirect or reload a page
////////////////////////////
function redirect($page,$timeMil, $keepSession = FALSE, $targetId = "", $functionName="redirect"){

if ($timeMil == ""){$timeMil = "1000";}
	$script .= JS_TAG_START;
	$script .= "<!--"."\n";
	if ($targetId == ""){
		$script .= "	function ".$functionName."()"."\n";
		if ($page <> ""){
			if ($keepSession){
				$script .= "	{window.location='".$page."?".PHPSESSID."=".$sid."'\n";
				}else{
				$script .= "	{window.location='".$page."'"."\n";
				}
		}else{
			$script .= "	{location.reload()"."\n";
		}
		$script .= "}"."\n";
		$script .= "setTimeout('redirect()',".$timeMil."); "."\n";
	}else{
		$script .= "function ".$functionName."(){"."\n";
		$script .= "	var currentTime = new Date();"."\n";		
		$script .= "	window.parent.document.getElementById('".$targetId."').src = window.parent.document.getElementById('".$targetId."').src+\"?\"+currentTime.getTime();"."\n";
		$script .= "}"."\n";
		$script .= "setTimeout('".$functionName."()',".$timeMil."); "."\n";
	}
	$script .= "-->"."\n";
	$script .= JS_TAG_END;
return $script;
}

////////////////////////////
// Convert word in unicode to be readable  ...
////////////////////////////
function cTXT($str){
	return htmlentities($str,ENT_QUOTES,"UTF-8");
}




////////////////////////////
// Loading Box
////////////////////////////

function LoadingScript($message = "loading ... ", $additionalScript = ""){

//<div id="loading" style="	filter:alpha(opacity=100); 	opacity: 0.5; 	-moz-opacity:0.8; position:fixed; width:120%; height:120%; z-index:100; background:#FFFFFF">

?>
<div id="loading">
  <table width="100%" height="100%" border="0" cellpadding="0" cellspacing="10">
    <tr align="center" valign="middle" bgcolor="#FFFFFF" >
      <td align="right" width="50%"><img src="<?php echo ROOT_URI ?>inc/img/loading.gif"></td>
      <td align="left"><div><font face="Verdana, Arial, Helvetica, sans-serif"><?php echo $message?></font></div></td>
    </tr>
  </table>
</div>
<?php
LoadingPopUp("none", $additionalScript);
//ob_flush();
//flush();
}


function LoadingPopUp($value, $additionalScript=""){
	echo '<script type="text/javascript">
	window.onload=function(){'
		.LoadingPopUpShow($value)."\n".
		$additionalScript."\n".
	'}
	</script>';
}

function LoadingPopUpShow($value){
	return 'if (document.getElementById("loading")!=null){
		document.getElementById("loading").style.display="'.$value.'";
	}';
	//return 'document.getElementById("loading").style.display="'.$value.'";';
}

function updateFormField($formName, $fieldName, $value){
	echo '<script type="text/javascript">
		document.'.$formName.'.'.$fieldName.'.value="'.$value.'";
	</script>';
}


////////////////////////////
// Info pop-up
////////////////////////////
function infoPopUpInit(){
	//echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">'."\n";
	//echo '<html>'."\n".'<head>'."\n".'<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">'."\n";
	echo '<style type="text/css">'."\n";
	include_once (ROOT_FOLDER."inc/PopUp/popup_header.css");
	echo "\n";
	echo '</style>'."\n";
	//echo "</head>"."\n"."<body>"."\n";
	echo '<div id="dhtmltooltip"></div>'."\n";
	echo '<script type="text/javascript">'."\n";
	include_once (ROOT_FOLDER."inc/PopUp/popup.js");
	echo '</script>'."\n";
}

function infoPopUp($content, $xSize = 300, $ySize = 300, $color='FAFAFF', $styleVisible='', $visibleData = ''){
	// Always use the INIT part into <Head>
	if ( $visibleData == '' ){ $visibleData = '<img src="'.ROOT_URI.'inc/img/info.gif">'; }	
	
	$output .= '<span onMouseover="ddrivetip(\''.addslashes($content).'\', \'#'.$color.'\',\''.$xSize.'\')"; onMouseout="hideddrivetip()" style='.$styleVisible.'>';
	$output .= $visibleData;
	$output .= '</span>';
	return $output;
}

function noPermission(){
	echo "<b>You don't have permissions... </b><br>";
	echo "This access will be reported to the administrator.";
	exit();
}

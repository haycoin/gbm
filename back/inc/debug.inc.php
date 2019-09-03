<?php
/**
 * Debuging mode
 * 
 * Warning do not use this function on production server ! Set SERVER_TEST to FALSE into gbm.conf.php
 * 
 * For testing, just add to your url (case sensitive) : DEBUGMODE=ON
 * 
 * @author Alec Avedisyan
 * @copyright 2017 Sparkou.com
 * @package GBM
 * @version 2.2.1
 * @access private
 */



if(SERVER_TEST){
	// Virtual debug mode
	$showDebugMenu=FALSE;
	if(!defined(DEBUG_MODE)){
		// DEBUGMENU
		if ($_POST["DEBUGMENU"]=="OFF"){
			$_SESSION["DEBUGMODE"]="OFF";
			$_SESSION["DEBUGMENU"]=FALSE;		
			define("DEBUG_MODE", FALSE);
		}elseif($_SESSION["DEBUGMODE"]=="ON" ||  $_GET["DEBUGMODE"]=="ON" || $_SESSION["DEBUGMENU"]){
			if ($_SESSION["ID_Entity"] == DEBUG_MODE_USER){
				define("DEBUG_MODE",TRUE);
			}elseif	($_REQUEST["DEBUGMODE"]=="OFF"){
				$_SESSION["DEBUGMODE"]="OFF";
				define("DEBUG_MODE",FALSE);
			}elseif	($_REQUEST["DEBUGMODE"]=="ON" || $_SESSION["DEBUGMODE"]=="ON"){
				$_SESSION["DEBUGMODE"]="ON";
				define("DEBUG_MODE",TRUE);
			}else{
				$_SESSION["DEBUGMODE"]="OFF";
				define("DEBUG_MODE",FALSE);		
			}
			$_SESSION["DEBUGMENU"]=TRUE;
		}else{
			$_SESSION["DEBUGMENU"]=FALSE;		
			define("DEBUG_MODE", FALSE);
		}
	}

	// 
	if ($_SESSION["DEBUGMENU"]==TRUE){ $_SESSION["DEBUG_TIME_START"] = array_sum(explode(' ', microtime()));}


	/**
	 * Show execution time until this point 
	 * 
	 * @param string $text
	 * @return string
	 */
	function stopDebugTime($text="Execution time : "){	
		if ($_SESSION["DEBUGMENU"]==TRUE){
			$output = '<div id="apDiv2" class="Style1" align="center">'.$text."<B>";
			$output .= array_sum(explode(' ', microtime())) - $_SESSION["DEBUG_TIME_START"];
			$output .= '</B></div>';
			echo $output;
		}
	}

	if ($_SESSION["DEBUGMENU"]){
	?>
	<style type="text/css">
	<!--
	#apDiv1 {
		position:fixed;
		right:0px;
		top:60px;
		width:320px;
		height:25px;
		z-index:100;
		background-color: #996699;
		margin: 0px;
		padding: 0px;
		filter:alpha(opacity=50);
		opacity: 0.5;
		-moz-opacity:0.9;
		 border: 2px solid black;
	}

	#apDiv2 {
		position:fixed;
		right:0px;
		bottom:0px;
		width:320px;
		height:15px;
		z-index:1;
		background-color: #996699;
		margin: 0px;
		padding: 0px;
		filter:alpha(opacity=50);
		opacity: 0.5;
		-moz-opacity:0.9;
		 border: 2px solid black;
	}

	.Style1 {
		font-size: 9px;
		font-family: Verdana, Arial, Helvetica, sans-serif;
		color: #FFFFFF;
	}
	-->
	</style>
	<form name="form1" method="post" action="">
	<div id="apDiv1" class="Style1" align="center">
	<strong>DEBUG Mode</strong> 
	ON<input name="DEBUGMODE" type="radio" value="ON"  <?php if($_SESSION["DEBUGMODE"]=="ON"){echo "checked";} ?> onChange="submit();">
	OFF<input name="DEBUGMODE" type="radio" value="OFF" <?php if($_SESSION["DEBUGMODE"]<>"ON"){echo "checked";} ?> onChange="submit();">
	| <strong>DEBUG Menu </strong>OFF
	<input name="DEBUGMENU" type="checkbox" value="" onChange="this.value='OFF';submit();">
	</div>
	</form>
	<?php
	}
	
}
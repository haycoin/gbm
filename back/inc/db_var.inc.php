<?php
/**
 * To update manualy the variable file : http://test.av-d.net/sp2/gbm/back_FO/inc/db_var.inc.php?action=manualUpdate
 *
 * @author Alec Avedisyan
 * @copyright 2017 Sparkou.com
 * @package GBM
 * @version 1.5.0
 * @subpackage Conceptual
 * @access private
 */

/*@FIXIT*/

create_file("E", $excluded);

function create_file($language = "E", $where= ""){
	$f_filename = "inc/db_var".$language.".inc";

  if ($_GET["action"] == "manualUpdate"){
  	include_once ("db.inc.php");
  	include_once ("std_function.inc.php");
  	$f_filename = "db_var.inc";
  	echo "Manual Update ongoing ...<HR>";
  	flush();
  	ob_flush();
  }else{
  	include_once ("inc/db.inc.php");
  	$f_filename = "inc/db_var.inc";
  }
  //#################################
  // PHP FILE REGENERATION
  //#################################
  // READ FILE
  if (!(isset($_SESSION["F_TIMES"]))){
  	if (!$handle = fopen($f_filename, 'r')) {
  	if (DEBUG_MODE){echo "error on opening var file";}}
  	$f_split0=split('"F_TIMESTAMP",', fread($handle,150)); // Crop left part of the file
  	$f_split1=split(')', $f_split0[1]); // Crop right part of the file
  	fclose($handle);
  	$_SESSION["F_TIMES"] = $f_split1[0];
  // END OF READ FILE
  if (($_SESSION["F_TIMES"]+F_REMAKE) < time() ){
  	$fcontent = "<?php \n";
  	$fcontent .= "//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%\n";		
  	$fcontent .= "// AUTOMATICLY GENERATED FILE\n";		
  	$fcontent .= "//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%\n";			
  	$fcontent .= "\n";	
  	$fcontent .= "define(\"F_TIMESTAMP\",".time().") ;\n";
  	$fcontent .= "\n";	
  	$fcontent .= "// FO_SYS_Var\n";	
  	
  	$dbSYS_Var = new dbSelectMultiple ; 
  	$dbSYS_Var -> execSql("*", "\"FO_SYS_Var\"", $where, "", 'f');
  	while($row_var = $dbSYS_Var->getNext()){
	
  	$fcontent .= "define(\"F_SYS_Var_".$row_var["ID_Var"]."\",\"".trim($row_var["Name_".$language])."\") ;\n";
  		if ($row_var["Value"]<>""){
			$langVal = trim($row_var["Value"]);
			$langVal = str_replace('"','',$langVal);
		
  			$fcontent .= "define(\"F_SYS_Var_".$row_var["ID_Var"]."_Val\",\"".$langVal."\") ;\n";
  		}
  	}
  	
  	$fcontent .= "\n";	
  	$fcontent .= "\n";				
  	$fcontent .= "?>";
  
  	// WRITE FILE
  	// Attention the file permsion must be 777!
  	if (!$handle = fopen($f_filename, 'w')) {
  			if (DEBUG_MODE){echo "error on opening var file";}}
  	if (fwrite($handle, $fcontent) === FALSE) {
  			if (DEBUG_MODE){echo "error on wrinting var file";}}
  	fclose($handle);
  	// END OF WRITE FILE
  	
  	if ($_GET["action"] == "manualUpdate"){
  		echo "<HR>Manual Update <B>Done</B>";
  	}
  	
}}
}

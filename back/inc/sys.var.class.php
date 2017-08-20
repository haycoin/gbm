<?php
/**
 * Class for all relations 
 *
 * Include this file into your code <code>include_once(ROOT_FOLDER."inc/relation.class.php");</code>
 *
 * @author Alec Avedisyan
 * @copyright 2017 Sparkou.com
 * @package GBM
 * @version 3.0.0
 * @subpackage Includes
 */

 class SYS_Var{
	 function get(){
		 
	}
	
	function getByGroup($Grp = ''){
		
	}
	
	function getGroups(){
		$sql  = 'SELECT Grp, count(Grp),min(ID_Var),max(ID_Var), min(Name) as example FROM GBM_SYS_Var GROUP BY Grp ORDER BY Grp';
		return db($sql);
	}
	 
	 
 }
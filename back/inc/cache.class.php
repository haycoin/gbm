<?php
/**
 * Class for Cached data's 
 *
 * Include this file into your code <code>include_once(ROOT_FOLDER."inc/cache.class.php");</code>
 *
 * @author Alec Avedisyan
 * @copyright 2017 Sparkou.com
 * @package GBM
 * @version 3.0.0
 * @subpackage Includes
 */


class Cache{

	function add($ID_Type, $ID_Status, $ID_Action, $ID_Related='', $Message='', $MsgFrom='', $MsgTo='', $ModifiedBy=''){
		
		if(''==$ModifiedBy){
			$ModifiedBy = $_SESSION['ID_User'];
		}
		
		$cols = array(	'ID_Type' => $ID_Type, 	
						'ID_Status' => $ID_Status,
						'ID_Action' => $ID_Action,
						'ID_Related' => $ID_Related,
						'Message' => $Message,
						'MsgFrom' => $MsgFrom,
						'MsgTo' => $MsgTo,
						'ModifiedBy' => $ModifiedBy);
			
		return dbInsert($cols, 'GBM_SYS_Cache');		
	}
	
	function getLast($ID_Type, $ID_Status, $ID_Action, $ID_Related){
		$sql = 'SELECT * FROM GBM_SYS_Cache WHERE ID_Type=? AND ID_Status=? AND ID_Action=? AND ID_Related=? ORDER BY Time DESC LIMIT 1';
		return db($sql, array($ID_Type, $ID_Status, $ID_Action, $ID_Related));
	}
	
	
	
}
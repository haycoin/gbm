<?php
/**
 * Connection and function to connect Postgres/MySql databases
 * 
 * Include this file into your code <code>include_once(ROOT_FOLDER."inc/db.inc.php");</code>
 *
 * @author Alec Avedisyan
 * @copyright 2017 Sparkou.com
 * @package GBM
 * @version 3.1.0
 * @subpackage Includes
 */

/*
 * Most of functions come from GBM 2.*.* and must be revieved and tested
 */

if (!defined("PG_ENCODING")){ 	define("PG_ENCODING",SYS_ENCODING_DEFAULT) ;}


/**
 * @internal
 * Debugin functions
 * The reason the debug functions are here is that db is called into validSession too.
 */
if (defined("ROOT_FOLDER")){
	include_once (ROOT_FOLDER."inc/debug.inc.php");
	}else{
	include_once ("debug.inc.php");
	}
if (DEBUG_MODE && defined("DEBUG_MODE")){
	// Error debuging
	echo '<div style="text-align:center">--- Debug mode ---</div>';
	ini_set("display_errors", "on");
}else{
	ini_set("display_errors", "off");
}

/*
// Database connection Function
if (file_exists("../../../gbm.conf.php")){
	require_once("../../../gbm.conf.php");	
}elseif(file_exists("../../gbm.conf.php")){
	require_once("../../gbm.conf.php");	
}elseif(file_exists("../../../../gbm.conf.php")){
	require_once("../../../../gbm.conf.php");	
}*/

if(DEBUG_MODE){
	//$params = array(PDO::ATTR_EMULATE_PREPARES => 1, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_PERSISTENT => true); 
}

// ******************************  En cas de Transaction .... *********************************** dans les autre cas ca foire la DB !!!!!!!!!!!!!!!!
	//$params = array(    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_EMULATE_PREPARES => false); 


$dbCon = new PDO(DB_PDO_CONNECT, DB_USER, DB_PASSWORD, $params);

/**
 * 
 * @global PDO $dbCon
 * @param type $sql
 * @param type $pdoArray
 * @param type $sqlType		i = insert, u = update, d = delete
 * @param type $exec		0 (FALSE) : simulate, 1 (TRUE) : execute, 2 : Transaction (return exception if error)
 * @param type $forceMultipleRow
 * @return boolean
 */
function db($sql, $pdoArray=NULL, $sqlType='', $exec=1, $forceMultipleRow=FALSE){
	global $dbCon;
	if(''==$dbCon){
		echo msg('$dbCon are not set ! ', 'e'); exit();
	}
	// String or array allowed
	if(!is_array($pdoArray)){
		$arr[0] = $pdoArray;
		$pdoArray = $arr;
	}

	try {
		$stmt = $dbCon->prepare($sql);
		$execr= $stmt->execute($pdoArray);
		if(DEBUG_MODE){ 	echo msg($sql.' '.json_encode($pdoArray), $sqlType );}
		// In case of Insert
		if('i'==$sqlType){
			return $dbCon->lastInsertId();	
		}elseif('u'==$sqlType){	
			return TRUE;
		}elseif('d'==$sqlType){
			return $stmt->rowCount();
		}
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
	} catch(PDOException $e) {
		$code = $e->getCode();
		//pr($ex->getTrace());
		$err = DICO_ERR_PDO;
		switch ($code){
			case 1045:		$err = DICO_ERR_DB_CONNECT;			break;
			case 23000:
			case 42000:
			case '42S02':
			case '42S22':
			case 'HY093':
				$tra = $e->getTrace();
				unset($tra[0]);
				foreach($tra as $val){	$T = $val['line'].":\t".$val['file']."\n";	}
				$err = $e->getMessage().'<pre>'.$sql."<hr>".$T.'</pre>';
			break;
			case 'HY000':	// General Error
				if(0==sizeof($pdoArray)){	
					$err = DICO_ERR_PDO_ARRAY_EMPTY;
				}elseif($execr){
					$err = DICO_WORD_SQL_EXECUTED.', '.DICO_ERR_FETCH;
					$stmt->debugDumpParams();
				}	
			break;
		}
		echo msg(DICO_ERR_CODE.' '.$code.' | '.$err, 'e'); //user friendly message
		if($exec==2){
			return $e;
		}else{
			return null;
		}		
	}
	
	// Duplicate info in case of unique tuple. 
	if($forceMultipleRow){
		return $rows;
	}elseif(1==sizeof($rows)){
		return $rows[0];
		/*
		foreach($rows[0] as $ku=>$ru){
			$rows[$ku] = $ru;
		}*/
	}else{
		return $rows;
	}
	
}

/**
 * 
 * @global PDO $dbCon
 * @param type $stat : 	begin / commit / rollback / sql
 */
function dbTransact($stat='begin'){
	global $dbCon;
	
	if(DEBUG_MODE){ 	echo msg('Transaction: '.$stat,'d' );}

	if($stat=='begin'){
		$dbCon->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$dbCon->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
		$dbCon->beginTransaction();
	}elseif($stat=='commit'){
		$dbCon->commit();
	}elseif($stat=='rollback'){
		$dbCon->rollBack();
	}else{
		$dbCon->commit();
	}
	
}


class DbTransact{
	var $stat;
	var $dbCon;
	
	public function __construct() {
		global $dbCon;
		$this->dbCon = $dbCon;
	}
	
	public function begin(){
		$this->dbCon->beginTransaction();
	}
	
	public function db($sql, $pdoArray=NULL, $sqlType=''){
		$res = db($sql, $pdoArray, $sqlType, 2);
	} 
	
	
}


/**
 * Force multiple row
 */
function dbm($sql, $pdoArray=NULL, $sqlType='', $exec=1){
	return db($sql, $pdoArray, $sqlType, $exec, TRUE);
}

function dbInsert($allCols, $table, $exec=1){
	foreach($allCols as $col=>$val){
		$colname.= $col.', ';
		$colval.=  ':'.$col.', ';
	}
	$sql = 'INSERT INTO '.$table.'('.rtrim($colname, ', ').') VALUES ('.rtrim($colval, ', ').');';
	if($exec!=0){
		return db($sql, $allCols, 'i', $exec);
	}else{
		return $sql;
	}
}

function dbUpdate($allCols, $table, $where, $exec=1){
	foreach($allCols as $col=>$val){
		$colname.= $col.' = :'.$col.', ';
	}
	$sql = 'UPDATE '.$table.' SET '.rtrim($colname, ', ').' WHERE '.$where.';';
	if($exec!=0){
		return db($sql, $allCols, 'u', $exec);
	}else{
		return $sql;
	}
}

function dbDelete($allCols, $table, $checkUser=FALSE, $checkStatus=FALSE, $exec=1){
	if(!($checkUser===FALSE)){		$allCols['CreatedBy'] = $checkUser; }
	if(!($checkStatus===FALSE)){	$allCols['ID_Status'] = $checkStatus; }
	
	foreach($allCols as $col=>$val){ 	$colname.= ' AND '.$col.' = :'.$col;	}
	
	$sql = 'DELETE FROM '.$table.' WHERE '.ltrim($colname, ' AND ').';';
	if($exec!=0){
		return db($sql, $allCols, 'd', $exec);
	}else{
		return $sql;
	}
	
	
}

function dbPrimaryKey($table){
	return db("SHOW KEYS FROM $table WHERE Key_name = 'PRIMARY'");
}
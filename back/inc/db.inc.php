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

// Database connection Function
if (file_exists("../../../gbm.conf.php")){
	require_once("../../../gbm.conf.php");	
}elseif(file_exists("../../gbm.conf.php")){
	require_once("../../gbm.conf.php");	
}elseif(file_exists("../../../../gbm.conf.php")){
	require_once("../../../../gbm.conf.php");	
}


/**
 * 
 * @global type $id
 * @param type $sql
 * @param type $pdoArray
 * @param type $sqlType		i for insert
 * @return type
 */
function db($sql, $pdoArray=NULL, $sqlType=''){
	global $id;
	if(DEBUG_MODE){
		$params = array(PDO::ATTR_EMULATE_PREPARES => 1, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION); 
	}
	
	// String or array allowed
	if(!is_array($pdoArray)){
		$arr[0] = $pdoArray;
		$pdoArray = $arr;
	}
	
	try {
		$connect = new PDO(DB_PDO_CONNECT, DB_USER, DB_PASSWORD, $params);
		$stmt = $connect->prepare($sql);
		$execr= $stmt->execute($pdoArray);
		// In case of Insert
		if('i'==$sqlType){	return $connect->lastInsertId();}
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);	
	} catch(PDOException $ex) {
		$code = $ex->getCode();
		//pr($ex->getTrace());
		$e = DICO_ERR_PDO;
		switch ($code){
			case 1045:		$e = DICO_ERR_DB_CONNECT;			break;
			case 23000:
			case 42000:
			case '42S02':
			case 'HY093':
				$tra = $ex->getTrace();
				unset($tra[0]);
				foreach($tra as $val){	$T = $val['line'].":\t".$val['file']."\n";	}
				$e = $ex->getMessage().'<pre>'.$sql."<hr>".$T.'</pre>';
			break;
			case 'HY000':	// General Error
				if(0==sizeof($pdoArray)){	
					$e = DICO_ERR_PDO_ARRAY_EMPTY;
				}elseif($execr){
					$e = DICO_WORD_SQL_EXECUTED.', '.DICO_ERR_FETCH;
					$stmt->debugDumpParams();
				}	
			break;
		}
		echo msg(DICO_ERR_CODE.' '.$code.' | '.$e, 'e'); //user friendly message
		return null;
	}
	
	return $rows;
}


function dbInsert($allCols, $table){
	foreach($allCols as $col=>$val){
		$colname.= $col.', ';
		$colval.=  ':'.$col.', ';
	}
	$sql = 'INSERT INTO '.$table.'('.rtrim($colname, ', ').') VALUES ('.rtrim($colval, ', ').');';
	return db($sql, $allCols, 'i');
}

function defConn(){
	$conn = pg_connect(PG_CONNECT);
	if (PG_ENCODING<>"NO_ENCODING"){
		pg_set_client_encoding($conn, PG_ENCODING);
		if(DEBUG_MODE){
			echo "Encoding : ".pg_client_encoding()." Set encoding ".PG_ENCODING." Session Lang : ".$_SESSION["Lang"]." Session encoding ".$_SESSION["encode"];
		 }
	}else{
		if(DEBUG_MODE){
			echo "No Encoding conversion for PG ".pg_client_encoding();
		 }
	}
	return $conn;
}



/**
 * Buliding Block's
 *
 * This class get all the information to build a new block
 * Block's data's stand into FO_SYS_Block
 *
 * @link http://www.resanow.com/dev/back_DOC/tutorial/FO_SYS_Block.php This tutorial explain how fill each fields.
 * @package GBM
 * @subpackage Conceptual
 */
class block{

	var $length;

	/**
	 * @param	integer		$ID_Block
	 *  @param	string		$Template	Specified template
	 */
	 
 
	function getBlock($ID_Block, $Template = ""){
		
		if ($Template == ""){

			$AllowRead = new dbAllowRead();
			if (($AllowRead->allow($ID_Block))=='t'){
				$query = "SELECT * FROM \"FO_SYS_Block\" WHERE \"ID_Block\"=".$ID_Block;
				
			
				$result = pg_exec(defConn(),$query);
				$row = pg_fetch_array($result);
	
			if (DEBUG_MODE){
				echo $ID_Block;
				echo $query;
				print_r($row);
			}
				
			// Replace all GET var into SESSION var	
			$allvars = split("&",$_SERVER['QUERY_STRING']);
			foreach ($allvars as $onevar){
				$particle = split("=",$onevar);
				if ($particle[0]<>"ID_User" && $particle[0]<>"ID_FO"  && $particle[0]<>"ID_Block" ){
					$_SESSION[$particle[0]] = intval($particle[1]);
				}
				//echo $particle[0]." ".$_SESSION[$particle[0]]."<BR>";
			}			
				
				$row["Template"] = str_replace("\n","",$row["Template"]);
				$Template = split(";",$row["Template"]);
				$row["Template"] = $Template[0];
				
				// FIXIT Regarder pour optimiser
				if  ($row["Template"] == "block_horizontal"){
					include_once ROOT_FOLDER."inc/".$row["Template"].".inc.php";
					$dBlock = new drawBlockH();
					$blockCreated = TRUE;
				}elseif ($row["Template"] == "block_vertical"){
	
					include_once ROOT_FOLDER."inc/".$row["Template"].".inc.php";
					$dBlock = new drawBlock();
					$blockCreated = TRUE;				
				}elseif ($row["Template"] == "block_file_browse"){
	
					include_once ROOT_FOLDER."inc/".$row["Template"].".inc.php";
					$dBlock = new drawBlockFB();
					$blockCreated = TRUE;	
				}elseif ($row["Template"] == "block_empty"){
					include_once ROOT_FOLDER."inc/".$row["Template"].".inc.php";
					$dBlock = new drawBlockE();
					$blockCreated = FALSE;	
					$dBlock -> makeBlock($row["ColLinkAddress"],$row["ID_Block"],$row["Title"],$row["Width"],$row["Lenght"]);							
				}elseif($row["Template"] == "block_h_sparkou"){
					include_once ROOT_FOLDER."inc/".$row["Template"].".inc.php";
					$dBlock = new drawBlockH();
					$blockCreated = TRUE;				
				}elseif($row["Template"] == "block_v_sparkou"){
					include_once ROOT_FOLDER."inc/".$row["Template"].".inc.php";
					$dBlock = new drawBlock();
					$blockCreated = TRUE;
				}else{
					$blockCreated = FALSE;
				}
				
				if 	($blockCreated){
					
					if($this->length!=''){	$row['Lenght'] = $this->length;	}
					
					$dBlock -> makeBlock($row);
					pg_freeresult($result);
				}
			}
			//return $this->row;
			
			
			}else{
			// If template 
				include ROOT_FOLDER."inc/block_list_".$Template.".inc.php";
			}
		}
		
	function setLength($length=''){		$this->length = $length;	}		
		
}


/**
 * This class Select one line
 *
 * This class Select only one row into the database
 * Can be also be used to execute a query without geting any response 
 * such as INSERT, DELETE, etc...
 * @package GBM
 * @subpackage DB
 */
class dbSelectUnique {
	var $col=0; // Number of column
	var $row; 
	var $result;
	var $status_msg = '';
	var $oid; 	// !!! Using OID is dangerous because oid are not support in next postgres version ... 
				// Better use RETURNING oin stead of OID INSERT INTO person (name) VALUES ('Blaise Pascal') RETURNING id;
	var $status;
	
	/**
	 * Execute a sql query
	 * @param	string	$query This must be a regular PostgreSQL query 
 	 * @param	string	$ReturningCol only available for an INSERT if value is '*' then it return all the cols otherwise specify the column name like a normal SQL SELECT 
	 * @return	mixed	Return a row, if available datas are existing
	 */	
	function execSql($query, $ReturningCol = NULL){
		if (trim($query)==""){
			if (DEBUG_MODE){
				write_error("<BR>Query Empty !"); 
				return FALSE;
			}	
		}
		$query = removeSlashQuote($query);
		$query = $this->addReturning($query, $ReturningCol);
		$query = str_replace('"*"', '*', $query);

		if (DEBUG_MODE){write_error("<BR>Unique query: ".$query);}
		if (!($result = pg_exec(defConn(),$query))){
			if (DEBUG_MODE){
				echo write_debug(ERR_DB_CONN."\n".$query);
			}else{
				write_error(ERR_DB_CONN);
				//mail("aa@av-d.ch","DEBUG", $query."<HR>".pr($_GET,FALSE));
				return -1;
			}
		}else{
			if (defined(DICO_CONFIRMATION_UPDATED)){
				$this->status_msg = DICO_CONFIRMATION_UPDATED;
			}else{
				$this->status_msg = "The datas has been saved properly";
			}

			$this->status_msg = DICO_CONFIRMATION_UPDATED;
		}
		$this->col = pg_numfields($result);
		$this->row = pg_fetch_array($result);
		$this->oid = pg_last_oid($result);
		$this->status = pg_result_status($result);
		pg_freeresult($result);
		return $this->row;
	}
	
	/**
	 * Execute a non formated query
	 *
	 * Example of use:
	 * <code>
	 * $dbU = new dbSelectUnique;
	 * $dbU->execSqlLong("*", '"FO_Doc"', '"ID_Doc"='.$ID_Doc.' AND "ID_Entity"='.$ID_Entity, 'f');
	 * </code>
	 *
	 * @param	string	$Field Fields(s) name(s) (regular PostgreSQL syntax)
	 * @param	string	$Table Table(s) name(s) (regular PostgreSQL syntax)	
	 * @param	string	$Where 
	 * @param	string	$Debug	$Debug value must be 't' for TRUE or 'f' for FALSE
	 * @return	mixed	Return a row, if available datas are existing
	 */		
	function execSqlLong($Field, $Table, $Where, $Debug){
		$query = "SELECT ".$Field." FROM ".$Table;
		if ($Where<>""){ $query .= " WHERE ".$Where;}
		if ($Debug=='t'){echo $query;}
		$query = removeSlashQuote($query);
		if (DEBUG_MODE){write_error("<BR>Unique Long query: ".$query);}
		$this->result = pg_exec(defConn(),$query);
		$this->col = pg_numfields($this->result);
		$this->row = pg_fetch_array($this->result);
		return $this->row;
	}
	
	// Adding new row
	function formatEmptyRow(){
		// Sépare les variables des types de variables
		if (empty($this->row)){
			write_error("Please Add at least one ROW into the the database");
			exit;
		}
		while (list($key, $val) = each($this->row)) {
				$this->row[$key] = "";			
		}
		return $this->row;
	}
	
	
	function getResults(){
		return $this->result;
	}

	function getCol (){
		return $this->col;
	}
	
	function free(){
		pg_freeresult($this->result);
	}
	
	function addReturning($SQL, $Column = NULL){
		if (!empty($Column)){
			$SQL = trim($SQL);
			if(eregi("^INSERT .*$",$SQL)){
				$SQL = $SQL." RETURNING ".$Column;
			}
		}
		return $SQL;
	}
	
}


/**
 * This class Select multiple line row into the database
 * Use only for "SELECT" sql command
 * @package GBM
 * @subpackage DB
 */
class dbSelectMultiple {
	var $col=0; // Number of column
	var $line=0;
	var $row;
	var $array_result;
	var $result;
	
	/**
	 * Execute a non formated query
	 *
	 * Example of use:
	 * <code>
	 * $dbM = new dbSelectMultiple ;
	 * $dbM -> execSql("*", "\"FO_SYS_Cache\"","\"Status\"=".$Status ." AND \"From\"=".$from , " \"Time\" ASC ", 'f');
	 * </code>
	 *
	 * @param	string	$Field Fields(s) name(s) (regular PostgreSQL syntax)
	 * @param	string	$Table Table(s) name(s) (regular PostgreSQL syntax)	
	 * @param	string	$Where 
 	 * @param	string	$Order 
	 * @param	string	$Debug	$Debug value must be 't' for TRUE or 'f' for FALSE
	 * @return	mixed	Return a row, if available datas are existing
	 */		
	
	function execSql($Field, $Table, $Where, $Order = "", $Debug = 'f'){
		$query = "SELECT ".$Field." FROM ".$Table;
		if ($Where<>""){ $query .= " WHERE ".$Where;}
		if ($Order<>""){ $query .= " ORDER BY ".$Order;} 
		$query = removeSlashQuote($query);
		if ($Debug=='t'){echo $query;}
		if (DEBUG_MODE){write_error("<BR>Multiple query: ".$query);}
		if (!($this->result = pg_query(defConn(),$query))){
			if (DEBUG_MODE){
				echo write_debug(ERR_DB_CONN."\n".$query);
			}else{
				write_error(ERR_DB_CONN);
				//mail("aa@av-d.ch","DEBUG", $query."<HR>".pr($_GET,FALSE));				
			}		
		};
		$this->col = pg_numfields($this->result);
		$this->line = pg_numrows($this->result);
	}
	
	/**
	* @return	array	 The results into an array
	*/
	function getNext(){
		$this->row = pg_fetch_array($this->result);
		return $this->row;
	}

	/**
	* free the data's, use it only if a verly large amount of resquest returning data's is used.
	*/
	function free(){
		pg_freeresult($this->result);
	}

	/**
	* @return	integer	  	number of column
	*/
	function getCol (){
		return $this->col;
	}
	
	/**
	* @return	integer		total number of lines
	*/
	function getLine (){
		return $this->line;
	}
	
	/**
	* Make an array with all the data's comming from result
	* And reset the pointer position.
	*
	* Note: Work only with single key.
	* @param	$ID		string	Used ID for the array index. 
	* @return	array			Return the results
	*/	
	function makeArray($ID, $ID_ONLY = FALSE){
		while($val= $this->getNext()){
			if ($ID_ONLY){
				$this->array_result[$val[$ID]] = "";
			}else{
				$this->array_result[$val[$ID]] = $val;
			}
		}
		pg_result_seek($this->result, 0);
		return $this->array_result;
	}
	
}

/**
 * Check Security Allow Add
 * 
 * Check into FO_SYS_Right if the current Sessioned user have the right 
 * to add into a specific block
 *
 * @package GBM
 * @subpackage DB
 */
class dbAllowAdd {
	function allow ($ID_Block, $ID_Block_Next, $Table, $Template ,$W){
		$result = pg_exec(defConn(),"SELECT \"Write\",\"Delete\" FROM \"FO_SYS_Right\" WHERE \"ID_User\"=".$_SESSION["ID_User"]." AND \"ID_Block\"=".$ID_Block);
		$row = pg_fetch_array($result);
		pg_freeresult($result);
		if ($row["Write"]=='t'){
		
		// Fill the formulary part for a add Temporly here, must must be somwhere else.
		if ($Template == "block_v_sparkou"){
			$TemplateFile = "block_list_horizontal";
		}
		if ($Template == "block_vertical"){
			$TemplateFile = "block_list_horizontal";
		}
		
			print("<form name='".$ID_Block."' method='post' action='".ROOT_URI."inc/".$TemplateFile.".inc.php?ID_Block=".$ID_Block_Next."' >");
			//print("<input type='submit' name='Submit' value='quick Add'>"); // ToDo but not a priority
			print("<input type='submit' name='Submit' value='".DICO_WORD_ADD."'>");
			print("<input type='hidden' name='SQL_TABLENAME' value=".$Table.">");
			print("<input type='hidden' name='AllowAdd' value='TRUE'>");
			if ($W <>""){ // FIXIT SECU Dangerous
				$param = split("=", $W);
				print("<input type='hidden' name=".$param[0]." value=".$param[1].">");
			}
			if ($_SESSION["ID_Entity"]<>""){
				print("<input type='hidden' name='ID_Entity' value=".$_SESSION["ID_Entity"].">");
			}else{
				// For security reson this must check if this user is allowed to see this box
			}
			//print("<input type='hidden' name='W' value='".$_REQUEST["W"]."'>");

			print("</form>");
		}
		return $row["Write"];
	}
}

///////////////////////////////
// Check if there is at least one row.
///////////////////////////////
function check_row_exist_count($count_field, $Where,$Table){
	$dbU = new dbSelectUnique;
	$row = $dbU->execSqlLong("count(".$count_field.")", $Table, $Where, 'f');
	return $row[0];
}

/**
 * Usefull for the query building
 * 
 * Var Type :
 * - int4, numeric, bool 
 * - varchar
 * - text
 * - money				:	Output format 999.99
 * - timestamptz		:   Input format std PG format
 * - date				:	Input format YYYY-MM-DD
 * - date_DD.MM.YY		:	Input format DD.MM.YY
 * - autocount			:	Volutary ignore this field 
 * 
 * 
 * @param mixed $val
 * @param mixed $ShortKey
 * @param mixed $varValues
 * @return mixed
 */
function varAnalysis($val,$ShortKey,$varValues){

				if (is_array($val)){
					$lenght = $val[1];
					$val = $val[0];
				}else{
					$lenght = 1000000;
				}
				
				switch ($val) {
					case 'int4':
					case 'numeric':
					case 'bool':
						if ($varValues[$ShortKey]==""){
							$varValues[$ShortKey] = "NULL";
							// FIXIT but needed for now Dangerous!
							if ($_SESSION[$ShortKey] <> ""){
								$varValues[$ShortKey] = $_SESSION[$ShortKey];
							}
						}
					break;
					case 'varchar':
						if(SYS_ENCODING_DEFAULT!="NO_ENCODING"){
							$varValues[$ShortKey] = "'".substr(str_replace("'", "''", utf8_encode($varValues[$ShortKey])),0,$lenght)."'";
						}else{
							$varValues[$ShortKey] = "'".substr(str_replace("'", "''", $varValues[$ShortKey]),0,$lenght)."'";
						}
					break;					
					case 'text':
					   $varValues[$ShortKey] = "'".substr(addslashes(utf8_encode($varValues[$ShortKey])),0,$lenght)."'";
					break;
					case 'money':
						if ($varValues[$ShortKey]==""){
							$varValues[$ShortKey] = "NULL";
						}else{
							$varValues[$ShortKey] =  number_format($varValues[$ShortKey], 2, '.', '');
						}
					break;
					case 'timestamptz':
						if ($varValues[$ShortKey]==""){
					   		$varValues[$ShortKey] = "NULL";
					   }else{
					   		if ($ShortKey == "TimeStart" ||$ShortKey == "TimeStop" ){
									$varValues[$ShortKey] = "'".$varValues[$ShortKey]."'";
							}else{
					   			$varValues[$ShortKey] = "to_timestamp('".$varValues[$ShortKey]."','YYYY-MM-DD')";
							}
					   }
					break;
					case 'date':
						if ($varValues[$ShortKey]=="" || $varValues[$ShortKey]=="0000-00-00"){
					   		$varValues[$ShortKey] = "NULL";
					   }else{
				   			$varValues[$ShortKey] = "to_timestamp('".$varValues[$ShortKey]."','YYYY-MM-DD')";
					   }
					break;		
					case 'date_YYYYMMDD':
						if ($varValues[$ShortKey]==""){
					   		$varValues[$ShortKey] = "NULL";
					   }else{
				   			$varValues[$ShortKey] = "to_timestamp('".$varValues[$ShortKey]."','YYYYMMDD')";
					   }
					break;
					case 'date_DD.MM.YY':
						if ($varValues[$ShortKey]=="" || $varValues[$ShortKey] == "00.00.00"){
					   		$varValues[$ShortKey] = "NULL";
					   }else{
						   //if(DEBUG_MODE){
							if($varValues[$ShortKey][6].$varValues[$ShortKey][7]>30){
								//echo $varValues[$ShortKey][6].$varValues[$ShortKey][7].'Plus que 30';
								$YY = 19; 
							}else{
								//echo $varValues[$ShortKey][6].$varValues[$ShortKey][7].'moins que 30';
								$YY = 20; 
							}
							$varValues[$ShortKey] = substr($varValues[$ShortKey],0,6).$YY.substr($varValues[$ShortKey],6);
							//echo $varValues[$ShortKey].'<BR>';
						   //}
						   
					   		$varValues[$ShortKey] = "to_timestamp('".$varValues[$ShortKey]."','DD.MM.YYYY')";
					   }
					break;						
					case 'autocount': 	// Volutary ignore this field 
						unset($varValues[$ShortKey]);
					/*case 'bool':
						$varValues[$ShortKey] = "''";
					break;*/
					default:
	
					break;
				}
	return 	$varValues[$ShortKey];			
}

	/**
	* UPDATE from an predefined Array
	*
	* Example of use:
	* <code>
	*	$dbU = new dbSelectUnique;
	*
	*	$varValues2[SQL_TABLENAME]	= '"FO_Person_Relation"';
	*	$varValues2[SQL_WHERE]		= '"ID_Person_Contact" = '.$FO_Person_Contact["ID_Person_Contact"];
	*	$varValues2["ID_Person1"]	= $ID1;
	*	$varValues2["ID_Person2"]	= $ID2;
	*
	*	$varType2["ID_Person1".TYPESQL_ADDS]	= "int4";
	*	$varType2["ID_Person2".TYPESQL_ADDS]	= "int4";	
	*	$dbU->execSql(makeUpdateQuery($varType2, $varValues2));
	* </code>		
	*
	* Mandatory : "SQL_TABLENAME" value must exist into the array $varValues
	* <br>Mandatory : "SQL_WHERE" value must exist into the array $varValues
	* <br><b>Available varType</b> are : int4, numeric, varchar, text, timestamptz,  bool
	*
	* @param	array	$varType	Type of the data
	* @param	array	$varValues	Values of the data
	* @param	int		$modifiedBy	The id of the user who change the data, id value is NULL the no modifiction field, 0 means server change
	* @return	string		Formated query
	* @see varAnalysis() to see the kind of existing type	
	*/
	function makeUpdateQuery($varType, $varValues, $modifiedBy = 0){
	
		unset($varType["?column?".TYPESQL_ADDS]);
		unset($varValues["?column?"]);
	
		$queryUset = '';
		$queryU = "UPDATE ";
		$queryU .= $varValues[SQL_TABLENAME];
		$queryU .= " SET ";
		
		if ($modifiedBy > 0){
			$varType["ModifiedBy".TYPESQL_ADDS]=	'int';
			$varType["Modified".TYPESQL_ADDS]	=	'int';
			$varValues["ModifiedBy"]		=	$modifiedBy;
			$varValues["Modified"]			=	"now()";		
		}
		
		reset ($varType);
		while (list($key, $val) = each($varType)) {
			// Check si la clé existe aussi bien pour la variable que le type
			$ShortKey = str_replace(TYPESQL_ADDS,'',$key);
			if (array_key_exists($ShortKey,$varValues)){
				$formatedValue = varAnalysis($val,$ShortKey,$varValues);
				if($queryUset<>''){$queryUset .= ", ";}
				$queryUset .= " \"".$ShortKey."\"=".$formatedValue." ";
			}else{
				echo "Error: the key is not existing for ".$ShortKey."<BR>\n";
				exit();
			}
		}
			$queryU .= $queryUset;
			$queryU .= " WHERE ";
			$queryU .= $varValues[SQL_WHERE];
			//if (DEBUG_MODE){write_error("<BR>UPDATE query: ".$queryU);}
			
	
			return $queryU;
	}

	/**
	* UPDATE from an predefined Array
	*
	* Example of use:
	* <code>
	*	$dbU = new dbSelectUnique;
	*
	*	$varValues2[SQL_TABLENAME]	= '"FO_Person_Relation"';
	*	$varValues2["ID_Person1"]	= $ID1;
	*	$varValues2["ID_Person2"]	= $ID2;
	*
	*	$varType2["ID_Person1".TYPESQL_ADDS]	= "int4";
	*	$varType2["ID_Person2".TYPESQL_ADDS]	= "int4";	
	*	$dbU->execSql(makeInsertQuery($varType2, $varValues2));
	* </code>		
	*
	* <br>Mandatory : "SQL_TABLENAME" value must exist into the array $varValues
	* <br><b>Available varType</b> are : int4, numeric, varchar, text, timestamptz,  bool
	*
	* @param	array	$varType	Type of the data
	* @param	array	$varValues	Values of the data
	* @return	string		Formated query
	* @see varAnalysis() to see the kind of existing type
	*/
	function makeInsertQuery($varType, $varValues){
	
		unset($varType["?column?".TYPESQL_ADDS]);
		unset($varValues["?column?"]);	
	
		if (!(isset($varType))){
				// in the case that there is no too make an insert
			if (DEBUG_MODE){echo "Unable to insert, please fill the data's";}
		}else{
			// Replace all Additional quotes:
			$varValues[SQL_TABLENAME] = str_replace('"', '', $varValues[SQL_TABLENAME]);			
			$queryUSetP1 = '';
			$queryUSetP2 = '';
			$queryU = "INSERT INTO ";
			$queryU .=  " \"".$varValues[SQL_TABLENAME]."\" ";
			//$queryU .= " VALUES ";
			
			reset ($varType);
			while (list($key, $val) = each($varType)) {
				// Check si la clé existe aussi bien pour la variable que le type
				$ShortKey = str_replace(TYPESQL_ADDS,'',$key);
				if (array_key_exists($ShortKey,$varValues)){
					// Format well all expression
					$formatedValue = varAnalysis($val,$ShortKey,$varValues);
					// Exceptions for ID_FO value
					if ("ID_FO"==$ShortKey){$formatedValue = $_SESSION["ID_FO"]; }
					
					if($queryUSetP1<>''){$queryUSetP1 .= ", "; $queryUSetP2 .= ", ";}
					$queryUSetP1 .= " \"".$ShortKey."\" ";
					$queryUSetP2 .= $formatedValue." ";
				}else{
					echo "Error: the key is not existing for ".$ShortKey."<BR>\n";
					exit();
				}
			}
			
			$queryU = $queryU . "(".$queryUSetP1.") VALUES (".$queryUSetP2.")";
			//$queryU .= " WHERE ";
			//$queryU .= $varValues[SQL_WHERE];
			//echo $queryU;
			
			if ($deleteBeforeInsert){
				$queryU =$transactDelete .= ' DELETE FROM "'.$varValues[SQL_TABLENAME].'" WHERE '.$varValues[SQL_WHERE]."; \n ".$queryU;
			}			
			
			if (DEBUG_MODE){write_error("<BR>INSERT query: ".$queryU);}
			return $queryU;
		}
	}

	function makeUniqueUpdate($Table, $Field, $Value, $Where){
		$varType[$Field.TYPESQL_ADDS]	= "int4";
		$varValues[$Field] 				= $Value; 
		$varValues[SQL_WHERE]			= $Where;	
		$varValues[SQL_TABLENAME]		= '"'.$Table.'"';	
		return makeUpdateQuery($varType, $varValues);
	}

/**
 * Check Security Allow Modify
 * 
 * Check into FO_SYS_Right if the current Sessioned user have the right 
 * to write into a specific block
 *
 */
class dbAllowModify {
	function allow($ID_Block,$AllowAdd, $BackUrl){
		$result = pg_exec(defConn(),"SELECT \"Write\", \"Delete\" FROM \"FO_SYS_Right\" WHERE \"ID_User\"=".$_SESSION["ID_User"]." AND \"ID_Block\"=".$ID_Block);
		$row = pg_fetch_array($result);
		pg_freeresult($result);
		
		drawBackButton($BackUrl);

		if ($row["Write"]=='t'){
			if ($AllowAdd=="TRUE"){
				// Make an insert button
				print("<input type='button' name='ButtonInsert' id='ButtonInsert' value='".DICO_WORD_ADD."' onClick='submit();' >");
				print("<input type='hidden' name='AllowAdd' value='".$AllowAdd."'>");
				print("<input type='hidden' name='SQL_TABLENAME' value=".$_POST["SQL_TABLENAME"].">");
			}else{
				// Make an modify button
				print("<input type='button' name='ButtonModify' id='ButtonModify' value='".DICO_WORD_SAVE."' onClick='submit();' >");
				print("<input type='hidden' name='AllowModify' value='t'>");
				// MARCHE SOUS EXPLORER	 print("<input type='button' name='Submit' value='Modify' onClick='window.iframe".$ID_Block.".document.formulaire.submit(); ' >");
				
				// Make a Delete button
				if ($row["Delete"]=='t'){
					$_SESSION["MD5_DEL"] = md5(MD5_KEY_MINI.time());
					drawDeleteButton(DICO_DELETE_CONFIRM,$_SESSION["MD5_DEL"]);
				}
			}
		}


		return $row["Write"];
	}
	
	function updateW($AllowUpdate){
		// The W request is the current Where (where the ID is!)
		if ((isset($_REQUEST["W"])OR isset($_REQUEST["SQL_WHERE"]))  AND $_POST["AllowModify"]=='t' AND $AllowUpdate==TRUE){
			$gv = new getVar();
			$gv -> getPosted();
			$query =  makeUpdateQuery($gv->VarArrayType , $gv->VarArrayVal);

			if(PG_ENCODING=='ISO-8859-1'){
				$query = utf8_decode($query);	
			}

			// Exectue DB
			$dbSU = new dbSelectUnique();
			$dbSU -> execSQL($query);
			//echo $dbSU -> status_msg;
			if (DEBUG_MODE){$gv -> echoVar(); echo "<BR><BR>\n\n".$query;}; 
		}
		return $dbSU -> status_msg;
	}
	
	function insert($AllowUpdate,$VariableQuiSertARienMaisPresent){
		//$AllowUpdate=TRUE; // Force le insert
		if ($AllowUpdate==TRUE){
			$gv = new getVar();
			$gv -> getPosted();
			$query =  makeInsertQuery($gv->VarArrayType , $gv->VarArrayVal);
						
			// In the case that at this time there is no Insert (fill the data before)
			if (trim($query)<>""){
				// Exectue DB
				$dbSU = new dbSelectUnique();
				$dbSU -> execSQL($query);
				//echo $dbSU -> status_msg;
				if (DEBUG_MODE){$gv -> echoVar(); echo "<BR><BR>\n\n".$query;}; 
			}
		}

		return $dbSU -> status_msg;
	}
	
	function delete($AllowUpdate,$Where,$Table){
		//$AllowUpdate=TRUE; // Force le insert
		if ($AllowUpdate==TRUE && trim($Where) <> ""){
			$query =  "DELETE FROM ".$Table." WHERE ".$Where;
			if (DEBUG_MODE){write_error("<BR>Delete query: ".$query);}
			// Exectue DB
			$dbSU = new dbSelectUnique();
			$dbSU -> execSQL($query);
		}
		return $dbSU -> status_msg;
	}
	
	
	
}

/**
 * Check Security Allow Read
 * 
 * Check into FO_SYS_Right if the current Sessioned user have the right 
 * to read into a specific block
 *
 * @package GBM
 * @subpackage DB
 */
class dbAllowRead {
	function allow ($ID_Block){
		$result = pg_exec(defConn(),"SELECT \"Read\" FROM \"FO_SYS_Right\" WHERE \"ID_User\"=".$_SESSION["ID_User"]." AND\"ID_FO\"=".$_SESSION["ID_FO"]." AND \"ID_Block\"=".$ID_Block);
		$row = pg_fetch_array($result);
		pg_freeresult($result);
		return $row["Read"];
	}
}


/**
 * Check if the user is allowed to see the data's
 * 
 * @param string $query query into the CheckPreDisplay column
 */
function checkPreDisplay($query){
	if (trim($query)<>""){
		eregi("^(.*)(:[I|L|S]:[a-z0-9_:]*)( .*|)$",$query,$result);
		$queryWhere = new queryAnalyse();
		$replace_val = array($result[2]);
		$SelectWhere = $queryWhere->analyse($replace_val);	
		
		$newquery = $result[1]." ".$SelectWhere[0]." ".$result[3];
		
		if (eregi("^(.*)(:[I|L|S]:[a-z0-9_:]*)( .*|)$",$newquery,$result2)){
			$queryWhere = new queryAnalyse();
			$replace_val = array($result2[2]);
			$SelectWhere = $queryWhere->analyse($replace_val);				
			$newquery = $result2[1]." ".$SelectWhere[0]." ".$result2[3];
		}
		
		$dbU = new dbSelectUnique;
		$dbU->execSql($newquery);
		
		if (!$dbU->row){
			unauthorisedAction();
		}
	}
}

/**
 * Generic Select List
 * 
 * This create a HTML select based on database data's
 *
 * @package GBM
 * @subpackage DB
 */
class dbSelectListSelect {
	var $Select = "";
	var $onChange = "";
	var $row = "";

	/**
	 * Create a select field
	 * 
	 * @param mixed $query
	 * @param mixed $ID_Name	SQL colum name for the ID of the selected object 
	 * @param mixed $Visible	SQL colum name for the Visible value of the selected object 
	 * @param mixed $ID_Current	Default id value
	 * @param bool	$active		Add onChange JS to the select that send the form.
	 * @return mixed
	 */
	function makeSelect($query, $ID_Name, $Visible, $ID_Current, $active = FALSE, $params = ""){
		$result = pg_exec(defConn(),$query);
		$this->row = pg_num_rows($result);
		
		if ($active){
			$this->onChange = " onChange=\"document.miniForm.submit();\"";
		}
		
		$this->Select .= "<select name='".$ID_Name."' ".$this->onChange." ".$params."><option value='0'>&nbsp;</option>";
		while($row = pg_fetch_array($result)){
			$this->Select .= "<option ";
			if ($ID_Current== $row[$ID_Name]) {$this->Select .=  "selected ";}
			$this->Select .= "value='".$row[$ID_Name]."'>".$row[$Visible]."</option>";
		}
		$this->Select .= "</select>";
		pg_freeresult($result);
		return $row[$ID_Name];
	}
	
	/**
	 * Build a simple Form that include the current Select
	 * 
	 * @param mixed $link
	 * @param mixed $target
	 * @return mixed
	 */
	function activeSelect($link, $target){
		$this->Select = "<form name='miniForm' method='post' action='".$link."' target='".$target."'>".$this->Select."</form>";
		return true;
	}
	
	function getSelect(){
		echo $this->Select;
		return true;
	}
	
}

////////////////////////////////////
// Select List : FO
////////////////////////////////////
function SelectFO()
{
$ListFamily = new dbSelectListSelect();
$query="SELECT \"FO\".* FROM (SELECT \"ID_FO\" FROM \"FO_SYS_Right\" WHERE \"ID_User\"= ".$_SESSION["ID_User"]." GROUP BY \"ID_FO\") As \"foo\", \"FO\" WHERE \"FO\".\"ID_FO\"=\"foo\".\"ID_FO\"";
$ListFamily->makeSelect($query, "ID_FO", "Name", $_SESSION["ID_FO"], true);
$ListFamily->activeSelect(ROOT_URI."inc/validFO.php", "_top");
$ListFamily->getSelect();
}

////////////////////////////////////
// Show from FO_SYS_Var with ID
////////////////////////////////////
function getFO_SYS_Var($ID, $col = "Name_E" )
{
	if($ID!=""){
		$var = new dbSelectUnique();
		$var->execSql('SELECT "'.$col.'" FROM "FO_SYS_Var" WHERE "ID_Var"='.$ID);
		return $var->row;
	}else{
		if(DEBUG_MODE){ write_error("Error 2323dk0293sfxy09k2 : ID Empty"); }
		return FALSE;
	}
}


/**
 * getFO_SYS_Var_Cached()
 * 
 * @param mixed $ID
 * @param string $col
 * @param bool $returnArray
 * @return mixed related to $returnArray
 */
function getFO_SYS_Var_Cached($ID, $col = "default", $returnArray = TRUE ){
	
	if($col=="*"){
		$col = "*";
	}elseif($_SESSION["Lang"]<>"" && $col == "default"){
		$col = "Name_".getLangCol($_SESSION["Lang"]);
	}elseif($col == "default"){
		$col = "Name_E";
	}

	if ($ID <> ""){
		if ($_SESSION["cached_FO_SYS_Var"][$ID][$col]==""){
			$var = new dbSelectUnique();
			$var->execSql('SELECT "'.$col.'" FROM "FO_SYS_Var" WHERE "ID_Var"='.$ID);
			if ($returnArray){
				if($col=="*"){
					$_SESSION["cached_FO_SYS_Var"][$ID] = $var->row;
				}else{
					$_SESSION["cached_FO_SYS_Var"][$ID][$col] = $var->row;
				}
			}else{
				$_SESSION["cached_FO_SYS_Var"][$ID][$col] = $var->row[$col];
			}
		}
		if($col=="*"){
			return $_SESSION["cached_FO_SYS_Var"][$ID];
		}else{
			return $_SESSION["cached_FO_SYS_Var"][$ID][$col];
		}
	}
}

function getFO_SYS_Var_ID($Value, $Group, $col = "Value", $cached = TRUE)
{
	if (is_array($_SESSION["cached_FO_SYS_Var"][$Group][$Value]) && $cached){
		return $_SESSION["cached_FO_SYS_Var"][$Group][$Value];
	}else{
		$var = new dbSelectUnique();
		//echo 'SELECT * FROM "FO_SYS_Var" WHERE "Group"=\''.$Group.'\' AND "'.$col.'"='.$Value;
		$var->execSql('SELECT * FROM "FO_SYS_Var" WHERE "Group"=\''.$Group.'\' AND "'.$col.'"='.$Value);
		$_SESSION["cached_FO_SYS_Var"][$Group][$Value] = $var->row;
		return $var->row;
	}
}


////////////////////////////////////
// Show from FO_SYS_Var with Group
////////////////////////////////////
function getFO_SYS_Var_group($group, $col = "Name", $defined = FALSE, $utf8 = 0, $orderBy = ""){
	// Lang
	if ($_SESSION["Lang"]<>""){ $L = getLangCol($_SESSION["Lang"]);	}
	
	if($orderBy == ""){ $orderBy = "Name_".$L; 	}
	
	$i = 0 ;
	$var = new dbSelectMultiple();
	$var->execSql("*", "\"FO_SYS_Var\"", "\"Group\"='$group'", '"'.$orderBy.'"', FALSE);
	while ($var->getNext()){
		$row[$i][0] = $var->row["ID_Var"];
		if ($var->row["Name_".$L]<>"" && $col == "Name"){
			$row[$i][1] = $var->row["Name_".$L];
		}elseif($var->row[$col]<>""){
			if ($defined){
				$row[$i][1] = constant($var->row[$col]);				
			}else{
				$row[$i][1] = $var->row[$col];
			}
		}else{
			$row[$i][1] = $var->row["Name_E"];
		}
		if 		($utf8 == -1){	$row[$i][1] = utf8_decode($row[$i][1]);
		}elseif	($utf8 == 1){	$row[$i][1] = utf8_encode($row[$i][1]);}
		$i++;
	}
	return $row;
} 
 


// Function dedicated for the function getFO_SYS_Var_list
// clone of : class dbSelectListSelect
function makeDatas_array($query, $ID_Name, $Visible, $ID_Current){
	$result = pg_exec(defConn(),$query);
	if (DEBUG_MODE){write_error("<BR>makeDatas_array query: ".$query);}
	$counter = 0;
	while($row = pg_fetch_array($result)){
		$output[$counter][0] = $row[$ID_Name];
		if (is_array($Visible)){
			for ($i = 0 ; $i < sizeof($Visible); $i++){
				$output[$counter][1] .= $row[$Visible[$i]]." ";
			}
		}else{
			$output[$counter][1] = $row[$Visible];
		}
		$counter++;
	}
	pg_freeresult($result);
	return $output;
}

/*
 * Change with previous version ! onChange -> additionalParams, old command : 
 * getFO_SYS_Var_list($datas_array, $name, $style_css, $selectedData, $empty_field = "", $onChange = "")
 */

function getFO_SYS_Var_list($datas_array, $name, $style_css, $selectedData, $empty_field = "", $additionalParams=""){
	
	if(mb_eregi('MSIE',$_SERVER['HTTP_USER_AGENT'])){
		$output .= "<span class=\"".$style_css."\" ><select id='".$name."' name='".$name."'  class=\"".$style_css."\" ".$additionalParams."></span>";
	}else{
		$output .= "<select id='".$name."' name='".$name."' style=\"".$style_css."\" class=\"".$style_css."\" ".$additionalParams.">";	
	}

		if (is_array($empty_field)){
			$output .=  "<option value='".$empty_field[0]."'>".$empty_field[1]."</option>";
		}
		for ($i = 0; $i < sizeof($datas_array); $i++){
			$output .=  "<option value='".$datas_array[$i][0]."'";
				if ($datas_array[$i][0] == $selectedData){$output .=  " selected ";}
			$output .=  ">".utf8_decode($datas_array[$i][1])."</option>";
		}
	$output .=  "</select>";
	return $output;
}


/**
 * Analyse a given query
 * and make some security check before the query is send
 *
 * @package GBM
 * @subpackage DB
 */
class queryAnalyse{
	function analyse($whereArray){
		////////////////////
		// Case Where 
		////////////////////
		if (DEBUG_MODE){print_r($whereArray);}
		
		// foreach ($whereArray as &$t){ // MODIFICATION POUR PHP 5
		$arrayCount =0;
		
		foreach ($whereArray as $t){
			$particule = '';
			if(mb_eregi('^or (.*)',$t,$res)){
				$t = trim($res[1]);
				$particule = 'or ';
			}
			// Search if there is constant into the query
			if(ereg('^(.*)@F@([A-z0-9]*)$',$t,$cst)==TRUE){
				$t = $cst[1].constant($cst[2]);
			}
		
/*			// MUST BE OPTIMIZE USING eregi !
			$splied__xx = split(":",substr($t,3));
			if ($splied[1]<>""){
				$t_destfield = $splied[1];
				}
			$t_short = $splied[0];*/	
			
			mb_eregi("^:([A-Z]):([A-Z0-9_]*):?([A-Z0-9_]*)([><=]*)$",$t,$splited_ereg);
			
			//pr($splited_ereg);echo $t_short." - ".$t_destfield." - ".$t."<HR>";
			
			list($vide,$vide,$t_short,$t_destfield) = $splited_ereg;
			
			$t_short = str_replace("=","",$t_short);
			$t_short = str_replace("<","",$t_short);
			$t_short = str_replace(">","",$t_short);

			$t_destfield = str_replace("=","",$t_destfield);
			$t_destfield = str_replace("<","",$t_destfield);
			$t_destfield = str_replace(">","",$t_destfield);
			
			if ($_SESSION[$t_short]<>"" && !eregi('^:G:.*',$t)){
				//if(DEBUG_MODE){ echo "<hr>Short ".$t_short.", Destfiled ".$t_destfield."<hr>"; 	}
				$t_value = $_SESSION[$t_short];
					// If new value is sended by POST or GET then get this last one
					if($_GET[$t_short]<>""){
						if(DEBUG_MODE){ echo "<hr>MODIF. 23.09.2009 on file db.inc.php - GET Value not accepted if session value exist"; 	};//$t_value = $_GET[$t_short]; 
						//$_SESSION[$t_short] = $t_value; // TO BE MONITORED ! added 16.02.2009 // ID_Entity can be changed !!!! 
						if(DEBUG_MODE){ echo "<hr>Short ".$t_short.", Value ".$t_value."<hr>"; 	}
					}
			}else{
				if (eregi('^:G:.*',$t)){
					if (eregi("ID_User",$t) || eregi("ID_Entity",$t) || eregi("IDU",$t) || eregi("ID_FO",$t)){
						write_error("Verify your block, :G: is limited too non critical values!");
						exit();
					}
				}				
			
				$t_value = "";
				//$t_value = $_REQUEST[$t_short]; // Get any value from URL !!! Securty remove
				$Val = $this->wAnalyse();
				if ($Val[0]==$t_short){
					$t_value = $Val[1];
					$_SESSION[$t_short] = $t_value;
				}else{
					$t_value = $_GET[$t_short];
					$_SESSION[$t_short] = $t_value;
				}
			}
			// If begin with :I: the this is an ID that is sended trought a GET, POST, SESSION or REQUEST format INTERGER
			if(ereg('^:I:[A-z0-9:.]*([><=]*)$',$t,$equals)==TRUE){
				//... If the Atribute name is precided with the table name
				if($equals[1]==""){ 	$eq = "="; 	}else{ 	$eq = $equals[1]; 	}
				$t_short = str_replace('.','"."',$t_short);
				if ($t_destfield <>""){
					$t_destfield = str_replace('.','"."',$t_destfield);
					$t = " \"".$t_destfield."\"".$eq.$t_value." ";
				}else{
					$t = " \"".$t_short."\"".$eq.$t_value." ";
				}
			// If begin with :S: the this is an ID that is sended trought a GET, POST, SESSION or REQUEST format STRING			
			}elseif(ereg('^:S:[A-z0-9:.]*([><=]*)$',$t,$equals)==TRUE){
				//... If the Atribute name is precided with the table name
				//pr($equals);
				if($equals[1]==""){ 	$eq = "="; 	}else{ 	$eq = $equals[1]; 	}
				$t_short = str_replace('.','"\"."\"',$t_short);	
				if ($t_destfield <>""){
					$t_destfield = str_replace('.','"\"."\"',$t_destfield);
					$t = " \"".$t_destfield."\"".$eq."'".$t_value."' ";
				}else{						
					$t = " \"".$t_short."\"".$eq."'".$t_value."' ";
				}
			// If begin with :D: the this is an ID that is sended trought a GET, POST, SESSION or REQUEST format Date comparator			
			}elseif(ereg('^:D:[0-9A-z:,\.-]*$',$t)==TRUE){
				//... If the Atribute name is precided with the table name
				//$t_short = str_replace('.','"\"."\"',$t_short);	
				//if ($t_destfield <>""){
				//	$t_destfield = str_replace('.','"\"."\"',$t_destfield);
				//	$t = " \"".$t_destfield."\"=".$t_value." ";
				//}else{						
					$t = " to_char(\"".$t_short."\", '".$t_destfield."')='".$t_value."' ";
				//}		
			// If begin with :G: the this is an ID that is sended trought a GET string			
			}elseif(ereg('^:G:[0-9A-z:,\.-]*$',$t)==TRUE){
				// same as S but keep the Get value in session

				//	echo "T:".trace($t);
				//	echo "t_short:".trace($t_short);
				//	echo "<HR>";
					
				$t_short = str_replace('.','"\"."\"',$t_short);	
				if ($t_destfield <>""){
					$t_destfield = str_replace('.','"\"."\"',$t_destfield);
					$t = " \"".$t_destfield."\"=".$t_value." ";
				}else{						
					$t = " \"".$t_short."\"='".$t_value."' ";
				}
				if($_GET[$t_short]<>""){
					$_SESSION[$t_short] = $t_value; // TO BE MONITORED ! added 16.02.2009 // ID_Entity can be changed !!!! 
													// Just Avoid to use ID_User, ID_Entity, IDU with a G
				}
			}elseif(ereg('^:L:[A-z0-9.,ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿ]*$',$t)==TRUE){
				//... If the qualtiy is an ILIKE !!! Must use the translate function into the database !
				$t_short = str_replace('.','"\"."\"',$t_short);			
				$t = " \"".$t_short."\" ILIKE '%".desaccentise($t_value)."%' ";
			}elseif(ereg('^.*@F@.*$',$t)==TRUE){
				echo $t_short." ";
				echo $t;
				//$t_short = str_replace('@F@','',$t_short);			
				//$t = " \"".$t_short."\" = '%".desaccentise($t_value)."%' ";
				
			}
		$newWhereArray[$arrayCount]	= $particule.$t;
		$arrayCount ++;	
		}
		if (DEBUG_MODE){print_r($newWhereArray);} // MODIFICATION POUR PHP 4
		return $newWhereArray; // MODIFICATION POUR PHP 4

		//if (DEBUG_MODE){print_r($whereArray);} // MODIFICATION POUR PHP 5
		//return $whereArray; // MODIFICATION POUR PHP 5
	}
	
	// if , => AND
	// if ,or => (... OR ...)
	function addAND($whereArray, $add = 'AND'){
		$newWhere = '';
		//foreach ($whereArray as &$t){
		foreach ($whereArray as $t){
			if(mb_eregi('^or(.*)',$t, $res)){
				if ($newWhere==''){$newWhere= $t;}else{$newWhere= " (".$newWhere." OR ".trim($res[1]).") ";}
			}else{
				if ($newWhere==''){$newWhere= $t;}else{$newWhere.= " AND ".$t;}
			}
		}
		return $newWhere;
	}
	
	// If part of the SQL query is sent trought a GET or POST call it must be treated here
	function wAnalyse(){
		$W = $_REQUEST["W"];
		// For securty reason only Column name and Value can be given trought URL.
		if (ereg("^((\"[A-z0-9]{3,20}\".)?\"[A-z0-9]{3,20}\"=(')?[A-z0-9]{3,20}(')?(&)?){0,5}$",$W)){
			// Remove all \"
			$W = str_replace('\"', '',$W);
			// Split the first parametters
			$Warg = split("&",$W);
			// Valable for only one argument for now. FIXIT
			$Val = split("=",$Warg[0]);
		}
		return $Val;
	}
	
}

function drawBackButton($BackUrl, $showIT = TRUE){
	// Draw Back Button	
	$pi = pathinfo(trim($BackUrl));

	if ($pi["extension"] != ""){
		if($showIT){
			print("<input type='button' name='Back' id='Back' value='".DICO_WORD_BACK."' onClick='document.location.href=\"".$BackUrl."\"' >");
		}else{
			return "<input type='button' name='Back' id='Back' value='".DICO_WORD_BACK."' onClick='document.location.href=\"".$BackUrl."\"' >";
		}
	}
}

////////////////// TO DO !!!!!!!!!!!!!!!!!!!!!!!
function drawDeleteButton($Message, $SessionKey){
	// Draw Back Button
	print("<input type='button' name='Delete' id='Delete' value='".DICO_WORD_DELETE."' onClick='if(confirm(\"".$Message."\")){window.location=\"".ROOT_URI."inc/delete.php?SK=".$SessionKey."\";}' >");
}

////////////////////////////////////
////////////////////////////////////
// Returns an array with infos of every field in the table (name, type, length, size)
////////////////////////////////////
////////////////////////////////////

function SQLConstructFieldsInfo($TABLE)
{
   $s="SELECT a.attname AS name, t.typname AS type, a.attlen AS size, a.atttypmod AS len, a.attstorage AS i
   FROM pg_attribute a , pg_class c, pg_type t
   WHERE c.relname = '$TABLE' 
   AND a.attrelid = c.oid AND a.atttypid = t.oid";
   
   $s="SELECT *
   FROM pg_attribute a , pg_class c, pg_type t
   WHERE c.relname = '$TABLE' 
   AND a.attrelid = c.oid AND a.atttypid = t.oid";
   echo $s;
  
   if ($r = pg_exec(defConn(),$s))
       {
       $i=0;
       while ($q = pg_fetch_assoc($r))
       {
               $a[$i]["type"]=$q["type"];
               $a[$i]["name"]=$q["name"];
               if($q["len"]<0 && $q["i"]!="x")
               {
                   // in case of digits if needed ... (+1 for negative values)
                   $a[$i]["len"]=(strlen(pow(2,($q["size"]*8)))+1);
               }
               else
               {
                   $a[$i]["len"]=$q["len"];
               }
               $a[$i]["size"]=$q["size"];
           $i++;           
       }
       return $a;
   }
   return null;
}

//---------------------------------------------------
// Convertions Functions
//---------------------------------------------------

////////////////////////////
// Function that calculate the difference between 2 timestamps in second
////////////////////////////
function timeDiff($timeStart, $timeStop){
	$second = pgTimestampToSecond($timeStop)- pgTimestampToSecond($timeStart);
	return $second;
}

function timeDiffNow($timeStart){
	$second = time() - pgTimestampToSecond($timeStart);
	return $second;
}

function pgTimestampToSecond($TimeStamp){
	// From Epoch 1er janvier 1970 00:00:00 GMT
	$second = date('U',strtotime($TimeStamp));
	return $second;
}

// Par tranche de 10 minutes
function sparkouRoundTime($second){
	$minute = 10+(floor($second/600)*10);
	$roundedHours = $minute / 60;
	return $roundedHours;
}

function extractHour($TimeStamp){
	$hour = date('G:i',strtotime($TimeStamp));
	return $hour;
}

function dayOfWeek(){
	if (($daynumber = date('w'))==0){	$daynumber = 7;	}
	// TESTING PURPOSE
	// $daynumber = 6;
	return $daynumber;
}

function dayOfWeekTomorow(){
	$tomorow = dayOfWeek()+1;
	if (($daynumber = $tomorow)==8){	$daynumber = 1;	}
	return $daynumber;
}

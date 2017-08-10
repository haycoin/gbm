<?php
/**
 * This is additionals function file for GBM_Entity
 *
 * Include this file into your code <code>include_once(ROOT_FOLDER."GBM_Entity/functions.inc.php");</code>
 *
 * @author Alec Avedisyan
 * @copyright 2017 Sparkou.com
 * @package GBM
 * @version 3.0.0
 * @subpackage Includes
 */
include_once(ROOT_FOLDER."inc/db.inc.php");

/**
 * Get the Entity informations (GBM_Entity)
 * 
 * @param mixed $ID_Entity
 * @return array
 */
function getEntityInfo($ID_Entity){
	$dbU = new dbSelectUnique;
	$row = $dbU->execSqlLong("*", '"GBM_Entity"', '"ID_Entity"='.$ID_Entity, 'f');
	return $row;
}

/**
 * Get the User informations (FO_SYS_User)
 * 
 * @param mixed $ID_Entity
 * @return array
 */
function getEntityUserInfo($ID_Entity){
	$dbU = new dbSelectUnique;
	$row = $dbU->execSqlLong("*", '"UserInfo"', '"ID_Entity"='.$ID_Entity, 'f');
	return $row;
}

/**
 * Build query that update one value for an Entity (GBM_Entity)
 * 
 * @param mixed $ID_Entity
 * @param mixed $Field
 * @param mixed $Value
 * @param string $Type
 * @return string
 */
function setEntityField($ID_Entity, $Field, $Value, $Type = "varchar"){
	$varType[$Field]	= $Type;
	$varValues[$Field] 	= $Value;
	$varValues[SQL_TABLENAME] 		= '"GBM_Entity"';
	$varValues[SQL_WHERE]			= '"ID_Entity"='.$ID_Entity;
	return makeUpdateQuery($varType, $varValues);
}


//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ FIXIT
/**
 * getEntityInfoIlikeField()
 * @access private
 * @param mixed $FieldName
 * @param mixed $FieldValue
 * @return array
 */
function getEntityInfoIlikeField($FieldName, $FieldValue){
	$dbU = new dbSelectUnique;
	$row = $dbU->execSqlLong("*", '"GBM_Entity"', '"'.$FieldName.'" ILIKE '.$FieldValue, 'f');
	return $row;	
}
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ FIXIT

 function createEntity($ID_Entity = 0, $Name, $Firstname, $ID_Type,  $ID_FO = 0, $Nickname = "" ,$Gender = "", $Birthdate = "", $Comment = "", $Secondname = ""){
	$dbUU = new dbSelectUnique;
	
	$varType["ID_Type".TYPESQL_ADDS]	= "int4";
	$varType["ID_FO".TYPESQL_ADDS]		= "int4";
	$varType["Gender".TYPESQL_ADDS]		= "int4";	
	$varType["Name".TYPESQL_ADDS]		= "text";	
	$varType["Firstname".TYPESQL_ADDS]	= "text";	
	$varType["Nickname".TYPESQL_ADDS]	= "text";		
	$varType["Secondname".TYPESQL_ADDS]	= "text";		
	$varType["Comment".TYPESQL_ADDS]	= "text";		
	$varType["Opening".TYPESQL_ADDS]	= "timestamptz";	


	$varValues["ID_FO"] 				= $ID_FO;
	$varValues["ID_Type"] 				= $ID_Type;
	$varValues["Gender"] 				= $Gender;
	$varValues["Name"] 					= $Name;
	$varValues["Firstname"] 			= $Firstname;
	$varValues["Secondname"] 			= $Secondname;	
	$varValues["Nickname"] 				= $Nickname;		// Usually the username or the e-mail.
	$varValues["Comment"] 				= $Comment;
	$varValues["Opening"] 				= $Birthdate;

	//$varValues[SQL_WHERE]				= '"ID_Entity" = '.$ID_Entity;
	$varValues[SQL_TABLENAME] 			= '"GBM_Entity"';			
	
	// Force ID_entity ! Be carfull with sequence ! 
	if ($ID_Entity > 1){
		$varType["ID_Entity".TYPESQL_ADDS] 	= "int4";			
		$varValues["ID_Entity"] 			= $ID_Entity;					
	}
	return makeInsertQuery($varType, $varValues);

}


/**
 * addEntityRelation()
 * 
 * @param mixed $ID_Entity
 * @param mixed $ContactType
 * @param mixed $Information
 * @param string $Comment
 * @return string SQL query Insert OR Update
 */
function addEntityRelation($ID_Entity, $ContactType, $Information, $Comment ="", $Priority = "", $table = '"GBM_Entity_Relation"'){

		if ($Information<>""){

			if($table == '"GBM_Entity_Relation"'){
				$FO_Person_Contact = getEntityRelation($ID_Entity, $ContactType);
			}else{
				$FO_Person_Contact = getEntityRelation_obsolete($ID_Entity, $ContactType);
			}
			
			$varType["ID_Entity".TYPESQL_ADDS]			= "int4";
			$varType["ID_Contact_Type".TYPESQL_ADDS]	= "int4";
			$varType["Information".TYPESQL_ADDS]		= "text";	
			$varType["Comment".TYPESQL_ADDS] 			= "text";	
            $varType["Priority".TYPESQL_ADDS] 			= "int4";		
	
			$varValues["ID_Entity"] 		= $ID_Entity;
			$varValues["ID_Contact_Type"]	= $ContactType;
			$varValues["Information"]		= $Information;		
			$varValues["Comment"]			= $Comment;	
			$varValues["Priority"]			= $Priority;	
			$varValues[SQL_TABLENAME] 		= $table;
			$varValues[SQL_WHERE]			= '"ID_Person_Contact" = '.$FO_Person_Contact["ID_Person_Contact"];
					
			if ($FO_Person_Contact["ID_Person_Contact"]<>""){
				return makeUpdateQuery($varType, $varValues);
			}else{
				return makeInsertQuery($varType, $varValues);
			}
		}
}
 
 
/**
 * getEntityRelation()
 * 
 * @param mixed $ID_Entity
 * @param mixed $ContactType
 * @return array
 */
function getEntityRelation($ID_Entity, $ContactType){
	$dbU = new dbSelectUnique;
	$row = $dbU->execSqlLong("*", '"GBM_Entity_Relation"', '"ID_Entity"='.$ID_Entity.' AND "ID_Contact_Type"='.$ContactType, 'f');
	return $row;
} 

function getEntityRelation_obsolete($ID_Entity, $ContactType){
	$dbU = new dbSelectUnique;
	$row = $dbU->execSqlLong("*", '"FO_Person_Contact"', '"ID_Entity"='.$ID_Entity.' AND "ID_Contact_Type"='.$ContactType, 'f');
	return $row;
} 

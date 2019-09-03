<?php
/**
 * Class for all entities 
 *
 * Include this file into your code <code>include_once(ROOT_FOLDER."inc/entity.class.php");</code>
 *
 * @author Alec Avedisyan
 * @copyright 2017 Sparkou.com
 * @package GBM
 * @version 3.0.0
 * @subpackage Includes
 */
 
 class Entity{
	 
	var $entity; 
	var $id;
	var $shortCache = array();
	
	function __construct($id='') {

		if($id!=''){
			$this->entity = $this->get($id);
			$this->id = $this->entity['ID_Entity'];
		}else{
			return FALSE;
		}
	}
	
	function get($ID_Entity, $shortCache = TRUE, $secure=TRUE){
		if($secure){
			// Todo
		}
		if(is_array($this->shortCache[$ID_Entity]) && $shortCache){
			$this->entity = $this->shortCache[$ID_Entity];
			if(DEBUG_MODE){ echo msg('Cached ID_Entity: <b>'.$ID_Entity.'</b>', 'c');}
		}else{
			$sql = 'SELECT * FROM GBM_Entity WHERE ID_Entity=?';
			$res = db($sql, $ID_Entity);
			$this->entity = $res;
			$this->id = $this->entity['ID_Entity'];
			$this->shortCache[$ID_Entity] = $this->entity;
		}	   
	   return $this->entity;
   }
   
	function getStatus(){
		return $this->entity['ID_Status'];
	}   
   
	function create($ID_Type, $Name, $minorCols=array()){
		$majorCols = array(	'ID_Type'	=>	$ID_Type,
							'Name'		=>	$Name);
		$cols = array_merge($minorCols,$majorCols);
		return dbInsert($cols, 'GBM_Entity');
   }
   
   	function set($cols=array(), $secure=TRUE){ // TODO make a set secure with Entity that are related or he is author etc ... 
		if($this->id!=''){
			return dbUpdate($cols, 'GBM_Entity', ' ID_Entity='.(int)$this->id );
		}else{
			echo msg('Entity->set() failed, ID_Entity error ! ','e');
			return FALSE;
		}
   }
   
	function last(){
			$sql = 'SELECT max(ID_Entity) as id FROM GBM_Entity';
			$res = db($sql);
			return $res['id'];
	}

	function loadParams(){
		 return json_decode($this->entity['Params'], TRUE);
	 }
   

	function getVal($ID_Entity, $field = 'ID_Status'){
		$sql = 'SELECT '.$field.' FROM GBM_Entity WHERE ID_Entity=?';
		$val = db($sql, $ID_Entity);
		return $val[$field];
	}
   
   /**
	* Build query that update one value for an Entity (FO_Entity)
	* 
	* @param mixed $ID_Entity
	* @param mixed $Field
	* @param mixed $Value
	* @param string $Type
	* @return string
	*/
   function setEntityField($ID_Entity, $Field, $Value, $quotedValue=FALSE){
	   $varType[$Field]	= $Type;
	   $varValues[$Field] 	= $Value;
	   $varValues[SQL_TABLENAME] 		= '"FO_Entity"';
	   $varValues[SQL_WHERE]			= '"ID_Entity"='.$ID_Entity;
	   return makeUpdateQuery($varType, $varValues);
   }
   
   

   /**
	* Get the User informations (FO_SYS_User)
	* 
	* @param mixed $ID_Entity
	* @return array
	*/
   function getEntityUserInfo($ID_Entity){
	   return db('SELECT * FROM "GBM_Attribute" WHERE "ID_Entity"='.$ID_Entity, TRUE);
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
	   $row = $dbU->execSqlLong("*", '"FO_Entity"', '"'.$FieldName.'" ILIKE '.$FieldValue, 'f');
	   return $row;	
   }
   //++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ FIXIT

   
   
   /**
	* addEntityRelation()
	* 
	* @param mixed $ID_Entity
	* @param mixed $ContactType
	* @param mixed $Information
	* @param string $Comment
	* @return string SQL query Insert OR Update
	*/
   function addEntityRelation($ID_Entity, $ContactType, $Information, $Comment ="", $Priority = "", $table = '"FO_Entity_Relation"'){

		   if ($Information<>""){

			   if($table == '"FO_Entity_Relation"'){
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
	   $row = $dbU->execSqlLong("*", '"FO_Entity_Relation"', '"ID_Entity"='.$ID_Entity.' AND "ID_Contact_Type"='.$ContactType, 'f');
	   return $row;
   } 

   function getEntityRelation_obsolete($ID_Entity, $ContactType){
	   $dbU = new dbSelectUnique;
	   $row = $dbU->execSqlLong("*", '"FO_Person_Contact"', '"ID_Entity"='.$ID_Entity.' AND "ID_Contact_Type"='.$ContactType, 'f');
	   return $row;
   } 

	 
	 
 }
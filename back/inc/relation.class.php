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

define(GBM_SYS_Var_Name_Parent, 75);

class Relation{
	
	var $exec=TRUE;
	var $cols;

	 function add($ID_Entity1, $ID_Entity2, $ID_Type, $ID_Status=''){
		$cols = array(		'ID_Entity1'	=>	$ID_Entity1,
							'ID_Entity2'	=>	$ID_Entity2,
							'ID_Type'		=>	$ID_Type,
							'ID_Status'		=>	$ID_Status);
		$this->cols = $cols;
		return dbInsert($cols, 'GBM_Relation', $this->exec); 
	 }
	 
	 function addAsParent($ID_Entity1, $IsParentOfEntity){
		 return $this->add($ID_Entity1, $IsParentOfEntity, GBM_SYS_Var_Name_Parent);
	 }

	 function getByType($ID_Type, $ID_Entity, $Field='ID_Entity1'){
		 $selector['ID_Entity1'] = array(1,2);
		 $selector['ID_Entity2'] = array(2,1);
		 $sql  = 'SELECT ID_Entity'.$selector[$Field][1].' FROM GBM_Relation WHERE ID_Type='.$ID_Type.' AND ID_Entity'.$selector[$Field][0].'='.$ID_Entity;
		 return db($sql);
	 }
	 
	 function getParents($ID_Entity){
		 return $this->getByType(GBM_SYS_Var_Name_Parent, $ID_Entity, 'ID_Entity2');
	 }
	 
	 function getChilds($ID_Entity){
		 return $this->getByType(GBM_SYS_Var_Name_Parent, $ID_Entity);
	 }
	 
	 function setExec2View($val = FALSE){
		 $this->exec = $val;
	 }
	 
 }

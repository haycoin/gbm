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

class Relation{
	
	var $exec=TRUE;
	var $cols;
	var $current = array();
	var $datas = array();

	 function add($ID_Entity1, $ID_Entity2, $ID_Type, $ID_Status=''){
		$cols = array(		'ID_Entity1'	=>	$ID_Entity1,
							'ID_Entity2'	=>	$ID_Entity2,
							'ID_Type'		=>	$ID_Type,
							'ID_Status'		=>	$ID_Status);
		$this->cols = $cols;
		return dbInsert($cols, 'GBM_Relation', $this->exec); 
	 }
	 
	 function addAsParent($ID_Entity1, $IsParentOfEntity){
		 return $this->add($ID_Entity1, $IsParentOfEntity, SYS_VAR_RELATION_PARENT);
	 }

	/**
	 * 
	 * @param type $ID_Entity1
	 * @param type $ID_Entity2
	 * @param type $ID_Type		Can be an array, All values will be OR
	 * @param type $ID_Status
	 * @return type
	 */
	function get($ID_Entity1='', $ID_Entity2='', $ID_Type='', $ID_Status='', $showEntity = FALSE){
		if($ID_Entity1!=''){	$r .= ' AND ID_Entity1 = '.$ID_Entity1;	 }
		if($ID_Entity2!=''){	$r .= ' AND ID_Entity2 = '.$ID_Entity2;	 }
		if($ID_Status!=''){		$r .= ' AND ID_Status = '.$ID_Status;	}
		
		if(is_array($ID_Type)){
			$rOr = implode(', ', $ID_Type);
			$r .= ' AND ( GBM_Relation.ID_Type IN ('.$rOr.')) ';
		}elseif($ID_Type!=''){
			$r .= ' AND GBM_Relation.ID_Type = '.$ID_Type;	
		}
		if($showEntity!==FALSE){
			$sql  = 'SELECT *, GBM_Relation.ID_Type AS R_ID_Type  FROM GBM_Relation, GBM_Entity WHERE '.str_replace('@@@ AND', ' ', '@@@'.$r).' AND GBM_Entity.ID_Entity=GBM_Relation.ID_Entity'.$showEntity;
		}else{
			$sql  = 'SELECT * FROM GBM_Relation WHERE '. str_replace('@@@ AND', ' ', '@@@'.$r);
		}
		
		$res = db($sql);
		$this->current = $res;
		return $res;
	 }
	

	 
	function getByType($ID_Type, $ID_Entity, $Field='ID_Entity1', $showEntity = FALSE){
		if($Field=='ID_Entity1'){
			$I1 = $ID_Entity;
		}else{
			$I2 = $ID_Entity;
		}
		$res = $this->get($I1, $I2, $ID_Type, '', $showEntity);
		$this->current = $res;
		return $res;
	}	 
	
	 
	 function exist($ID_Entity1, $ID_Entity2, $ID_Type){
		 if($ID_Entity1=='' || $ID_Entity2 =='')	 return FALSE;
		$cols = array(		0	=>	$ID_Entity1,
							1	=>	$ID_Entity2,
							2	=>	$ID_Type);
		$this->cols = $cols;
		$res = db('SELECT * FROM GBM_Relation WHERE ID_Entity1=? AND ID_Entity2=? AND ID_Type=? ', $cols ); 
		$this->current = $res;
		if($this->current['ID_Entity1']==$ID_Entity1){
			return TRUE;
		}else{
			return FALSE;
		}
	 }
	 
	 function getParents($ID_Entity){
		 return $this->getByType(SYS_VAR_RELATION_PARENT, $ID_Entity, 'ID_Entity2');
	 }
	 
	 function getChilds($ID_Entity){
		 return $this->getByType(SYS_VAR_RELATION_PARENT, $ID_Entity);
	 }
	 
	 function setExec2View($val = FALSE){
		 $this->exec = $val;
	 }
	 
 }

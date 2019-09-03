<?php
/**
 * Class for attributs
 *
 * Include this file into your code <code>include_once(ROOT_FOLDER."inc/attribute.class.php");</code>
 *
 * @author Alec Avedisyan
 * @copyright 2017 Sparkou.com
 * @package GBM
 * @version 3.0.0
 * @subpackage Includes
 */

class Attribute{
	
	var $types = array(	'phone'=>40,
						'mobile'=>42,
						'email'=>41);
	var $cTypes = array('private'=>72,
						'ICE'=>73,
						'professional'=>74);
	
	
	function add($ID_Entity, $ID_Type, $ID_Type_Sub, $Value, $Comment='', $minorCols=array()){
		$cols = array(		'ID_Entity'		=>	$ID_Entity,
							'ID_Type'		=>	$ID_Type,
							'ID_Type_Sub'	=>	$ID_Type_Sub,
							'Value'			=>	$Value,
							'Comment'		=>	$Comment);
		return dbInsert($cols, 'GBM_Attribute'); 
	}
	
	function addPhone($ID_Entity, $Value, $ID_Type_Sub='', $Comment=''){
		$this->add($ID_Entity, $this->types['phone'], $ID_Type_Sub, $Value, $Comment);
	}
	
	function addEmail($ID_Entity, $Value, $ID_Type_Sub='', $Comment=''){
		$this->add($ID_Entity, $this->types['email'], $ID_Type_Sub, $Value, $Comment);
	}
	
	function addMobilePhone($ID_Entity, $Value, $ID_Type_Sub='', $Comment=''){
		$this->add($ID_Entity, $this->types['mobile'], $ID_Type_Sub, $Value, $Comment);
	}
	
	function get($ID_Entity, $ID_Type='', $ID_Type_Sub=''){
		if($ID_Type==''){
			$sql = 'SELECT * FROM GBM_Attribute WHERE ID_Entity='.$ID_Entity;	
		}elseif($ID_Type_Sub!=''){
			$sql = 'SELECT * FROM GBM_Attribute WHERE ID_Entity='.$ID_Entity.' AND ID_Type='.$ID_Type.' AND ID_Type_Sub='.$ID_Type_Sub;		
		}else{
			$sql = 'SELECT * FROM GBM_Attribute WHERE ID_Entity='.$ID_Entity.' AND ID_Type='.$ID_Type;					
		}
		return db($sql);
	}
	
	
}
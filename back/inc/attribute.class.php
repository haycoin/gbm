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
	
	
	function add($ID_Entity, $ID_Type, $Value, $Comment='', $minorCols=array()){
		$cols = array(		'ID_Entity'		=>	$ID_Entity,
							'ID_Type'		=>	$ID_Type,
							'Value'			=>	$Value,
							'Comment'		=>	$Comment);
		return dbInsert($cols, 'GBM_Attribute'); 
	}
	
	function addPhone($ID_Entity, $Value, $Comment=''){
		$this->add($ID_Entity, $this->types['phone'], $Value, $Comment);
	}
	
	function addEmail($ID_Entity, $Value, $Comment=''){
		$this->add($ID_Entity, $this->types['email'], $Value, $Comment);		
	}
	
	function addMobilePhone($ID_Entity, $Value, $Comment=''){
		$this->add($ID_Entity, $this->types['mobile'], $Value, $Comment);
	}
	
	function get($ID_Entity, $ID_Type=''){
		if($ID_Type==''){
			$sql = 'SELECT * FROM GBM_Attribute WHERE ID_Entity='.$ID_Entity;		
		}else{
			$sql = 'SELECT * FROM GBM_Attribute WHERE ID_Entity='.$ID_Entity.' AND ID_Type='.$ID_Type;					
		}
		return db($sql);
	}
	
	
}
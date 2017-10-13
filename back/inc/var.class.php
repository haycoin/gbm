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
	 
	 var $current = array();
	 var $replace = array(	'i'	=>	'ID_Var',
							'n'	=>	'Name',
							'g'	=>	'Grp',
							'c'	=>	'Comment',
							'v'	=>	'Value',
							'r'	=>	'Reserveed');
	 
	 
	 
	function get($ID_Var, $cached=TRUE){
		if($cached && is_array($_SESSION['Cached_GBM_SYS_Var'][$ID_Var])){
			$this->current = $_SESSION['Cached_GBM_SYS_Var'][$ID_Var]; 
			return $_SESSION['Cached_GBM_SYS_Var'][$ID_Var]; 
		}
		
		$sql  = 'SELECT * FROM GBM_SYS_Var WHERE ID_Var = '.$ID_Var;
		$res = db($sql);
		$this->current = $res; 
		if($cached){
			$_SESSION['Cached_GBM_SYS_Var'][$ID_Var] = $res; 
		}
		return $res; 
	}
	
	function getValue(){
		return $this->current[0]['Value'];
	}

	function getName(){
		return $this->current[0]['Name'];
	}	
	
	function getReserved(){
		return $this->current[0]['Reserved'];
	}
	
	/**
	 * 
	 * @param type $Grp
	 * @param type $name output i, n, g, c or v
	 * @param type $id output i, n, g, c or v
	 * @return type
	 */
	function getByGroup($Grp, $name='n', $id='i'){

		// Name
		$Name = $this->convert2concat($name, $this->replace).' as Name';
		
		// ID_Var
		$ID_Var = $this->convert2concat($id, $this->replace).' as ID_Var';
		
		$sql  = 'SELECT '.$Name.','.$ID_Var.' FROM GBM_SYS_Var WHERE Grp = \''.$Grp.'\' ORDER BY Name';
		return db($sql);
	}
	
	function getById(...$id){
		foreach($id as $ID_Var){
			$this->get($ID_Var);
			$ar[] = $this->current[0];
		}
		return $ar;
	}
	
	function getByValue($value, $Grp = '', $valueCol='n', $outputVal='n'){
		$cols[]	= '%'.$value.'%';
		if($Grp!=''){
			$ral = ' AND Grp=?';
			$cols[]	= $Grp;
		}		
		$sql  = 'SELECT '.$this->replace[$outputVal].' FROM GBM_SYS_Var WHERE '.$this->replace[$valueCol].' LIKE ? '.$ral.' ORDER BY '.$this->replace[$outputVal];
		return db($sql, $cols);		
	}
	
	function getGroups(){
		$sql  = 'SELECT Grp, count(Grp),min(ID_Var),max(ID_Var), min(Name) as example FROM GBM_SYS_Var GROUP BY Grp ORDER BY ID_Var';
		return db($sql);
	}
	
	function generateFile($name, $datas, $prefix='GBM_SYSVAR_'){
		foreach($datas as $val){
			$l .= 'define(\''.strtoupper($prefix.$val['ID_Var']).'\',\''.addslashes($val['Name']).'\'); '."\n";
		}
		$fileHeader = "<?php\n/*\n Automatically generated ".$name." file \n ".date(DATE_RFC2822)."\n*/\n";
		return file_put_contents(ROOT_FOLDER_UI.'inc/generated.'.$name.'.php', $fileHeader.$l);
	}
	
	function convert2concat($str, $replace){
		if(strlen($str)>1){
			$av = str_split($str);
			foreach($av as &$val){	
				if(key_exists($val, $replace)){		
					$Str .= $replace[$val].',';	
				}else{
					$Str .= "'".$val."',";
				}	
			}
			$Str = str_replace(",)", ")", " concat(".$Str.") ");
		}else{
			$Str = $replace[$str];
		}
		return $Str;
	}
	 
 }
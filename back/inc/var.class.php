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
							'r'	=>	'Reserved');
	 
	 
	 
	function get($ID_Var, $cached=TRUE){
		
		$currVal = $_SESSION['Cached_GBM_SYS_Var'][$ID_Var];
		
		if($cached && is_array($currVal) && sizeof($currVal)>0){
			$this->current = $_SESSION['Cached_GBM_SYS_Var'][$ID_Var]; 
			if(DEBUG_MODE){ echo msg('<B>Cached</B> SYS_Var for <B>'.$ID_Var.'</B> = '.print_r($this->current, TRUE), 'c');}
			return $_SESSION['Cached_GBM_SYS_Var'][$ID_Var]; 
		}
		
		$sql  = 'SELECT * FROM GBM_SYS_Var WHERE ID_Var = '.$ID_Var;
		$res = db($sql);
		//pr($res);
		$this->current = $res; 
		if($cached){
			$_SESSION['Cached_GBM_SYS_Var'][$ID_Var] = $res; 
		}
		return $res; 
	}
	
	function getValue($ID_Var=''){
		if($ID_Var!=''){	$this->get($ID_Var); }
		return $this->current['Value'];
	}

	function getName($ID_Var=''){
		if($ID_Var!=''){	$this->get($ID_Var); }
		return $this->current['Name'];
	}	
	
	function getReserved($ID_Var=''){
		if($ID_Var!=''){	$this->get($ID_Var); }
		return $this->current['Reserved'];
	}
	
	/**
	 * 
	 * @param type $Grp
	 * 
	 * i = ID_Var, n = Name, g = Group, c = Comment, v = Value 
	 * all = All cols "*"
	 * @param type $name output all, i, n, g, c or v
	 * @param type $id output i, n, g, c or v
	 * @return type
	 */
	function getByGroup($Grp, $name='n', $id='i'){
		
		if($name=='all'){
			$selCol = '*';
		}else{
			// Name
			$Name = $this->convert2concat($name, $this->replace).' as Name';

			// ID_Var
			$ID_Var = $this->convert2concat($id, $this->replace).' as ID_Var';
			$selCol = $Name.','.$ID_Var;
		}
			
		$sql  = 'SELECT '.$selCol.' FROM GBM_SYS_Var WHERE Grp=? ORDER BY Name';
		return dbm($sql, array($Grp));
	}
	
	function getById(...$id){
		foreach($id as $ID_Var){
			$this->get($ID_Var);
			$ar[] = $this->current;
		}
		return $ar;
	}
	
	function getByValue($value, $Grp='', $valueCol='n', $outputVal='n', $valOnly=FALSE){
		$cols[]	= '%'.$value.'%';
		if($Grp!=''){
			$ral = ' AND Grp=?';
			$cols[]	= $Grp;
		}
		
		if(!is_array($_SESSION['Cached_fct']['getByValue'][$value.$Grp.$valueCol.$outputVal.$valOnly])){
			$sql  = 'SELECT '.$this->replace[$outputVal].' FROM GBM_SYS_Var WHERE '.$this->replace[$valueCol].' LIKE ? '.$ral.' ORDER BY '.$this->replace[$outputVal];
			$res = db($sql, $cols);
			$_SESSION['Cached_fct']['getByValue'][$value.$Grp.$valueCol.$outputVal.$valOnly] = $res;
		}else{
			$res = $_SESSION['Cached_fct']['getByValue'][$value.$Grp.$valueCol.$outputVal.$valOnly];
		}
		
		if($valOnly){
			return $res[$this->replace[$outputVal]];
		}else{
			return $res;
		}	
	}
	
	function getGroups(){
		$sql  = 'SELECT Grp, count(Grp),min(ID_Var),max(ID_Var), min(Name) as example FROM GBM_SYS_Var GROUP BY Grp ORDER BY ID_Var';
		return db($sql);
	}
	
	function generateFile($name, $datas, $prefix='SYSVAR_'){
		return $this->saveFile($name, $this->generateDefines($datas, $prefix));
	}
	
	function generateDefines($datas, $prefix='SYSVAR_', $delimiter = "'"){
		$from =	array(' ',	'"',	"'");
		$to =	array('_',	'',		'');

		foreach($datas as $val){
			$l .= 'define(\''. str_replace($from, $to, strtoupper($prefix.$val['ID_Var'])).'\','.$delimiter.addslashes($val['Name']).$delimiter.'); '."\n";
		}
		return $l;
	}
	
	function saveFile($name, $lines){
		$fileHeader = "<?php\n/*\n Automatically generated ".$name." file \n ".date(DATE_RFC2822)."\n*/\n";
		return file_put_contents(ROOT_FOLDER_UI.'inc/generated.'.$name.'.php', $fileHeader.$lines);
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
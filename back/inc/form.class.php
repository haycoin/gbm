<?php

class Form{
	
	var $style;
	var $output;
	var $stdType = array('text', 'password', 'submit', 'reset', 'radio', 'checkbox', 'button', 'color', 'number', 'range', 'time');
	
	
	function addField($Name, $Label='', $Type='text', $Value='', $params=''){
		
		if($this->style !=''){
			
		}else{
			$l = '<label for="'.$Name.'">'.$Label.'</label>';
		}
		
		
		if(in_array($Type, $this->stdType)){
			$o = '<input name="'.$Name.'" value="'.$Value.'" type="'.$Type.'"  '.$params.'>';
		}
		
		$this->output .= $l.$o;
		return $l.$o; 
	}
	
	function get(){
		return $this->output;
	}
	
}
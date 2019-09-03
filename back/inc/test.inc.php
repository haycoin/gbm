<?php

function testEqual(&$args, $val1, $val2, $comment=''){
	$args['counterTest']++;
	if($val1!=$val2){
		$args['counterError']++;
		$val = colorize(DICO_WORD_ERROR);
	}elseif(!($val1===$val2)){
		$args['counterWarning']++;
		$args['verbose'] .= 'Not strict equal, check type for <b>'.$args['currentFct'].'</b><br>';
		$val = DICO_WORD_WARNING;
	}else{
		if($comment!=''){
			$val1 = $comment; 
		}
	}
	testSaveResult($args, $val1);
}

function testNotEmpty(&$args, $val, $saveValue = TRUE){
	$args['counterTest']++;
	if(empty($val)){
		$args['counterError']++;
		$val = colorize(DICO_WORD_ERROR);
	}
	if(!$saveValue){
		$val = 'Set $saveValue to TRUE to see result';
	}
	testSaveResult($args, $val);
}

function testArrayEmpty(&$args, $val){
	$args['counterTest']++;
	foreach($val as $var){
		if(!empty($var)){
			$args['counterError']++;
			$res .= $var.'<br>';
		}
	}
	testSaveResult($args, colorize($res));
}


function testResult($args, $verbose = TRUE){
	$args['outpout'] .= 'Tests: '.$args['counterTest'].' | Warning: '.$args['counterWarning'].' | Errors: '.colorize($args['counterError']).'<br>';
	return $args;
}

function testFlatName($pathFile){
	return 'test_'.str_replace(array('.', '/'), '_', $pathFile);
}

function testSaveResult(&$args, $val){
	$args['res'][$args['currentFct']] = $val;
}

function testSetFilePath(&$args, $pathFile){
	$fctlst = array();
	include_once ROOT_FOLDER_TEST.$pathFile.'.test.php';
	$fctsname = testFlatName($pathFile);
	$fctname = $fctsname.'_fctlst';
	if(! function_exists($fctname)){
		$args['verbose'] = testExample($fctname, $fctsname);
		$args['counterError']++;
		return $args;
	}

	eval('$fctlst = $fctname();');
	//pr($fctlst);pr($fctname);
	foreach($fctlst as $fct){
		$args['currentFct'] = $fct;
		eval(''.$fct.'($args);');
	}
	
	$args['verbose'] .= '<BR>&nbsp;&nbsp;&nbsp;&nbsp; - Tested file: '.$pathFile.'';

}

function colorize($val, $color='#FF0000'){
	return '<span style="font-weight:bold;color:'.$color.'">'.$val.'</span>';
}

function testExample($fctname, $fctsname){
	
$path = str_replace(array('test_back_', 'inc_', '_'), array('', 'inc/', '.'), $fctsname).'.php';	
	
	return "
Error, Create function, Replace TRUE, FALSE for your === test: 
<pre style='border: 1px dashed #AAAAAA; color:#3333FF;'>include_once ROOT_FOLDER.\"".$path."\";

function ".$fctname."(){
	".'$testFileList[]	=\''.$fctsname.'_yourFct\';'."
	return $"."testFileList;
}

function ".$fctsname.'_yourFct(&$args){'."
	testEqual($"."args,TRUE,FALSE);
	//testNotEmpty($"."args, $"."val);
}</pre>";
}
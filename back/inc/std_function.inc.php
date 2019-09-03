<?php
/**
 * Standard, universial and usefull function 
 *
 * Condition for the functions to stand here, no use of realtive path, like ROOT_FOLDER
 *
 * @author Alec Avedisyan
 * @copyright 2017 Sparkou.com
 * @package GBM
 * @version 1.8.1
 */


/*
 * Most used function, this is a print_r very practical for debug
 * 
 * @param	mixed	$array
 * @param	bool	$display	If false return value instead of printing it
 */
function pr($array, $display = TRUE){
	$output =  "<PRE>".print_r($array, TRUE)."</PRE>";
	
	if($display){
		echo $output;	
	}else{
		return $output;
	}
	
}

/**
 * 
 * @param type $msg
 * @param type $type
 * @param type $framework	gbm or bs for bootstrap
 * @return boolean
 */
function msg($msg, $type='info', $framework='gbm'){
		if($type=='error' || $type=='e'){ 
			$cl = ' msgError';
			$bs = ' alert-danger';
		}elseif($type=='delete' || $type=='d'){ 
			$cl = ' msgDelete';
			$bs = ' alert-warning';
		}elseif($type=='update' || $type=='u'){ 
			$cl = ' msgUpdate';
			$bs = ' alert-info';
		}elseif($type=='insert' || $type=='i'){ 
			$cl = ' msgInsert';
			$bs = ' alert-info';
		}elseif(($type=='cached' || $type=='c')){
			if(!DEBUG_MODE_SHOW_CACHED){return FALSE;}
			$cl = ' msgCached';
			$bs = ' alert-secondary';
		}elseif($type=='debug' || $type=='b'){ 
			$br = debug_backtrace();
			$msg .= ' <span style="color:#118278;">#'.$br[0]['line'].'</span>';
			$cl = ' msgDebug';
			$bs = ' alert-secondary';
		}elseif($type=='report' || $type=='r'){
			if($msg == ''){	$msg = DICO_ERR_PERMISSION;}
			 $br = debug_backtrace();
			echo '<div class="msgInfo msgError">'.$msg.' #'.$br[0]['line'].'<BR> IP : '.$_SERVER['REMOTE_ADDR'].'</div>'; exit(); // TODO Like noPermission , must add Reporting into SYS_Cache
		}elseif($type=='fatal' || $type=='f'){ 
			echo '<div class="msgInfo msgError">'.$msg.'</div>'; exit();
		}
		if($framework=='gbm'){
			return '<div class="msgInfo'.$cl.'">'.$msg.'</div>';
		}else{
			return '<div class="alert'.$bs.'">'.$msg.'</div>';
		}
}


if(''==$_SESSION['lang']){
	$_SESSION['lang'] = SYS_LANGUAGE_DEFAULT;
}elseif(''!=$_GET['lang']){
	$_SESSION['lang'] = $_GET['lang'];
}

function getLangInfo($lang='', $output='i'){
	if(''==$lang){
		$lang = $_SESSION['lang'];
	}
	$sv = new SYS_Var();
	return $sv->getByValue($lang, 'Language', 'v', $output, TRUE);
}

/*
 * Debug
 */
function _d($txt=''){
	echo msg('Debug :'.$txt.'<hr>'.pr(debug_backtrace(),FALSE), 'd');
}


function getFromUrl($url){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_URL,$url);
	$res=curl_exec($ch);
	curl_close($ch);
	return $res;
}



/********************* UP New function *********** Down CHeck if util ********************3/







/**
 * Unhackable string
 * Performe some changes on the string assuming that it must be only a value. Avoid SQL injection
 * 
 * @param mixed $str
 * @param mixed $maxLength
 * @return string
 */
function unHack($str, $maxLength){
	$newStr = substr ($str, 0, $maxLength);
	$newStr = str_replace(array("\"","'",";","(",")"), "",$newStr);
	return $newStr;
}

/**
 * Remove \" usefull into database case.
 * 
 * @param mixed $str
 * @return string
 */
function removeSlashQuote($str){
	$newStr = str_replace('\"', '"',$str);
	$newStr = str_replace("\'", "'",$newStr);
	$newStr = str_replace('"\\', '"',$newStr);
	return $newStr ;
}

/**
 * Addquotes , like the addslashes but usfull javascript
 * 
 * @param mixed $string
 * @return string
 */
function addquotes($string){
	return str_replace("'","´",$string);
}


/**
 * @access private 
 * @param mixed $msg
 * @return string
 */
function addSparkouMsgFooter($msg){
	$footLen = 160-strlen($msg); 
	$footer = " www.sparkou.com";
	if ($footLen >= strlen($footer)){
		$msg .= $footer;
	}else{
		$footer = " sparkou.com";
		if ($footLen >= strlen($footer)){
			$msg .= $footer;
		}else{
			$footer = " sparkou";
			if ($footLen >= strlen($footer)){
				$msg .= $footer;
			}
		}
	}

    return $msg;
}

/**
 * Remove all accent or not regular (ASCII) char
 * 
 * @param mixed $str
 * @return string
 */
function desaccentise2($str) {
  $str = htmlentities($str, ENT_COMPAT, "UTF-8");
  $str = preg_replace('/&([a-zA-Z])(uml|acute|grave|circ|tilde);/','$1',$str);
  return html_entity_decode($str);
}

/**
 * Remove all accent or not regular (ASCII) char
 * @deprecated Use desaccentise2
 * @see desaccentise2()
 * @param mixed $string
 * @return string
 */
function desaccentise($string)
{
   $accent  ="ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿ";
   $noaccent="aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuyyby";
   //$noaccent="AAAAAAACEEEEIIIIDNOOOOOOUUUUYBSaaaaaaaceeeeiiiidnoooooouuuyyby";
   return strtr(trim($string),$accent,$noaccent);
} 



/**
 * Experimental ! 
 * @param mixed $contents
 * @param	integer	$splitSize In case of large file this can be handy to split the processing. this avoid to use large memory. 	
 * @return string
 */
function convertASCII2UTF8($contents, $splitSize = 100000){

	$output = "";
	$ucode =		array(chr(130),	chr(138),	chr(135), 	chr(136), 	chr(129), 	chr(139),	chr(133),	chr(131),	chr(137),	chr(140),	chr(132),	chr(147),	chr(148));
	$convertion_val=array("é",		'è',		'ç', 		'ê', 		'ü',		'ï', 		'à', 		'â', 		'ë', 		'î', 		'ä', 		'ô', 		'ö');
	$convertion = 	array(chr(233), chr(232),	chr(231), 	chr(234), 	chr(252), 	chr(239),	chr(224),	chr(226), 	chr(235),	chr(238),	chr(228),	chr(244),	chr(246));

	for ($i =0; $i < strlen($contents); $i = $i + $splitSize){
		$output  .= str_replace($ucode, $convertion, substr($contents,$i,$splitSize));		
	}

	return $output; 
}




/**
 * Change to HTML char, but keep < > and "
 * for example : é -> &eacute;
 * 
 * @param mixed $string
 * @return string
 */
function transformHtmlChar($string)
{
	if(SYS_ENCODING_DEFAULT != "NO_ENCODING"){
		$trans = get_html_translation_table(HTML_ENTITIES);
		$encoded = strtr($string, $trans);
	}else{
		$encoded = $string;
	}
	$encoded = str_replace("&lt;","<",$encoded);
	$encoded = str_replace("&gt;",">",$encoded);
	$encoded = str_replace("&quot;",'"',$encoded);	
	return $encoded;
} 


function unstreetize($string) // Remove Street related keyword
{
	// Street (St)  	rue (rue)
	// Avenue (Ave) 	avenue (av)
	// Boulevard (Blvd) 	boulevard (boul.)
	// use : DICO_VOCAB_STREET
} 


/**
 * Write an error message
 * 
 * @param mixed $message
 * @param bool $showIT
 * @return string
 */
function write_error($message, $display = TRUE){
	if ($display){
		echo '<strong><font color="#FF0000" size="2" face="Arial, Helvetica, sans-serif">'.$message.'</font></strong><BR>';
		return TRUE;
	}else{
		return '<strong><font color="#FF0000" size="2" face="Arial, Helvetica, sans-serif">'.$message.'</font></strong>';
	}
}

/**
 * Write debug message
 * 
 * @param mixed $message
 * @return string
 */
function write_debug($message = "", $included = TRUE, $location = TRUE){

	if($location){
		//$output = '<BR><font color="#0000FF" size="2" face="sans-serif">'.__FILE__." : ".__LINE__.'</font>';
		ob_start();        
        debug_print_backtrace();
		$output =  "<PRE>".ob_get_contents()."</PRE>";
        ob_end_clean(); 

	}
	
	if($included){
		$output .= pr(get_included_files(), FALSE);
	}

	return '<div style="border:solid:#FF6600; background-color:#FFCC33; ">'.$message.$output.'</div>';
}

function wd($message,  $included = TRUE, $location = TRUE){	echo write_debug($message,  $included , $location );}


/**
 * Write trace message (flush the message)
 * 
 * @param mixed $message
 */
function trace($message){
	echo '	<font color="#AAAAAA" size="1" face="Arial, Helvetica, sans-serif">Trace
			<font color="#000FF0">'.$message.'</font>
			<font color="#AAAAAA"> -- '.$_SERVER['PHP_SELF']."</font></font><BR>\n";
	ob_flush();
	flush();
}

/**
 * In case of Unauthorised Action
 * You dont have permissions... 
 * This access will be reported to the administrator.
 */
function unauthorisedAction(){
	// Add real report, Table FO_SYS_Cache
	echo "<b>You don't have permissions... </b><br>";
	echo "This access will be reported to the administrator.";
	exit();
}

/**
 * Make an A HREF link
 * Mostly used into dico.inc.php
 * @param mixed $Path
 * @param mixed $Text
 * @return string
 */
function MakeLink($Path,$Text){
	$link = "<a href='$Path'>$Text</a>";
	return $link;
}

/**
 * Click here icon
 * 
 * @param mixed $Text
 * @return string
 */
function clickHere($Text){
	$add = "<div><img src='".ROOT_URI."inc/img/animated-arrow2.gif' border='0'> ".$Text." </div>";
	//$add = "<div style='vertical-align:middle'><img src='".ROOT_URI."inc/img/animated-dot.gif'> ".$Text." <img src='".ROOT_URI."inc/img/animated-dot.gif'></div>";	
	return $add;
}

/**
 * Add link to clickHere function
 * 
 * @param mixed $Path
 * @param mixed $Text
 * @return string
 */
function MakeLinkBlink($Path,$Text){
	$link =  clickHere("<a href='$Path'>".$Text."</a>");
	return $link;
}


/**
 * microtime_float()
 * 
 * @return float
 */
function microtime_float(){
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

/**
 * Fake the browser type
 * @category used?
 * @param mixed $url
 * @return string
 */
function urlOpen($url, $size = 8192)
{
        ini_set('user_agent',$_SERVER['HTTP_USER_AGENT']);
        $dh = fopen("$url",'r');
        $result = fread($dh,$size);                                                                                                                            
        return $result;
} 


/**
 * Update the value of an input field
 * @param mixed $Text
 * @param mixed $Field_Location
 */
function UpdateJSField($Text,$Field_Location){
	echo "\n <script type='text/javascript'> \n";
	echo $Field_Location.'.value='."'".$Text."';\n";
	//echo "document.getElementById('BOX_MESSAGE_USERDIV').innerHTML = '".$Text."'";
	echo "\n </script>\n";
}


/**
 * Fonction that show true or false (see also true_or_false function into back_FO_UI)
 * @param mixed $value
 * @return string
 */
function trueORfalse($value){
	if ($value){
		$output =  '<strong><font color="#006600">'.DICO_WORD_OK.'</font> </strong>';
	}else{
		$output =  '<strong><font color="#FF0000">'.DICO_WORD_ERROR.'</font></strong>';
	}
	return $output;
}

/**
 * For security reason use header_css_ui()
 * @see header_css_ui
 * @param string $model
 */
function header_css($model = "inscription", $secure = FALSE){
	if ($secure){
		echo '<link href="'.ROOT_URI_UI.'css/'.$model.'.inc.css.php" rel="stylesheet" type="text/css">';		
	}else{
		echo '<link href="'.ROOT_URI.'css/'.$model.'.inc.css.php" rel="stylesheet" type="text/css">';
	}
}

/**
 * header_css_ui()
 * 
 * @param string $model
 */
function header_css_ui($model = "model_std"){
	echo '<link href="'.ROOT_URI_UI.'css/'.$model.'.inc.css.php" rel="stylesheet" type="text/css">';
}


/**
 * Redirect SESSION Keep
 * 
 * @param mixed $location
 * @param bool $include
 */
function RedirectSessionKeep( $location, $include = FALSE){
   if (!$include){
			ini_set("allow_url_include", "on");
   }
   
   $sid = session_id();
   if( strpos( $location, "?" ) > 0 )
	   $separator = "&";
   else
	   $separator = "?";
   $fixed = $location . $separator . PHPSESSID."=".$sid;
   header("Location: ".$fixed );
   //print_r( headers_list());

	return;
}

/**
 * JS Redirect
 * 
 * @param type $page
 * @param string $timeMil
 * @param type $keepSession
 * @param type $targetId
 * @param type $functionName
 * @return type
 */
function redirect($page,$timeMil='0', $keepSession = FALSE, $targetId = "", $functionName="redirect"){
	$script .= "\n<script>";
	if ($targetId == ""){
		$script .= "function ".$functionName."() ";
		if ($page <> ""){
			if ($keepSession){
				$script .= "{window.location='".$page."?".PHPSESSID."=".$sid."' ";
				}else{
				$script .= "{window.location='".$page."' ";
				}
		}else{
			$script .= "{location.reload() ";
		}
		$script .= "} \n setTimeout('redirect()',".$timeMil."); ";
	}else{
		$script .= "function ".$functionName."(){ ";
		$script .= " var currentTime = new Date(); ";		
		$script .= " window.parent.document.getElementById('".$targetId."').src = window.parent.document.getElementById('".$targetId."').src+\"?\"+currentTime.getTime();}";
		$script .= " setTimeout('".$functionName."()',".$timeMil."); "."\n";
	}
	$script .= "</script>"."\n";
return $script;
}



/**
 * Make a encriped Key
 * @access private
 * @param mixed $var
 * @return string
 */
function makeKey($var, $keyPre = "dj934hsHHd2w" ){
	$var = str_pad($var, 24, $keyPre, STR_PAD_LEFT);
	$var = md5($var);
	return $var;
}

/**
 * checkKey()
 * @access private
 * @param mixed $var
 * @param mixed $key
 * @return bool
 */
function checkKey($var, $key, $keyPre = "dj934hsHHd2w"){
	if (makeKey($var,$keyPre)==$key){
		return TRUE;
	}else{
		return FALSE;
	}
}

/**
 * @access private
 */
function makeLoginDKey($IDE, $time = ""){
	include_once(ROOT_FOLDER."FO_Entity/functions.inc.php");
	if($time==""){	$time = time();	}
	$User = getEntityUserInfo($IDE);
	$_SESSION["Entity"] = $User;
	if($User["ID_Entity"]!=""){
		if($User["ID_FO"]==""){	write_error("ID_FO Not defined ! Error Code : 2w34s989s438uru492 ");	}else{ $_SESSION["ID_FO"] = $User["ID_FO"];}
		return makeKey($User["Nickname"].$IDE.$time).$time;		
	}else{
		return FALSE;
	}
}

function checkLoginDKey($IDE, $k, $maxDelay = 2592000){
	$md5 = substr($k,0,32);
	$time = substr($k,32);
	if(makeLoginDKey($IDE, $time)==$k){
		if ($time>(time()-$maxDelay)){
			return TRUE;
		}else{
			return FALSE;
		}
	}else{
		return FALSE;
	}
			
}


/**
 * Delete all the session values but keep only ID_User, ID_Entity and ID_FO
 * Seems to have a problem ?? Should be Rights in stead od Right ? 
 */
function sessionRefresh(){    
	$tempID_User 	= $_SESSION["ID_User"];
	$tempID_FO 		= $_SESSION["ID_FO"];
	$tempID_Entitiy = $_SESSION["ID_Entity"];
	$tempID_Right	= $_SESSION["Right"];
	$tempID_Rights	= $_SESSION["Rights"];
	session_destroy();
	session_start();
	$_SESSION["ID_User"] = $tempID_User ;
	$_SESSION["ID_FO"] = $tempID_FO ;
	$_SESSION["ID_Entity"] = $tempID_Entitiy;
	$_SESSION["Right"] = $tempID_Right;
	$_SESSION["Rights"] = $tempID_Rights;
	
}

/**
 * Fill an array from $start number to $end number (included)
 * the id of the array and it's value are the same
 * 
 * @param int $start
 * @param int $end
 * @return string
 */
function fill_range($start,$end, $step = 1){
	foreach(range($start, $end, $step) as $val) {
    	$result[]=array($val,$val);
	}
	return $result;
}

/**
 * Recursive sort of the arrawy
 * @param mixed $array
 */
function recusive_sort(&$array)
  {
  ksort($array);
  foreach(array_keys($array) as $k)
    {
    if(gettype($array[$k])=="array")
      {
      recusive_sort($array[$k]);
      }
    }
  }
  

/**
 * reIndexArray()
 * Recursive re-num the array
 * @param mixed $array
 * @return array
 */
function reIndexArray($array){
	foreach ($array as $blockIndex => $value){
		if (is_array($value)){
			$newArray[] = reIndexArray($value);
		}else{
			$newArray[] = $value;
		}
	}
	return $newArray;
}  
  

/**
 * This function reformat data with given mask
 * @param mixed $data
 * @param mixed $IOFormat
 * @return string
 */
function dataFormat($data, $IOFormat){
	if (is_array($IOFormat)){
		mb_ereg($IOFormat[0], $data, $resultArray);
		preg_match_all("`@([0-9]{1,2})@|[:,/._/-]`",   $IOFormat[1],   $out, PREG_SET_ORDER);
		foreach ($out as &$value) {
			if (!(is_null($value[1]))){
				// @x@
				$output .= $resultArray[$value[1]+1];
			}else{
				$output .= $value[0];
			}
		}
	}else{
		$output = $data;
	}
	return $output; 
}

/**
 * dataFormatZZZ()
 * @category used?
 * @param mixed $data
 * @param mixed $IOFormat
 * @return string
 */
function dataFormatZZZ($data, $IOFormat){
	if (is_array($IOFormat)){
		mb_ereg($IOFormat[0], trim($data), $resultArray);
		print_r($resultArray);
		preg_match_all("`@([0-9]{1,2})@|[:,/._/-]`",   $IOFormat[1],   $out, PREG_SET_ORDER);
		foreach ($out as &$value) {
			//echo $value[1];
			if (!(is_null($value[1]))){
				// @x@
				$output .= $resultArray[$value[1]+1];
			}else{
				$output .= $value[0];
			}
		}
	}else{
		$output = $data;
	}
	return $output; 
}



/**
 * Get the SVN current version number
 * 
 * @return int|string Either the number or Not versioned if not found
 */
function svnVersion(){
	if(file_exists(ROOT_FOLDER.".svn/entries")){
		$dh = fopen(ROOT_FOLDER.".svn/entries",'r');
		$result = fread($dh,100); 
		$ra = explode("\n",$result);
		$version = str_replace("","\n",$ra[3]);
		fclose($dh);
		return $version;
	}else{
		return " Not versioned ";
	}
}


/**
 * Reverse function of bin2hex (nativly existing into php)
 * @param mixed $str
 * @return string
 */
if(!function_exists('hex2bin')){
function hex2bin($str) {
    $build = '';
    while(strlen($str) > 1) {
        $build .= chr(hexdec(substr($str, 0, 2)));
        $str = substr($str, 2, strlen($str)-2);
    }
    return $build;
}}

function hex2str($hex) {
    $str = '';
    for($i=0;$i<strlen($hex);$i+=2){
		$str .= chr(hexdec(substr($hex,$i,2)));
	}
    return $str;
}


//	} 

/**
 * alternColor()
 * 
 * @param mixed $color1
 * @param mixed $color2
 * @param mixed $colorCurr
 * @return string
 */
function alternColor($color1,$color2,$colorCurr){
	if ($color1 == $colorCurr){
		return $color2;
	}else{
		return $color1;
	}
}


function printfReadable($row){
	foreach ($row as $key => $value) {
		if(!is_numeric($key) && $value<>"" && !mb_ereg("_TypeSQL$",$key) && !mb_ereg("^SQL_",$key) && !mb_ereg("^Allow",$key)){
			$o .=  "<b>$key </b>: $value<br />\n";
		}
	}
	return $o;
}


/**
 * Put in session var all the variables into $currentArray
 * 
 * There is an exception for ID_Goods, ID_Entity, ID_Doc
 * 
 * @todo This function is not running
 * @param mixed $currentArray
 * @return void
 */
function sessionize($currentArray){
	
}

/**
 * Build a file name. 
 * 
 * @return file name
 */
function createFileName(){
	$_SESSION["dlCurrentFileName"] = md5($_SESSION["ID_Entity"].$_SESSION["dlKeyword"].$_SESSION["ID_User"].$_SESSION["ID_Goods"]);
	return $_SESSION["dlCurrentFileName"];
}


function addIcon($filename, $attribute = "border='0'", $svgFigure=FALSE){
	if($svgFigure){
		$path_parts = pathinfo($filename);
		$ext = $path_parts['extension'];
		if($ext==''){	$ext = 'svg'; $filename = $filename.'.'.$ext;}
		if($ext=='svg'){
			$pre = '<figure><span>';
			$post = '</span></figure>';
		}
	}
	
	return $pre.'<img src="'.ROOT_URI_UI.'img/icon/'.$filename.'" '.$attribute.' />'.$post;
}


// Direct submit
function customCheckbox($name, $imgOn, $imgOff, $defautVal = "Off", $imgRollover = ""){

	if($_GET["customCheckbox_".$name] == ""){
		if ($_SESSION["customCheckbox_".$name] == "On"){
			$status = "On";
			$img = $imgOn;
			$reverseVal = "Off";
		}else{
			$status = "Off";
			$img = $imgOff;
			$reverseVal = "On";
		}
	}
	
	if ($_GET["customCheckbox_".$name] == "On" ){
		$status = "On";
		$reverseVal = "Off";
		$img = $imgOn;
		$_SESSION["customCheckbox_".$name] = "On";
	}
	if ($_GET["customCheckbox_".$name] == "Off" ){
		
		$status = "Off";
		$reverseVal = "On";
		$img = $imgOff;
		$_SESSION["customCheckbox_".$name] = "Off";
	}
	
	if (mb_eregi("^(.*customCheckbox_".$name."=)(On|Off)(.*)$",$_SERVER["REQUEST_URI"],$url )){
		$path = 'window.location = \''.$url[1].$reverseVal.'\'';
	}else{
		$path = 'window.location = \''.$_SERVER["REQUEST_URI"].'&customCheckbox_'.$name."=".$reverseVal.'\'';
	}
	
	
	return '<img style="cursor:pointer;" border="0" src="'.$img.'" onclick="document.getElementById(\'customCheckbox_'.$name.'\').value=\''.$reverseVal.'\'; '.$path.'">
			<input name="customCheckbox_'.$name.'" id="customCheckbox_'.$name.'" type="hidden" value="'.$status.'">';

}

function customDateSelect($name, $defautVal = "ALL"){

	if($_GET["customCheckbox_".$name] != ""){
		$_SESSION["customCheckbox_".$name] = $_GET["customCheckbox_".$name];
	}else{
		if ($_SESSION["customCheckbox_".$name] == ""){
			$_SESSION["customCheckbox_".$name] = "ALL";
		}	
	}
	
	$output = "";
	
	if (mb_eregi("^(.*customCheckbox_".$name."=)(ALL|D|M|Y)(.*)$",$_SERVER["REQUEST_URI"],$url )){
		$path = 'window.location = \''.$url[1];
	}else{
		$path = 'window.location = \''.$_SERVER["REQUEST_URI"].'&customCheckbox_'.$name."=";
	}
	
	if ($_SESSION["customCheckbox_".$name] == "ALL"){ 
		$output .= '<img border="0" src="'.ROOT_URI_UI.'img/icon/round_grey.png" >'; // 		$output .= '<img border="0" src="'.ROOT_URI_UI.'img/icon/date_group_all.png" >';
	}else{
		$output .= '<img style="cursor:pointer;" border="0" src="'.ROOT_URI_UI.'img/icon/round_grey.png" onclick="'.$path.'ALL\'">'; // 		$output .= '<img style="cursor:pointer;" border="0" src="'.ROOT_URI_UI.'img/icon/date_group_all_off.png" onclick="'.$path.'ALL\'">';
	}
	
	if ($_SESSION["customCheckbox_".$name] == "D"){		
		$output .= '<img border="0" src="'.ROOT_URI_UI.'img/icon/date_group_day2.png" >'; // $output .= '<img border="0" src="'.ROOT_URI_UI.'img/icon/date_group_day.png" >';
	}else{
		$output .= '<img style="cursor:pointer;" border="0" src="'.ROOT_URI_UI.'img/icon/date_group_day_off.png" onclick="'.$path.'D\'">';
	}
	
	if ($_SESSION["customCheckbox_".$name] == "M"){
		$output .= '<img border="0" src="'.ROOT_URI_UI.'img/icon/date_group_month2.png" >'; // $output .= '<img border="0" src="'.ROOT_URI_UI.'img/icon/date_group_month.png" >';
	}else{
		$output .= '<img style="cursor:pointer;" border="0" src="'.ROOT_URI_UI.'img/icon/date_group_month_off.png" onclick="'.$path.'M\'">';
	}
	
	if ($_SESSION["customCheckbox_".$name] == "Y"){
		$output .= '<img border="0" src="'.ROOT_URI_UI.'img/icon/date_group_year2.png" >'; // 		$output .= '<img border="0" src="'.ROOT_URI_UI.'img/icon/date_group_year.png" >';
	}else{
		$output .= '<img style="cursor:pointer;" border="0" src="'.ROOT_URI_UI.'img/icon/date_group_year_off.png" onclick="'.$path.'Y\'">';
	}
	
	
	return $output .= '<input name="customCheckbox_'.$name.'" id="customCheckbox_'.$name.'" type="hidden" value="'.$status.'">';
	
}



function serializeArray($arr){		return bin2hex(gzcompress(serialize($arr)));}
function unserializeArray($str){	return unserialize(gzuncompress(hex2bin($str)));}

function unserializeForBlock($str, $showPre= TRUE){
	$str = str_replace("'",'"',$str);
	if($showPre){
		$str = "<PRE>".print_r(unserialize($str),TRUE)."</PRE>";
	}else{
		$str = print_r(unserialize($str),TRUE);		
	}
	$str = str_ireplace("array\n","",$str);
	$str = str_ireplace("(\n","",$str);	
	$str = str_ireplace(")\n","",$str);	
	return $str;
}



/**
* check if a data is serialized or not
*
* @param mixed $data    variable to check
* @return boolean
*/
function is_serialized($data){
    if (trim($data) == "") {
        return false;
    }
    if (preg_match("/^(i|s|a|o|d)(.*);/si",$data)) {
        return true;
    }
    return false;
} 

function nb($val){
	return number_format($val, 2, '.', "'");
}




/*** TODO *** DECREPATED USE CLASS TABLE */
function makeTableInit(){
	return '<link href="'.ROOT_SRC_DATATABLES_CSS.'" rel="stylesheet" type="text/css"/>';
}

/*** TODO *** DECREPATED USE CLASS TABLE */
function makeTable($body, $header = '',$footer = '', $tableId = 'tid' , $params='class="display" cellspacing="0"' ){
	if(sizeof($body)==0){
		return FALSE;
	}
	if($header!=''){		$head = '<thead><tr><th>'.implode('</th><th>', $header).'</th></tr></thead>'; 	}
	if($footer!=''){		$foot = '<tfoot><tr><th>'.implode('</th><th>', $footer).'</th></tr></tfoot>'; 	}
	foreach($body as $vals){$trs .= '<tr><td>'.implode('</td><td>', $vals).'</td></tr>';}
	$trs = '<tbody>'.$trs.'</tbody>';
	return '<table id="'.$tableId.'" '.$params.' >'.$head.$trs.$foot.'</table>';
}

/*** TODO *** DECREPATED USE CLASS TABLE */
function makeTableScript($additionalParams = '', $tableId='tid', $defaultParams = ' "paging":false, "ordering":true, "info":false, "select":false, "searching":false '){
	if($additionalParams!=''){		$additionalParams .= ',';	}
	return '<script type="text/javascript" class="init">$(document).ready(function() {	$(\'#'.$tableId.'\').DataTable({'.$additionalParams.$defaultParams.'});} );</script>';
}
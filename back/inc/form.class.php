<?php
include_once ROOT_FOLDER_UI.'inc/generated.pattern.php';
class Form{
	
	var $name;
	var $style;
	var $styles = array('div'=>array('<div>','</div><div>','</div>')); 
	var $output;
	var $stdType = array(  'submit', 'reset', 'radio', 'checkbox', 'button', 'color', 'number', 'range', 'time', 'hidden');
	var $customType = array('text', 'phone', 'email', 'textonly','password', 'money');
	var $blockDivParams = '';
	var $method;
	var $validForm = TRUE;
	var $js;
	var $jsInclude = array('boostrap-datepicker'=>FALSE); 
	var $ro = FALSE;			// ReadOnly
	var $cssLabel = 'formLabel';
	var $inlineForce = FALSE;
	var $initAjax = FALSE;
	var $paramsAjax = FALSE;
	var $count = array();
	var $showError = FALSE;
	var $allplaceholder = FALSE;
	
	function __construct($name, $style = 'div', $method='POST') {
		$this->setStyle($style);
		$this->name = $name;
		$this->method = $method;
		$this->getCurrentFormToken('newToken');
	}
	
	/**
	 * Choose for regular form or readOnly without fields
	 * @param type $readOnly
	 */
	function mode($readOnly = FALSE, $datas=array()){
		$this->ro = $readOnly;
		if($readOnly){
			$this->cssLabel = 'formRo-label';
			if(sizeof($datas)>0){		
				foreach ($datas as $key=>$val){
					$_POST[$key] = $val;
				}
			}
		}
	}
		
	/**
	 * 
	 * @param type $Name
	 * @param type $Label
	 * @param type $Type		can be 'text', 'textonly', 'phone', etc ... or an array, see below for array format
	 * @param type $mandatory
	 * @param type $defaultValue
	 * @param type $params
	 * 
	 * @example array type SYS_Var ($sv), usage : $sv->getByGroup('CountryISO'); // 2 Column per array, ID_Var and Name. 
	 * @example array $cantonList[] = array('ID_Var'=>'VD',	'Name'=>'Vaud');
	 * 
	 */
	function addField($Name, $Label='', $Type='text', $mandatory=FALSE, $defaultValue='', $params=''){	
		// Count
		if(!in_array($Type, array('submit', 'hidden'))){
			$this->count['name'][$Name]++;
			if($this->count['name'][$Name]==1){
				$this->count['field']++;
			}
			if(trim($_POST[$Name])!=''){
				$this->count['filled']++;
			}
		}
						
		if(trim($_POST[$Name])!=''){
			$_SESSION['form'][$this->name][$Name] = $_POST[$Name];
			if(!($Type=='radio' || $Type=='submit')){
				$defaultValue = $_POST[$Name];
			}
		}elseif($defaultValue==''){
			$defaultValue = $_SESSION['form'][$this->name][$Name];
		}

		
		if($mandatory && !$this->ro){
			if($this->allplaceholder){
				$mand = '*';
			}else{
				$mand = '<span class="formMandSign"></span>';
			}
			
			$mandHtml = ' required="required" ';
			
			if($this->showError && (trim($_SESSION['form'][$this->name][$Name])=='' || trim($_POST[$Name])=='') ){
				$params .= ' class="formMandError" ';
			}
			
			/*
			if(isset($_SESSION['form'][$this->name]) && $this->valid()){
				$params = $params.' class="formMandError" ';
			}*/
		}
		if($Label!=''){
			if($this->allplaceholder){
				$ph = $Label.$mand;
				$phh = ' placeholder="'.$ph.'" ';		
			}else{
				$l = '<label for="'.$Name.'" class="'.$this->cssLabel.'">'.$Label.$mand.'</label>';
			}
		}
		$fparams = ' id="'.$Name.'"  name="'.$Name.'"  '.$mandHtml.' '.$params;
		
		if($this->ro && !in_array($Type, array('radio'))){
			if(is_array($Type)){			// In case of select for example
				foreach($Type as $vt){
					if($vt['ID_Var']==$defaultValue){
						$defaultValue=$vt['Name'];
						break;
					}
				}
			}
			if(!in_array($Type, array('submit', 'hidden'))){
				$o = '<div class="formRo-val">'.$defaultValue.'</div>';
			}
			
		}elseif(is_array($Type)){
			
			if(is_array($Type['datalist'])){
				$Type = $Type['datalist'];
				$datalist = TRUE;
			}else{
				$datalist = FALSE;
			}
			
			if(sizeof($Type)==0){
				$o = DICO_WORD_PLEASE_CREATE_ONE.': <b>'.$Label.'</b>';
			}else{
				foreach($Type as $key=>$val){
					$selected = '';
					if((string)$key=='default'	&&	$_POST[$Name]==''){			$selected = 'selected';	}
					if($defaultValue==$val['ID_Var']){							$selected = 'selected';	}
					if($datalist){
						$ls .=  '<option value="'.$val['ID_Var'].'" '.$selected.'>'.$val['Name'].'</option>'; 
					}else{
						$ls .=  '<option value="'.$val['ID_Var'].'" '.$selected.'>'.$val['Name'].'</option>'; 
					}
				}
				if($datalist){
					$o = '<input list="'.$Name.'_lst" '.$fparams.' '.$phh.'><datalist  id="'.$Name.'_lst" >'.$ls.'</datalist>';
				}else{
					$empty = '<option disabled selected hidden value="">'.$ph.'</option>';
					$o = '<select '.$fparams.'>'.$empty.$ls.'</select>';					
				}
			}
		}elseif($Type=='hidden'){
			$o = '<input '.$fparams.' value="'.$defaultValue.'" type="'.$Type.'" > ';
			$l = '';
			$this->blockDivParams = ' style="display:none" ';
		}elseif($Type=='checkbox'){
			$o = '<input '.$fparams.' value="'.$defaultValue.'" type="'.$Type.'" > '.$Label.$mand;
			$l = '';
		//}elseif($Type=='submit'){

		}elseif($Type=='radio'){
			$checked = '';
			if($_POST[$Name]==$defaultValue && $_POST[$Name]!=''){
				$checked = ' checked="checked" ';
			}
			if($this->ro){
				$disable = ' disabled ';
			}
			$o = '<input '.$fparams.$checked.' value="'.$defaultValue.'" type="'.$Type.'" '.$disable.' > '.$Label.$mand;
			$l = '';
		}elseif($Type=='textarea'){
			$o = '<textarea '.$fparams.' >'.$defaultValue.'</textarea>';
		}elseif($Type=='date'){
			$this->jsInclude['boostrap-datepicker'] = TRUE;
			$o = '<input '.$fparams.' value="'.$defaultValue.'" type="text" '.$phh.' class="form-control">';
			$this->js .= '<script> $(\'#'.$Name.'\').datepicker({ format: "dd.mm.yyyy",autoclose: true  });</script>';
		}elseif($Type=='datebirth'){ 
			$yearCurr = date('Y')-100;
			$this->jsInclude['boostrap-datepicker'] = TRUE;
			$o = '<input '.$fparams.' value="'.$defaultValue.'" type="text" '.$phh.' class="form-control">';
			$this->js .= '<script> $(\'#'.$Name.'\').datepicker({ format: "dd.mm.yyyy", startDate: \'01.01.'.$yearCurr.'\', endDate: \''.date('d.m.Y').'\', orientation: \'bottom right\', startView: "decade", autoclose: true  });</script>';
		}elseif(in_array($Type, $this->stdType)){
			$o = '<input '.$fparams.' value="'.$defaultValue.'" type="'.$Type.'" '.$phh.'>';
		}elseif(in_array($Type, $this->customType)){
    	$constName = strtoupper('PATTERN_'.$Type);
			if(constant($constName)=='')	echo msg('Constant '.$constName.' not defined or include missing !', 'e');
			$pattern = constant($constName);
			$pattex = preg2regex($pattern);
			$title = constant(strtoupper('DICO_PREG_'.$Type));
			//echo '<BR>- '.strtoupper('PATTERN_'.$Type).' '.$defaultValue.' '.$Name.' : '.$pattern;
			if(!preg_match($pattern, $defaultValue)){
				$this->validForm = FALSE;
				if(sizeof($_POST)>1){
					//$e = msg(DICO_WORD_ERROR.' '.$Label, 'e');
				}		
			}
			
			$o = $e.'<input '.$fparams.' value="'.$defaultValue.'" type="'.$Type.'" pattern="'.$pattex.'" title="'.$title.'" '.$phh.'>';
		}


		
		$this->output .= '<div '.$this->blockDivParams.'>'.$this->style[0].$l.$this->style[1].$o.$this->style[2].'</div>';
		if(!$this->inlineForce){ 			$this->blockDivParams = ''; 		}
	}
	
	/**
	 * 
	 * @param type $Name
	 * @param type $Label
	 * @param type $Type
	 * @param type $mandatory
	 * @param type $defaultValue
	 * @param string $params
	 * 
	 * Simple ajax php file example :
		<?php
		session_start();
		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Mon, 01 Jan 1996 00:00:00 GMT');
		header('Content-type: application/json');
		if($_SESSION["tokenForm"]==$_GET['t']){
			$val[] = 'Valeur 1 '.$_GET['val'];
			$val['id'] = $_GET['id'];
		}else{
			$val[] = 'Incompatible token';
		}
	 */
	function addFieldAjax($Name, $Label='', $Path='', $mandatory=FALSE, $defaultValue='', $params='', $event='onkeyup', $minChar = 3, $callBackFct='callBackFct'){
		$this->initAjax();
		if($Path==''){
			$Path = ROOT_URI_UI.'inc/field.ajax.php';
		}
		
		$params = $this->paramsAjax($Path, $Name, $event, $minChar, $callBackFct).' '.$params;
		
		$innerDiv = '<div id="'.$Name.'_list_div" class="formAjaxResult"><datalist id="'.$Name.'_list"><option>'.DICO_WORD_MIN_3CHARS.'</option></datalist></div>';
		$this->style[2] = $innerDiv.'</div>';
		$this->addField($Name, $Label, 'text', $mandatory, $defaultValue, $params);
		$this->style[2] = '</div>';

	}
	
	function paramsAjax($Path, $Name, $event='onkeyup', $minChar = 3, $callBackFct='callBackFct', $addValues=array()){
		foreach($addValues as $key=>$val){
			$addVals .= " , '".$key."':".$val ;
		}
		return $event.'="if(this.value.length>'.$minChar.')$.getJSON(\''.$Path.'\', {\'val\':this.value,\'id\':\''.$Name.'\',\'t\':\''.$this->getCurrentFormToken().'\''.$addVals.'}, '.$callBackFct.'); " ';
	}
	

	function initAjax($js='', $jsHandler = ''){
		if($jsHandler==''){
			$jsHandler = ' onclick="$(´#\'+fname+\'´).val(´\'+result[k][0]+\'´); document.getElementById(´\'+fname+\'_list_div´).style.display=´none´; $(´#\'+fname+\'´).focus();" ';
			$jsHandler = str_replace('´', "\\'", $jsHandler);
		}
		if(!$this->initAjax){
			if($js==''){
				$js ='<script language="javascript">
				function callBackFct(result) {
					var txt = \'\';
					var line = \'\';
					var fname = result[\'id\'];
					result[\'id\'] = \'\';
					for(var k in result) {
						line =\'\';
						for(var k2 in result[k]) {	
							line = line+\'<td>\'+result[k][k2]+\'</td>\';	
						}
						txt = txt+\'<tr '.$jsHandler.'>\'+line+\'</tr>\';	
					}
					txt = \'<table id="\'+fname+\'_list">\'+txt+\'</table>\';
					document.getElementById(fname+\'_list_div\').innerHTML = txt;
				}</script>';
			}
			$this->initAjax = TRUE;
			$this->output .= $js;
			
			// http://jsfiddle.net/beyym/ pour implémenter un select dans une table
			
		}
		
		/*
document.getElementById(fname).value=\'+result[k][0]+\'
		for(var k in result) {	txt = txt+\'<option>\'+result[k]+\'</option>\';	}
		txt = \'<datalist id="\'+fname+\'_list">\'+txt+\'</datalist>\';
		 */
		
	}
	
	
	
	function addHtml($html){
		$this->output .= $html; 
	}
	
	function addHR($title=''){
		if($title!=''){
			$html = '<div style="position:relative; width:100%; height:30px;" >
					<hr style="position:relative; top:18px">
					<div class="hrTitle">'.$title.'</div>
				</div>';
		}else{
			$html = '<hr>';
		}
		$this->addHtml($html);
	}
	
	
	
	function posInline($force = FALSE, $params = ' class="posInline" '){
		$this->inlineForce = $force;
		$this->blockDivParams = $params;
	}
	
	function createDataArray($ID_Var, $Name, $Table, $Where){
		$sql = 'SELECT '.$ID_Var.' as ID_Var, '.$Name.' as Name FROM '.$Table.' WHERE '.$Where;
		return db($sql);
	}
	
	function formatDataArray($arr){
		foreach ($arr as $key=>$val){
			$res[$key] = array('Name'=>$val[0], 'ID_Var'=>$val[1]);
		}
		return $res;
	}
	
	/**
	 * 
	 * @param type $position	S: Start	, N: Next Group (N=E+S), E: End group
	 */
	function addGroup($poscode, $title='', $style=''){
		$pos = array(	'S'	=>	'<div class="formGroup" style="'.$style.'">',
						'N'	=>	'</div><div class="formGroup"  style="'.$style.'">',
						'E'	=>	'</div>');
		$this->addHtml($pos[$poscode]);
		if($title!=''){
			$this->addTitle($title);
		}
	}
	
	function addFormTags($url='?', $formName='myForm', $params = ''){
		$this->output = '<form action="'.$url.'" method="'.$this->method.'" name="'.$formName.'" '.$params.' >'.$this->output.'<input name="t" type="hidden" id="t" value="'.$this->getCurrentFormToken().'"></form>';
	}
	
	function addTitle($title){
		$this->output .= '<div class="formTitle">'.$title.'</div>'; 
	}
	
	function get(){
		return $this->output;
	}
	
	function setStyle($style = 'div'){
		if(is_array($style)){
			$this->style = $style;
		}else{
			$this->style = $this->styles[$style];
		}
	}
	
	function destruct(){
		unset($_SESSION['form'][$this->name]);
	}
	
	function valid(){
		return $this->validForm;
	}
	
	/**
	 * 
	 * @param type $parts js or css
	 * @return string
	 */
	function getApps($parts = 'js'){
		$paths['js']['boostrap-datepicker']	= ROOT_URI_JS.'bootstrap-datepicker-1.6.4-dist/js/bootstrap-datepicker.min.js';
		$paths['css']['boostrap-datepicker']= ROOT_URI_JS.'bootstrap-datepicker-1.6.4-dist/css/bootstrap-datepicker3.min.css';
		
		foreach($this->jsInclude as $key=>$var){
			if($var){
				if($parts=='js'){
					$includes .= '<script src="'.$paths[$parts][$key].'" type="text/javascript"></script>'."\n";
				}else{
					$includes .= '<link href="'.$paths[$parts][$key].'" rel="stylesheet" type="text/css"/>'."\n";
				}
			}
		}
		
		$o = $includes;
		if($parts =='js'){	$o .= $this->js;	}
		return $o;
	}
	
	
	function getCss(){
		return '<link rel="stylesheet" href="'.ROOT_CSS.'std.inc.css">';
	}
	
	function getCurrentFormToken($action = ""){
		$tmpT = rand(1,100000);
		if ($action == "newToken"){ 	
			$_SESSION["tokenForm"] = md5($tmpT);
			$t = $_SESSION["tokenForm"];
		}else{
			$t = $_SESSION["tokenForm"];
		}		
		return $t;		
	}
		
	function resetToken(){
		return $this->getCurrentFormToken("newToken");
	}
	
	function signUp($user=''){
		
		$this->validForm = FALSE;
		//pr($user);pr($_POST);
		if($_POST['password']==$_POST['password2'] && preg_match(PATTERN_PASSWORD, $_POST['password'])){
			if($_SERVER['ID_User']=='guest' && preg_match(PATTERN_EMAIL, $_POST['username'])){
				$this->validForm = TRUE;
			}elseif($_SESSION['tmp_ID_User']!='' && $_SESSION['secretKey']=='passed'){
				$user->getById($_SESSION['tmp_ID_User']);
				$this->validForm = TRUE;
				$_SESSION['ID_User'] = $user->ID_User;
			}elseif($_SESSION['ID_User']!='guest' && $_SESSION['username']!=''){
				$this->validForm = TRUE;				
			}
		}elseif($_POST['password']!=$_POST['password2'] && $_POST['password']==''){
			$this->addHtml(msg(DICO_ERR_PASSWORD_DIFFER, 'e'));
		}
		
		if($this->validForm){
			if($_SESSION['ID_User']!='guest'){
				$user->setPassword($_POST['password']);
			}else{
				$user->create($_POST['username'], $_POST['password']);
				$user->verifyEmail();
			}
			return TRUE;
		}else{
			if($_SESSION['secretKey']=='passed' && $_SESSION['username']!=''){
				$this->addHtml(DICO_WORD_USERNAME.' <b>'.$_SESSION['username'].'</b>');
			}else{
				$this->addField('username',	DICO_WORD_USERNAME,			'email',	TRUE, $email);
			}
			
			$this->addField('password', DICO_WORD_PASSWORD,			'password',	TRUE);
			$this->addField('password2',DICO_WORD_PASSWORD_REPEAT,	'password', TRUE);
			$this->addField('submit',	'',							'submit',	TRUE, DICO_WORD_SIGNUP);
			$this->addFormTags();
			return $this->get();
		}
			
	}
	
	/**
	 * 
	 * @param type $action recover, to recover password
	 * @return type
	 */
	function login($redirect=''){
		$passwordType = 'password';
		$submit = DICO_WORD_LOGIN;
		$lostLink = '<a href="?action=recover">'.DICO_WORD_PASSWORD_LOST.'</a>';
	
		if($_GET['action']=='recover'){
			$passwordType = 'hidden';
			$action = 'recover';
			$submit = DICO_WORD_PASSWORD_RECOVER;
			$lostLink = '';
		}
		
		$this->addField('username',	DICO_WORD_EMAIL,			'email',	TRUE);
		$this->addField('password', DICO_WORD_PASSWORD,			$passwordType,	TRUE,	$action);
		$this->addHtml('<input type="hidden" name="redirect" value="'.$redirect.'">');
		$this->addField('submit',	'',							'submit',	TRUE, $submit);
		$this->addHtml($lostLink);
		$this->addFormTags();
		return $this->get();
	}
	
	function loginRecover(){
		
	}
	
}

function preg2regex($exp){
	$res = mb_ereg('(\/)(.*)(\/[a-z]*)', $exp, $matches);
	return $matches[2];
}
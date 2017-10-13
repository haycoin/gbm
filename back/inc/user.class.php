<?php
/**
 * Connection to GBM 
 *
 * @author Alec Avedisyan
 * @copyright 2017 Sparkou.com
 * @package GBM
 * @version 3.0.1
 * @subpackage Includes
 */

include_once ROOT_FOLDER_UI.'inc/generated.pattern.php';
include_once ROOT_FOLDER.'inc/send_mail.inc.php';
		include_once ROOT_FOLDER."inc/cache.class.php";
		include_once ROOT_FOLDER.'inc/user.class.php';
include_once ROOT_FOLDER.'inc/db.inc.php';
include_once ROOT_FOLDER.'inc/form.class.php';

/**
 * SecureLevel
 * 0	Root
 * 1	Super admin
 * 2	GBM admin
 * 3	-
 * 4	User group admin	2 factors
 * 5	User				2 factors
 * 6	User				1 factor
 * 7	User				Guest
 * 8	User				Recover password
 * 9	User Disabled		Login not allowed, not yet validated
 * 10	User Blocked	
 */

class User{
	var $user;
	var $ID_User;
	var $username;
	var $secureLevel;
	var $linkValidyDelay = 3600*24;
	var $logged = array();
	
	public function __construct($ID_User='') {
		if($ID_User!=''){
			$this->IU = $ID_User;
		}
	}
	
	function create($username, $password='', $secureLevel=9){ // TODO add check if user exist ! 
		$username = trim(strtolower($username));
		if(preg_match(PATTERN_EMAIL, $username)){
			$cols = array(		'Username'	=>	$username,
								'Password'	=>	$password,
								'SecureLevel'	=>	$secureLevel);
			$this->ID_User = dbInsert($cols, 'GBM_SYS_User');
			$this->username = $username;
			return $this->ID_User;
		}else{
			return FALSE;
		}
	}
	
	function get($username, $field = 'Username'){
		$sql = 'SELECT * FROM GBM_SYS_User WHERE '.$field.'=?';
		$this->user = db($sql, $username);
		if($this->user[0]['Username']==''){ 
			// User not found
			$this->ID_User = 0;
			return FALSE;
		}else{
			$this->username = $this->user[0]['Username'];
			$ID_User = $this->user[0]['ID_User'];
			$this->ID_User = $ID_User;
			$this->secureLevel = $this->user[0]['SecureLevel'];
			return $this->user;
		}
	}
	
	function getById($id){
		return $this->get($id,'ID_User');
	}
	
	function setPassword($password){
		if(trim($password)!='' && strlen($password)>=8){
			$cols = array(	'Password'		=>	md5($password.SECRET_KEY),
							'SecureLevel'	=>	6);
			dbUpdate($cols, 'GBM_SYS_User', 'ID_User='.$this->ID_User);
			return TRUE;
		}else{
			return DICO_WORD_PASSWORD_NOT_SET;
		}
	}
	
	function resetPassword(){
		$cols = array(		'Password'	=>	$this->generatePassword(16),
							'SecureLevel'	=>	9, 
							'Created'	=>	date('Y-m-d H:i:s')	);
		dbUpdate($cols, 'GBM_SYS_User', ' ID_User='.$this->ID_User);
	}
	
	function verifyPassword($password){
		if(md5($password.SECRET_KEY) == $this->user[0]['Password']){
			return TRUE;
		}else{
			return FALSE;
		}
	}
	
	function validDelay(){
		if((strtotime($this->user['0']['Created'])+$this->linkValidyDelay) > time()){
			return TRUE;
		}else{
			return FALSE;
		}
	}
	
	/**
	 * Send info to user to confirm the e-mail, validity 1 hours
	 */
	function verifyEmail($action = ''){
		//$url = ROOT_URI_UI.'validUser.php?k='.$this->secretKey($this->username).'&e='.$this->username;

		$url = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'].'?k='.$this->secretKey($this->username).'&e='.$this->username;
		
		if('recover' == $action){
			$msg = DICO_WORD_PASSWORD_RESTORE;
		}else{
			$msg = DICO_WORD_CONFIRM_EMAIL;
		}
		
		$body_message = DICO_MAIL_VALID_HEAD.'<br>'
						.'<a href="'.$url.'">'.$msg.'</a><br><br>'
						.$url.'<br>'
						.DICO_MAIL_VALID_FOOT;

		
		return send_mail(ROOT_EMAIL, $this->username, '', ROOT_EMAIL_NAME.' '.DICO_WORD_AUTH,$body_message);
	}
	
	
		
	function setSecureLevel($level){
		if($level>=4 && $level<=9){
			dbUpdate(array('SecureLevel'=>$level), 'GBM_SYS_User', 'ID_User='.$this->ID_User);
		}
	}
	
	function login($username, $password, $factor2=''){
		// TODO ADD Securelevel
		
		if($_GET['action']=='recover'){				// Recover new password
			return FALSE;
		}elseif($username=='' && $password==''){
			return FALSE;
		}
		

		$ch = new Cache();
		$this->get($username);
		$lastLog = $ch->getLast(2500, 21, 2510, $this->ID_User);
		$vp = $this->verifyPassword($password);	
		
		if($password == 'recover'){
			$ch->add(2500, 21, 2510, $this->ID_User, 'Password recover', $_SERVER['REMOTE_ADDR']);
			$this->resetPassword();
			$this->verifyEmail('recover');
			return FALSE;	
		}elseif(strtotime($lastLog[0][Time])+10>time()){		// Avoid brut force
			$ch->add(2500, 21, 2510, $this->ID_User, 'Login fail again', $_SERVER['REMOTE_ADDR']);	
			return msg(DICO_ERR_LOGIN_WAIT, 'e');
		}elseif($this->ID_User===FALSE || !$vp){		// Username not found or Woring password
			$ch->add(2500, 21, 2510, $this->ID_User, 'Login fail', $_SERVER['REMOTE_ADDR']);
			return msg(DICO_ERR_LOGIN, 'e');
		}elseif($vp){
			return TRUE;
		}
	//	pr($this->user);		
		return FALSE;
	}
	
	function generatePassword($length = 8) {
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789#@?!.:,;-_';
		$count = mb_strlen($chars);

		for ($i = 0, $result = ''; $i < $length; $i++) {
			$index = rand(0, $count - 1);
			$result .= mb_substr($chars, $index, 1);
		}

		return $result;
	}
	
	/**
	 * Session must be started from the file calling before this function
	 * 
	 * session_start(); 
	 * and DO NOT include_once '../back/inc/inc.php';
	 * 
	 */
	function valid($redirect = ''){
		$f = new Form('login');

		$log = $this->login($_POST['username'], $_POST['password']);

		if($this->secretKey($_GET['e'], $_GET['k'])===TRUE ){
			// Faire les conditions suivant le Securelevel en cas de 2 factor p.ex
			$this->get($_GET['e']);
			//pr($this->user); 	$this->validDelay = 3600*24*30; // Testing
			if($this->secureLevel==9 && $this->validDelay()){			// Profil is validated
				$_SESSION['secretKey'] = 'passed';
				$_SESSION['ID_User'] = $this->ID_User;
				$_SESSION['username'] = $this->username;
				$o .=  $f->signUp($this);
			}else{
				$o .=  msg(DICO_ERR_CONFIRM_USER_LINK_INVALID, 'e'); // If y user has benn banned he will get the same message. 
			}

		}elseif($_SESSION['secretKey'] == 'passed'){		
			$this->getById($_SESSION['ID_User']);
			$ff = $f->signUp($this);
			if($ff===TRUE){
				unset($_SESSION['secretKey']);
			}else{
				$o .=  $ff;
			}
		}elseif($log===TRUE){	
			// Logged in 
			$_SESSION['ID_User'] = $this->ID_User;
			$_SESSION['username'] = $this->username;
			if('' != $_POST['redirect']){
				$o = '<script type="text/javascript">window.location="'.$_POST['redirect'].'";</script>';
			}
		}elseif('' != $log){
			$o .= $log;
			$o .= $f->login($redirect);
		}else{
			// Startup login page	
			$_SESSION['ID_User'] = 'guest';
			$o = $f->login($redirect);
		}
		
		return $o;
		
	}
	
	
	function secretKey($valE, $valid=''){
		if('' == $valid){
			return md5($valE.SECRET_KEY);
		}else{
			if($valid==md5($valE.SECRET_KEY)){
				return TRUE;
			}else{
				return FALSE;
			}
		}
	}
	
}
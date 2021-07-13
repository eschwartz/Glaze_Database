<?php
/**
 * PHP Class to user access (login, register, logout, etc)
 * 
 * <code><?php
 * include('access.class.php');
 * $user = new flexibleAccess();
 * ? ></code>
 * 
 * For support issues please refer to the webdigity forums :
 *				http://www.webdigity.com/index.php/board,91.0.html
 * or the official web site:
 *				http://phpUserClass.com/
 * ==============================================================================
 * 
 * @version $Id: access.class.php,v 0.93 2008/05/02 10:54:32 $
 * @copyright Copyright (c) 2007 Nick Papanotas (http://www.webdigity.com)
 * @author Nick Papanotas <nikolas@webdigity.com>
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * 
 * ==============================================================================

 */
 
 // * Summary of Functions (by Edan)
 // Function:			Param:								Return:				Description:
 // insertUser			$data (tblField => value)			$userID				Creates a new user, and returns their UserID
 // checkAvail			$login								(true/false)		Checks availablility of a given UserLogin. (by Edan)
 // reqLogin			*$targetPage, *$redirect			none				Checks if user is logged in, otherwise, sends to $redirect url. (by Edan)
 // reqUsrLogin			$reqUsrID, *$targetPage, 			none				Checks if loaded user is $userID, otherwise sends to $redirect url. (by Edan)	

// '*' indicates optional field

/**
 * Flexible Access - The main class
 * 
 * @param string $dbName
 * @param string $dbHost 
 * @param string $dbUser
 * @param string $dbPass
 * @param string $dbTable
 */

class flexibleAccess{
  /*Settings*/
  /**
   * The database that we will use
   * var string
   */
  var $dbName = 'earthcr1_DYLMG';
  /**
   * The database host
   * var string
   */
  var $dbHost = 'localhost';
  /**
   * The database port
   * var int
   */
  var $dbPort = 3306;
  /**
   * The database user
   * var string
   */
  var $dbUser = 'earthcr1_DYLMG';
  /**
   * The database password
   * var string
   */
  var $dbPass = 'Plastichorse22@';
  /**
   * The database table that holds all the information
   * var string
   */
  var $dbTable  = 'Users';
  /**
   * The session variable ($_SESSION[$sessionVariable]) which will hold the data while the user is logged on
   * var string
   */
  var $sessionVariable = 'userSessionValue';
  /**
   * Those are the fields that our table uses in order to fetch the needed data. The structure is 'fieldType' => 'fieldName'
   * var array
   */
	var $tbFields = array(
		'userID'=> 'UserID', 
		'login' => 'UserLogin',
		'pass' => 'UserPass',
		'email' => 'UserEmail',
		'active'=> 'UserActive'
	);
	/**
   * When user wants the system to remember him/her, how much time to keep the cookie? (seconds)
   * var int
   */
  var $remTime = 2592000;//One month
  /**
   * The name of the cookie which we will use if user wants to be remembered by the system
   * var string
   */
  var $remCookieName = 'ckSavePass';
  /**
   * The cookie domain
   * var string
   */
  var $remCookieDomain = 'dylmg.earthcreaturespottery.com';
  /**
   * The method used to encrypt the password. It can be sha1, md5 or nothing (no encryption)
   * var string
   */
  var $passMethod = 'md5';
  /**
   * Display errors? Set this to true if you are going to seek for help, or have troubles with the script
   * var bool
   */
  var $displayErrors = true;
  /**
  	* Testing Mode (created by Edan). Will show sql error and code if query fails.
	* NOT SECURE!!!!!!
	*/
  var $testingMode = true;
  
  // Default redirect URL if user does not have access page
  var $redirect = '/usr/login.php';
  // For a kicked user, default of where to send them to after login. 
  var $targetPage = '/usr/myAccount.php';
	
  /*Do not edit after this line*/
  var $userID;
  var $dbConn;
  var $userData=array();
  /**
   * Class Constructure
   * 
   * @param string $dbConn
   * @param array $settings
   * @return void
   */
  function flexibleAccess($dbConn = '', $settings = '')
  {
	    if ( is_array($settings) ){
		    foreach ( $settings as $k => $v ){
				    if ( !isset( $this->{$k} ) ) die('Property '.$k.' does not exists. Check your settings.');
				    $this->{$k} = $v;
			}
	    }
	    $this->remCookieDomain = $this->remCookieDomain == '' ? $_SERVER['HTTP_HOST'] : $this->remCookieDomain;
	    $this->dbConn = ($dbConn=='')? mysql_connect($this->dbHost.':'.$this->dbPort, $this->dbUser, $this->dbPass):$dbConn;
	    if ( !$this->dbConn ) die(mysql_error($this->dbConn));
	    mysql_select_db($this->dbName, $this->dbConn)or die(mysql_error($this->dbConn));
	    if( !isset( $_SESSION ) ) session_start();
	    if ( !empty($_SESSION[$this->sessionVariable]) )
	    {
		    $this->loadUser( $_SESSION[$this->sessionVariable] );
	    }
	    //Maybe there is a cookie?
	    if ( isset($_COOKIE[$this->remCookieName]) && !$this->is_loaded()){
	      //echo 'I know you<br />';
	      $u = unserialize(base64_decode($_COOKIE[$this->remCookieName]));
	      $this->login($u['uname'], $u['password']);
	    }
  }
  
  /**
  	* Login function
  	* @param string $uname
  	* @param string $password
  	* @param bool $loadUser
  	* @return bool
  */
  function login($uname, $password, $remember = false, $loadUser = true)
  {
    	$uname    = $this->escape($uname);
    	$password = $originalPassword = $this->escape($password);
		switch(strtolower($this->passMethod)){
		  case 'sha1':
		  	$password = "SHA1('$password')"; break;
		  case 'md5' :
		  	$password = "MD5('$password')";break;
		  case 'nothing':
		  	$password = "'$password'";
		}
		$sql = "SELECT * FROM `{$this->dbTable}` 
		WHERE `{$this->tbFields['login']}` = '$uname' AND `{$this->tbFields['pass']}` = $password LIMIT 1";
		$res = $this->query($sql ,__LINE__);
		if ( @mysql_num_rows($res) == 0)
			return false;
		if ( $loadUser )
		{
			$this->userData = mysql_fetch_array($res);
			$this->userID = $this->userData[$this->tbFields['userID']];
			$_SESSION[$this->sessionVariable] = $this->userID;
			if ( $remember ){
			  $cookie = base64_encode(serialize(array('uname'=>$uname,'password'=>$originalPassword)));
			  $a = setcookie($this->remCookieName, 
			  $cookie,time()+$this->remTime, '/', $this->remCookieDomain);
			}
		}
		return true;
  }
  
  /**
  	* Logout function
  	* param string $redirectTo
  	* @return bool
  */
  function logout($redirectTo = '')
  {
    setcookie($this->remCookieName, '', time()-3600);
    $_SESSION[$this->sessionVariable] = '';
    $this->userData = '';
    if ( $redirectTo != '' && !headers_sent()){
	   header('Location: '.$redirectTo );
	   exit;//To ensure security
	}
  }
  /**
  	* Function to determine if a property is true or false
  	* param string $prop
  	* @return bool
  */
  function is($prop){
  	return $this->get_property($prop)==1?true:false;
  }
  
	// Returns the loaded userID (by Edan)
	function getUserID() {
		if (empty($this->userID)) { 
			$this->error('No user is loaded', __LINE__);
		}
		else {
			return $this->userID;
		}
	}
		

	/**
  	* Get a property of a user. You should give here the name of the field that you seek from the user table
  	* @param string $property
  	* @return string
  */
  function get_property($property)
  {
    if (empty($this->userID)) $this->error('No user is loaded', __LINE__);
    if (!isset($this->userData[$property])) $this->error('Unknown property <b>'.$property.'</b>', __LINE__);
    return $this->userData[$property];
  }
  /**
  	* Is the user an active user?
  	* @return bool
  */
  function is_active()
  {
    return $this->userData[$this->tbFields['active']];
  }
  
  /**
   * Is the user loaded?
   * @ return bool
   */
  function is_loaded()
  {
    return empty($this->userID) ? false : true;
  }

	
	// Checks if user is logged in, otherwise, sends to $redirect url. (by Edan)
	function reqLogin($targetPage=NULL) {
		is_null($targetPage) ? $this->targetPage : $targetPage;		// sets default $targetPage to $this->targetPage
		
		// Target page is where you want the user to get after they log in
		if (!$this->is_loaded()) {				// Checks is user is already logged in
			$this->kickUsr($targetPage);
		}
	} // end of reqLogin
	
	// Checks if loaded user is $userID, otherwise sends to $redirect url. (by Edan)
	function reqUsrLogin($reqUsrID, $targetPage=NULL) {
		$targetPage = is_null($targetPage) ? $this->targetPage : $targetPage;		// sets default $targetPage to $this->targetPage

		if (!$this->is_loaded() || $this->userID != $reqUsrID) {	// if user is wrong (or not logged in)
			$this->kickUsr($targetPage);							// Send user to login, then try to bring them to target
		}
		else {														// Correct user logged in
			return true;
		}
	} // end of reqUsrLogin
	
	// sends user to $redirect page
	// $targetRage is where user will go after login
	function kickUsr($targetPage=NULL, $redirect=NULL) {
		$targetPage = is_null($targetPage) ? $this->targetPage : $targetPage;		// sets default $targetPage to $this->targetPage
		$redirect = is_null($redirect) ? $this->redirect : $redirect;				// sets default $redirect to $this->redirect
		
		ob_start();	// needed for header redirect
		$url = "$redirect?targetPage=$targetPage";			// maybe instead we should just set SESSION[$nextPage] here.
		header('Location: ' . $url);		// sends to account page. (Should detect what page they are coming from, and send them back there)
		ob_flush();
	} // end of kickUsr
  
  /**
  	* Activates the user account
  	* @return bool
  */
  function activate()
  {
    if (empty($this->userID)) $this->error('No user is loaded', __LINE__);
    if ( $this->is_active()) $this->error('Allready active account', __LINE__);
    $res = $this->query("UPDATE `{$this->dbTable}` SET {$this->tbFields['active']} = 1 
	WHERE `{$this->tbFields['userID']}` = '".$this->escape($this->userID)."' LIMIT 1");
    if (@mysql_affected_rows() == 1)
	{
		$this->userData[$this->tbFields['active']] = true;
		return true;
	}
	return false;
  }

	function checkAvail($login) {
	// Checks availablility of a given UserLogin. Return true if login is available. (by Edan)
		$sql = "SELECT UserLogin \n"
			. "FROM Users \n"
			. "WHERE UserLogin = '$login'";
		$rs = $this->query($sql);
		
		if (!$rs || (mysql_numrows($rs) < 1)) {
			return true;		// no matching results => login is available
		}
		else {
			return false;		// some matching result => login NOT available
		}
	} // end of checkAvail

  /*
   * Creates a user account. The array should have the form 'database field' => 'value'
   * @param array $data
   * return int
   */  
  function insertUser($data){
	 // Creates a new user, and returns their UserID
    if (!is_array($data)) $this->error('Data is not an array', __LINE__);
    switch(strtolower($this->passMethod)){
	  case 'sha1':
	  	$password = "SHA1('".$data[$this->tbFields['pass']]."')"; break;
	  case 'md5' :
	  	$password = "MD5('".$data[$this->tbFields['pass']]."')";break;
	  case 'nothing':
	  	$password = $data[$this->tbFields['pass']];
	}
    foreach ($data as $k => $v ) $data[$k] = "'".$this->escape($v)."'";
    $data[$this->tbFields['pass']] = $password;
    $this->query("INSERT INTO `{$this->dbTable}` (`".implode('`, `', array_keys($data))."`) VALUES (".implode(", ", $data).")");
    return (int)mysql_insert_id($this->dbConn);	
  }
  /*
   * Creates a random password. You can use it to create a password or a hash for user activation
   * param int $length
   * param string $chrs
   * return string
   */
  function randomPass($length=10, $chrs = '1234567890qwertyuiopasdfghjklzxcvbnm'){
    for($i = 0; $i < $length; $i++) {
        $pwd .= $chrs{mt_rand(0, strlen($chrs)-1)};
    }
    return $pwd;
  }
  ////////////////////////////////////////////
  // PRIVATE FUNCTIONS
  ////////////////////////////////////////////
  
  /**
  	* SQL query function
  	* @access private
  	* @param string $sql
  	* @return string
  */
  function query($sql, $line = 'Uknown')
  {
    //if (defined('DEVELOPMENT_MODE') ) echo '<b>Query to execute: </b>'.$sql.'<br /><b>Line: </b>'.$line.'<br />';
	if ($this->testingMode == true) {	// Shows sql error on fail. NOT SECURE.
		//echo "SQL is: " . str_replace('\n',"<br>\n",$sql) . "<br><br>";
		$res = mysql_db_query($this->dbName, $sql, $this->dbConn) or die("Could not execute query: " . mysql_error() . "<br>\n SQL code is: ". str_replace("\n","<br>\n",$sql));
	}
	else {
		"Could not execute query: " . mysql_error() . "<br>\n SQL code is: ". str_replace("\n","<br>\n",$sql);
	}
	
	// Supposed to show errors, but I don't know if it works...
	if ( !res )
		$this->error(mysql_error($this->dbConn), $line);
	return $res;
  }
  
  /**
  	* A function that is used to load one user's data
  	* @access private
  	* @param string $userID
  	* @return bool
  */
  function loadUser($userID)
  {
	$res = $this->query("SELECT * FROM `{$this->dbTable}` WHERE `{$this->tbFields['userID']}` = '".$this->escape($userID)."' LIMIT 1");
    if ( mysql_num_rows($res) == 0 )
    	return false;
    $this->userData = mysql_fetch_array($res);
    $this->userID = $userID;
    $_SESSION[$this->sessionVariable] = $this->userID;
    return true;
  }

  /**
  	* Produces the result of addslashes() with more safety
  	* @access private
  	* @param string $str
  	* @return string
  */  
  function escape($str) {
    $str = get_magic_quotes_gpc()?stripslashes($str):$str;
    $str = mysql_real_escape_string($str, $this->dbConn);
    return $str;
  }
  
  /**
  	* Error holder for the class
  	* @access private
  	* @param string $error
  	* @param int $line
  	* @param bool $die
  	* @return bool
  */  
  function error($error, $line = '', $die = false) {
    if ( $this->displayErrors )
    	echo '<b>Error: </b>'.$error.'<br /><b>Line: </b>'.($line==''?'Unknown':$line).'<br />';
    if ($die) exit;
    return false;
  }
  
}
?>
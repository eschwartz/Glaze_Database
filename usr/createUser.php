<?php
include_once('../includes/DYLMG_SuperHTML.php');
include_once('../includes/DYLMG_quickArrays.php');
?>
<?php
// Code to insert user into database
// * Summary of Functions
// Function:			Param:								Return:									Description:
// checkAvail			login								true/false								Checks if username is available 
// insertUser			none								none									Inserts login/pass to database (pass encrypted)

// Gets rid of harmful tags, and limits to 16 chars
$login = strip_tags(substr($_POST['login'],0,16));
$pass = strip_tags(substr($_POST['pass'],0,16));

// Checks if form was submitted
if ($_POST['submit_createUser']) {
	insertUser();
}

function insertUser() {		// Called on form submission
// Inserts login/pass to database (pass encrypted)
	global $login;
	global $pass;
	
	require_once('../includes/phpUserClass/access.class.php');
	$user = new flexibleAccess();
	
	$avail = $user->checkAvail($login);

	if (!$user->checkAvail($login)) {	// checkAvail returns true if UserLogin is available
		echo "User name already exists";
		return;
	}
	else {
		$data = array(
			'UserLogin' => $login,
			'UserPass' => $pass,
			'UserActive' => 1		// Should eventually use email activation...
		);

	  $userID = $user->insertUser($data);//The method returns the userID of the new user or 0 if the user is not added
	  
	  echo "User registered with ID: $userID";
	}
	
	
} // end of checkAvail
?>
<?php
$s = new DYLMG_SuperHTML('Create User');
$s->buildTop();

$self = $_SERVER['PHP_SELF'];
$body = <<<HERE

<h1>Create User</h1>
<form action="$self" method=post>
<!-- Need form validation, and for 16 chars -->
User Name: <input type=text name=login />
<input type=button value='Check availability' /><br /><!-- Will need to send login via AJAX to insertUser.php -->
Password: <input type='password' name='pass' /><br />
Again: <input type='password' name='passCopy' /><br><!-- Use JS validation for copy-->
<input type=submit name="submit_createUser" value="Create User" />
</form>

HERE;
// end of HTML body

$s->addText($body);
$s->buildBottom();
print $s->getPage();
?>
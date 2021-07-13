<?php
include_once('../includes/DYLMG_SuperHTML.php');
include_once('../includes/DYLMG_quickArrays.php');
?>
<?php
// Make sure to VALIDATE for 16 chars
$login = strip_tags(substr($_POST['login'],0,16));
$pass = strip_tags(substr($_POST['pass'],0,16));
$remember = true;

// After login, send user to targetPage (or to myAccount.php, if none specified)
session_start();					// needed to keep $nextPage/targetPage the same across multiple login attemps
if ($_GET['targetPage']) {
	$_SESSION['nextPage'] = $_GET['targetPage'];
}
elseif (!isset($_SESSION['nextPage'])) { 	// if $nextPage is not set
	$_SESSION['nextPage'] = 'myAccount.php';	// default $nextPage
}

// Creates an instance of user
require_once ('../includes/phpUserClass/access.class.php');
$user = new flexibleAccess();

if ($_GET['logout'] == 1) {					// Logs out user
	$user->logout('/usr/login.php');		// and sends to this url (root)
}
elseif ($user->is_loaded()) {				// Checks is user is already logged in
	$nextPage = $_SESSION['nextPage'];
	unset($_SESSION['nextPage']);
	header('Location: ' . $nextPage);		// sends to account page. (Should detect what page they are coming from, and send them back there)
}
elseif ($_POST['submit_login']) { 			// Checks if form is submitted
	userLogin();							// Then attempts to log in user
}

function userLogin() {
	global $user;
	global $login;
	global $pass;
	global $nextPage;
	if ( !$user->login($login,$pass,$_POST['remember'] ))	{
		echo 'Wrong username and/or password';
	}
	else{
		//user is now loaded
		$nextPage = $_SESSION['nextPage'];
		unset($_SESSION['nextPage']);
		header('Location: ' . $nextPage);	// gets target page from form
	}
}

?>

<?php
$s = new DYLMG_SuperHTML('User Login');
$s->buildTop();

$self = $_SERVER['PHP_SELF'];
$body = <<<HERE
<div id='content-container' >
	<div id='content-column'>
		
		<div class="section" style="margin-left:80px; width:300px;"><div class="sectionHead">User Login</div>
		<div class="sectionContent">
			<form action='$self' method='post'>
			<!-- Need form validation, and for 16 chars -->
			User Name: <input type='text' name='login' /><br>
			Password: <input type='password' name='pass' /><br />
			<input type=submit name="submit_login" value="Login" />
			</form>
		</div></div><!-- end .section, .sectionContent -->
		
		<div class="section" style="margin-left:80px; width:500px; font-size:0.9em"><div class="sectionContent">
		<p>
			The <i>Do You Like My Glaze?</i> ceramic materials database is a social tool for ceramic artists to find and upload glaze recipes, troubleshoot technical issues, and share their favorite glazes with their peers. The database is a project of ceramic artist and web designer <a href="http://www.edanschwartz.com/">Edan Schwartz</a>, and is currently in early stages of development.
		</p>
		<p>
			Feel free to play around with the database on the <a href="/glazes/glazeView.php?glazeID=1">Sample Glaze</a> page. I admit, it ain't too pretty right now, and you might find a couple buttons that don't do what you'd think. But it should give you an a sense of the database architecture and desktop-like application environment that I envision for the site.
		</p>
		<p>
		 If you would like to learn more about the database, <a href="http://www.edanschwartz.com/contact.php">please contact Edan.</a>
		</p>
		</div></div>
	</div><!-- end content-column -->
</div><!-- end content-container -->	
HERE;
// end of HTML body

$s->addText($body);
$s->buildBottom();
print $s->getPage();
?>
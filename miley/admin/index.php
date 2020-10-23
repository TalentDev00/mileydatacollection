<?php
//require_once(__DIR__ . '/../../../includes/adminGlobals.php');
require_once('../../../includes/adminGlobals.php');

	ForceSecureURL();

	switch(Request("a")) {
		case "logout":			LogOutAdminUser(); DrawAdminLoginPage();	break;
		case "login":			LoginViaAJAX();								break;
		case "loginHelper":		AdminLoginHelper();							break;
		case "ping":														break;
		default:				DrawAdminLoginPage();
	}
	exit();



function LoginViaAJAX() {

	$loginResult = LogInAdminUser(Request("username"), Request("password"));

	if($loginResult) {
		$targetURL = isset($_SESSION['intendedPage']) ? $_SESSION['intendedPage'] : defaultLoggedInPage;
		$_SESSION['intendedPage'] = null;
?>
<script type="text/javascript" language="JavaScript">
	$('loginMessage').set('text', 'Login successful!  Please wait...');
	window.location.href = <?php echo JSValue($targetURL)?>;
</script>
<?php
	} else {
?>
<script type="text/javascript" language="JavaScript">
	$('loginMessage').set('text', <?php echo JSValue(Session("loginError"))?>);
</script>
<?php	}
}



function AdminLoginHelper() {

	//$message;
	$email = StraightRequestText("email", 50);

	switch(Request("helpMode")) {
		case "forgotPassword":

			if($uR = GetR(
				"SELECT ID, email
				   FROM tblAdminUsers
				  WHERE username=" . SQLValue(Request("username")) . "
				    AND isActive=1")) {
				SendEmail($uR["email"], systemName,
					"loginhelp-noreply@" . systemDomain,
					"Password Reset",
					"You may reset your password by clicking the following link:\n\n" .
					AdminUserQuickLoginLink(uRS("ID"), "adminPasswordReset.php", 1, 1));
		    }

			$message = "To reset your password, please check your email.";
			break;
		case "forgotUsername":
			if($uR = GetRS(
				"SELECT ID, username, email
				   FROM tblAdminUsers
				  WHERE email=" & SQLValue($email) . "
				    AND isActive=1")) {
				SendEmail($email, systemName,
					"loginhelp-noreply@" . systemDomain,
					"AdminUsername Reminder",
					"Greetings!\n\n" .
					"Your username on file is: " . $uRS["username"] . "\n\n" .
					"Thanks for being a part of " . systemName . ".");

				$message = "To recover your username, please check your email.";
			} else
				$message = "Sorry, $email is not associated with any " .
					systemName . " admin user account.";
	}
?>
<script type="text/javascript" language="JavaScript">
	$('loginHelperMessage').set('text', <?php echo JSValue($message)?>);
	swapSections('loginHelperFormDiv', 'loginHelperCompletion');
</script>
<?php
	exit();
}


function DrawAdminLoginPage() {

?>
<!DOCTYPE HTML>
<html>
<head>
  <link rel="SHORTCUT ICON" HREF="../images/favicon.ico">
  <title><?php echo GetSystemName()?> System Administration</title>
  <link rel="stylesheet" type="text/css" href="../../assets/admin/css/admin.css" />
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <?php echo GetAdminJSHTML(); ?>
</head>
<body>
<div id="loadingMessage" style="display: none;">Loading...</div>

<div style="background: #0f0f0f;">
 <div id="header"></div>
</div>
<?php DrawNonLoggedInNav(); ?>

<div id="lightBG">
<div class="fullWidthWrapper">
  <div style="height: 120px;"></div>
  <div id="contentPanel"
    style="margin: 0 auto; width: 400px; background: #fff; border-radius: 20px; padding: 20px 40px; box-shadow: 0 0 15px 10px  #FC6806;">
  	<div id="panelHeader">
  	  <h1>Please Log In:</h1>
    </div>
  	<form action="JavaScript: void(0);" id="loginForm" class="nextElement"
            onsubmit="JavaScript: submitLogin();" style="width: 309px;">
  		<input type="hidden" name="step" value="1">
  		<label for="nameInput">Your Username:</label>
  		<input type="text" name="username" id="nameInput" style="width: 290px;" class="text" maxlength="50" /><br />
  		<br />
  		<label for="emailInput">Your Password:</label>
  		<input type="password" name="password" id="emailInput" style="width: 290px;" class="text" maxlength="50" /><br />
  		<br />
		<input type="submit" class="centered" value="Submit"/>
  		<div id="loginMessage">&nbsp;</div>
  	</form>
  </div>
  <script type="text/javaScript">$('nameInput').focus();</script>
</div>
</div>
<script>
	function submitLogin() {
		$('loginMessage').set('text', 'Logging in...');
		new Request.HTML({ url: App.thisPage + '?a=login',
			method: 'post',
			data: $('loginForm'),
			evalScripts: true
		}).send();
	}

	function launchLoginHelper() {
		$('loginHelperForm').username.value = $('loginForm').username.value;
		swapSections('loginFormDiv', 'loginHelperFormDiv');
	}

	function cancelLoginHelper() { swapSections('loginHelperFormDiv', 'loginFormDiv'); }
	function closeLoginHelper() { swapSections('loginHelperCompletion', 'loginFormDiv'); }


	function submitLoginHelpRequest() {
		var theForm = $('loginHelperForm');

		if(theForm.helpMode[0].checked  &&  theForm.username.value == '') {
			alert('Please indicate your username.');
			theForm.username.focus();
			return;
		}

		if(theForm.helpMode[1].checked  &&  !isValidEmailAddress(theForm.email.value)) {
			alert('Please indicate your email address on file (must be valid).');
			theForm.email.select();
			return;
		}

		new Request.HTML({ url: App.thisPage + '?a=loginHelper',
			method: 'post',
			data: theForm,
			evalScripts: true
		}).send();
	}

	function redirectToURL(URL) { window.location.href = URL; }
</script>
</body>
</html>
<?php
}
<?php
	$LOGIN_PAGE = 1;
	$LOGIN_FAILED = 0;
	$error_message = "";

	require("forced.php");

	// Generate CSRF token if not exists
	if (!isset($_SESSION['csrf_token'])) {
		$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
	}

	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		// Validate CSRF token
		if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
			$error_message = "Invalid CSRF token";
		} 
		elseif (
			isset($_POST["username"]) &&
			isset($_POST["password"]) &&
			isset($_POST["password2"]) &&
			isset($_POST["description"]) &&
			isset($_POST["capcha"])
		) {
			$LOGIN_FAILED = 1;
			
			// Input validation
			$username = trim(htmlspecialchars($_POST["username"]));
			$description = trim(htmlspecialchars($_POST["description"]));
			
			// Username validation
			if (empty($username) || strlen($username) < 3 || strlen($username) > 50) {
				$error_message = "Le nom d'utilisateur doit comporter entre 3 et 50 caractères.";
			}
			// Password validation
			elseif (empty($_POST["password"]) || strlen($_POST["password"]) < 8) {
				$error_message = "Le mot de passe doit comporter au moins 8 caractères.";
			}
			elseif ($_POST["password"] !== $_POST["password2"]) {
				$error_message = "Les mots de passe ne correspondent pas.";
			}
			// Captcha validation
			elseif ($_POST["capcha"] !== $_SESSION["capcha"]) {
				$error_message = "Captcha incorrect.";
			}
			else {
				if (!search_user($username)) {
					$hashed_pwd = password_hash($_POST["password"], PASSWORD_DEFAULT);
					create_user($username, $hashed_pwd, $description);
					
					// Regenerate session ID to prevent session fixation
					session_regenerate_id(true);
					
					// Redirect with exit to prevent further code execution
					header("Location: /");
					exit();
				} else {
					$error_message = "Ce nom d'utilisateur existe déjà.";
				}
			}
		}
	}
	
	// Generate a new captcha for each page load
	$captcha = rand(10000, 99999);
	$_SESSION["capcha"] = $captcha;
?>
<html>
	<head>
		<title>Register Page</title>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<!-- Add Content Security Policy -->
		<meta http-equiv="Content-Security-Policy" content="default-src 'self'; style-src 'self' 'unsafe-inline'">
	</head>
	<body>
		<form action="#" method="POST">
			<img src="alexcloud.png" alt="Alex Cloud Logo"/>
			<?php if(!empty($error_message)) echo '<div>'.htmlspecialchars($error_message).'</div>'; ?>
			<input type="text" placeholder="username" name="username" maxlength="50" required/>
			<input type="password" placeholder="Password" name="password" minlength="8" required/>
			<input type="password" placeholder="Re-enter password" name="password2" minlength="8" required/>
			<input type="text" placeholder="Description du compte" name="description" maxlength="255"/>
			<img src="capcha.php" style="width: 50%; margin-top: 2%;" alt="Captcha"/>
			<input type="text" placeholder="Capcha" name="capcha" required/>
			<!-- Add CSRF token -->
			<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>"/>
			<input type="submit" value="Register !"/>
		</form>
	</body>
	<style>
		html { background: rgb(2,0,36); background: linear-gradient(90deg, rgba(2,0,36,1) 0%, rgba(9,9,121,1) 35%, rgba(0,212,255,1) 100%); }
		html, body, div { margin: 0; padding: 0; }
		* { position: relative; transition: all 1s; text-decoration: none; list-style: none; }
		form { width: 50%; margin-left: 22%; margin-top: 5%; background: rgba(200,200,200,0.4); padding: 3%; text-align: center; }

		form img { width: 85%; margin-bottom: 3%; }
		form a { color: #444; text-decoration: underline; }

		input { width: 75%; padding: 2%; margin: 1%; }
		input[type=submit] { width: 25%; }

		form div { width: 75%; margin: 1% 0; box-shadow: 0px 0px 2px red; padding: 1% 0; color: #944; transform: translateX(-50%); left: 50%; }
	</style>
</html>

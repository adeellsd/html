<?php
	// Ne pas démarrer de session ici car forced.php le fait déjà
	require("forced.php");
	// forced.php inclut déjà user_functions.php et lib_login.php

	// Vérification supplémentaire des droits administrateur
	if (!isset($_SESSION["username"]) || $_SESSION["admin"] != 1) {
		header("Location: /login.php");
		exit();
	}
?>

<html>
	<head>
		<title>Login Page</title>
	</head>
	<body>
		<h1>Cette page n'est pas écrite pour le moment...</h1>
	</body>
	<style>
		html { background: rgb(2,0,36); background: linear-gradient(90deg, rgba(2,0,36,1) 0%, rgba(9,9,121,1) 35%, rgba(0,212,255,1) 100%); }
		html, body, div { margin: 0; padding: 0; }
		* { position: relative; transition: all 1s; text-decoration: none; list-style: none; }
		
		h1 { color: white; }
	</style>
</html>

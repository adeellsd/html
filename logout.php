<?php
	// Importation des fichiers nécessaires
	require_once("lib_login.php");
	
	logout_user();
	header("Location: /");
	die();
?>

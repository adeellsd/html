<?php

	require("forced.php");

	if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
		die("Method not allowed");
	}

	if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
		die("Invalid CSRF token");
	}

	$file = basename($_POST["file"]);
	$filepath = "files/".$__connected["username"]."/".$file;

	if (file_exists($filepath)) {
		unlink($filepath);
		unlink($filepath.".alexdescfile");
	}

	header("Location: /");
?>

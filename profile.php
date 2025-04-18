<?php
require("forced.php");
// forced.php inclut déjà user_functions.php et lib_login.php
require_once("message_functions.php");

// Récupérer le nom d'utilisateur depuis l'URL
$username = isset($_GET['username']) ? $_GET['username'] : '';

// Vérifier si l'utilisateur existe
if (!($user_data = search_user($username))) {
    header("HTTP/1.0 404 Not Found");
    echo "<h1>Utilisateur introuvable</h1>";
    echo "<p>L'utilisateur demandé n'existe pas ou a été supprimé.</p>";
    echo "<p><a href='/'>Retour à l'accueil</a></p>";
    exit;
}

// Génération du jeton CSRF pour le formulaire
if (!isset($_SESSION)) {
    session_start();
}
$csrf_token = generate_csrf_token();

// Récupération de la liste des utilisateurs pour le sélecteur
$dbi = get_db_infos();
$conn = new mysqli($dbi["srv"], $dbi["usr"], $dbi["pwd"], $dbi["db"]);
$users = [];

if (!$conn->connect_error) {
    $stmt = $conn->prepare("SELECT username FROM users ORDER BY username");
    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $users[] = $row['username'];
        }
        
        $stmt->close();
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Profil de <?php echo htmlspecialchars($username); ?></title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body>
        <div id="profile">
            <a href="/"><img src="/alexcloud.png"/></a>
            <h1>Bienvenue sur la page de profil de <?php echo htmlspecialchars($user_data["username"]); ?></h1>
            <h3><?php if($user_data["admin"] == 1) echo "L'utilisateur est administrateur"; else echo "L'utilisateur n'est pas administrateur"; ?></h3>
            <br><br>
            <div id="joli">__________</div>
            <br><br>
            <h2>description de l'utilisateur :</h2>
            <div id="desc">
            <?php echo htmlspecialchars($user_data["description"]); ?>
            </div>
            <br><br>
            <h2>Envoyer un message :</h2>
            
            <!-- Message d'état pour l'envoi du message (initialement caché) -->
            <div id="message-status" style="display:none;"></div>
            
            <?php if(isset($__connected) && $__connected): ?>
                <!-- Formulaire d'envoi de message avec sélecteur de destinataire -->
                <form id="message-form">
                    <div class="form-group">
                        <label for="receiver-select">Destinataire :</label>
                        <select id="receiver-select" required>
                            <option value="">Sélectionnez un destinataire</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?php echo htmlspecialchars($user); ?>" <?php echo ($user === $user_data["username"]) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($user); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <textarea id="message-content" required>Message to send</textarea>
                    <input type="hidden" id="csrf-token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" id="sender-username" value="<?php echo htmlspecialchars($__connected["username"]); ?>">
                    <br>
                    <button type="submit" id="send-button">Envoyer</button>
                </form>
            <?php else: ?>
                <div class="login-prompt">
                    <p>Vous devez être connecté pour envoyer un message.</p>
                    <a href="/login.php" class="login-button">Se connecter</a>
                </div>
            <?php endif; ?>
            
            <?php if(isset($__connected) && $__connected && $__connected["username"] === $user_data["username"]): ?>
            <br><br>
            <div class="message-link">
                <a href="/view_messages.php">Voir tous mes messages reçus</a>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Script JavaScript pour l'envoi AJAX du message -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const messageForm = document.getElementById('message-form');
                if (!messageForm) return; // Sortir si l'utilisateur n'est pas connecté
                
                // Effacer le texte par défaut lors du focus
                document.getElementById('message-content').addEventListener('focus', function() {
                    if (this.value === 'Message to send') {
                        this.value = '';
                    }
                });
                
                // Rétablir le texte par défaut si rien n'est entré
                document.getElementById('message-content').addEventListener('blur', function() {
                    if (this.value === '') {
                        this.value = 'Message to send';
                    }
                });
                
                // Gérer la soumission du formulaire
                messageForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const messageContent = document.getElementById('message-content').value;
                    const receiverusername = document.getElementById('receiver-select').value;
                    const csrfToken = document.getElementById('csrf-token').value;
                    const senderusername = document.getElementById('sender-username').value;
                    const statusDiv = document.getElementById('message-status');
                    
                    // Validation basique côté client
                    if (messageContent === '' || messageContent === 'Message to send') {
                        statusDiv.textContent = 'Veuillez entrer un message.';
                        statusDiv.className = 'error';
                        statusDiv.style.display = 'block';
                        return;
                    }
                    
                    if (receiverusername === '') {
                        statusDiv.textContent = 'Veuillez sélectionner un destinataire.';
                        statusDiv.className = 'error';
                        statusDiv.style.display = 'block';
                        return;
                    }
                    
                    // Créer les données du formulaire
                    const formData = new FormData();
                    formData.append('content', messageContent);
                    formData.append('receiver_username', receiverusername);
                    formData.append('csrf_token', csrfToken);
                    formData.append('sender_username', senderusername); // Ajout explicite de l'expéditeur
                    
                    // Désactiver le bouton pendant l'envoi
                    document.getElementById('send-button').disabled = true;
                    
                    // Envoi de la requête AJAX
                    fetch('/send_message.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        console.log('Response status:', response.status);
                        return response.text().then(text => {
                            try {
                                return JSON.parse(text);
                            } catch(e) {
                                console.error('Erreur de parsing JSON:', e);
                                console.log('Texte reçu:', text);
                                throw new Error('Réponse invalide du serveur');
                            }
                        });
                    })
                    .then(data => {
                        console.log('Données reçues:', data);
                        statusDiv.textContent = data.message;
                        statusDiv.className = data.success ? 'success' : 'error';
                        statusDiv.style.display = 'block';
                        
                        // Réinitialiser le formulaire si l'envoi a réussi
                        if (data.success) {
                            document.getElementById('message-content').value = 'Message to send';
                        }
                    })
                    .catch(error => {
                        statusDiv.textContent = 'Une erreur est survenue lors de l\'envoi du message.';
                        statusDiv.className = 'error';
                        statusDiv.style.display = 'block';
                        console.error('Error:', error);
                    })
                    .finally(() => {
                        // Réactiver le bouton
                        document.getElementById('send-button').disabled = false;
                    });
                });
            });
        </script>
        
        <style>
            html { background: rgb(2,0,36); background: linear-gradient(90deg, rgba(2,0,36,1) 0%, rgba(9,9,121,1) 35%, rgba(0,212,255,1) 100%); text-align: center; color: white; }
            html, body, div { margin: 0; padding: 0; }
            * { position: relative; transition: all 1s; text-decoration: none; list-style: none; }
            
            #profile { width: 75%; background: rgba(200,200,200,0.4); padding: 2.5%; margin-left: 10%; margin-top: 20px; margin-bottom: 20px; border-radius: 8px; }
            
            #desc { border: 1px solid white; box-shadow: 0px 0px 2px white; padding: 2% 0; width: 70%; margin-left: 15%; font-size: 120%; background: rgba(255,255,255,0.4); color: #444; }
            
            #joli { width: 100%; background: #449; height: 2px; margin: 10px 0; }
            
            .form-group {
                margin-bottom: 15px;
                text-align: left;
                width: 70%;
                margin-left: 15%;
            }
            
            label {
                display: block;
                margin-bottom: 5px;
                text-align: left;
                color: white;
                font-weight: bold;
            }
            
            select {
                width: 100%;
                padding: 8px;
                border: 1px solid white;
                box-shadow: 0px 0px 2px white;
                background: rgba(255,255,255,0.4);
                color: #444;
                font-size: 16px;
                border-radius: 4px;
            }
            
            textarea { width: 70%; padding: 2%; font-size: 120%; background: rgba(255,255,255,0.4); color: #444; border: 1px solid white; box-shadow: 0px 0px 2px white; border-radius: 4px; }

            button { padding: 10px 20px; margin-top: 15px; background-color: #449; color: white; border: none; cursor: pointer; border-radius: 4px; font-weight: bold; }
            button:hover { background-color: #336; }
            button:disabled { background-color: #999; cursor: not-allowed; }
            
            #message-status {
                width: 70%;
                margin: 10px auto;
                padding: 10px;
                border-radius: 3px;
                font-weight: bold;
            }
            
            #message-status.success {
                background-color: rgba(100, 255, 100, 0.4);
                color: #060;
            }
            
            #message-status.error {
                background-color: rgba(255, 100, 100, 0.4);
                color: #600;
            }
            
            .message-link {
                margin-top: 20px;
            }
            
            .message-link a {
                display: inline-block;
                padding: 10px 20px;
                background-color: rgba(0, 0, 150, 0.5);
                color: white;
                border-radius: 4px;
                text-decoration: none;
            }
            
            .message-link a:hover {
                background-color: rgba(0, 0, 150, 0.7);
            }
            
            .login-prompt {
                width: 70%;
                margin: 20px auto;
                padding: 20px;
                background-color: rgba(255, 255, 255, 0.2);
                border-radius: 8px;
            }
            
            .login-button {
                display: inline-block;
                padding: 10px 20px;
                background-color: #449;
                color: white;
                border-radius: 4px;
                text-decoration: none;
                margin-top: 10px;
            }
            
            .login-button:hover {
                background-color: #336;
            }
        </style>
    </body>
</html>
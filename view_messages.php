<?php
require("forced.php");
require("message_functions.php");

// Vérification de l'authentification
if (!$__connected) {
    header("Location: /login.php");
    exit;
}

// Récupération des messages
$messages = get_received_messages($__connected["username"]);

// Si une demande de marquage comme lu est reçue
if (isset($_POST['mark_read']) && isset($_POST['message_id']) && isset($_POST['csrf_token'])) {
    if (verify_csrf_token($_POST['csrf_token'])) {
        mark_message_as_read($_POST['message_id'], $__connected["username"]);
    }
    // Redirection pour éviter les soumissions multiples
    header("Location: /view_messages.php");
    exit;
}
?>
<html>
    <head>
        <title>Mes messages</title>
    </head>
    <body>
        <div id="menu">
            <img src="/alexcloud.png"/>
            <div class="menu_entries"><a href="/">Accueil</a></div>
            <div class="menu_entries"><a href="/logout.php">Déconnexion</a></div>
            <div class="menu_entries"><a href="/profile/<?php echo htmlspecialchars($__connected["username"]); ?>">Mon Profil</a></div>
        </div>
        <div id="joli">__________</div>
        <div id="messages-container">
            <h1>Mes messages reçus</h1>
            
            <?php if (empty($messages)): ?>
                <div class="no-messages">Vous n'avez aucun message.</div>
            <?php else: ?>
                <div class="message-list">
                    <?php foreach ($messages as $message): ?>
                        <div class="message <?php echo $message['read_status'] ? 'read' : 'unread'; ?>">
                            <div class="message-header">
                                <span class="sender">De: <?php echo htmlspecialchars($message['sender_username']); ?></span>
                                <span class="timestamp"><?php echo htmlspecialchars($message['timestamp']); ?></span>
                            </div>
                            <div class="message-content">
                                <?php echo htmlspecialchars($message['content']); ?>
                            </div>
                            <?php if (!$message['read_status']): ?>
                                <form method="POST" class="mark-read-form">
                                    <input type="hidden" name="message_id" value="<?php echo (int)$message['id']; ?>">
                                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                    <input type="hidden" name="mark_read" value="1">
                                    <button type="submit">Marquer comme lu</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
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

        #messages-container { width: 76%; background: rgba(255,255,255,0.6); margin-left: 10%; padding: 2%; color: #333; }
        h1 { text-align: center; margin-bottom: 30px; }
        
        .message-list { display: flex; flex-direction: column; gap: 15px; }
        
        .message { 
            padding: 15px; 
            border-radius: 5px; 
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .message.unread { 
            background: rgba(255,255,255,0.8); 
            border-left: 5px solid #449;
        }
        
        .message.read { 
            background: rgba(200,200,200,0.4); 
            border-left: 5px solid #999;
        }
        
        .message-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-weight: bold;
        }
        
        .message-content {
            padding: 10px;
            background: rgba(255,255,255,0.4);
            border-radius: 3px;
        }
        
        .mark-read-form {
            text-align: right;
            margin-top: 10px;
        }
        
        button {
            padding: 5px 10px;
            background: #449;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        
        button:hover {
            background: #336;
        }
        
        .no-messages {
            text-align: center;
            padding: 20px;
            background: rgba(255,255,255,0.4);
            border-radius: 5px;
        }
    </style>
</html>
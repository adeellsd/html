<?php
// Démarrage de la session
session_start();

// Vérification de l'authentification
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Vérification CSRF
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    header('Location: index.php?error=csrf');
    exit();
}

// Vérification si un fichier a été téléchargé
if (isset($_FILES['userfile']) && $_FILES['userfile']['error'] === UPLOAD_ERR_OK) {
    
    // Obtenir l'extension et vérifier le type MIME
    $fileInfo = pathinfo($_FILES['userfile']['name']);
    $extension = strtolower($fileInfo['extension']);
    
    // Définir les types de fichiers autorisés
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt'];
    $allowedMimeTypes = [
        'image/jpeg', 'image/png', 'image/gif', 
        'application/pdf', 'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'text/plain'
    ];
    
    // Vérifier que l'extension est autorisée
    if (!in_array($extension, $allowedExtensions)) {
        header('Location: index.php?error=invalid_extension');
        exit();
    }
    
    // Vérifier le type MIME
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($_FILES['userfile']['tmp_name']);
    if (!in_array($mimeType, $allowedMimeTypes)) {
        header('Location: index.php?error=invalid_mime');
        exit();
    }
    
    // Générer un nom de fichier sécurisé
    $newFileName = md5(time() . rand()) . '.' . $extension;
    
    // Chemin de destination sécurisé
    $uploadDir = './files/' . $_SESSION['username'] . '/';
    
    // S'assurer que le répertoire existe
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0750, true);
    }
    
    $targetPath = $uploadDir . $newFileName;
    
    // Déplacer le fichier
    if (move_uploaded_file($_FILES['userfile']['tmp_name'], $targetPath)) {
        header('Location: index.php?success=upload');
    } else {
        header('Location: index.php?error=upload_failed');
    }
    
} else {
    header('Location: index.php?error=no_file');
}
exit();
?>
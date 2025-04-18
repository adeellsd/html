<?php
/**
 * En-têtes de sécurité HTTP pour renforcer la sécurité de l'application
 * Ce fichier doit être inclus au début de chaque page
 */

// Protection contre le clickjacking
header('X-Frame-Options: DENY');

// Protection XSS
header('X-XSS-Protection: 1; mode=block');

// Prévention du MIME-sniffing
header('X-Content-Type-Options: nosniff');

// Politique de sécurité du contenu (CSP)
// header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self'; font-src 'self'");

// Référer Policy
header('Referrer-Policy: strict-origin-when-cross-origin');

// Protection HSTS (HTTP Strict Transport Security) - si HTTPS est configuré
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
}

// Empêcher les navigateurs de stocker des données sensibles
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

// Empêcher PHP de divulguer des informations sur le serveur
ini_set('expose_php', 'Off');
?>
<?php
// Configuración de seguridad para el token (No toca la base de datos)
define('SECRET_KEY', 'pim_pom_es_un_muñeco_muy_guapo_y_de_carton'); 
define('CIPHER_METHOD', 'aes-256-cbc');

// Credenciales de Mailtrap (Copia los datos exactos de tu cuenta)
define('SMTP_HOST', 'sandbox.smtp.mailtrap.io');
define('SMTP_PORT', 587); 
define('SMTP_USER', '0f020bdbe5f488'); 
define('SMTP_PASS', '6bb039dc4ea053'); 
?>
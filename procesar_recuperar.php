<?php
include 'config.php';
include 'conexion.php';

require 'phpmailer/Exception.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (isset($_POST['correo'])) {
    $email = trim($_POST['correo']);

    // 1. Verificar si el correo existe en la base de datos
    $stmt = $conn->prepare("SELECT id_usuario FROM usuario WHERE correo = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        // 2. Generar código de 6 dígitos y su expiración (15 minutos)
        $codigo = rand(100000, 999999);
        $expiracion = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        // 3. Guardar el código en la base de datos
        $update = $conn->prepare("UPDATE usuario SET codigo_recuperacion = ?, codigo_expiracion = ? WHERE correo = ?");
        $update->bind_param("sss", $codigo, $expiracion, $email);
        $update->execute();

        // 4. Enviar el código por correo (Usando tu SMTP real de Gmail o Mailtrap)
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            $mail->Password   = SMTP_PASS;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // O STARTTLS según tu config
            $mail->Port       = SMTP_PORT;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom('soporte@vetvital.local', 'VetVital');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Código de recuperación - VetVital';
            $mail->Body    = "
                <div style='font-family: Arial, sans-serif; max-width: 500px; margin: 0 auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 8px;'>
                    <h2 style='color: #115e59; text-align: center;'>Restablecer Contraseña</h2>
                    <p>Hola, tu código de verificación para cambiar tu contraseña es el siguiente:</p>
                    <div style='background-color: #f1f5f9; font-size: 24px; font-weight: bold; text-align: center; padding: 15px; letter-spacing: 5px; color: #115e59; margin: 20px 0; border-radius: 6px;'>
                        {$codigo}
                    </div>
                    <p style='font-size: 12px; color: #64748b; text-align: center;'>Este código vencerá en 15 minutos.</p>
                </div>
            ";

            $mail->send();

            // 5. Redirigir a la pantalla donde meterá el código
            header("Location: verificar_codigo?correo=" . urlencode($email));
            exit;

        } catch (Exception $e) {
            echo "Error al enviar el correo: {$mail->ErrorInfo}";
        }
    } else {
        echo "<script>alert('El correo no está registrado.'); window.history.back();</script>";
    }
}
?>
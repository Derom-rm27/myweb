<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * @param array<string, string> $feedback
 * @param array<string, string> $oldInput
 */
function redirectWithFeedback(array $feedback, array $oldInput = []): void
{
    $_SESSION['contact_feedback'] = $feedback;

    if (!empty($oldInput)) {
        $_SESSION['contact_old_input'] = $oldInput;
    } else {
        unset($_SESSION['contact_old_input']);
    }

    header('Location: /index.php#contact');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithFeedback([
        'type'    => 'error',
        'message' => 'Método no permitido para enviar el mensaje.',
    ]);
}

$nameRaw    = $_POST['nombre'] ?? '';
$emailRaw   = $_POST['correo'] ?? '';
$messageRaw = $_POST['mensaje'] ?? '';

$name    = is_string($nameRaw) ? trim($nameRaw) : '';
$email   = is_string($emailRaw) ? trim($emailRaw) : '';
$message = is_string($messageRaw) ? trim($messageRaw) : '';

$oldInput = [
    'nombre'  => $name,
    'correo'  => $email,
    'mensaje' => $message,
];

$errors = [];

if ($name === '') {
    $errors[] = 'Debes indicar tu nombre completo.';
}

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Debes escribir un correo electrónico válido.';
}

if ($message === '') {
    $errors[] = 'Por favor, escribe un mensaje para el superusuario.';
}

if (!empty($errors)) {
    redirectWithFeedback([
        'type'    => 'error',
        'message' => implode(' ', $errors),
    ], $oldInput);
}

require_once __DIR__ . '/../../adm/script/conex.php';

try {
    $connection = new MySQLcn();
    $connection->Query("SELECT email, nombres FROM usuarios WHERE nivel = 1 AND estado = 1 AND email <> ''");
    $recipients = $connection->Rows();
    $connection->Close();
} catch (Throwable $exception) {
    redirectWithFeedback([
        'type'    => 'error',
        'message' => 'No fue posible contactar a los superusuarios en este momento. Inténtalo más tarde.',
    ], $oldInput);
}

if (empty($recipients)) {
    redirectWithFeedback([
        'type'    => 'error',
        'message' => 'No hay superusuarios disponibles para recibir tu mensaje en este momento.',
    ], $oldInput);
}

$subject = 'Nuevo mensaje de contacto desde el portal';
$bodyLines = [
    'Has recibido un nuevo mensaje desde el formulario de contacto.',
    '',
    'Detalles:',
    'Nombre: ' . $name,
    'Correo: ' . $email,
    '',
    'Mensaje:',
    $message,
    '',
    'Enviado el ' . date('d/m/Y H:i:s'),
];
$body = implode(PHP_EOL, $bodyLines);

$headers   = [];
$headers[] = 'From: Calidad de Software <no-reply@calidad-software.local>';
$headers[] = 'Reply-To: ' . $email;
$headers[] = 'Content-Type: text/plain; charset=UTF-8';

$headersString = implode("\r\n", $headers);

$sentCount = 0;
foreach ($recipients as $recipient) {
    $toAddress = isset($recipient['email']) ? trim((string)$recipient['email']) : '';
    if ($toAddress === '') {
        continue;
    }

    if (@mail($toAddress, $subject, $body, $headersString)) {
        $sentCount++;
    }
}

if ($sentCount === 0) {
    redirectWithFeedback([
        'type'    => 'error',
        'message' => 'No se pudo enviar tu mensaje. Por favor, inténtalo nuevamente en unos minutos.',
    ], $oldInput);
}

redirectWithFeedback([
    'type'    => 'success',
    'message' => '¡Tu mensaje fue enviado! Un superusuario se comunicará contigo pronto.',
]);

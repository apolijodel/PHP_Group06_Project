<?php
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/customer/index.php#contact');
}
csrf_check();

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');

$errors = [];
if ($name === '') $errors[] = 'Name is required.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
if ($message === '') $errors[] = 'Message is required.';

if ($errors) {
    flash_set('contact_error', implode(' ', $errors));
    flash_set('contact_old', ['name' => $name, 'email' => $email, 'message' => $message]);
    redirect('/customer/index.php#contact');
}

$stmt = db()->prepare('INSERT INTO feedback_messages (name, email, message) VALUES (:name, :email, :message)');
$stmt->execute(['name' => $name, 'email' => $email, 'message' => $message]);

flash_set('contact_success', 'Thanks for reaching out! We\'ll get back to you soon.');
redirect('/customer/index.php#contact');

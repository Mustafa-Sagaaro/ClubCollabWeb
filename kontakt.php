<?php
// ClubCollab Kontaktformular — Versand an verkauf@clubcollab.ch (Hostinger, PHP mail)
declare(strict_types=1);
mb_internal_encoding('UTF-8');

$quelle = ($_POST['quelle'] ?? '') === 'index' ? 'index' : 'kontakt';
$back   = $quelle === 'index' ? 'index.html' : 'kontakt.html';
$anchor = $quelle === 'index' ? '#kontakt' : '#form';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ./kontakt.html'); exit; }

// Honeypot: Menschen sehen dieses Feld nicht — Bots füllen es aus
if (trim((string)($_POST['website'] ?? '')) !== '') {
    header("Location: ./{$back}?sent=1{$anchor}"); exit;
}

$line = function (string $k): string {
    return mb_substr(trim(str_replace(["\r", "\n"], ' ', (string)($_POST[$k] ?? ''))), 0, 200);
};

$name    = $line('name');
$email   = filter_var($line('email'), FILTER_VALIDATE_EMAIL) ?: '';
$telefon = $line('telefon');
$verein  = $line('verein');
$groesse = $line('mitglieder');
$text    = mb_substr(trim((string)($_POST['nachricht'] ?? '')), 0, 5000);

if ($name === '' || $email === '' || $text === '') {
    header("Location: ./{$back}?sent=0{$anchor}"); exit;
}

$to      = 'verkauf@clubcollab.ch';
$subject = '=?UTF-8?B?' . base64_encode('Anfrage über clubcollab.ch — ' . ($verein !== '' ? $verein : $name)) . '?=';
$body    = "Neue Anfrage über das Kontaktformular (Seite: {$quelle})\n\n"
         . "Name:    {$name}\n"
         . "E-Mail:  {$email}\n"
         . ($telefon !== '' ? "Telefon: {$telefon}\n" : '')
         . ($verein  !== '' ? "Verein:  {$verein}\n"  : '')
         . ($groesse !== '' ? "Grösse:  {$groesse} Mitglieder\n" : '')
         . "\nAnliegen:\n{$text}\n";
$headers = "From: ClubCollab Website <no-reply@clubcollab.ch>\r\n"
         . "Reply-To: {$email}\r\n"
         . "MIME-Version: 1.0\r\n"
         . "Content-Type: text/plain; charset=UTF-8";

$ok = @mail($to, $subject, $body, $headers);
header("Location: ./{$back}?sent=" . ($ok ? '1' : '0') . $anchor);

<?php
session_start(); // Avvia la sessione

// Distruggi tutte le variabili di sessione
session_unset();

// Distruggi la sessione
session_destroy();

// Reindirizza l'utente alla pagina di login   
header("Location: index.php"); // Sostituisci "login.php" con il nome della tua pagina di login
exit;
?>
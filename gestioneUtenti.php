<?php
// Avvio della sessione
session_start();
if (!isset($_SESSION['id'])) {
    header('Location: index.php');
    exit;
}



$mysqli = new mysqli("localhost", "root", "", "gestioneutenti");
if ($mysqli->connect_error) {
    die("Connessione fallita: " . $mysqli->connect_error);
}



$idUtente = $_SESSION['id'];

$stmt = $mysqli->prepare("SELECT permessi FROM loginutente WHERE id = ?");
$stmt->bind_param('s', $idUtente);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($permessi);
$stmt->fetch();

?>


<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>Sezione con Card</title>
    <!-- Inclusione di UIKit -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/uikit/3.9.0/css/uikit.min.css">
   
</head>

<body>

<div class="uk-container">
        <!-- Navbar -->
        <nav class="uk-navbar-container uk-margin" uk-navbar>
            <div class="uk-navbar-left">
                <ul class="uk-navbar-nav">
                    <li><a href="gestioneUtenti.php" style="color:black">Home</a></li>
                    <?php if ($permessi == "admin"): ?>
                        <li><a href="GestioneUtentiDB.php" style="color:black">Gestione Utenti_DB</a></li>
                        <li><a href="logAzioniUtenti.php" style="color:black">Log Azioni Utenti_DB</a></li>
                    <?php endif; ?>

                    <?php if ($permessi == "admin" || $permessi=="scrittura"): ?>
                        <li><a href="inserimentoModificaDati.php" style="color:black">Inserimento/Modifica Dati</a></li>
                    <li><a href="inserimentoRichieste.php" style="color:black">Inserimento Richieste</a></li>
                    <?php endif; ?>
                    <li><a href="calendarioMensile.php" style="color:black">Calendario Mensile</a></li>
                    <li><a href="calendarioRichieste.php" style="color:black">Calendario Richieste</a></li>
                    <li><a href="logout.php" style="color:black">Logout</a></li>
                </ul>
            </div>
            <div class="uk-navbar-right">
                <a href=""><img src=".\images\logo.png" alt="Logo" width="100" height="100"></a>
            </div>
        </nav>

        <!-- Sezione con 6 card centrata -->
        <div class="uk-grid-small uk-grid-center uk-child-width-1-3@s uk-text-center" uk-grid>

            <!-- Card 1 -->
            <?php
            if ($permessi == "scrittura" || $permessi == "lettura") {
            } else {
                ?>
                <div>
                    <a href="GestioneUtentiDB.php">
                        <div class="uk-card uk-card-hover uk-card-default uk-card-body">
                            <h3 class="uk-card-title">Gestione Utenti_DB</h3>
                        </div>
                    </a>
                </div>
                <!-- Card 2 -->
                <div>
                    <a href="logAzioniUtenti.php">
                        <div class="uk-card uk-card-hover uk-card-default uk-card-body">
                            <h3 class="uk-card-title">Log Azioni Utenti_DB</h3>
                        </div>
                    </a>
                </div>
                <?php
            }

            ?>
            <!-- Card 5 -->
            <div>
                <a href="calendarioMensile.php">
                    <div class="uk-card uk-card-hover uk-card-default uk-card-body">
                        <h3 class="uk-card-title">Calendario Mensile</h3>
                    </div>
                </a>
            </div>

            <!-- Card 6 -->
            <div>
                <a href="calendarioRichieste.php">
                    <div class="uk-card uk-card-hover uk-card-default uk-card-body">
                        <h3 class="uk-card-title">Calendario Richieste</h3>
                    </div>
                </a>
            </div>






            <?php
            if ($permessi == "lettura") {
            } else {
                ?>
                <!-- Card 3 -->
                <div>
                    <a href="inserimentoModificaDati.php">
                        <div class="uk-card uk-card-hover uk-card-default uk-card-body">
                            <h3 class="uk-card-title">Inserimento/Modifica Dati</h3>
                        </div>
                    </a>
                </div>



                <!-- Card 4 -->
                <div>
                    <a href="inserimentoRichieste.php">
                        <div class="uk-card uk-card-hover uk-card-default uk-card-body">
                            <h3 class="uk-card-title">Inserimento Richieste</h3>
                        </div>
                    </a>
                </div>
                <?php
            }
            ?>

        </div>
    </div>

    <!-- Inclusione di UIKit JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/uikit/3.9.0/js/uikit.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/uikit/3.9.0/js/uikit-icons.min.js"></script>
</body>

</html>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Sezione con Card</title>
    <!-- Inclusione di UIKit -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/uikit/3.9.0/css/uikit.min.css">
    <style>
        /* Centrare le card verticalmente e orizzontalmente */
        .uk-grid-center {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 80vh; /* Imposta l'altezza minima per centrare verticalmente */
        }
        /* Personalizzazione della navbar */
        .uk-navbar-container {
            background-color: #1e87f0;
        }
        .uk-navbar-nav > li > a {
            color: #fff;
            font-weight: bold;
        }
        .uk-navbar-nav > li > a:hover {
            color: #ffdd57;
        }
    </style>
</head>
<body>

<div class="uk-container">

    <!-- Navbar con puntamenti e immagine in alto a destra -->
    <nav class="uk-navbar-container uk-margin" uk-navbar>
        <div class="uk-navbar-left">
            <ul class="uk-navbar-nav">
                <li><a href="GestioneUtentiDB.php" style="color:black">Gestione Utenti_DB</a></li>
                <li><a href="logAzioniUtenti.php" style="color:black">Log Azioni Utenti_DB</a></li>
                <li><a href="inserimentoModificaDati.php" style="color:black">Inserimento/Modifica Dati</a></li>
                <li><a href="calendarioMensile.php" style="color:black">Calendario Mensile</a></li>
                <li><a href="calendarioRichieste.php" style="color:black">Calendario Richieste</a></li>
                <li><a href="inserimentoRichieste.php" style="color:black">Inserimento Richieste</a></li>
            </ul>
        </div>
        <div class="uk-navbar-right">
            <!-- Immagine in alto a destra -->
            <a href=""><img src=".\images\logo.png" alt="Logo" width="100" height="100"></a>
        </div>
    </nav>

    <!-- Sezione con 6 card centrata -->
    <div class="uk-grid-small uk-grid-center uk-child-width-1-3@s uk-text-center" uk-grid>
        
        <!-- Card 1 -->
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
            <a href="calendarioMensile.php.php">
                <div class="uk-card uk-card-hover uk-card-default uk-card-body">
                    <h3 class="uk-card-title">Calendario Mensile</h3>
                </div>
            </a>
        </div>

        <!-- Card 5 -->
        <div>
            <a href="calendarioRichieste.php">
                <div class="uk-card uk-card-hover uk-card-default uk-card-body">
                    <h3 class="uk-card-title">Calendario Richieste</h3>
                </div>
            </a>
        </div>

        <!-- Card 6 -->
        <div>
            <a href="inserimentoRichieste.php">
                <div class="uk-card uk-card-hover uk-card-default uk-card-body">
                    <h3 class="uk-card-title">Inserimento Richieste</h3>
                </div>
            </a>
        </div>

    </div>
</div>

<!-- Inclusione di UIKit JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/uikit/3.9.0/js/uikit.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/uikit/3.9.0/js/uikit-icons.min.js"></script>
</body>
</html>
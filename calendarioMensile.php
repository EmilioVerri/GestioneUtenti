<?php

session_start();
if (!isset($_SESSION['id'])) {
    header('Location: index.php');
    exit;
} else {
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
}

?>


<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendario Mensile</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/uikit@3.6.16/dist/css/uikit.min.css" />
    <style>
        /* Ingrandire le celle */
        td {
            min-width: 150px;
            /* Larghezza minima */
            min-height: 150px;
            /* Altezza minima */
            vertical-align: top;
            /* Allinea il contenuto in alto */
        }

        /* Centrare il testo del giorno della settimana */
        th {
            text-align: center;
        }
    </style>
</head>

<body>

    <script>
        function stampaCalendario() {
            // Nascondi tutti gli elementi tranne la tabella
            const calendario = document.getElementById('calendario');
            const stampaDiv = document.createElement('div');
            stampaDiv.appendChild(calendario.cloneNode(true)); // Clona la tabella per evitare di modificare l'originale
            document.body.innerHTML = '';
            document.body.appendChild(stampaDiv);

            // Stampa la pagina
            window.print();

            // Ripristina il contenuto originale
            location.reload();
        }
    </script>


    <!-- Navbar -->
    <nav class="uk-navbar-container uk-margin" uk-navbar>
        <div class="uk-navbar-left">
            <ul class="uk-navbar-nav">
                <li><a href="gestioneUtenti.php" style="color:black">Home</a></li>
                <?php if ($permessi == "admin"): ?>
                    <li><a href="GestioneUtentiDB.php" style="color:black">Gestione Utenti_DB</a></li>
                    <li><a href="logAzioniUtenti.php" style="color:black">Log Azioni Utenti_DB</a></li>
                <?php endif; ?>
                <?php if ($permessi == "admin" || $permessi == "scrittura"): ?>
                    <li><a href="inserimentoRichieste.php" style="color:black">Inserimento Richieste</a></li>
                <?php endif; ?>
                <li><a href="calendarioMensile.php" style="color:black">Calendario Mensile</a></li>
                <li><a href="calendarioRichieste.php" style="color:black">Calendario Richieste</a></li>
                <li><a href="logout.php" style="color:black">Logout</a></li>
            </ul>
        </div>
        <div class="uk-navbar-right">
            <a href=""><img src="./images/logo.png" alt="Logo" width="100" height="100"></a>
        </div>
    </nav>

    <!-- Sezione Calendario -->

    <h2>Calendario Mensile</h2>

    <!-- Filtro per data e reparto -->
    <div class="uk-margin">
        <form method="GET" action="calendarioMensile.php">
            <div class="uk-grid-small uk-child-width-auto uk-grid">
                <div>
                    <label for="data_inizio">Da:</label>
                    <input class="uk-input" type="date" id="data_inizio" name="data_inizio" required>
                </div>
                <div>
                    <label for="data_fine">A:</label>
                    <input class="uk-input" type="date" id="data_fine" name="data_fine" required>
                </div>
                <div>
                    <label for="reparto">Reparto:</label>
                    <select class="uk-select" id="reparto" name="reparto">
                        <option value="">Tutti</option>
                        <?php
                        $reparti_result = $mysqli->query("SELECT nome FROM reparti");
                        while ($reparto = $reparti_result->fetch_assoc()) {
                            echo "<option value=\"" . htmlspecialchars($reparto['nome']) . "\">" . htmlspecialchars($reparto['nome']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div>
                    <button class="uk-button uk-button-primary" type="submit">Filtra</button>
                </div>
                <div>
                    <button class="uk-button uk-button-default" type="button" onclick="stampaCalendario();">Stampa</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Tabella Calendario Giornaliero -->
    <div class="uk-overflow-auto">
        <table class="uk-table uk-table-divider" id="calendario">
            <tbody>
                <?php
                // Array per contenere le box di ogni giorno della settimana
                $giorni_settimana = [
                    'Lunedì' => [],
                    'Martedì' => [],
                    'Mercoledì' => [],
                    'Giovedì' => [],
                    'Venerdì' => [],
                    'Sabato' => [],
                    'Domenica' => []
                ];

                $giorni_traduzione = [
                    'Monday' => 'Lunedì',
                    'Tuesday' => 'Martedì',
                    'Wednesday' => 'Mercoledì',
                    'Thursday' => 'Giovedì',
                    'Friday' => 'Venerdì',
                    'Saturday' => 'Sabato',
                    'Sunday' => 'Domenica'
                ];

                // Connessione al database e query
                $mysqli = new mysqli("localhost", "root", "", "gestioneutenti");

                if (isset($_GET['data_inizio']) && isset($_GET['data_fine'])) {
                    $data_inizio = DateTime::createFromFormat('Y-m-d', $_GET['data_inizio']);
                    $data_fine = DateTime::createFromFormat('Y-m-d', $_GET['data_fine']);
                    $reparto_filtrato = $_GET['reparto'] ?? '';

                    $period = new DatePeriod(
                        $data_inizio,
                        new DateInterval('P1D'),
                        $data_fine->modify('+1 day')
                    );

                    foreach ($period as $date) {
                        $giorno_settimana_inglese = $date->format('l');
                        $giorno_settimana = $giorni_traduzione[$giorno_settimana_inglese];
                        $data_formattata = $date->format('Y-m-d');

                        // Estrazione utenti in ferie per il giorno corrente con filtro reparto
                        $query = "SELECT utenti.nome, utenti.reparto FROM richieste 
                                JOIN utenti ON richieste.id_utente = utenti.id 
                                WHERE richieste.data = ?";
                        if ($reparto_filtrato) {
                            $query .= " AND utenti.reparto = ?";
                        }

                        $stmt = $mysqli->prepare($query);
                        if ($reparto_filtrato) {
                            $stmt->bind_param('ss', $data_formattata, $reparto_filtrato);
                        } else {
                            $stmt->bind_param('s', $data_formattata);
                        }
                        $stmt->execute();
                        $result = $stmt->get_result();

                        $utenti_ferie = [];
                        $reparti = [];
                        while ($row = $result->fetch_assoc()) {
                            $utenti_ferie[] = $row['nome'] . " (" . $row['reparto'] . ")";
                            $reparti[] = $row['reparto'];
                        }

                        $numero_utenti = count($utenti_ferie);
                        $numero_reparti = count(array_unique($reparti));

                        // Crea la box del giorno
                        $box_contenuto = "<div class='uk-card uk-card-default uk-card-body'>";
                        $box_contenuto .= "<p>$data_formattata</p>";
                        if ($numero_utenti > 0) {
                            $box_contenuto .= "<ul>";
                            foreach ($utenti_ferie as $utente) {
                                $box_contenuto .= "<li>$utente</li>";
                            }
                            $box_contenuto .= "</ul>";
                        } else {
                            $box_contenuto .= "<p>Nessuno in ferie</p>";
                        }
                        $box_contenuto .= "<hr>";
                        $box_contenuto .= "<p>Persone: $numero_utenti</p>";
                        $box_contenuto .= "<p>Reparti: $numero_reparti</p>";
                        $box_contenuto .= "</div>";

                        // Aggiungi la box alla lista corrispondente al giorno della settimana
                        $giorni_settimana[$giorno_settimana][] = $box_contenuto;
                    }

                    // Numero massimo di giorni (per gestire la lunghezza delle righe)
                    $max_giorni = max(array_map('count', $giorni_settimana));

                    // Popola la tabella
                    // Popola la tabella
                    foreach ($giorni_settimana as $giorno => $giorni) {
                        echo "<tr>";
                        
                        $info=1;
                        foreach ($giorni as $box) {
                            if($info==1){
                                echo "<td>$giorno</td>";
                                $info++;
                            }
                            echo "<td>$box</td>";
                        }
                        echo "</tr>";
                    }
                }
                ?>
            </tbody>
        </table>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/uikit@3.6.16/dist/js/uikit.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/uikit@3.6.16/dist/js/uikit-icons.min.js"></script>
</body>

</html>
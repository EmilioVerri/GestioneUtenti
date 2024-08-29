<?php
// Avvio della sessione e connessione al database
// Avvio della sessione
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

    if (!($permessi == "admin" || $permessi == "scrittura")) {
        header('Location: index.php');
        exit;
    }
}

// Ottieni i dati dell'utente selezionato
$id_utente = $_GET['id'];
$utente_result = $mysqli->query("SELECT nome FROM utenti WHERE id = $id_utente");
$utente = $utente_result->fetch_assoc();

// Estrazione delle tipologie di richiesta
$tipologie_result = $mysqli->query("SELECT id, nome FROM tipologie");

// Variabili di feedback
$insert_msg = '';
$update_msg = '';
$delete_msg = '';

// Gestione delle richieste (inserimento, modifica, cancellazione)
if (isset($_POST['azione'])) {
    $id_utente = $_POST['id_utente'];
    $tipologia = $_POST['tipologia'];
    $includi_sabato = isset($_POST['includi_sabato']);
    $includi_domenica = isset($_POST['includi_domenica']);

    if ($_POST['azione'] == 'inserisci') {
        $tipo_richiesta = $_POST['tipo_richiesta'];
        if ($tipo_richiesta == 'singolo') {
            $data = $_POST['data_singola'];
            inserisci_richiesta($mysqli, $id_utente, $data, $tipologia, $includi_sabato, $includi_domenica);
        } elseif ($tipo_richiesta == 'periodo') {
            $data_inizio = $_POST['data_inizio'];
            $data_fine = $_POST['data_fine'];
            inserisci_richiesta_per_periodo($mysqli, $id_utente, $data_inizio, $data_fine, $tipologia, $includi_sabato, $includi_domenica);
        }
        $insert_msg = "Richiesta inserita con successo!";
    } elseif ($_POST['azione'] == 'segna_usufruito') {



        if ($_POST['data_inizio'] && $_POST['data_fine']) {
            $data_inizio = $_POST['data_inizio'];
            $data_fine = $_POST['data_fine'];
            aggiorna_stato_richiesta_per_periodo($mysqli, $id_utente, $data_inizio, $data_fine, 'si');
            $update_msg = "Richiesta segnata come usufruita!";
        } else {
            $data = $_POST['data_singola'];
            aggiorna_stato_richiesta($mysqli, $id_utente, $data, 'si');
            $update_msg = "Richiesta segnata come usufruita!";
        }


    } elseif ($_POST['azione'] == 'cancella') {
        if ($_POST['data_inizio'] && $_POST['data_fine']) {
            $data_inizio = $_POST['data_inizio'];
            $data_fine = $_POST['data_fine'];
            cancella_richiesta_per_periodo($mysqli, $id_utente, $data_inizio, $data_fine);
            $delete_msg = "Richiesta cancellata!";
        } else {
            $data = $_POST['data_singola'];
            cancella_richiesta($mysqli, $id_utente, $data);
            $delete_msg = "Richiesta cancellata!";
        }

    }
}
function inserisci_richiesta($mysqli, $id_utente, $data, $tipologia, $includi_sabato, $includi_domenica)
{
    $data_formattata = DateTime::createFromFormat('Y-m-d', $data);
    if (!$data_formattata) {
        // Data non valida
        return;
    }
    $giorno_settimana = date('l', strtotime($data));
    if ((!$includi_sabato && $giorno_settimana == 'Saturday') || (!$includi_domenica && $giorno_settimana == 'Sunday')) {
        return;
    }
    $stmt = $mysqli->prepare("INSERT INTO richieste (id_utente, data, tipologia, usufruito) VALUES (?, ?, ?, 'no')");
    $stmt->bind_param('iss', $id_utente, $data, $tipologia);
    $stmt->execute();
    $stmt->close();
}


function inserisci_richiesta_per_periodo($mysqli, $id_utente, $data_inizio, $data_fine, $tipologia, $includi_sabato, $includi_domenica)
{
    $data_inizio_obj = DateTime::createFromFormat('Y-m-d', $data_inizio);
    $data_fine_obj = DateTime::createFromFormat('Y-m-d', $data_fine);

    if (!$data_inizio_obj || !$data_fine_obj) {
        // Data non valida
        return;
    }

    $data_inizio = $data_inizio_obj->format('Y-m-d');
    $data_fine = $data_fine_obj->format('Y-m-d');
    $current_date = strtotime($data_inizio);
    $end_date = strtotime($data_fine);

    while ($current_date <= $end_date) {
        $data = date('Y-m-d', $current_date);
        inserisci_richiesta($mysqli, $id_utente, $data, $tipologia, $includi_sabato, $includi_domenica);
        $current_date = strtotime('+1 day', $current_date);
    }
}
function aggiorna_stato_richiesta($mysqli, $id_utente, $data, $stato)
{
    $stmt = $mysqli->prepare("UPDATE richieste SET usufruito = ? WHERE id_utente = ? AND data = ?");
    $stmt->bind_param('sis', $stato, $id_utente, $data);
    $stmt->execute();
    $stmt->close();
}

function aggiorna_stato_richiesta_per_periodo($mysqli, $id_utente, $data_inizio, $data_fine, $stato)
{
    $data_inizio_obj = DateTime::createFromFormat('Y-m-d', $data_inizio);
    $data_fine_obj = DateTime::createFromFormat('Y-m-d', $data_fine);

    if (!$data_inizio_obj || !$data_fine_obj) {
        // Date non valide
        return;
    }

    $current_date = strtotime($data_inizio);
    $end_date = strtotime($data_fine);

    while ($current_date <= $end_date) {
        $data = date('Y-m-d', $current_date);
        aggiorna_stato_richiesta($mysqli, $id_utente, $data, $stato);
        $current_date = strtotime('+1 day', $current_date);
    }
}

function cancella_richiesta($mysqli, $id_utente, $data)
{
    $stmt = $mysqli->prepare("DELETE FROM richieste WHERE id_utente = ? AND data = ?");
    $stmt->bind_param('is', $id_utente, $data);
    $stmt->execute();
    $stmt->close();
}

function cancella_richiesta_per_periodo($mysqli, $id_utente, $data_inizio, $data_fine)
{
    $data_inizio_obj = DateTime::createFromFormat('Y-m-d', $data_inizio);
    $data_fine_obj = DateTime::createFromFormat('Y-m-d', $data_fine);

    if (!$data_inizio_obj || !$data_fine_obj) {
        // Date non valide
        return;
    }

    $current_date = strtotime($data_inizio);
    $end_date = strtotime($data_fine);

    while ($current_date <= $end_date) {
        $data = date('Y-m-d', $current_date);
        cancella_richiesta($mysqli, $id_utente, $data);
        $current_date = strtotime('+1 day', $current_date);
    }
}

// Estrazione delle richieste dell'utente
$richieste_result = $mysqli->query("SELECT * FROM richieste WHERE id_utente = $id_utente");
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>Dettagli Utente</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/uikit/3.9.0/css/uikit.min.css">
    <style>
        .uk-list-divider {
            max-height: 300px;
            /* Adatta l'altezza massima in base alle tue esigenze */
            overflow-y: auto;
        }
    </style>
</head>

<body>
    <div class="uk-container">
        <!-- Navbar -->
        <nav class="uk-navbar-container uk-margin" uk-navbar>
            <div class="uk-navbar-left">
                <ul class="uk-navbar-nav">
                    <li><a href="inserimentoRichieste.php" style="color:black">Torna alla lista utenti</a></li>
                </ul>
            </div>
            <div class="uk-navbar-right">
                <a href="#"><img src="images/logo.png" alt="Logo" width="100" height="100"></a>
            </div>
        </nav>

        <!-- Messaggi di feedback -->
        <?php if ($insert_msg): ?>
            <div class="uk-alert-success" uk-alert>
                <p><?= htmlspecialchars($insert_msg); ?></p>
            </div>
        <?php endif; ?>
        <?php if ($update_msg): ?>
            <div class="uk-alert-primary" uk-alert>
                <p><?= htmlspecialchars($update_msg); ?></p>
            </div>
        <?php endif; ?>
        <?php if ($delete_msg): ?>
            <div class="uk-alert-danger" uk-alert>
                <p><?= htmlspecialchars($delete_msg); ?></p>
            </div>
        <?php endif; ?>

        <h2>Gestione Richieste per <strong><?= htmlspecialchars($utente['nome']); ?></strong></h2>

        <!-- Form per inserire nuove richieste -->
        <form method="POST">
            <input type="hidden" name="id_utente" value="<?= htmlspecialchars($id_utente); ?>">
            <div class="uk-margin">
                <label class="uk-form-label">Tipologia richiesta</label>
                <select class="uk-select" name="tipologia">
                    <?php while ($tipologia = $tipologie_result->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($tipologia['nome']); ?>">
                            <?= htmlspecialchars($tipologia['nome']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="uk-margin">
                <div class="uk-form-controls">
                    <label>
                        <input class="uk-radio" type="radio" name="tipo_richiesta" value="singolo" checked> Giorno
                        singolo
                    </label><br>
                    <label>
                        <input class="uk-radio" type="radio" name="tipo_richiesta" value="periodo">
                        Periodo di giorni
                    </label>
                </div>
            </div>

            <!-- Data singola -->
            <div class="uk-margin" id="campo-data-singola">
                <label class="uk-form-label">Seleziona data</label>
                <input class="uk-input" type="date" name="data_singola">
            </div>

            <!-- Periodo -->
            <div class="uk-grid-small" id="campo-periodo" uk-grid style="display:none;">
                <div class="uk-width-1-2">
                    <label class="uk-form-label">Data inizio</label>
                    <input class="uk-input" type="date" name="data_inizio">
                </div>
                <div class="uk-width-1-2">
                    <label class="uk-form-label">Data fine</label>
                    <input class="uk-input" type="date" name="data_fine">
                </div>
            </div>


            <div class="uk-margin">
                <label><input class="uk-checkbox" type="checkbox" name="includi_sabato"> Includi sabato</label><br>
                <label><input class="uk-checkbox" type="checkbox" name="includi_domenica"> Includi domenica</label>
            </div>

            <hr>

            <!-- Elenco richieste dell'utente -->
            <h4>Richieste effettuate</h4>
            <ul class="uk-list uk-list-divider">
                <?php while ($richiesta = $richieste_result->fetch_assoc()): ?>
                    <li data-tipologia="<?= htmlspecialchars($richiesta['tipologia']); ?>">
                        <p><?= htmlspecialchars($richiesta['data']); ?> -
                            <?= htmlspecialchars($richiesta['tipologia']); ?> - Usufruito:
                            <?= htmlspecialchars($richiesta['usufruito']); ?>
                        </p>
                    </li>
                <?php endwhile; ?>
            </ul>

            <hr>

            <!-- Pulsanti azione -->
            <div class="uk-margin">
                <button type="submit" name="azione" value="inserisci" class="uk-button uk-button-primary">Inserisci
                    richiesta</button>
                <button type="submit" name="azione" value="segna_usufruito" class="uk-button uk-button-secondary">Segna
                    usufruito</button>
                <button type="submit" name="azione" value="cancella" class="uk-button uk-button-danger">Cancella
                    selezione</button>
            </div>
        </form>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/uikit/3.9.0/js/uikit.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/uikit/3.9.0/js/uikit-icons.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tipoRichiestaRadios = document.querySelectorAll('input[name="tipo_richiesta"]');
            const campoDataSingola = document.getElementById('campo-data-singola');
            const campoPeriodo = document.getElementById('campo-periodo');

            // Funzione per gestire la visualizzazione dei campi
            function aggiornaVisualizzazioneCampi() {
                if (document.querySelector('input[name="tipo_richiesta"]:checked').value === 'singolo') {
                    campoDataSingola.style.display = 'block';
                    campoPeriodo.style.display = 'none';
                } else {
                    campoDataSingola.style.display = 'none';
                    campoPeriodo.style.display = 'block';
                }
            }

            // Event listener per cambiare la visualizzazione in base alla selezione
            tipoRichiestaRadios.forEach(radio => {
                radio.addEventListener('change', aggiornaVisualizzazioneCampi);
            });

            // Esegui la funzione di aggiornamento all'avvio della pagina
            aggiornaVisualizzazioneCampi();
        });
    </script>
</body>

</html>

<?php
// Chiudi la connessione al database
$mysqli->close();
?>
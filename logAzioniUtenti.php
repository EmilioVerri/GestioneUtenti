<?php
// Avvio della sessione e connessione al database
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

    if ($permessi != "admin") {
        header('Location: index.php');
        exit;
    }
}

// Estrazione dei dati

$users_result = $mysqli->query("SELECT id, nome, reparto FROM utenti");
$records_result = $mysqli->query("SELECT id_utente, data, tipologia, record FROM logazioni");
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Utenti</title>
    <!-- Collegamento a UIKit CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/uikit@3.15.10/dist/css/uikit.min.css" />
    <style>
        .filter-input {
            margin-bottom: 10px;
        }
    </style>
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
                    <li><a href="inserimentoRichieste.php" style="color:black">Inserimento/Modifica Dati</a></li>
                <?php endif; ?>
                <li><a href="inserimentoModificaDati.php" style="color:black">Inserimento/Modifica Dati</a></li>
                <li><a href="calendarioMensile.php" style="color:black">Calendario Mensile</a></li>
                <li><a href="calendarioRichieste.php" style="color:black">Calendario Richieste</a></li>
                <li><a href="logout.php" style="color:black">Logout</a></li>
            </ul>
        </div>
        <div class="uk-navbar-right">
            <a href=""><img src=".\images\logo.png" alt="Logo" width="100" height="100"></a>
        </div>
    </nav>

    <div class="uk-container uk-margin-top">
        <h2>Gestione Utenti</h2>

        <!-- Filtri -->
        <div class="filter-input">
            <input type="text" id="filterName" placeholder="Filtra per nome utente" class="uk-input">
        </div>
        <!--
        <div class="filter-input">
            <input type="date" id="filterDate" class="uk-input">
        </div>-->

        <!-- Tabella con 4 campi -->
        <table class="uk-table uk-table-divider uk-table-striped" id="userTable">
            <thead>
                <tr>
                    <th>Utente</th>
                    <th>Data</th>
                    <th>Tipologia</th>
                    <th>Record</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($record = $records_result->fetch_assoc()): ?>
                    <?php
                    // Trova il nome dell'utente
                    $stmt = $mysqli->prepare("SELECT nome FROM loginutente WHERE id = ?");
                    $stmt->bind_param('i', $record['id_utente']);
                    $stmt->execute();
                    $stmt->store_result();
                    $stmt->bind_result($nomeUtente);
                    $stmt->fetch();



                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($nomeUtente); ?></td>
                        <td><?php echo htmlspecialchars($record['data']); ?></td>
                        <td><?php echo htmlspecialchars($record['tipologia']); ?></td>
                        <td><?php echo htmlspecialchars($record['record']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Collegamento a UIKit JS -->
    <script src="https://cdn.jsdelivr.net/npm/uikit@3.15.10/dist/js/uikit.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/uikit@3.15.10/dist/js/uikit-icons.min.js"></script>

    <!-- Script per filtrare la tabella -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const nameFilter = document.getElementById('filterName');
            const dateFilter = document.getElementById('filterDate');
            const table = document.getElementById('userTable');
            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

            function filterTable() {
                const nameValue = nameFilter.value.toLowerCase();
                const dateValue = dateFilter.value;
                
                for (let row of rows) {
                    const cells = row.getElementsByTagName('td');
                    const name = cells[0].textContent.toLowerCase();
                    const date = cells[1].textContent;

                    const nameMatch = name.includes(nameValue);
                    const dateMatch = date.includes(dateValue);

                    if (nameMatch && dateMatch) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                }
            }

            nameFilter.addEventListener('input', filterTable);
            dateFilter.addEventListener('input', filterTable);
        });
    </script>
</body>
</html>
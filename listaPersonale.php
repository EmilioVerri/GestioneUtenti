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
}

// Estrazione dei reparti e utenti
$reparti_result = $mysqli->query("SELECT id, nome FROM reparti");
$utenti_result = $mysqli->query("SELECT nome, reparto FROM utenti");
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista del Personale</title>
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
                <li><a href="gestioneUtenti.php" style="color:black">Torna alla Home</a></li>
            </ul>
        </div>
        <div class="uk-navbar-right">
            <a href=""><img src=".\images\logo.png" alt="Logo" width="100" height="100"></a>
        </div>
    </nav>

    <div class="uk-container uk-margin-top">
        <h2>Lista del Personale</h2>

        <!-- Filtro per nome e reparto -->
        <div class="filter-input">
            <input type="text" id="filterNome" placeholder="Filtra per nome" class="uk-input">
        </div>
        <div class="filter-input">
            <select id="filterReparto" class="uk-select">
                <option value="">Tutti i Reparti</option>
                <?php while ($reparto = $reparti_result->fetch_assoc()): ?>
                    <option value="<?php echo htmlspecialchars($reparto['nome']); ?>">
                        <?php echo htmlspecialchars($reparto['nome']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <!-- Tabella con lista del personale -->
        <table class="uk-table uk-table-divider uk-table-striped" id="personaleTable">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Reparto</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($utente = $utenti_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($utente['nome']); ?></td>
                        <td><?php echo htmlspecialchars($utente['reparto']); ?></td>
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
            const nomeFilter = document.getElementById('filterNome');
            const repartoFilter = document.getElementById('filterReparto');
            const table = document.getElementById('personaleTable');
            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

            function filterTable() {
                const nomeValue = nomeFilter.value.toLowerCase();
                const repartoValue = repartoFilter.value.toLowerCase();

                for (let row of rows) {
                    const cells = row.getElementsByTagName('td');
                    const nome = cells[0].textContent.toLowerCase();
                    const reparto = cells[1].textContent.toLowerCase();

                    const nomeMatch = nome.includes(nomeValue);
                    const repartoMatch = reparto.includes(repartoValue) || repartoValue === "";

                    if (nomeMatch && repartoMatch) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                }
            }

            nomeFilter.addEventListener('input', filterTable);
            repartoFilter.addEventListener('change', filterTable);
        });
    </script>
</body>
</html>
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

    if (!($permessi == "admin" || $permessi == "scrittura")) {
        header('Location: index.php');
        exit;
    }
}

// Estrazione dei reparti e utenti
$reparti_result = $mysqli->query("SELECT id, nome FROM reparti");
$users_result = $mysqli->query("SELECT id, nome, reparto FROM utenti");
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>Gestione Richieste</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/uikit/3.9.0/css/uikit.min.css">
    <style>
        .uk-list {
            max-height: 300px;
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
                <li><a href="gestioneUtenti.php" style="color:black">Home</a></li>
                <?php if ($permessi == "admin"): ?>
                    <li><a href="GestioneUtentiDB.php" style="color:black">Gestione Utenti_DB</a></li>
                    <li><a href="logAzioniUtenti.php" style="color:black">Log Azioni Utenti_DB</a></li>
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

        <!-- Filtro utenti e reparti -->
        <div class="uk-margin">
            <h3>Filtra Utenti</h3>
            <form class="uk-grid-small" uk-grid>
                <div class="uk-width-1-3@s">
                    <input class="uk-input" type="text" placeholder="Cerca per nome utente" id="filtro-nome">
                </div>
                <div class="uk-width-1-3@s">
                    <select class="uk-select" id="filtro-reparto">
                        <option value="">Tutti i reparti</option>
                        <?php while ($row = $reparti_result->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($row['nome']); ?>"><?= htmlspecialchars($row['nome']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="uk-width-1-3@s">
                    <button type="button" class="uk-button uk-button-primary" onclick="filtroUtenti()">Filtra</button>
                </div>
            </form>
        </div>

        <!-- Lista degli utenti filtrati -->
        <ul class="uk-list uk-list-divider" id="lista-utenti">
            <?php while ($row = $users_result->fetch_assoc()): ?>
                <li class="utente" data-nome="<?= htmlspecialchars($row['nome']); ?>"
                    data-reparto="<?= htmlspecialchars($row['reparto']); ?>">
                    <a href="dettagliUtente.php?id=<?= htmlspecialchars($row['id']); ?>">
                        <?= htmlspecialchars($row['nome']); ?>
                    </a>
                </li>
            <?php endwhile; ?>
        </ul>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/uikit/3.9.0/js/uikit.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/uikit/3.9.0/js/uikit-icons.min.js"></script>
    <script>
        // Filtro utenti per nome e reparto
        function filtroUtenti() {
            const nome = document.getElementById('filtro-nome').value.toLowerCase();
            const reparto = document.getElementById('filtro-reparto').value;

            document.querySelectorAll('#lista-utenti .utente').forEach(utente => {
                const nomeUtente = utente.getAttribute('data-nome').toLowerCase();
                const repartoUtente = utente.getAttribute('data-reparto');

                if ((nome === '' || nomeUtente.includes(nome)) && (reparto === '' || repartoUtente === reparto)) {
                    utente.style.display = 'block';
                } else {
                    utente.style.display = 'none';
                }
            });
        }
    </script>

</body>

</html>

<?php
// Chiudi la connessione al database
$mysqli->close();
?>
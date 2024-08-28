<?php
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

// Variabili di feedback
$update_msg = '';
$create_msg = '';
$delete_msg = '';

// Modifica utente esistente
if (isset($_POST['modifica_utente'])) {
    $user_id = $_POST['user_id'];
    $nome = $_POST['nome'];
    $reparto = $_POST['reparto'];
    $dipendente = isset($_POST['dipendente']) ? 'si' : 'no';

    $stmt = $mysqli->prepare("UPDATE utenti SET nome = ?, reparto = ?, dipendente = ? WHERE id = ?");
    $stmt->bind_param('sssi', $nome, $reparto, $dipendente, $user_id);

    if ($stmt->execute()) {
        $update_msg = "Utente modificato con successo!";
    } else {
        $update_msg = "Errore nella modifica dell'utente.";
    }

    $stmt->close();
}

// Creazione nuovo utente
if (isset($_POST['crea_utente'])) {
    $nome = $_POST['nome'];
    $reparto = $_POST['reparto'];
    $dipendente = isset($_POST['dipendente']) ? 'si' : 'no';

    $stmt = $mysqli->prepare("INSERT INTO utenti (nome, reparto, dipendente) VALUES (?, ?, ?)");
    $stmt->bind_param('sss', $nome, $reparto, $dipendente);

    if ($stmt->execute()) {
        $create_msg = "Utente creato con successo!";
    } else {
        $create_msg = "Errore nella creazione dell'utente.";
    }

    $stmt->close();
}

// Eliminazione utente
if (isset($_POST['elimina_utente'])) {
    $user_id = $_POST['user_id'];

    $stmt = $mysqli->prepare("DELETE FROM utenti WHERE id = ?");
    $stmt->bind_param('i', $user_id);

    if ($stmt->execute()) {
        $delete_msg = "Utente eliminato con successo!";
    } else {
        $delete_msg = "Errore nell'eliminazione dell'utente.";
    }

    $stmt->close();
}

// Estrazione degli utenti e reparti
$users_result = $mysqli->query("SELECT id, nome, reparto, dipendente FROM utenti");
$reparti_result = $mysqli->query("SELECT id, nome FROM reparti");
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Gestione Utenti - Dipendenti</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/uikit/3.9.0/css/uikit.min.css">
</head>
<style>
  .uk-list {
    max-height: 300px;
    overflow-y: auto;
  }
</style>
<body>

<div class="uk-container">

    <!-- Header con navbar -->
    <nav class="uk-navbar-container uk-margin" uk-navbar>
        <div class="uk-navbar-left">
            <ul class="uk-navbar-nav">
                <li><a href="gestioneUtenti.php" style="color:black">Home</a></li>
                <?php if ($permessi == "admin"): ?>
                    <li><a href="GestioneUtentiDB.php" style="color:black">Gestione Utenti_DB</a></li>
                    <li><a href="logAzioniUtenti.php" style="color:black">Log Azioni Utenti_DB</a></li>
                <?php endif; ?>
                
                <li><a href="inserimentoRichieste.php" style="color:black">Inserimento Richieste</a></li>
                <li><a href="calendarioMensile.php" style="color:black">Calendario Mensile</a></li>
                <li><a href="calendarioRichieste.php" style="color:black">Calendario Richieste</a></li>
                <li><a href="logout.php" style="color:black">Logout</a></li>
            </ul>
        </div>
        <div class="uk-navbar-right">
            <a href=""><img src=".\images\logo.png" alt="Logo" width="100" height="100"></a>
        </div>
    </nav>

    <!-- Messaggi di feedback -->
    <?php if ($update_msg): ?>
        <div class="uk-alert-primary" uk-alert>
            <p><?= htmlspecialchars($update_msg); ?></p>
        </div>
    <?php endif; ?>
    <?php if ($create_msg): ?>
        <div class="uk-alert-success" uk-alert>
            <p><?= htmlspecialchars($create_msg); ?></p>
        </div>
    <?php endif; ?>
    <?php if ($delete_msg): ?>
        <div class="uk-alert-danger" uk-alert>
            <p><?= htmlspecialchars($delete_msg); ?></p>
        </div>
    <?php endif; ?>

    <div class="uk-grid-match uk-grid-small uk-child-width-1-2@m" uk-grid>
        <!-- Colonna dei Reparti -->
        <div>
            <h3>Reparti</h3>
            <ul class="uk-list uk-list-divider">
                <?php while ($row = $reparti_result->fetch_assoc()): ?>
                    <li><?= htmlspecialchars($row['nome']); ?></li>
                <?php endwhile; ?>
            </ul>
        </div>

        <!-- Colonna degli Utenti -->
        <div>
            <h3>Dipendenti</h3>
            <ul class="uk-list uk-list-divider">
                <?php while ($row = $users_result->fetch_assoc()): ?>
                    <li>
                        <?= htmlspecialchars($row['nome']); ?>
                        <a href="#modal-modifica-<?= htmlspecialchars($row['id']); ?>" uk-toggle>Modifica</a>
                        <a href="#modal-elimina-<?= htmlspecialchars($row['id']); ?>" uk-toggle>Elimina</a>
                        
                        <!-- Modal Modifica Utente -->
                        <div id="modal-modifica-<?= htmlspecialchars($row['id']); ?>" uk-modal>
                            <div class="uk-modal-dialog uk-modal-body">
                                <h2 class="uk-modal-title">Modifica Utente</h2>
                                <form method="POST">
                                    <input type="hidden" name="user_id" value="<?= htmlspecialchars($row['id']); ?>">
                                    <div class="uk-margin">
                                        <label class="uk-form-label">Nome</label>
                                        <input class="uk-input" type="text" name="nome" value="<?= htmlspecialchars($row['nome']); ?>" required>
                                    </div>
                                    <div class="uk-margin">
                                        <label class="uk-form-label">Reparto</label>
                                        <select class="uk-select" name="reparto" required>
                                            <?php
                                            // Re-query the departments for each modal
                                            $reparti_result_modal = $mysqli->query("SELECT id, nome FROM reparti");
                                            while ($reparto_row = $reparti_result_modal->fetch_assoc()): ?>
                                                <option value="<?= htmlspecialchars($reparto_row['nome']); ?>" <?= ($row['reparto'] == $reparto_row['nome']) ? 'selected' : ''; ?>>
                                                    <?= htmlspecialchars($reparto_row['nome']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    <div class="uk-margin">
                                        <label class="uk-form-label">Dipendente</label>
                                        <input class="uk-checkbox" type="checkbox" name="dipendente" <?= ($row['dipendente'] == 'si') ? 'checked' : ''; ?>>
                                    </div>
                                    <button class="uk-button uk-button-primary" type="submit" name="modifica_utente">Salva Modifiche</button>
                                    <button class="uk-button uk-button-default uk-modal-close" type="button">Annulla</button>
                                </form>
                            </div>
                        </div>

                        <!-- Modal Elimina Utente -->
                        <div id="modal-elimina-<?= htmlspecialchars($row['id']); ?>" uk-modal>
                            <div class="uk-modal-dialog uk-modal-body">
                                <h2 class="uk-modal-title">Conferma Eliminazione</h2>
                                <p>Sei sicuro di voler eliminare <?= htmlspecialchars($row['nome']); ?>?</p>
                                <form method="POST">
                                    <input type="hidden" name="user_id" value="<?= htmlspecialchars($row['id']); ?>">
                                    <button class="uk-button uk-button-danger" type="submit" name="elimina_utente">Elimina</button>
                                    <button class="uk-button uk-button-default uk-modal-close" type="button">Annulla</button>
                                </form>
                            </div>
                        </div>
                    </li>
                <?php endwhile; ?>
            </ul>
        </div>
    </div>

    <!-- Pulsante per Creare un Nuovo Utente -->
    <div class="uk-margin">
        <button class="uk-button uk-button-primary" uk-toggle="target: #modal-crea-utente">Crea Nuovo Utente</button>
    </div>

    <!-- Modal Crea Utente -->
    <div id="modal-crea-utente" uk-modal>
        <div class="uk-modal-dialog uk-modal-body">
            <h2 class="uk-modal-title">Crea Nuovo Utente</h2>
            <form method="POST">
                <div class="uk-margin">
                    <label class="uk-form-label">Nome</label>
                    <input class="uk-input" type="text" name="nome" required>
                </div>
                <div class="uk-margin">
                    <label class="uk-form-label">Reparto</label>
                    <select class="uk-select" name="reparto" required>
                        <?php
                        // Re-query for the departments list
                        $reparti_result = $mysqli->query("SELECT id, nome FROM reparti");
                        while ($row = $reparti_result->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($row['nome']); ?>"><?= htmlspecialchars($row['nome']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="uk-margin">
                    <label class="uk-form-label">Dipendente</label>
                    <input class="uk-checkbox" type="checkbox" name="dipendente">
                </div>
                <button class="uk-button uk-button-primary" type="submit" name="crea_utente">Crea Utente</button>
                <button class="uk-button uk-button-default uk-modal-close" type="button">Annulla</button>
            </form>
        </div>
    </div>

</div>

<!-- Inclusione di UIKit JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/uikit/3.9.0/js/uikit.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/uikit/3.9.0/js/uikit-icons.min.js"></script>
</body>
</html>

<?php
// Chiusura della connessione
$mysqli->close();
?>
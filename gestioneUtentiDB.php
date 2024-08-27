<?php
// Avvio della sessione e connessione al database
session_start();
$mysqli = new mysqli("localhost", "root", "", "gestioneutenti");

if ($mysqli->connect_error) {
    die("Connessione fallita: " . $mysqli->connect_error);
}

// Funzione per hashare la password
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Variabili di feedback
$insert_msg = '';
$update_msg = '';
$delete_msg = '';

// Inserimento nuovo utente
if (isset($_POST['inserisci_utente'])) {
    $nome = $_POST['nome'];
    $password = hashPassword($_POST['password']);
    $permessi = $_POST['permessi'];

    $stmt = $mysqli->prepare("INSERT INTO loginutente (nome, password, permessi) VALUES (?, ?, ?)");
    $stmt->bind_param('sss', $nome, $password, $permessi);

    if ($stmt->execute()) {
        $insert_msg = "Utente inserito con successo!";
    } else {
        $insert_msg = "Errore nell'inserimento dell'utente.";
    }

    $stmt->close();
}

// Modifica utente esistente
if (isset($_POST['modifica_utente'])) {
    $user_id = $_POST['user_id'];
    $nome = $_POST['nome'];
    $password = hashPassword($_POST['password']);
    $permessi = $_POST['permessi'];

    $stmt = $mysqli->prepare("UPDATE loginutente SET nome = ?, password = ?, permessi = ? WHERE id = ?");
    $stmt->bind_param('sssi', $nome, $password, $permessi, $user_id);

    if ($stmt->execute()) {
        $update_msg = "Utente modificato con successo!";
    } else {
        $update_msg = "Errore nella modifica dell'utente.";
    }

    $stmt->close();
}

// Cancellazione utente
if (isset($_POST['cancella_utente'])) {
    $user_id = $_POST['user_id'];

    $stmt = $mysqli->prepare("DELETE FROM loginutente WHERE id = ?");
    $stmt->bind_param('i', $user_id);

    if ($stmt->execute()) {
        $delete_msg = "Utente cancellato con successo!";
    } else {
        $delete_msg = "Errore nella cancellazione dell'utente.";
    }

    $stmt->close();
}

// Estrazione degli utenti per le operazioni di modifica e cancellazione
$users_result = $mysqli->query("SELECT id, nome FROM loginutente");

?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Gestione Utenti</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/uikit/3.9.0/css/uikit.min.css">
</head>
<body>

<div class="uk-container">

    <!-- Sezione per l'inserimento di un nuovo utente -->
    <h2>Inserisci un nuovo utente</h2>
    <?php if ($insert_msg): ?>
        <div class="uk-alert-success" uk-alert>
            <p><?= htmlspecialchars($insert_msg); ?></p>
        </div>
    <?php endif; ?>
    <form  method="POST">
        <div class="uk-margin">
            <label class="uk-form-label">Nome</label>
            <input class="uk-input" type="text" name="nome" required>
        </div>
        <div class="uk-margin">
            <label class="uk-form-label">Password</label>
            <input class="uk-input" type="password" name="password" required>
        </div>
        <div class="uk-margin">
            <label class="uk-form-label">Permessi</label>
            <select class="uk-select" name="permessi" required>
                <option value="admin">Admin</option>
                <option value="scrittura">Scrittura</option>
                <option value="lettura">Lettura</option>
            </select>
        </div>
        <button class="uk-button uk-button-primary" type="submit" name="inserisci_utente">Inserisci Utente</button>
    </form>

    <hr>

    <!-- Sezione per la modifica di un utente esistente -->
    <h2>Modifica un utente</h2>
    <?php if ($update_msg): ?>
        <div class="uk-alert-primary" uk-alert>
            <p><?= htmlspecialchars($update_msg); ?></p>
        </div>
    <?php endif; ?>
    <form action="gestioneUtentiDB.php" method="POST">
        <div class="uk-margin">
            <label class="uk-form-label">Seleziona Utente</label>
            <select class="uk-select" name="user_id" required>
                <?php while ($row = $users_result->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($row['id']); ?>"><?= htmlspecialchars($row['nome']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="uk-margin">
            <label class="uk-form-label">Nuovo Nome</label>
            <input class="uk-input" type="text" name="nome" required>
        </div>
        <div class="uk-margin">
            <label class="uk-form-label">Nuova Password</label>
            <input class="uk-input" type="password" name="password" required>
        </div>
        <div class="uk-margin">
            <label class="uk-form-label">Nuovi Permessi</label>
            <select class="uk-select" name="permessi" required>
                <option value="admin">Admin</option>
                <option value="scrittura">Scrittura</option>
                <option value="lettura">Lettura</option>
            </select>
        </div>
        <button class="uk-button uk-button-primary" type="submit" name="modifica_utente">Modifica Utente</button>
    </form>

    <hr>

    <!-- Sezione per la cancellazione di un utente -->
    <h2>Cancella un utente</h2>
    <?php if ($delete_msg): ?>
        <div class="uk-alert-danger" uk-alert>
            <p><?= htmlspecialchars($delete_msg); ?></p>
        </div>
    <?php endif; ?>
    <form action="gestioneUtenti.php" method="POST">
        <div class="uk-margin">
            <label class="uk-form-label">Seleziona Utente da Cancellare</label>
            <select class="uk-select" name="user_id" required>
                <?php
                $users_result = $mysqli->query("SELECT id, nome FROM loginutente"); // Re-query for the delete section
                while ($row = $users_result->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($row['id']); ?>"><?= htmlspecialchars($row['nome']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <button class="uk-button uk-button-danger" type="submit" name="cancella_utente">Cancella Utente</button>
    </form>

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
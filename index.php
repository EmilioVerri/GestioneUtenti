<?php
session_start();
// Connessione al database
$mysqli = new mysqli("localhost", "root", "", "gestioneutenti");

// Verifica della connessione
if ($mysqli->connect_error) {
    die("Connessione fallita: " . $mysqli->connect_error);
}

// Variabili per messaggi di errore
$login_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ricezione dei dati
    $nome = $_POST['nome'];
    $password = $_POST['password'];

    // Query per ottenere l'utente dal nome
    $stmt = $mysqli->prepare("SELECT id, password FROM loginutente WHERE nome = ?");
    $stmt->bind_param('s', $nome);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $password_hash);
        $stmt->fetch();


        // Verifica della password
        if (password_verify($password, $password_hash)) {
            // Login riuscito: avvia la sessione
            $_SESSION['id'] = $id; // Salva l'id nella sessione
            header("Location: gestioneUtenti.php");
            exit();
        } else {
            $login_error = "Password non corretta.";
        }
    } else {
        $login_error = "Utente non trovato.";
    }

    $stmt->close();
}

// Estrazione dei nomi per il menu a tendina
$nomi_result = $mysqli->query("SELECT nome FROM loginutente");

?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <!-- Inclusione di UIKit -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/uikit/3.9.0/css/uikit.min.css">
</head>
<body>

<div class="uk-section uk-section-muted uk-flex uk-flex-middle uk-height-viewport">
    <div class="uk-container">
        <div class="uk-grid-margin uk-grid uk-grid-stack" uk-grid>
            <div class="uk-width-1-1@m">
                <div class="uk-card uk-card-default uk-card-body uk-box-shadow-large">
                    <h3 class="uk-card-title uk-text-center">Login</h3>
                    <?php if ($login_error): ?>
                        <div class="uk-alert-danger" uk-alert>
                            <p><?= htmlspecialchars($login_error); ?></p>
                        </div>
                    <?php endif; ?>
                    <form action="index.php" method="POST" class="uk-form-stacked">
                        <div class="uk-margin">
                            <label class="uk-form-label" for="nome">Nome Utente</label>
                            <div class="uk-form-controls">
                                <select class="uk-select" id="nome" name="nome" required>
                                    <option value="">Seleziona un utente</option>
                                    <?php while ($row = $nomi_result->fetch_assoc()): ?>
                                        <option value="<?= htmlspecialchars($row['nome']); ?>"><?= htmlspecialchars($row['nome']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="uk-margin">
                            <label class="uk-form-label" for="password">Password</label>
                            <div class="uk-form-controls">
                                <input class="uk-input" id="password" type="password" name="password" required>
                            </div>
                        </div>
                        <div class="uk-margin">
                            <button class="uk-button uk-button-primary uk-width-1-1" type="submit">Login</button>
                        </div>
                    </form>
                </div>
            </div>
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
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

// Funzione per calcolare l'inizio e la fine della settimana in base alla settimana corrente
function getWeekStartAndEnd($currentDate)
{
    $monday = date('Y-m-d', strtotime('monday this week', strtotime($currentDate)));
    $sunday = date('Y-m-d', strtotime('sunday this week', strtotime($currentDate)));
    return [$monday, $sunday];
}

$currentDate = isset($_GET['currentDate']) ? $_GET['currentDate'] : date('Y-m-d');
list($data_inizio, $data_fine) = getWeekStartAndEnd($currentDate);

$reparto = isset($_GET['reparto']) && $_GET['reparto'] != '*' ? $_GET['reparto'] : null;

// Recupera i reparti
$reparti_result = $mysqli->query("SELECT id, nome FROM reparti");
$reparti = [];
while ($row = $reparti_result->fetch_assoc()) {
    $reparti[] = $row;
}

// Recupera tutte le richieste filtrate
$query = "SELECT r.data, u.nome AS nome_utente, r.tipologia, r.usufruito, u.reparto 
          FROM richieste r 
          JOIN utenti u ON r.id_utente = u.id 
          WHERE r.data >= ? AND r.data <= ?";

if ($reparto) {
    $query .= " AND u.reparto = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('sss', $data_inizio, $data_fine, $reparto);
} else {
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('ss', $data_inizio, $data_fine);
}

$stmt->execute();
$result = $stmt->get_result();

$richieste = [];
while ($row = $result->fetch_assoc()) {
    $richieste[] = $row;
}

// Raggruppamento delle richieste per giorno
$richiestePerGiorno = [];
foreach ($richieste as $richiesta) {
    $data = $richiesta['data'];
    if (!isset($richiestePerGiorno[$data])) {
        $richiestePerGiorno[$data] = [];
    }
    $richiestePerGiorno[$data][] = $richiesta;
}
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confronto Richieste Settimanali</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/uikit@3.6.16/dist/css/uikit.min.css" />
    <style>
        .uk-table {
            width: 100%;
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

                    <?php if ($permessi == "admin" || $permessi=="scrittura"): ?>
                        <li><a href="inserimentoModificaDati.php" style="color:black">Inserimento/Modifica Dati</a></li>
                    <li><a href="inserimentoRichieste.php" style="color:black">Inserimento Richieste</a></li>
                    <?php endif; ?>
                    <li><a href="calendarioMensile.php" style="color:black">Calendario Mensile</a></li>
                    <li><a href="logout.php" style="color:black">Logout</a></li>
                </ul>
            </div>
            <div class="uk-navbar-right">
                <a href=""><img src=".\images\logo.png" alt="Logo" width="100" height="100"></a>
            </div>
        </nav>
        <h2>Confronto Richieste Settimanali</h2>

        <div class="uk-margin">
            <label for="reparto">Da:</label>
            <input class="uk-input" type="text" placeholder="<?php if (isset($_GET['currentDate'])) {
                $dataOriginale=$_GET['currentDate'];
                $dataConvertita = date('d-m-Y', strtotime($dataOriginale));
                echo $dataConvertita;
            }else{
                $oggi = date("d/m/Y");
            echo $oggi; // Output: 20/11/2023

            }

            ?>" aria-label="Input" disabled style="width:15%">

            <label for="reparto">A:</label>
            <input class="uk-input" type="text" placeholder="<?php if (isset($_GET['currentDate'])) {
                $dataOriginale=$_GET['currentDate'];

                // Aggiungiamo 7 giorni alla data originale utilizzando strtotime()
                $dataAggiornata = strtotime($dataOriginale . ' +6 days');
                
                // Formattiamo la data aggiornata nel formato gg-mm-yyyy
                $dataFormattata = date('d-m-Y', $dataAggiornata);
                echo $dataFormattata;
            }else{
                $oggi = date("Y-m-d");
                $dataAggiornata = strtotime($oggi . ' +6 days');
                
                // Formattiamo la data aggiornata nel formato gg-mm-yyyy
                $dataFormattata = date('d-m-Y', $dataAggiornata);
                echo $dataFormattata;

            }?>" aria-label="Input" disabled style="width:15%">
        </div>
        <!-- Form per il filtro -->
        <form method="GET" action="" class="uk-grid-small uk-child-width-auto uk-grid">
            <div>
                <label for="reparto">Reparto:</label>
                <select class="uk-select" id="reparto" name="reparto" onchange="this.form.submit()">
                    <option value="*">Tutti</option>
                    <?php foreach ($reparti as $reparto_opt): ?>
                        <option value="<?= $reparto_opt['nome'] ?>" <?= isset($reparto) && $reparto == $reparto_opt['nome'] ? 'selected' : '' ?>><?= $reparto_opt['nome'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <input type="hidden" name="currentDate" value="<?= $currentDate ?>" />
        </form>

        <!-- Navigazione settimanale -->
      
        <div class="uk-margin">
  <a href="?currentDate=<?= date('Y-m-d', strtotime($currentDate . ' - 1 week')) ?>&reparto=<?= $reparto ?>" class="uk-button uk-button-default">Settimana Precedente</a>
  <a href="?currentDate=<?= date('Y-m-d', strtotime($currentDate . ' + 1 week')) ?>&reparto=<?= $reparto ?>" class="uk-button uk-button-default">Settimana Successiva</a>
  <button class="uk-button uk-button-primary" id="export-to-excel">Esporta in Excel</button>
</div>

        <!-- Visualizzazione delle richieste raggruppate per giorno -->
        <div class="uk-margin">
            
        <?php
// Ottieni tutti i giorni della settimana corrente
$start = new DateTime($data_inizio);
$end = new DateTime($data_fine);
$end->modify('+1 day'); // Include l'ultimo giorno

$interval = new DateInterval('P1D'); // Intervallo di 1 giorno
$period = new DatePeriod($start, $interval, $end);

foreach ($period as $date) {
    $giorno = $date->format('Y-m-d');
    echo "<h4 class='day-header'>" . $date->format('d/m/Y - l') . "</h4>";
    echo '<table class="uk-table uk-table-divider uk-table-hover">
          <thead>
            <tr>
              <th>Nome Dipendente</th>
              <th>Tipologia</th>
              <th>Usufruito</th>
              <th>Reparto</th>
            </tr>
          </thead>
          <tbody>';
  
    if (isset($richiestePerGiorno[$giorno])) {
      foreach ($richiestePerGiorno[$giorno] as $richiesta) {
        echo "<tr>
                <td>{$richiesta['nome_utente']}</td>
                <td>{$richiesta['tipologia']}</td>
                <td>{$richiesta['usufruito']}</td>
                <td>{$richiesta['reparto']}</td>
              </tr>";
      }
    } else {
      // Empty row if no requests for the day
      echo "<tr>
              <td colspan='4'>Nessuna richiesta per questo giorno.</td>
            </tr>";
    }
  
    echo "</tbody></table>";
  }
  ?>
  

  
  <script src="https://cdn.jsdelivr.net/npm/uikit@3.6.16/dist/js/uikit.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/uikit@3.6.16/dist/js/uikit-icons.min.js"></script>   
  
    <script>
document.getElementById('export-to-excel').addEventListener('click', function() {
    // Seleziona tutti gli header dei giorni
    const dayHeaders = document.querySelectorAll('h4.day-header');

    // Funzione per creare una nuova riga con il giorno come prima colonna
    function createRowWithDay(day, cells) {
        let newRow = '<tr>';
        newRow += `<td>${day}</td>`;
        cells.forEach(cell => {
            newRow += `<td>${cell.innerHTML}</td>`;
        });
        newRow += '</tr>';
        return newRow;
    }

    // Ottieni il contenuto della tabella
    let tableHTML = '<table><thead><tr><th>Giorno</th><th>Nome Dipendente</th><th>Tipologia</th><th>Usufruito</th><th>Reparto</th></tr></thead><tbody>';

    // Itera su ogni giorno della settimana
    dayHeaders.forEach(dayHeader => {
        const day = dayHeader.textContent;
        const rowsForDay = dayHeader.nextElementSibling.querySelectorAll('tbody tr');
        
        if (rowsForDay.length > 0) {
            rowsForDay.forEach(row => {
                const cells = row.querySelectorAll('td');
                tableHTML += createRowWithDay(day, cells);
            });
        } else {
            // Se non ci sono richieste per il giorno, crea una riga vuota per quel giorno
            tableHTML += `<tr><td>${day}</td><td colspan="4">Nessuna richiesta</td></tr>`;
        }
    });

    tableHTML += '</tbody></table>';

    // Crea un elemento anchor nascosto per il download
    const hiddenElement = document.createElement('a');
    hiddenElement.href = `data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;charset=utf-8,${encodeURIComponent(tableHTML)}`;
    hiddenElement.download = 'confronto_richieste_settimanali.xls';
    hiddenElement.style.display = 'none';
    document.body.appendChild(hiddenElement);

    // Simula un click per avviare il download
    hiddenElement.click();
    document.body.removeChild(hiddenElement);
});
</script>
</body>

</html>
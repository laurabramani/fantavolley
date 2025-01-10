<?php
$dbFile = 'fantacalcio.sqlite3';

// Connessione al database SQLite3
$db = new SQLite3($dbFile);

// Creazione della tabella per le partite
$db->exec("
    CREATE TABLE IF NOT EXISTS matches (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        team1 TEXT,
        team2 TEXT,
        score1 INTEGER,
        score2 INTEGER,
        non_justified_a TEXT,
        non_justified_b TEXT
    )
");

// Salvataggio della partita
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $score1 = (int)$_POST['score1'];
    $score2 = (int)$_POST['score2'];

    // Recupero dei giocatori non giustificati
    $justifiedA = $_POST['justified_a'] ?? [];
    $justifiedB = $_POST['justified_b'] ?? [];

    $playersA = ["Salvo Gabriele", "Barindelli Manuel", "Narciso Nicolò", "Bramani Laura", "Ghisolfi Andrea", "Palazzo David", "Zorzan Sara", "Tonet Matteo", "Yachou Yassin"];
    $playersB = ["Gumiero Alessandro", "Locatelli Daniele", "Rigamonti Andrea Umer", "Fontana Michele", "Mascheroni Martina", "Bodlli Matteo", "Ricali Lorenzo", "Jabir Ali"];

    $nonJustifiedA = implode(", ", array_diff($playersA, $justifiedA));
    $nonJustifiedB = implode(", ", array_diff($playersB, $justifiedB));

    // Calcolo delle penalità
    $penaltyA = count(array_diff($playersA, $justifiedA)) * 7;
    $penaltyB = count(array_diff($playersB, $justifiedB)) * 7;

    $finalScoreA = $score1 - $penaltyA;
    $finalScoreB = $score2 - $penaltyB;

    // Salvataggio nel database
    $stmt = $db->prepare("
        INSERT INTO matches (team1, team2, score1, score2, non_justified_a, non_justified_b)
        VALUES ('Squadra A', 'Squadra B', :score1, :score2, :non_justified_a, :non_justified_b)
    ");
    $stmt->bindValue(':score1', $finalScoreA, SQLITE3_INTEGER);
    $stmt->bindValue(':score2', $finalScoreB, SQLITE3_INTEGER);
    $stmt->bindValue(':non_justified_a', $nonJustifiedA, SQLITE3_TEXT);
    $stmt->bindValue(':non_justified_b', $nonJustifiedB, SQLITE3_TEXT);

    $stmt->execute();

    // Reindirizzamento per aggiornare la pagina
    header("Location: index.html");
    exit();
}

// Caricamento dello storico delle partite
function loadMatchHistory()
{
    global $db;
    $result = $db->query("SELECT * FROM matches ORDER BY id DESC");
    $html = '';

    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $html .= "<li>
            Squadra A ({$row['score1']}) vs Squadra B ({$row['score2']})<br>
            Non giustificati Squadra A: {$row['non_justified_a']}<br>
            Non giustificati Squadra B: {$row['non_justified_b']}
        </li>";
    }

    return $html;
}
?>

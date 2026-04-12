<?php
// database verbinding
include 'database_connect.php';
?>

<!DOCTYPE html>
<html>
<head>
  <title>PO - greedy</title>
  <meta charset="UTF-8">
  <link href="style.css?v=<?php echo time(); ?>" rel="stylesheet" type="text/css">
  <meta name="viewport" content="width=device-width">
</head>
<body>

<h1><b>Routeplanner</b></h1>

<!-- POST verwerking -->
<?php
// POST word hier verwerkt om het op de website te zetten
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dag = $_POST['dag'] ?? null;
    $algoritme = $_POST['algoritme'] ?? null;

    if ($algoritme === 'greedy') {
        $gekozen = "greedy";
        include 'greedy.php';
        $resultaat = algoritme($dag);
    } elseif ($algoritme === 'bruteforce') {
        $gekozen = "bruteforce";
        include 'bruteforce.php';
        $resultaat = algoritme($dag);
    }

}

// Bepaal juiste query afhankelijk van knop
if (isset($_POST['toon_alles'])) {
    // Alleen alle bestellingen tonen
    $query = "SELECT dag, adres_id, id FROM bestelling ORDER BY id";
    $dag = "alle dagen";
    $algoritme = "-";
    $rows = null;
} elseif (!empty($dag) && $dag !== "-") {
    // Bestellingen van geselecteerde dag
    $stmt = $conn->prepare("SELECT dag, adres_id, id FROM bestelling WHERE dag = ?");
    $stmt->bind_param("s", $dag);
    $stmt->execute();
    $result = $stmt->get_result();

    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }

    $stmt->close();
    $query = null;
} else {
    // Geen dag geselecteerd toon niets
    $query = null;
    $rows = null;
}
?>

<div class="wrapper">
    <div class="border">
        <h2><b>Bestellingen</b></h2>

        <!-- opvragen van gegevens -->
        <form method="POST">
            <button type="submit" name="toon_alles">Toon alle bestellingen</button>

            <label for="dag">of kies een dag:</label>
            <select name="dag" id="dag">
                <option value="-">-</option>
                <option value="maandag">Maandag</option>
                <option value="dinsdag">Dinsdag</option>
                <option value="Woensdag">Woensdag</option>
                <option value="Donderdag">Donderdag</option>
                <option value="Vrijdag">Vrijdag</option>
            </select>
            <button type="submit">Verstuur</button>
        </form>
    </div>

    <div class="tabellen">
        <table border="2" class="table">
            <tr>
                <th>id</th>
                <th>adres_id</th>
                <th>dag</th>
            </tr>

        <?php
        // Gebruik $rows als prepared statement is uitgevoerd
        if (!empty($rows)) {
            foreach ($rows as $row) {
                echo "<tr>";
                echo "<td>".$row['id']."</td>";
                echo "<td>".$row['adres_id']."</td>";
                echo "<td>".$row['dag']."</td>";
                echo "</tr>";
            }
        } elseif ($query !== null) {
            $result = $conn->query($query);

            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>".$row['id']."</td>";
                    echo "<td>".$row['adres_id']."</td>";
                    echo "<td>".$row['dag']."</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='3'>Geen bestellingen gevonden</td></tr>";
            }
        } else {
            echo "<tr><td colspan='3'>Geen dag geselecteerd</td></tr>";
        }
        ?>
        </table>

        <h2><b>Locaties</b></h2>
        <table border="2" class="table">
            <tr>
                <th>id</th>
                <th>adres</th>
                <th>x</th>
                <th>y</th>
            </tr>

        <?php
        // Query locaties (geen variabel us veilig)
        $query1 = "SELECT id, adres, x, y FROM locatie";
        $result1 = $conn->query($query1);

        if ($result1 && $result1->num_rows > 0) {
            while ($row1 = $result1->fetch_assoc()) {
                echo "<tr>";
                echo "<td>".$row1['id']."</td>";
                echo "<td>".$row1['adres']."</td>";
                echo "<td>".$row1['x']."</td>";
                echo "<td>".$row1['y']."</td>";
                echo "</tr>";
            }
        }
        ?>
        </table>
    </div>

    <h2><b>Gegevens toevoegen</b></h2>

    <div class="toevoegingen">
        <div class="border">
            <h3>Nieuwe bestelling toevoegen</h3>

            <form method="POST">
                <input placeholder="adres_ID" type="number" name="adres_id" required>
<!--<input placeholder="dag" type="text" name="dag" required> -->
                <select name="dag" id="dag">
<!--                <option value="-">-</option> -->
                    <option value="maandag">Maandag</option>
                    <option value="dinsdag">Dinsdag</option>
                    <option value="Woensdag">Woensdag</option>
                    <option value="Donderdag">Donderdag</option>
                    <option value="Vrijdag">Vrijdag</option>
                </select>
                <button type="submit" name="submit_locatie">Toevoegen</button>
            </form>
        

            <?php
            // bestelling toevoegen aan database

            if (isset($_POST['submit_locatie'])) {
                $nieuw_adres_id = $_POST['adres_id'];
                $nieuw_dag = $_POST['dag'];

                // Prepared als bescherming tegen sql injectie
                $stmt = $conn->prepare("INSERT INTO bestelling (adres_id, dag) VALUES (?, ?)");
                $stmt->bind_param("is", $nieuw_adres_id, $nieuw_dag);

                if ($stmt->execute()) {
                    // voorkomt dubbel versturen bij refresh
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit;
                    echo "<p style='color:green;'>Nieuwe bestelling toegevoegd!</p>";
                } else {
                    echo "<p style='color:red;'>Fout bij toevoegen: " . $stmt->error . "</p>";
                }

                $stmt->close();
            }
            ?>
        </div>
    
        <div class="border">
        <h3>Nieuw adres toevoegen</h3>

        <form method="POST">
            <input placeholder="adres" type="text" name="adres" required>
            <input placeholder="x" type="number" name="x" required>
            <input placeholder="y" type="number" name="y" required>

            <button type="submit" name="submit_adres">Toevoegen</button>
        </form>
        </div>

        <?php
        // adres toevoegen aan database

        if (isset($_POST['submit_adres'])) {
            $nieuw_locatie = $_POST['adres'];
            $nieuw_x = $_POST['x'];
            $nieuw_y = $_POST['y'];

            // Prepared als bescherming tegen sql injectie
            $stmt = $conn->prepare("INSERT INTO locatie (adres, x, y) VALUES (?, ?, ?)");
            $stmt->bind_param("sii", $nieuw_locatie, $nieuw_x, $nieuw_y);

            if ($stmt->execute()) {
                // voorkomt dubbel versturen bij refresh
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
                echo "<p style='color:green;'>Nieuwe locatie toegevoegd!</p>";
            } else {
                echo "<p style='color:red;'>Fout bij toevoegen: " . $stmt->error . "</p>";
            }

            $stmt->close();
        }
        ?>
    </div>

    <h2><b>algoritme uitvoeren</b></h2>

    <div class="border">
        <div class="select-algoritme">
            <form method="POST">
            <label for="algoritme">Kies een algoritme:</label>
                <select name="algoritme" id="algoritme">
                    <option value="-">-</option>
                    <option value="greedy">Greedy</option>
                    <option value="bruteforce">Brute Force</option>
                </select>
            <label for="dag">en kies een dag:</label>
                <select name="dag" id="dag">
                    <option value="-">-</option>
                    <option value="maandag">Maandag</option>
                    <option value="dinsdag">Dinsdag</option>
                    <option value="Woensdag">Woensdag</option>
                    <option value="Donderdag">Donderdag</option>
                    <option value="Vrijdag">Vrijdag</option>
                </select>
            <button type="submit">Verstuur</button>
            </form>
            <br>
        </div>



        <div class="uitkomst">
            <?php
            if ($gekozen === "greedy"){
                echo "<h2><bold> Uitkomst Greedy </bold></h2><br>";
                echo ("$resultaat");
            }

            if ($gekozen === "bruteforce"){
                echo "<h2><bold> Uitkomst brute force </bold></h2><br>";
                echo ("$resultaat");
                //ik weet niet waarom echo resultaat werkt maar dat doet het wel dus ik blijf er van af
            }
            ?>
        </div>
    </div>
</div>

<footer>
<p>
© 2025 server.bunkermixspecial.nl Alle rechten voorbehouden.<br>
Het greedy algoritme is geschreven door Mats de Jong.<br>
Het bruteforce algoritme is geschreven door Daniël Mulder.<br>
Deze pagina is gemaakt door Mats de Jong. <br>
Voor de zekerheid is hier een link naar een foto van alle waarden die eerst in de database stonden <br>
<a href="Bestellingen.png">bestelling</a> en <a href="Locaties.png">locaties</a>.
</p>
</footer>
</body>
</html>

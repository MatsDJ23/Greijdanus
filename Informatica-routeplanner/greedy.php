<?php
function algoritme($dag) {

  // database verbinding
  include 'database_connect.php';
  
    // Query: alle locaties en hun bestellingen voor de dag
    $stmt = $conn->prepare("SELECT l.id, dag, adres, x, y FROM locatie AS l JOIN bestelling AS b ON adres_id = l.id WHERE dag LIKE ?");
    $stmt->bind_param("s", $dag);
    $stmt->execute();
    $result = $stmt->get_result();

    $coords = $result->fetch_all(MYSQLI_ASSOC);

    $conn->close();

    // X/Y arrays vullen
    $uitkomst_id = array_column($coords, 'id');
    $uitkomst_x = array_column($coords, 'x');
    $uitkomst_y = array_column($coords, 'y');

    // ID → adres mapping
    $id_to_adres = [];
    foreach ($coords as $row) {
        $id_to_adres[$row['id']] = $row['adres'];
    }

    // arrays en variabelen declareren
    $afstanden = [];  
    $bezocht_id = [];
    $onbezocht_id = [];
    $current_index = 0; // beginpunt (postkantoor)
    $loop_hoevaak = count($uitkomst_x); 
    $count = 0;

    // lijst maken met onbezochte punten (indexen)
    while ($count < $loop_hoevaak) {
        $onbezocht_id[] = $count;
        $count++;
    }

    // startpunt direct op bezocht zetten
    $bezocht_id[] = $uitkomst_id[$current_index];

    // verwijder het startpunt uit onbezocht zodat we er niet naar teruggaan
    $startKey = array_search($current_index, $onbezocht_id);
    if ($startKey !== false) {
        unset($onbezocht_id[$startKey]);
        $onbezocht_id = array_values($onbezocht_id); // reindex
    }

    $totale_afstand = 0;

    // loop zolang er nog onbezochte punten zijn
    while (count($onbezocht_id) > 0) {
        $afstanden = []; // reset afstanden voor deze ronde

        // berekent afstand naar elk onbezocht punt
        foreach ($onbezocht_id as $oid_index) {
            if ($oid_index == $current_index) continue;

            // pythagoras
            $afstand = sqrt(
                pow($uitkomst_x[$oid_index] - $uitkomst_x[$current_index], 2) +
                pow($uitkomst_y[$oid_index] - $uitkomst_y[$current_index], 2)
            );
            
            $afstanden[$oid_index] = $afstand;
        }

        if (empty($afstanden)) break; // veiligheidscheck

        $kortste = min($afstanden);
        $dichtbij_index = array_search($kortste, $afstanden);
        $totale_afstand += $kortste;

        $bezocht_id[] = $uitkomst_id[$dichtbij_index];

        // verwijderen uit onbezocht
        $key = array_search($dichtbij_index, $onbezocht_id);
        if ($key !== false) {
            unset($onbezocht_id[$key]);
            $onbezocht_id = array_values($onbezocht_id);
        }

        $current_index = $dichtbij_index;
    }

    // route tonen met echte ID's **en locatienamen**
    $route_namen = [];
    foreach ($bezocht_id as $id) {
        $route_namen[] = $id_to_adres[$id]; // map ID → adres
    }

    $uitkomsten = "Snelste route: ".implode(" - ", $route_namen)."<br>";
    $uitkomsten .= "ID's: ".implode(" - ", $bezocht_id)."<br>";
    $uitkomsten .= "Totale afstand: ".$totale_afstand;

    return $uitkomsten;
}
?>

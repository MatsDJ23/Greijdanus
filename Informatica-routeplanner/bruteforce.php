<?php

function algoritme($dag) {

  //database verbinding
  include 'database_connect.php';
  
    // Query uitvoeren
    $query = "
        SELECT l.id, l.adres, l.x, l.y
        FROM locatie AS l
        JOIN bestelling AS b ON b.adres_id = l.id
        WHERE b.dag = ?
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $dag);
    $stmt->execute();

    $result = $stmt->get_result();
    $coords = $result->fetch_all(MYSQLI_ASSOC);

    $conn->close();

    // Arrays vullen
    $uitkomst_id = array_column($coords, 'id');
    $uitkomst_x  = array_column($coords, 'x');
    $uitkomst_y  = array_column($coords, 'y');

    // Permutaties met vast startpunt index 0
    $arraylength = count($uitkomst_x);

    if ($arraylength < 2) {
        return "Niet genoeg locaties voor een route.";
    }

    // Lijst van punten na index 0
    $rest = range(1, $arraylength - 1);

    // Permutaties genereren
    $routes_rest = permute($rest);

    // Elke route laten beginnen met index 0 (postkantoor)
    $routes = [];
    foreach ($routes_rest as $r) {
        array_unshift($r, 0);
        $routes[] = $r;
    }

    // Kortste route berekenen
    $besteAfstand = PHP_INT_MAX;
    $besteRoute   = null;

    foreach ($routes as $route) {

        $totaal = 0;

        for ($i = 0; $i < count($route) - 1; $i++) {

            $a = $route[$i];
            $b = $route[$i + 1];

            $dx = $uitkomst_x[$a] - $uitkomst_x[$b];
            $dy = $uitkomst_y[$a] - $uitkomst_y[$b];
            $afstand = sqrt($dx * $dx + $dy * $dy);

            $totaal += $afstand;
        }

        if ($totaal < $besteAfstand) {
            $besteAfstand = $totaal;
            $besteRoute   = $route;
        }
    }

    // Route-namen en id's
    $route_namen = [];
    $id_lijst = [];

    foreach ($besteRoute as $r) {
        $route_namen[] = $coords[$r]['adres'];
        $id_lijst[] = $coords[$r]['id'];
    }

    // Output in dezelfde stijl als greedy
    $uitkomsten  = "Snelste route: " . implode(" - ", $route_namen) . "<br>";
    $uitkomsten .= "ID's: " . implode(" - ", $id_lijst) . "<br>";
    $uitkomsten .= "Totale afstand: " . $besteAfstand;

    return $uitkomsten;
}


// Permutatiefunctie (nodig voor brute force)
function permute($items) {
    if (count($items) == 1) return [$items];

    $permutations = [];

    foreach ($items as $i => $item) {
        $remaining = $items;
        unset($remaining[$i]);
        $remaining = array_values($remaining);

        foreach (permute($remaining) as $perm) {
            $permutations[] = array_merge([$item], $perm);
        }
    }

    return $permutations;
}

?>

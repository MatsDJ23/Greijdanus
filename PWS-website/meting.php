<!DOCTYPE html>
<html>

<?php include 'include/head.php';?>
<body>
<?php include 'include/navbar.php';?>

<div class="titel">
  <h1> Waardes van de CO2 sensor </h1>
</div>

<p> Metingen voor de CO2 sensor </p>
  <!-- Hier komt de status -->
  <div class="return" id="value">Laden...</div>
  </div>
</div>  


<p>Hieronder staan de waardes wat goed of slecht is. <br>
   De afzuiging is nu ingesteld dat de afzuiging aan gaat als het CO2 ppm boven 850 komt. <br>
<br>
  - <span class="waarde heel-goed">400–420 ppm</span>: buitenlucht en theoretisch minimum voor binnenruimtes<br>
  - <span class="waarde goed">≤800 ppm</span>: goede luchtkwaliteit, ideaal voor leer- en werkomgevingen<br>
  - <span class="waarde matig">800–1000 ppm</span>: acceptabel, maar niet optimaal<br>
  - <span class="waarde slecht">1000–2000 ppm</span>: slechte luchtkwaliteit, kans op klachten neemt toe<br>
  - <span class="waarde gevaarlijk">>2000 ppm</span>: ongezond en ongeschikt voor langdurig verblijf
</p>


<?php include 'include/voetje.php'; ?>

<script>
// haalt om de seconde nieuwe CO2 waarden op
async function update() {
  const res = await fetch("scripts/sensor.txt?_=" + Date.now());
  const text = await res.text();
  document.getElementById("value").textContent = text;
}

setInterval(update, 1000);
update();
</script>

</body>
</html>

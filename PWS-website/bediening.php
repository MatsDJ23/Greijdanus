<!DOCTYPE html>
<html>

<?php include 'include/head.php';?>
<body>
<?php include 'include/navbar.php';?>

<div class="titel">
  <h1>bediening</h1>
</div>

<p>Bediening van de afzuiging</p>
<p>Hier kan je de stand van de afzuiging wijzigen, je kan kiezen tussen aan,uit en automatisch. </p>

<form method="POST">
  <select class="modus-kiezen" name="besturing" id="besturing">
    <b>
    <option value="0">Uit</option>
    <option value="1">Aan</option>
    <option value="2">Auto</option>
    </b>
  </select>
  <button class="opslaan-knop" type="submit" name="submit_modus"><b>Opslaan</b></button>
</form>

<?php
if (isset($_POST['submit_modus'])) {
    $mode = $_POST['besturing'] ?? '';
    if (in_array($mode, ['0', '1', '2'])) {
        file_put_contents('mode.txt', $mode);
        // Mapping van waarde word tekst
        $modes = ['0' => 'Uit','1' => 'Aan','2' => 'Auto'];
        echo "Modus opgeslagen: " . $modes[$mode];
    }
}
?>

<?php include 'include/voetje.php'; ?>

<script>
function sendMode(mode) {
    fetch('setmode.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'mode=' + mode
    })
    .then(r => r.text())
    .then(console.log);
}
</script>

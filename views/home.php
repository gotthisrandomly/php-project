<?php
$title = 'Home';
ob_start();
?>

<h1>sd777slots</h1>

<?php
$content = ob_get_clean();
include 'layout.php';
?>
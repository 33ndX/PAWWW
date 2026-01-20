<?php
$nr_indeksu = '175281';
$nrGrupy = 'ISI2';

echo "Paweł Milanowski $nr_indeksu $nrGrupy";
echo "<br>Zastosowanie metody include()<br>";

include(__DIR__ . '/test_include.php');
test_include();
require_once(__DIR__ . '/test_require_once.php');
test_require_once();

?>

<form action="" method="get">
    <input type="hidden" name="idp" value="10">
    <label for="val1">Podaj liczbę</label>
    <input type="number" name="val1" id="val1">
    <button type="submit">Wyślij</button>
</form>


<?php
if(!empty($_GET['val1'])){
    $value = $_GET['val1'];
} else {
    $value = 23;
}
if ($value == 0) {
    echo "<p>Liczba $value to zero</p>";
} else if ($value % 2 == 0) {
    echo "<p>Liczba $value jest parzysta</p>";
} else {
    echo "<p>Liczba $value jest nieparzysta</p>";
}


echo "<p>Pętla while<p>";
while ($value > 0) {
    echo "$value<br>";
    $value = $value - 3;
}

echo "<p>Pętla for<p>";
for($i = 10; $i!=0; $i--){
    echo "$i<br>";
}

?>

<form action="index.php?idp=10" method="post">
    <label for="val2">Podaj liczbę</label>
    <input type="number" name="val2" id="val2">
    <button type="submit">Wyślij</button>
</form>

<?php
if (!empty($_POST['val2'])){
    $value2 = $_POST['val2'];
} else {
    $value2 = rand(0, 10);
}
switch ($value2) {
    case ($value2 % 2 == 0):
        echo "<p>$value2 jest podzielne przez 2</p>";
        break;
    case ($value2 % 3 == 0):
        echo "<p>$value2 jest podzielne przez 3</p>";
        break;
    case ($value2 % 5 == 0):
        echo "<p>$value2 jest podzielne przez 5</p>";
        break;
    case ($value2 % 7 == 0):
        echo "<p>$value2 jest podzielne przez 7</p>";
        break;
    case ($value2 % 9 == 0):
        echo "<p>$value2 jest podzielne przez 9</p>";
        break;
    default:
        echo "<p>Zero dzieli się przez wszystko</p>";
        break;
}


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['visit_counter'])) {
    $_SESSION['visit_counter'] = 1;
} else {
    $_SESSION['visit_counter']++;
}

echo "Licznik wizyt: ".$_SESSION['visit_counter']."<br>";

?>

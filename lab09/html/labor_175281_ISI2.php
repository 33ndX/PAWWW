<?php
/**
 * ============================================================================
 * LABORATORIUM 4 - labor_175281_ISI2.php
 * ============================================================================
 * 
 * Opis:    Plik demonstracyjny prezentujący różne elementy języka PHP:
 *          include, require_once, pętle, instrukcje warunkowe, sesje.
 * Autor:   Paweł Milanowski (175281, ISI2)
 * Data:    2025
 * 
 * ============================================================================
 */

// ============================================================================
// DANE AUTORA
// ============================================================================

$nr_indeksu = '175281';
$nrGrupy    = 'ISI2';

echo "Paweł Milanowski $nr_indeksu $nrGrupy";
echo "<br>Zastosowanie metody include()<br>";


// ============================================================================
// DOŁĄCZANIE PLIKÓW ZEWNĘTRZNYCH
// ============================================================================

// include() - dołącza plik, kontynuuje wykonanie nawet jeśli plik nie istnieje
include('test_include.php');
test_include();

// require_once() - dołącza plik tylko raz, przerywa wykonanie jeśli brak pliku
require_once('test_require_once.php');
test_require_once();

?>

<!-- ========================================================================
     FORMULARZ GET - Sprawdzanie parzystości liczby
     ======================================================================== -->
<form action="labor_175281_ISI2.php" method="get">
    <label for="val1">Podaj liczbę</label>
    <input type="number" name="val1" id="val1">
    <button type="submit">Wyślij</button>
</form>


<?php
// ============================================================================
// OBSŁUGA FORMULARZA GET
// ============================================================================

// Zabezpieczenie parametru GET przez intval() - ochrona przed Code Injection
if (!empty($_GET['val1'])) {
    $value = intval($_GET['val1']);  // Konwersja na liczbę całkowitą
} else {
    $value = 23;  // Wartość domyślna
}


// ============================================================================
// INSTRUKCJA WARUNKOWA IF-ELSE - sprawdzenie parzystości
// ============================================================================

if ($value == 0) {
    echo "<p>Liczba $value to zero</p>";
} else if ($value % 2 == 0) {
    echo "<p>Liczba $value jest parzysta</p>";
} else {
    echo "<p>Liczba $value jest nieparzysta</p>";
}


// ============================================================================
// PĘTLA WHILE - odliczanie w dół co 3
// ============================================================================

echo "<p>Pętla while</p>";

while ($value > 0) {
    echo "$value<br>";
    $value = $value - 3;  // Dekrementacja o 3
}


// ============================================================================
// PĘTLA FOR - odliczanie od 10 do 1
// ============================================================================

echo "<p>Pętla for</p>";

for ($i = 10; $i != 0; $i--) {
    echo "$i<br>";
}

?>


<!-- ========================================================================
     FORMULARZ POST - Sprawdzanie podzielności
     ======================================================================== -->
<form action="labor_175281_ISI2.php" method="post">
    <label for="val2">Podaj liczbę</label>
    <input type="number" name="val2" id="val2">
    <button type="submit">Wyślij</button>
</form>


<?php
// ============================================================================
// OBSŁUGA FORMULARZA POST
// ============================================================================

// Zabezpieczenie parametru POST przez intval() - ochrona przed Code Injection
if (!empty($_POST['val2'])) {
    $value2 = intval($_POST['val2']);  // Konwersja na liczbę całkowitą
} else {
    $value2 = rand(0, 10);  // Losowa wartość domyślna
}


// ============================================================================
// INSTRUKCJA SWITCH - sprawdzenie podzielności
// ============================================================================
// UWAGA: Logika switch z wyrażeniami działa inaczej niż standardowy switch!
// Porównuje wartość $value2 z wartością true/false wyrażenia.
// ============================================================================

switch (true) {
    case ($value2 == 0):
        echo "<p>Zero dzieli się przez wszystko</p>";
        break;
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
        echo "<p>$value2 nie jest podzielne przez 2, 3, 5, 7 ani 9</p>";
        break;
}


// ============================================================================
// OBSŁUGA SESJI - Licznik odwiedzin
// ============================================================================

// Uruchomienie sesji PHP
session_start();

// Sprawdzenie czy licznik istnieje w sesji
if (!isset($_SESSION['visit_counter'])) {
    // Pierwsza wizyta - inicjalizacja licznika
    $_SESSION['visit_counter'] = 1;
} else {
    // Kolejna wizyta - inkrementacja licznika
    $_SESSION['visit_counter']++;
}

// Wyświetlenie licznika odwiedzin
echo "Licznik wizyt: " . $_SESSION['visit_counter'] . "<br>";

?>
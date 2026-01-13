<?php
/**
 * ============================================================================
 * PLIK TESTOWY - test_include.php
 * ============================================================================
 * 
 * Opis:    Plik demonstracyjny do testowania funkcji include().
 *          Wywoływany z pliku labor_175281_ISI2.php
 * Autor:   Paweł Milanowski
 * Data:    2025
 * 
 * ============================================================================
 */

// Zmienna globalna dostępna po dołączeniu pliku
$test_include_var = 452;


/**
 * ============================================================================
 * FUNKCJA: test_include
 * ============================================================================
 * 
 * Funkcja demonstracyjna wyświetlająca komunikat testowy.
 * Pokazuje działanie lokalnej zmiennej wewnątrz funkcji.
 * 
 * @return  void    Wyświetla komunikat przez echo
 * 
 * ============================================================================
 */
function test_include() {
    
    // Lokalna zmienna (przesłania zmienną globalną)
    $test_include_var = 420;
    
    // Wyświetlenie komunikatu testowego
    echo "<p>Test metody include $test_include_var</p>";
}

?>
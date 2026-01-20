<?php
/**
 * ============================================================================
 * PLIK TESTOWY - test_require_once.php
 * ============================================================================
 * 
 * Opis:    Plik demonstracyjny do testowania funkcji require_once().
 *          Wywoływany z pliku labor_175281_ISI2.php
 * Wersja:  v1.8
 * Data:    2026
 * 
 * ============================================================================
 */

// Zmienna globalna dostępna po dołączeniu pliku
$test_require_once_var = 254;


/**
 * ============================================================================
 * FUNKCJA: test_require_once
 * ============================================================================
 * 
 * Funkcja demonstracyjna wyświetlająca komunikat testowy.
 * Pokazuje działanie lokalnej zmiennej wewnątrz funkcji.
 * Różnica od include(): require_once przerywa wykonanie jeśli plik nie istnieje.
 * 
 * @return  void    Wyświetla komunikat przez echo
 * 
 * ============================================================================
 */
function test_require_once() {
    
    // Lokalna zmienna (przesłania zmienną globalną)
    $test_require_once_var = 24;
    
    // Wyświetlenie komunikatu testowego
    echo "<p>Test metody require_once $test_require_once_var</p>";
}

?>
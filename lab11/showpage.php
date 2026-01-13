<?php
/**
 * ============================================================================
 * PLIK WYŚWIETLANIA STRONY - showpage.php
 * ============================================================================
 * 
 * Opis:    Plik zawiera funkcję do pobierania i wyświetlania zawartości
 *          podstron z bazy danych na podstawie ich ID.
 * Autor:   Paweł Milanowski
 * Data:    2025
 * 
 * ============================================================================
 */

// Dołączenie pliku konfiguracyjnego z połączeniem do bazy danych
include('cfg.php');


/**
 * ============================================================================
 * FUNKCJA: PokazStrone
 * ============================================================================
 * 
 * Pobiera zawartość strony z bazy danych na podstawie podanego ID.
 * Używa prepared statements dla bezpieczeństwa (ochrona przed SQL Injection).
 * 
 * @param   int|string  $id     ID strony do wyświetlenia
 * @return  string              Zawartość strony lub komunikat o błędzie
 * 
 * ============================================================================
 */
function PokazStrone($id) {
    
    // Użycie globalnej zmiennej połączenia z bazą danych
    global $conn;
    
    // Zabezpieczenie ID przed atakami XSS
    $id_clear = htmlspecialchars($id);

    // -------------------------------------------------------------------------
    // ZAPYTANIE SQL - pobranie strony z bazy danych
    // -------------------------------------------------------------------------
    // Używamy prepared statement (?) zamiast wstawiania zmiennej bezpośrednio
    // LIMIT 1 - pobieramy tylko jeden rekord dla wydajności
    // -------------------------------------------------------------------------
    $query = "SELECT * FROM page_list WHERE id = ? LIMIT 1";
    
    // Przygotowanie zapytania SQL (prepared statement)
    $stmt = $conn->prepare($query);
    
    // Powiązanie parametru ID z zapytaniem (typ "s" = string)
    $stmt->bind_param("s", $id_clear);

    // Wykonanie zapytania
    $stmt->execute();
    
    // Pobranie wyniku zapytania
    $result = $stmt->get_result();
    
    // Pobranie wiersza z wynikami jako tablica asocjacyjna
    $row = $result->fetch_assoc();

    // Zamknięcie prepared statement
    $stmt->close();
    
    // Zwrócenie zawartości strony lub komunikatu o błędzie
    return empty($row['id']) ? '[nie_znaleziono_strony]' : $row['page_content'];
}


// ============================================================================
// OBSŁUGA ŻĄDANIA - sprawdzenie parametru idp
// ============================================================================
// Ten blok sprawdza czy parametr idp został przekazany w URL
// ============================================================================

if (isset($_GET['idp'])) {
    // Parametr idp istnieje - obsługa w index.php
} else {
    // Brak parametru idp - wyświetl komunikat o błędzie
    echo '[nie_znaleziono_strony]';
}

?>
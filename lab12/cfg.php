<?php
/**
 * ============================================================================
 * PLIK KONFIGURACYJNY - cfg.php
 * ============================================================================
 * 
 * Opis:    Plik zawiera konfigurację połączenia z bazą danych oraz 
 *          dane logowania administratora.
 * Wersja:  v1.8
 * Data:    2026
 * 
 * ============================================================================
 */

// ============================================================================
// KONFIGURACJA BAZY DANYCH
// ============================================================================

$dbhost = 'localhost';      // Adres hosta bazy danych
$dbuser = 'root';           // Nazwa użytkownika bazy danych
$dbpass = '';               // Hasło do bazy danych
$baza   = 'moja_strona';    // Nazwa bazy danych

// ============================================================================
// DANE LOGOWANIA ADMINISTRATORA
// ============================================================================

$login = 'admin';           // Login administratora
$pass  = 'haslo';           // Hasło administratora

// Definicja stałych dla danych logowania (zabezpieczenie przed redefinicją)
if (!defined('admin')) {
    define('admin', $login);
}

if (!defined('pass')) {
    define('pass', $pass);
}

// ============================================================================
// NAWIĄZANIE POŁĄCZENIA Z BAZĄ DANYCH
// ============================================================================

// Utworzenie nowego połączenia mysqli z bazą danych
$conn = new mysqli($dbhost, $dbuser, $dbpass, $baza);

// Sprawdzenie czy połączenie zostało nawiązane poprawnie
if ($conn->connect_error) {
    die('<b>Połączenie zostało przerwane: </b>' . $conn->connect_error);
}
?>
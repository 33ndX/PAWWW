<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Projekt 1">
    <meta name="keywords" content="HTML5, CSS3, JavaScript">
    <meta name="author" content="">
    <title>Największe budynki świata</title>
    
    <!-- ====================================================================
         DOŁĄCZENIE ARKUSZY STYLÓW I SKRYPTÓW
         ==================================================================== -->
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
    <script src="scripts/kolorujtlo.js"></script>
    <script src="scripts/timedate.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    
    <?php
    /**
     * ========================================================================
     * GŁÓWNY PLIK APLIKACJI - index.php
     * ========================================================================
     * 
     * Opis:    Główny plik aplikacji obsługujący nawigację między podstronami.
     *          Używa parametru $_GET['idp'] do określenia wyświetlanej treści.
     * Wersja:  v1.8
     * Data:    2026
     * 
     * ========================================================================
     */
    
    // Uruchomienie sesji PHP (wymagane dla panelu admina)
    session_start();
    
    // Ustawienie domyślnej wartości parametru idp na 1 (strona główna)
    if (!isset($_GET['idp'])) {
        $_GET['idp'] = '1';
    }
    ?>

</head>
<body onload='startClock()'>
    
    <!-- ====================================================================
         NAWIGACJA GŁÓWNA
         ==================================================================== -->
    <nav>
        <div>
            <a>
                <img src="./pics/logo.png" alt="logo" class="logo">        
            </a>
        </div>
        <ul>
            <li><a href="index.php?idp=1" >Strona główna</a></li>
            <li><a href="index.php?idp=2">Budynki</a></li>
            <li><a href="index.php?idp=3">Ranking</a></li>
            <li><a href="index.php?idp=5">Architektura</a></li>
            <li><a href="index.php?idp=-5">Kontakt</a></li>
            <li><a href="index.php?idp=6">Filmy</a></li>
            <li><a href="index.php?idp=8">JQuary</a></li>
            <li><a href="index.php?idp=10">Lab4</a></li>
            <li><a href="index.php?idp=7">SkryptyJS</a></li>
            <li><a href="index.php?idp=-11">Sklep</a></li>
            <li><a href="index.php?idp=-10">Koszyk</a></li>
            <li><a href="index.php?idp=-1">Panel Admina</a></li>
        </ul>
        <div class="nav-clock">
            <div id='zegarek'></div>
            <div id='data'></div>
        </div>
    </nav>

    <!-- ====================================================================
         GŁÓWNA ZAWARTOŚĆ STRONY
         ==================================================================== -->
    <main class="<?php echo (isset($_GET['idp']) && ($_GET['idp'] == '-11' || $_GET['idp'] == '-10')) ? 'main-wide' : ''; ?>">
    <?php
    
    // =========================================================================
    // DOŁĄCZENIE PLIKÓW POMOCNICZYCH
    // =========================================================================
    
    include('cfg.php');             // Konfiguracja bazy danych
    include('showpage.php');        // Funkcja wyświetlania podstron
    include('admin/admin.php');     // Klasa panelu administratora
    include('admin/admin_categories.php'); // Klasa zarządzania kategoriami
    include('admin/admin_products.php'); // Klasa zarządzania produktami (Sklep)
    include('php/contact.php');     // Klasa formularza kontaktowego
    include('php/cart.php');        // Klasa koszyka zakupowego
    
    // =========================================================================
    // ZABEZPIECZENIE PARAMETRU idp
    // =========================================================================
    // Używamy intval() dla wartości numerycznych - chroni przed SQL Injection
    // htmlspecialchars() zabezpiecza przed atakami XSS
    // =========================================================================
    
    $id = intval($_GET['idp']);
    
    // Zmienna statyczna dla obiektu Admin (singleton pattern)
    static $Admin = null;

    // =========================================================================
    // OBSŁUGA ROUTINGU - switch na podstawie parametru idp
    // =========================================================================
    // Wartości ujemne (-1 do -7) są zarezerwowane dla funkcji administracyjnych
    // Wartości dodatnie to ID podstron z bazy danych
    // =========================================================================
    
    switch ($id) {
        
        // -----------------------------------------------------------------
        // SKLEP - WIDOK PRODUKTÓW DLA KLIENTÓW
        // -----------------------------------------------------------------
        case -11:
            $cart = new Cart();
            $message = $cart->handleAction();
            echo $cart->formatMessage($message);
            echo $cart->showShop();
            break;
        
        // -----------------------------------------------------------------
        // KOSZYK ZAKUPOWY
        // -----------------------------------------------------------------
        case -10:
            $cart = new Cart();
            $message = $cart->handleAction();
            echo $cart->formatMessage($message);
            echo $cart->showCart();
            break;

        // -----------------------------------------------------------------
        // ZARZADZANIE KATEGORIAMI
        // -----------------------------------------------------------------
        case -8:
            $catManager = new ZarzadzanieKategoriami();
            echo $catManager->Zarzadzaj();
            break;

        // -----------------------------------------------------------------
        // ZARZADZANIE PRODUKTAMI (SKLEP)
        // -----------------------------------------------------------------
        case -9:
            $prodManager = new ProductManagement();
            echo $prodManager->Zarzadzaj();
            break;

        // -----------------------------------------------------------------
        // STRONY STATYCZNE I SKRYPTY (Fix dla JQuary i innych)
        // -----------------------------------------------------------------
        case 7:
            include('html/skryptyJS.html');
            break;
            
        case 8:
            include('html/JQuary.html');
            break;
            
        case 10:
            include('html/labor_175281_ISI2.php');
            break;
            


        // -----------------------------------------------------------------
        // PANEL LOGOWANIA ADMINISTRATORA
        // -----------------------------------------------------------------
        case -1:
            if ($Admin === null) {
                $Admin = new Admin();
            }
            echo $Admin->LoginAdmin();
            break;
        
        // -----------------------------------------------------------------
        // EDYCJA STRONY (wymaga zalogowania)
        // -----------------------------------------------------------------
        case -2:
            if ($Admin === null) {
                $Admin = new Admin();
            }
            echo $Admin->EditPage();
            break;
        
        // -----------------------------------------------------------------
        // USUWANIE STRONY (wymaga zalogowania)
        // -----------------------------------------------------------------
        case -3:
            if ($Admin === null) {
                $Admin = new Admin();
            }
            echo $Admin->DeletePage();
            break;
        
        // -----------------------------------------------------------------
        // TWORZENIE NOWEJ STRONY (wymaga zalogowania)
        // -----------------------------------------------------------------
        case -4:
            if ($Admin === null) {
                $Admin = new Admin();
            }
            echo $Admin->CreatePage();
            break;
        
        // -----------------------------------------------------------------
        // FORMULARZ KONTAKTOWY
        // -----------------------------------------------------------------
        case -5:
            $contact = new Contact();
            echo "<h1> Kontakt </h1>";
            // Wywołanie metody wysyłania maila z formularzem kontaktowym
            echo $contact->WyslijMailKontakt("pawelokej6@gmail.com");
            break;
        
        // -----------------------------------------------------------------
        // WYLOGOWANIE ADMINISTRATORA
        // -----------------------------------------------------------------
        case -6:
            if ($Admin === null) {
                $Admin = new Admin();
            }
            echo $Admin->Wyloguj();
            break;
        
        // -----------------------------------------------------------------
        // ODZYSKIWANIE HASŁA
        // -----------------------------------------------------------------
        case -7:
            $Contact = new Contact();
            echo "<h2> Odzyskanie hasla </h2>";
            // Wyświetlenie formularza odzyskiwania hasła
            echo $Contact->PrzypomnijHaslo("pawelokej6@gmail.com");
            break;

        // -----------------------------------------------------------------
        // WYŚWIETLANIE PODSTRON Z BAZY DANYCH
        // -----------------------------------------------------------------
        default:
            // Wywołanie funkcji PokazStrone() z showpage.php
            echo PokazStrone($id);
            break;
    }
    ?>
    </main>
    
    <?php
    // =========================================================================
    // DANE STOPKI
    // =========================================================================
    ?>

    <!-- ====================================================================
         STOPKA STRONY
         ==================================================================== -->
    <footer>
        <p>&copy; 2026 Największe budynki na świecie - Paweł Milanowski 175281</p>             
    </footer>
</body>
</html>
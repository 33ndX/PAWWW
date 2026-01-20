<?php
/**
 * ============================================================================
 * PANEL ADMINISTRATORA - admin.php
 * ============================================================================
 * 
 * Opis:    Klasa Admin obsługuje panel administracyjny aplikacji.
 *          Umożliwia logowanie, wylogowanie oraz operacje CRUD na podstronach.
 * Autor:   Paweł Milanowski
 * Data:    2025
 * 
 * Funkcjonalności:
 * - Logowanie i wylogowanie administratora
 * - Lista podstron (CRUD operations)
 * - Edycja istniejących stron
 * - Tworzenie nowych stron
 * - Usuwanie stron
 * 
 * ============================================================================
 */

// Dołączenie pliku konfiguracyjnego z połączeniem do bazy danych
include 'cfg.php';


/**
 * ============================================================================
 * KLASA: Admin
 * ============================================================================
 * 
 * Główna klasa panelu administracyjnego. Obsługuje wszystkie operacje
 * związane z zarządzaniem treścią strony.
 * 
 * ============================================================================
 */
class Admin {
    

    /**
     * ========================================================================
     * METODA: FormularzLogowania
     * ========================================================================
     * 
     * Generuje formularz HTML do logowania administratora.
     * Formularz wysyła dane metodą POST do bieżącej strony.
     * 
     * @return  string  Kod HTML formularza logowania
     * 
     * ========================================================================
     */
    function FormularzLogowania() {
        
        $wynik = '
            <div class="admin-container centered">
                <h2 class="admin-header">Logowanie do Panelu</h2>
                <form method="post" name="LoginForm" enctype="multipart/form-data" action="' . $_SERVER['REQUEST_URI'] . '">
                    <div class="admin-form-group">
                        <label>Login</label>
                        <input type="text" name="login" placeholder="Wprowadź login (np. admin)" />
                    </div>
                    <div class="admin-form-group">
                        <label>Hasło</label>
                        <input type="password" name="login_pass" placeholder="Wprowadź hasło" />
                    </div>
                    <div class="admin-form-group">
                        <input type="submit" name="x1_submit" class="btn btn-primary" value="Zaloguj się" style="width: 100%;" />
                    </div>
                </form>
            </div>
        ';
    
        return $wynik;
    }


    /**
     * ========================================================================
     * METODA: ListaPodstron
     * ========================================================================
     * 
     * Pobiera listę wszystkich podstron z bazy danych i wyświetla je
     * w formie tabeli HTML z przyciskami do edycji i usuwania.
     * 
     * Zapytanie SQL używa LIMIT 100 dla wydajności.
     * 
     * @return  void    Wyświetla tabelę bezpośrednio przez echo
     * 
     * ========================================================================
     */
    function ListaPodstron() {
        
        global $conn;
        
        $query = "SELECT id, page_title FROM page_list ORDER BY id ASC LIMIT 100";
        $result = $conn->query($query);
        
        echo '<div class="admin-container wide">';
        
        // --- NAV BAR ---
        echo '<div class="admin-nav">
                <div class="admin-nav-links">
                    <a href="?idp=-1">Pulpit Stron</a>
                    <a href="?idp=-8">Kategorie</a>
                    <a href="?idp=-9">Produkty</a>
                </div>
                <div>
                     <a href="?idp=-1&logout=1" class="btn btn-delete" style="padding: 5px 10px; font-size: 0.8em;">Wyloguj</a>
                </div>
              </div>';
        
        echo '<h2 class="admin-header">Zarządzanie Stronami</h2>';
        
        echo '
            <table class="admin-table">
                <thead>
                    <tr>
                        <th width="5%">ID</th>
                        <th width="55%">Tytuł Strony</th>
                        <th width="40%">Akcje</th>
                    </tr>
                </thead>
                <tbody>';
        
        while ($row = $result->fetch_assoc()) {
            
            $safeId = htmlspecialchars($row['id']);
            $safeTitle = htmlspecialchars($row['page_title']);
            
            echo '<tr>
                <td><b>' . $safeId . '</b></td>
                <td>' . $safeTitle . '</td>
                <td>
                    <a class="btn btn-edit" href="?idp=-2&ide=' . $safeId . '">Edytuj</a>
                    <a class="btn btn-delete" href="?idp=-3&idd=' . $safeId . '" onclick="return confirm(\'Czy jesteś pewien?\');">Usuń</a>
                </td>
            </tr>';
        }
        
        echo '</tbody></table>';
        
        echo '<div class="admin-actions">
                <a class="btn btn-primary" href="?idp=-4">+ Dodaj Nową Stronę</a>
                <a class="btn btn-success" href="?idp=-8">Zarządzaj Kategoriami</a>
              </div>';
        echo '</div>';
    }


    /**
     * ========================================================================
     * METODA: CheckLogin
     * ========================================================================
     * 
     * Sprawdza status logowania użytkownika.
     * Jeśli sesja jest aktywna - zwraca 1.
     * Jeśli przesłano dane logowania - weryfikuje je.
     * 
     * @return  int     1 = zalogowany, 0 = niezalogowany
     * 
     * ========================================================================
     */
    function CheckLogin() {
        
        // Sprawdzenie czy użytkownik jest już zalogowany (sesja)
        if (isset($_SESSION['loggedin']) && $_SESSION['loggedin']) {
            return 1;
        }

        // Sprawdzenie czy przesłano formularz logowania
        if (isset($_POST['login']) && isset($_POST['login_pass'])) {
            
            // Wywołanie metody CheckLoginCred() do weryfikacji danych
            return $this->CheckLoginCred($_POST['login'], $_POST['login_pass']);
        }
        else {
            return 0;
        }
    }


    /**
     * ========================================================================
     * METODA: CheckLoginCred
     * ========================================================================
     * 
     * Weryfikuje dane logowania (login i hasło) z danymi w konfiguracji.
     * W przypadku sukcesu ustawia zmienną sesji 'loggedin'.
     * 
     * @param   string  $login  Login do weryfikacji
     * @param   string  $pass   Hasło do weryfikacji
     * @return  int             1 = sukces, 0 = niepowodzenie
     * 
     * ========================================================================
     */
    function CheckLoginCred($login, $pass) {
        
        // Porównanie danych ze stałymi zdefiniowanymi w cfg.php
        if ($login == admin && $pass == pass) {
            
            // Ustawienie flagi zalogowania w sesji
            $_SESSION['loggedin'] = true;
            return 1;
            
        } else {
            echo '<div class="login-message msg-error">Logowanie się nie powiodło.</div>';
            return 0;
        }
    }


    /**
     * ========================================================================
     * METODA: LoginAdmin
     * ========================================================================
     * 
     * Główna metoda obsługująca panel logowania.
     * Jeśli użytkownik jest zalogowany - wyświetla listę podstron.
     * W przeciwnym razie - wyświetla formularz logowania.
     * 
     * @return  void    Wyświetla odpowiednią zawartość przez echo
     * 
     * ========================================================================
     */
    function LoginAdmin() {
        
        // Check for logout request
        if (isset($_GET['logout'])) {
            $this->Wyloguj();
            exit;
        }

        // Sprawdzenie statusu logowania
        $status_login = $this->CheckLogin();

        if ($status_login == 1) {
            // Zalogowany - wyświetl listę podstron
            echo $this->ListaPodstron();
        } else {
            // Niezalogowany - wyświetl formularz logowania
            echo $this->FormularzLogowania();
        }
    }


    /**
     * ========================================================================
     * METODA: EditPage
     * ========================================================================
     * 
     * Obsługuje edycję istniejącej strony.
     * Wymaga zalogowania i parametru $_GET['ide'] z ID strony.
     * 
     * BEZPIECZEŃSTWO:
     * - Używa intval() na parametrze GET (ochrona przed SQL Injection)
     * - Używa prepared statements dla zapytań UPDATE
     * - Używa htmlspecialchars() przy wyświetlaniu danych w formularzu
     * 
     * @return  string  Kod HTML formularza edycji lub komunikat o błędzie
     * 
     * ========================================================================
     */
    function EditPage() {
        
        // Sprawdzenie statusu logowania
        $status_login = $this->CheckLogin();
        
        if ($status_login == 1) {
            
            if (isset($_GET['ide'])) {
                
                // -----------------------------------------------------------------
                // OBSŁUGA FORMULARZA POST - aktualizacja strony
                // -----------------------------------------------------------------
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && 
                    isset($_POST['edit_title'], $_POST['edit_content'], $_POST['edit_alias'])) {
                    
                    // Zabezpieczenie ID przez intval() (tylko liczby całkowite)
                    $id = intval($_GET['ide']);
                    
                    // Pobranie i zabezpieczenie danych z formularza
                    $title   = htmlspecialchars($_POST['edit_title']);
                    $content = $_POST['edit_content'];  // Może zawierać HTML
                    $active  = isset($_POST['edit_active']) ? 1 : 0;
                    $alias   = htmlspecialchars($_POST['edit_alias']);

                    // ---------------------------------------------------------
                    // ZAPYTANIE SQL - UPDATE z prepared statement
                    // ---------------------------------------------------------
                    // Używamy prepared statement zamiast real_escape_string()
                    // dla maksymalnego bezpieczeństwa przed SQL Injection
                    // LIMIT 1 - aktualizujemy tylko jeden rekord
                    // ---------------------------------------------------------
                    $query = "UPDATE page_list 
                              SET page_title = ?, page_content = ?, status = ?, alias = ? 
                              WHERE id = ? 
                              LIMIT 1";
                    
                    $stmt = $GLOBALS['conn']->prepare($query);
                    
                    // Powiązanie parametrów: s=string, i=integer
                    $stmt->bind_param("ssisi", $title, $content, $active, $alias, $id);

                    if ($stmt->execute()) {
                        echo '<div class="login-message msg-success">Strona zaktualizowana</div>';
                        $stmt->close();
                        header("Location: ?idp=-1");
                        exit;
                    } else {
                        echo '<div class="login-message msg-error">Błąd: ' . $stmt->error . '</div>';
                        $stmt->close();
                    }
                    
                } else {
                    
                    // ---------------------------------------------------------
                    // WYŚWIETLENIE FORMULARZA EDYCJI
                    // ---------------------------------------------------------
                    
                    // Zabezpieczenie ID przez intval()
                    $id = intval($_GET['ide']);
                    
                    // Pobranie danych strony z bazy (prepared statement)
                    $query = "SELECT * FROM page_list WHERE id = ? LIMIT 1";
                    $stmt = $GLOBALS['conn']->prepare($query);
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result && $result->num_rows > 0) {
                        
                        $row = $result->fetch_assoc();
                        $stmt->close();

                        // Formularz HTML z zabezpieczonymi danymi (htmlspecialchars)
                        echo '<div class="admin-container centered">';
                        echo '<div class="admin-nav">
                                <div class="admin-nav-links">
                                    <a href="?idp=-1">Pulpit Stron</a>
                                    <a href="?idp=-8">Kategorie</a>
                                    <a href="?idp=-9">Produkty</a>
                                </div>
                                <div>
                                     <a href="?idp=-1&logout=1" class="btn btn-delete" style="padding: 5px 10px; font-size: 0.8em;">Wyloguj</a>
                                </div>
                              </div>';
                        
                        return '
                                <h3 class="admin-header">Edytuj stronę</h3>
                                
                                <form method="post" action="' . $_SERVER['REQUEST_URI'] . '">
                                    <div class="admin-form-group">
                                        <label for="edit_title">Tytuł strony</label>
                                        <input type="text" id="edit_title" name="edit_title" 
                                               value="' . htmlspecialchars($row['page_title']) . '" required />
                                    </div>
                                    <div class="admin-form-group">
                                        <label for="edit_content">Treść strony</label>
                                        <textarea id="edit_content" name="edit_content" required rows="10">' 
                                            . htmlspecialchars($row['page_content']) . 
                                        '</textarea>
                                    </div>
                                    <div class="admin-form-group">
                                        <label for="edit_alias">Alias (URLfriendly)</label>
                                        <input type="text" id="edit_alias" name="edit_alias" 
                                               value="' . htmlspecialchars($row['alias']) . '" required />
                                    </div>
                                    <div class="admin-form-group">
                                        <label for="edit_active">
                                            <input type="checkbox" id="edit_active" name="edit_active"' 
                                            . ($row['status'] ? ' checked' : '') . ' /> Aktywna na stronie
                                        </label>
                                    </div>
                                    <div class="admin-actions">
                                        <a href="?idp=-1" class="btn btn-delete">Anuluj</a>
                                        <input type="submit" class="btn btn-success" value="Zapisz zmiany" />
                                    </div>
                                </form>
                            </div>';
                                    
                    } else {
                        $stmt->close();
                        return '<div class="login-message msg-error">Nie znaleziono strony do edycji</div>';
                    }
                }
                
            } else {
                return '<div class="login-message msg-error">Nie podano ID strony do edycji</div>';
            }
            
        } else {
            // Niezalogowany - wyświetl formularz logowania
            return $this->FormularzLogowania();
        }
    }


    /**
     * ========================================================================
     * METODA: CreatePage
     * ========================================================================
     * 
     * Obsługuje tworzenie nowej strony.
     * Wymaga zalogowania. Wyświetla formularz lub przetwarza dane POST.
     * 
     * BEZPIECZEŃSTWO:
     * - Używa prepared statements dla zapytań INSERT
     * - Używa htmlspecialchars() dla danych z formularza
     * 
     * @return  string  Kod HTML formularza tworzenia strony
     * 
     * ========================================================================
     */
    function CreatePage() {
        
        // Sprawdzenie statusu logowania
        $status_login = $this->CheckLogin();
        
        if ($status_login == 1) {
            
            // -----------------------------------------------------------------
            // OBSŁUGA FORMULARZA POST - tworzenie nowej strony
            // -----------------------------------------------------------------
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && 
                isset($_POST['create_title'], $_POST['create_content'], $_POST['create_alias'])) {
                
                // Pobranie i zabezpieczenie danych z formularza
                $title   = htmlspecialchars($_POST['create_title']);
                $content = $_POST['create_content'];  // Może zawierać HTML
                $active  = isset($_POST['create_active']) ? 1 : 0;
                $alias   = htmlspecialchars($_POST['create_alias']);

                // ---------------------------------------------------------
                // ZAPYTANIE SQL - INSERT z prepared statement
                // ---------------------------------------------------------
                // Używamy prepared statement zamiast real_escape_string()
                // dla maksymalnego bezpieczeństwa przed SQL Injection
                // ---------------------------------------------------------
                $query = "INSERT INTO page_list (page_title, page_content, status, alias) 
                          VALUES (?, ?, ?, ?)";
                
                $stmt = $GLOBALS['conn']->prepare($query);
                
                // Powiązanie parametrów: s=string, i=integer
                $stmt->bind_param("ssis", $title, $content, $active, $alias);

                if ($stmt->execute()) {
                    echo '<div class="login-message msg-success">Strona utworzona pomyślnie</div>';
                    $stmt->close();
                    header("Location: ?idp=-1");
                    exit;
                } else {
                    echo '<div class="login-message msg-error">Błąd: ' . $stmt->error . '</div>';
                    $stmt->close();
                }
                
            } else {
                
                // ---------------------------------------------------------
                // WYŚWIETLENIE FORMULARZA TWORZENIA STRONY
                // ---------------------------------------------------------
                return '
                    <div class="admin-container centered">
                        <div class="admin-nav">
                             <div class="admin-nav-links">
                                <a href="?idp=-1">Wróć do listy</a>
                             </div>
                        </div>
                        
                        <h3 class="admin-header">Utwórz nową stronę</h3>
                        
                        <form method="post" action="' . $_SERVER['REQUEST_URI'] . '">
                            <div class="admin-form-group">
                                <label for="create_title">Tytuł strony</label>
                                <input type="text" id="create_title" name="create_title" required placeholder="Np. O nas" />
                            </div>
                            <div class="admin-form-group">
                                <label for="create_content">Treść strony</label>
                                <textarea id="create_content" name="create_content" required rows="10" placeholder="Wpisz treść HTML..."></textarea>
                            </div>
                            <div class="admin-form-group">
                                <label for="create_alias">Alias (URL)</label>
                                <input type="text" id="create_alias" name="create_alias" required placeholder="np. o-nas" />
                            </div>
                            <div class="admin-form-group">
                                <label for="create_active">
                                    <input type="checkbox" id="create_active" name="create_active" checked /> Strona aktywna po utworzeniu
                                </label>
                            </div>
                            <div class="admin-actions">
                                <a href="?idp=-1" class="btn btn-delete">Anuluj</a>
                                <input type="submit" class="btn btn-primary" value="Utwórz stronę" />
                            </div>
                        </form>
                    </div>';
            }
            
        } else {
            // Niezalogowany - wyświetl formularz logowania
            return $this->FormularzLogowania();
        }
    }


    /**
     * ========================================================================
     * METODA: DeletePage
     * ========================================================================
     * 
     * Usuwa stronę z bazy danych na podstawie ID.
     * Wymaga zalogowania i parametru $_GET['idd'] z ID strony.
     * 
     * BEZPIECZEŃSTWO:
     * - Używa intval() na parametrze GET (ochrona przed SQL Injection)
     * - Używa prepared statements dla zapytań DELETE
     * - LIMIT 1 - usuwa tylko jeden rekord
     * 
     * @return  string|void  Formularz logowania lub void (redirect)
     * 
     * ========================================================================
     */
    function DeletePage() {
        
        // Sprawdzenie statusu logowania
        $status_login = $this->CheckLogin();
    
        if ($status_login == 1) {

            if (isset($_GET['idd'])) {
                
                // Zabezpieczenie ID przez intval() (tylko liczby całkowite)
                $id = intval($_GET['idd']);
    
                // ---------------------------------------------------------
                // ZAPYTANIE SQL - DELETE z prepared statement
                // ---------------------------------------------------------
                // Używamy prepared statement dla bezpieczeństwa
                // LIMIT 1 - usuwamy tylko jeden rekord
                // ---------------------------------------------------------
                $query = "DELETE FROM page_list WHERE id = ? LIMIT 1";
                
                $stmt = $GLOBALS['conn']->prepare($query);
                $stmt->bind_param("i", $id);

                if ($stmt->execute()) {
                    echo '<div class="login-message msg-success">Strona została usunięta pomyślnie.</div>';
                    $stmt->close();
                    header("Location: ?idp=-1");
                    exit;
                } else {
                    echo '<div class="login-message msg-error">Błąd podczas usuwania: ' . $stmt->error . '</div>';
                    $stmt->close();
                }
                
            } else {
                echo '<div class="login-message msg-error">Nie podano ID strony do usunięcia.</div>';
            }
            
        } else {
            // Niezalogowany - wyświetl formularz logowania
            return $this->FormularzLogowania();
        }
    }


    /**
     * ========================================================================
     * METODA: Wyloguj
     * ========================================================================
     * 
     * Wylogowuje administratora poprzez usunięcie zmiennej sesji.
     * Po wylogowaniu przekierowuje na stronę główną.
     * 
     * @return  void    Wykonuje przekierowanie (exit)
     * 
     * ========================================================================
     */
    function Wyloguj() {
        
        // Sprawdzenie czy istnieje zmienna sesji logowania
        if (isset($_SESSION['loggedin'])) {
            
            // Usunięcie zmiennej sesji
            unset($_SESSION['loggedin']);
        }
        
        // Przekierowanie na stronę główną
        header('Location: ?idp=1');
        exit;
    }
}

?>

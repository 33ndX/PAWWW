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
            <div class="logowanie">
                <h2 class="head">Zaloguj do panelu admina:</h2>
                    <form method="post" name="LoginForm" enctype="multipart/form-data" action="' . $_SERVER['REQUEST_URI'] . '">
                        <table class="logowanie">
                            <tr>
                                <td class="log4_t">Login</td>
                                <td><input type="text" name="login" class="logowanie" /></td>
                            </tr>
                            <tr>
                                <td class="log4_t">Hasło</td>
                                <td><input type="password" name="login_pass" class="logowanie" /></td>
                            </tr>
                            <tr>
                                <td> </td>
                                <td><input type="submit" name="x1_submit" class="logowanie" value="zaloguj" /></td>
                            </tr>
                        </table>
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
        
        // ---------------------------------------------------------------------
        // ZAPYTANIE SQL - pobranie listy stron
        // ---------------------------------------------------------------------
        // ORDER BY id ASC - sortowanie rosnąco po ID
        // LIMIT 100 - ograniczenie wyników dla wydajności
        // ---------------------------------------------------------------------
        $query = "SELECT id, page_title FROM page_list ORDER BY id ASC LIMIT 100";
        $result = $conn->query($query);
        
        // Początek kontenera HTML
        echo '<div class="podstrony">';
        echo "<h1 class='lista_stron'>Lista Stron</h1>";
        
        // Nagłówek tabeli
        echo '
            <table class="stronki">
                <tr class="column_names">
                    <th>ID Strony</th>
                    <th>Tytuł Strony</th>
                    <th>Edytuj</th>
                    <th>Usuń</th>
                </tr>';
        
        // Iteracja przez wyniki i wyświetlanie wierszy
        while ($row = $result->fetch_assoc()) {
            
            // Zabezpieczenie wyświetlanych danych przed XSS
            $safeId = htmlspecialchars($row['id']);
            $safeTitle = htmlspecialchars($row['page_title']);
            
            echo '<tr class="el_listy">
                <td style="color: white;">' . $safeId . '</td>
                <td style="color: white;">' . $safeTitle . '</td>
                <td><a class="edit-button" href="?idp=-2&ide=' . $safeId . '">Edit</a></td>
                <td><a class="delete-button" href="?idp=-3&idd=' . $safeId . '" onclick="return confirm(\'Czy jesteś pewien?\');">Delete</a></td>
            </tr>';
        }
        
        echo '</table>';
        
        // Przycisk do tworzenia nowej strony
        echo '<a class="create_page" href="?idp=-4">Create New Page</a>';
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
            echo "Logowanie się nie powiodło.";
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
                        echo 'Strona zaktualizowana';
                        $stmt->close();
                        header("Location: ?idp=-1");
                        exit;
                    } else {
                        echo "Błąd: " . $stmt->error;
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
                        return '
                            <div class="edit-container">
                                <h3 class="edit-title">Edytuj stronę</h3>
                                <form method="post" action="' . $_SERVER['REQUEST_URI'] . '">
                                    <div class="form-group">
                                        <label for="edit_title">Tytuł:</label>
                                        <input type="text" id="edit_title" name="edit_title" 
                                               value="' . htmlspecialchars($row['page_title']) . '" required />
                                    </div>
                                    <div class="form-group">
                                        <label for="edit_content">Zawartość:</label>
                                        <textarea id="edit_content" name="edit_content" required>' 
                                            . htmlspecialchars($row['page_content']) . 
                                        '</textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="edit_active">Aktywna:</label>
                                        <input type="checkbox" id="edit_active" name="edit_active"' 
                                            . ($row['status'] ? ' checked' : '') . ' />
                                    </div>
                                    <div class="form-group">
                                        <label for="edit_alias">Alias:</label>
                                        <input type="text" id="edit_alias" name="edit_alias" 
                                               value="' . htmlspecialchars($row['alias']) . '" required />
                                    </div>
                                    <div class="form-group">
                                        <input type="submit" class="submit-button" value="Zapisz zmiany" />
                                    </div>
                                </form>
                            </div>';
                                    
                    } else {
                        $stmt->close();
                        return "Nie znaleziono strony do edycji";
                    }
                }
                
            } else {
                return "Nie podano ID strony do edycji";
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
            
            echo '<h3 class="create_page"> Nowa strona </h3>';
            
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
                    echo 'Strona utworzona pomyślnie';
                    $stmt->close();
                    header("Location: ?idp=-1");
                    exit;
                } else {
                    echo "Błąd: " . $stmt->error;
                    $stmt->close();
                }
                
            } else {
                
                // ---------------------------------------------------------
                // WYŚWIETLENIE FORMULARZA TWORZENIA STRONY
                // ---------------------------------------------------------
                return '
                    <div class="create-container">
                        <form method="post" action="' . $_SERVER['REQUEST_URI'] . '">
                            <div class="form-group">
                                <label for="create_title">Tytuł:</label>
                                <input type="text" id="create_title" name="create_title" required />
                            </div>
                            <div class="form-group">
                                <label for="create_content">Zawartość:</label>
                                <textarea id="create_content" name="create_content" required></textarea>
                            </div>
                            <div class="form-group">
                                <label for="create_active">Aktywna:</label>
                                <input type="checkbox" id="create_active" name="create_active" />
                            </div>
                            <div class="form-group">
                                <label for="create_alias">Alias:</label>
                                <input type="text" id="create_alias" name="create_alias" required />
                            </div>
                            <div class="form-group">
                                <input type="submit" class="submit-button" value="Dodaj stronę" />
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
                    echo "Strona została usunięta pomyślnie.";
                    $stmt->close();
                    header("Location: ?idp=-1");
                    exit;
                } else {
                    echo "Błąd podczas usuwania: " . $stmt->error;
                    $stmt->close();
                }
                
            } else {
                echo "Nie podano ID strony do usunięcia.";
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

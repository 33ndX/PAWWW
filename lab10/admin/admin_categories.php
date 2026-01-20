<?php
// admin/admin_categories.php

class ZarzadzanieKategoriami {

    // Helper to check login (reusing Admin's logic or just check session)
    function CheckLogin() {
        if (isset($_SESSION['loggedin']) && $_SESSION['loggedin']) {
            return 1;
        }
        return 0;
    }

    function DodajKategorie() {
        global $conn;
        
        $matka = isset($_POST['matka']) ? intval($_POST['matka']) : 0;
        $nazwa = isset($_POST['nazwa']) ? htmlspecialchars($_POST['nazwa']) : '';

        if (!empty($nazwa)) {
            $stmt = $conn->prepare("INSERT INTO categories (matka, nazwa) VALUES (?, ?)");
            $stmt->bind_param("is", $matka, $nazwa);
            if ($stmt->execute()) {
                echo '<div class="login-message msg-success">Kategoria dodana pomyślnie.</div>';
            } else {
                echo '<div class="login-message msg-error">Błąd dodawania kategorii: ' . $stmt->error . '</div>';
            }
            $stmt->close();
        } else {
            echo '<div class="login-message msg-error">Nazwa kategorii nie może być pusta.</div>';
        }
    }

    function UsunKategorie() {
        global $conn;
        
        $id = isset($_GET['delete_id']) ? intval($_GET['delete_id']) : 0;

        if ($id > 0) {
            $stmt = $conn->prepare("DELETE FROM categories WHERE id = ? LIMIT 1");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                echo '<div class="login-message msg-success">Kategoria usunięta pomyślnie.</div>';
            } else {
                echo '<div class="login-message msg-error">Błąd usuwania: ' . $stmt->error . '</div>';
            }
            $stmt->close();
        }
    }

    function EdytujKategorie() {
        global $conn;
        
        $id = isset($_POST['edit_id']) ? intval($_POST['edit_id']) : 0;
        $nazwa = isset($_POST['nazwa']) ? htmlspecialchars($_POST['nazwa']) : '';
        
        if ($id > 0 && !empty($nazwa)) {
            $stmt = $conn->prepare("UPDATE categories SET nazwa = ? WHERE id = ? LIMIT 1");
            $stmt->bind_param("si", $nazwa, $id);
            if ($stmt->execute()) {
                echo '<div class="login-message msg-success">Kategoria zaktualizowana pomyślnie.</div>';
            } else {
                echo '<div class="login-message msg-error">Błąd edycji: ' . $stmt->error . '</div>';
            }
            $stmt->close();
        }
    }

    function PokazKategorie() {
        global $conn;

        // --- DASHBOARD WRAPPER ---
        echo '<div class="admin-container wide">';

        // --- NAV BAR ---
        echo '<div class="admin-nav">
                <div class="admin-nav-links">
                    <a href="?idp=-1">Pulpit Stron</a>
                    <a href="?idp=-8" class="active">Kategorie</a>
                </div>
                <div>
                     <a href="?idp=-1&logout=1" class="btn btn-delete" style="padding: 5px 10px; font-size: 0.8em;">Wyloguj</a>
                </div>
              </div>';

        echo '<h2 class="admin-header">Zarządzanie Kategoriami</h2>';

        // --- LAYOUT: Dwie kolumny (Formularz dodawania + Drzewo) ---
        echo '<div style="display:flex; gap: 30px; flex-wrap: wrap;">';
        
        // LEWA KOLUMNA: Formularz dodawania
        echo '<div style="flex: 1; min-width: 300px;">
                <div class="admin-container centered" style="margin: 0; box-shadow: none; border: 1px solid #eee;">
                    <h3>Dodaj nową kategorię</h3>
                    <form method="post" action="?idp=-8&action=add">
                        <div class="admin-form-group">
                            <label>Nazwa kategorii:</label>
                            <input type="text" name="nazwa" required placeholder="Wpisz nazwę...">
                        </div>
                        <div class="admin-form-group">
                            <label>Kategoria nadrzędna (ID):</label>
                            <input type="number" name="matka" value="0" placeholder="0 = główna">
                            <small style="color:#718096; display:block; margin-top:5px;">Wpisz ID matki lub 0 dla głównej.</small>
                        </div>
                        <div class="admin-form-group">
                            <input type="submit" class="btn btn-primary" value="Dodaj Kategorię" style="width:100%">
                        </div>
                    </form>
                </div>
              </div>';
              
        // PRAWA KOLUMNA: Drzewo
        echo '<div style="flex: 2; min-width: 300px;">';
        echo '<h3>Drzewo Kategorii</h3>';
        echo '<div class="category-tree">';
        
        // 1. Get Main Categories (Mothers)
        $query_mothers = "SELECT * FROM categories WHERE matka = 0 ORDER BY id ASC LIMIT 100";
        $result_mothers = $conn->query($query_mothers);

        if ($result_mothers) {
            echo '<ul>';
            while ($mother = $result_mothers->fetch_assoc()) {
                echo '<li>';
                echo '<div class="category-item">
                        <span><b>' . htmlspecialchars($mother['nazwa']) . '</b> <small style="color:#a0aec0">(ID: ' . $mother['id'] . ')</small></span>
                        <div class="category-actions">
                            <a class="btn btn-edit" style="padding: 4px 8px; font-size: 0.8em;" href="?idp=-8&action=edit&id=' . $mother['id'] . '">Edytuj</a>
                            <a class="btn btn-delete" style="padding: 4px 8px; font-size: 0.8em;" href="?idp=-8&action=delete&delete_id=' . $mother['id'] . '" onclick="return confirm(\'Usunąć?\')">Usuń</a>
                        </div>
                      </div>';
                
                // 2. Get Children for this Mother
                $mother_id = $mother['id'];
                $query_children = "SELECT * FROM categories WHERE matka = $mother_id ORDER BY id ASC LIMIT 100";
                $result_children = $conn->query($query_children);
                
                if ($result_children && $result_children->num_rows > 0) {
                    echo '<ul>';
                    while ($child = $result_children->fetch_assoc()) {
                        echo '<li>';
                         echo '<div class="category-item">
                                <span>' . htmlspecialchars($child['nazwa']) . ' <small style="color:#a0aec0">(ID: ' . $child['id'] . ')</small></span>
                                <div class="category-actions">
                                    <a class="btn btn-edit" style="padding: 4px 8px; font-size: 0.8em;" href="?idp=-8&action=edit&id=' . $child['id'] . '">Edytuj</a>
                                    <a class="btn btn-delete" style="padding: 4px 8px; font-size: 0.8em;" href="?idp=-8&action=delete&delete_id=' . $child['id'] . '" onclick="return confirm(\'Usunąć?\')">Usuń</a>
                                </div>
                              </div>';
                        echo '</li>';
                    }
                    echo '</ul>';
                }
                
                echo '</li>';
            }
            echo '</ul>';
        } else {
            echo '<div style="padding:20px; text-align:center; color:#718096;">Brak kategorii.</div>';
        }
        echo '</div>'; // End .category-tree
        echo '</div>'; // End Right Column
        
        echo '</div>'; // End Flex Row

        echo '</div>'; // End .admin-container
    }
    
    // Shows Edit Form for a specific ID
    function FormularzEdycji($id) {
        global $conn;
        $id = intval($id);
        $result = $conn->query("SELECT * FROM categories WHERE id = $id LIMIT 1");
        if ($result && $row = $result->fetch_assoc()) {
            echo '<div class="admin-container centered">';
            echo '<div class="admin-nav"><div class="admin-nav-links"><a href="?idp=-8">Wróć do kategorii</a></div></div>';
            
            echo '<h3 class="admin-header">Edytuj Kategorię</h3>';
            echo '<form method="post" action="?idp=-8&action=update">
                    <input type="hidden" name="edit_id" value="' . $row['id'] . '">
                    <div class="admin-form-group">
                        <label>Nazwa:</label>
                        <input type="text" name="nazwa" value="' . htmlspecialchars($row['nazwa']) . '" required>
                    </div>
                    <div class="admin-actions">
                        <a href="?idp=-8" class="btn btn-delete">Anuluj</a>
                        <input type="submit" class="btn btn-success" value="Zapisz Zmiany">
                    </div>
                  </form>';
            echo '</div>';
        }
    }

    function Zarzadzaj() {
        if ($this->CheckLogin() == 0) {
            echo "Dostęp zabroniony. Proszę się zalogować.";
            return;
        }

        $action = isset($_GET['action']) ? $_GET['action'] : '';

        // Routing akcji
        switch ($action) {
            case 'add':
                $this->DodajKategorie();
                $this->PokazKategorie();
                break;
            case 'delete':
                $this->UsunKategorie();
                $this->PokazKategorie();
                break;
            case 'edit':
                if (isset($_GET['id'])) {
                    $this->FormularzEdycji($_GET['id']);
                } else {
                    $this->PokazKategorie();
                }
                break;
            case 'update':
                $this->EdytujKategorie();
                $this->PokazKategorie();
                break;
            default:
                $this->PokazKategorie();
                break;
        }
    }
}
?>

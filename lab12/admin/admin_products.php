<?php
/**
 * ============================================================================
 * ZARZĄDZANIE PRODUKTAMI - admin_products.php
 * ============================================================================
 * 
 * Opis:    Klasa ProductManagement obsługuje operacje CRUD na produktach.
 * Wersja:  v1.8
 * Data:    2026
 * 
 * ============================================================================
 */

class ProductManagement {

    /**
     * Główna metoda routera - decyduje co wykonać (dodaj, edytuj, usuń, pokaż)
     */
    function Zarzadzaj() {
        
        // Sprawdzenie sesji logowania
        if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
            echo '<div class="login-message msg-error">Dostęp zabroniony. Zaloguj się.</div>';
            echo '<div class="admin-actions"><a href="index.php?idp=-1" class="btn btn-primary">Przejdź do logowania</a></div>';
            return;
        }
        
        // Obsługa akcji z paska adresu
        if (isset($_GET['action'])) {
            $action = $_GET['action'];
            
            if ($action == 'add' && $_SERVER['REQUEST_METHOD'] == 'POST') {
                $this->DodajProdukt();
                return;
            }
            if ($action == 'edit' && isset($_GET['id'])) {
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $this->ZapiszEdycje($_GET['id']);
                } else {
                    $this->FormularzEdycji($_GET['id']);
                }
                return;
            }
            if ($action == 'delete' && isset($_GET['delete_id'])) {
                $this->UsunProdukt($_GET['delete_id']);
                return;
            }
        }
        
        // Domyślnie - pokaż listę produktów
        $this->PokazProdukty();
    }

    /**
     * Wyświetla listę produktów w formie tabeli
     */
    function PokazProdukty() {
        global $conn;

        echo '<div class="admin-container wide">';
        
        // --- NAV BAR ---
        echo '<div class="admin-nav">
                <div class="admin-nav-links">
                    <a href="?idp=-1">Pulpit Stron</a>
                    <a href="?idp=-8">Kategorie</a>
                    <a href="?idp=-9" class="active">Produkty</a>
                </div>
                <div>
                     <a href="?idp=-1&logout=1" class="btn btn-delete" style="padding: 5px 10px; font-size: 0.8em;">Wyloguj</a>
                </div>
              </div>';

        echo '<h2 class="admin-header">Zarządzanie Produktami (Sklep)</h2>';
        
        // Formularz dodawania (przycisk)
        echo '<div style="text-align:right; margin-bottom:20px;">
                <a href="#addProductForm" class="btn btn-primary" onclick="window.location.hash=\'addProductForm\';">+ Dodaj Nowy Produkt</a>
              </div>';

        // Tabela produktów
        echo '<table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Zdjęcie</th>
                        <th>Tytuł</th>
                        <th>Cena Netto</th>
                        <th>VAT</th>
                        <th>Magazyn</th>
                        <th>Status</th>
                        <th>Akcje</th>
                    </tr>
                </thead>
                <tbody>';

        $query = "SELECT * FROM products ORDER BY id DESC LIMIT 100";
        $result = $conn->query($query);

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                
                // Obliczanie dostępności
                $isAvailable = ($row['availability_status'] == 1 && 
                                $row['stock_quantity'] > 0 && 
                                ($row['expires_at'] == NULL || $row['expires_at'] > date('Y-m-d H:i:s')));
                
                $statusBadge = $isAvailable 
                    ? '<span style="color:white; background:#2ecc71; padding:2px 6px; border-radius:4px; font-size:0.8em;">Dostępny</span>'
                    : '<span style="color:white; background:#e74c3c; padding:2px 6px; border-radius:4px; font-size:0.8em;">Niedostępny</span>';

                $imgCmd = $row['image_url'] ? '<img src="'.$row['image_url'].'" style="height:40px;">' : '-';

                echo '<tr>
                        <td>'.$row['id'].'</td>
                        <td>'.$imgCmd.'</td>
                        <td><strong>'.htmlspecialchars($row['title']).'</strong></td>
                        <td>'.$row['net_price'].' PLN</td>
                        <td>'.($row['vat_tax']*100).'%</td>
                        <td>'.$row['stock_quantity'].' szt.</td>
                        <td>'.$statusBadge.'</td>
                        <td>
                            <a class="btn btn-edit" href="?idp=-9&action=edit&id='.$row['id'].'">Edytuj</a>
                            <a class="btn btn-delete" href="?idp=-9&action=delete&delete_id='.$row['id'].'" onclick="return confirm(\'Usunąć produkt?\')">Usuń</a>
                        </td>
                      </tr>';
            }
        } else {
            echo '<tr><td colspan="8" style="text-align:center;">Brak produktów w sklepie.</td></tr>';
        }
        
        echo '</tbody></table>';

        // Formularz dodawania (na dole tablicy)
        echo '<div id="addProductForm" class="admin-container centered" style="margin-top:40px; border-top: 4px solid #3498db;">
                <h3 class="admin-header">Dodaj Produkt</h3>
                <form method="post" action="?idp=-9&action=add">
                    
                    <div class="admin-form-group">
                        <label>Tytuł produktu:</label>
                        <input type="text" name="title" required>
                    </div>
                    
                    <div class="admin-form-group">
                        <label>Opis:</label>
                        <textarea name="description" rows="3"></textarea>
                    </div>
                    
                    <div style="display:flex; gap:15px;">
                        <div class="admin-form-group" style="flex:1;">
                            <label>Cena Netto (PLN):</label>
                            <input type="number" step="0.01" name="net_price" required>
                        </div>
                        <div class="admin-form-group" style="flex:1;">
                            <label>VAT (np. 0.23):</label>
                            <input type="number" step="0.01" value="0.23" name="vat_tax">
                        </div>
                    </div>
                    
                    <div style="display:flex; gap:15px;">
                         <div class="admin-form-group" style="flex:1;">
                            <label>Ilość (sztuk):</label>
                            <input type="number" name="stock_quantity" value="0">
                        </div>
                        <div class="admin-form-group" style="flex:1;">
                            <label>Status (1=Dostępny):</label>
                            <input type="number" name="availability_status" value="1">
                        </div>
                    </div>

                    <div class="admin-form-group">
                        <label>Data wygaśnięcia (opcjonalnie):</label>
                        <input type="datetime-local" name="expires_at">
                    </div>

                    <div class="admin-form-group">
                        <label>Gabaryt (wymiary):</label>
                        <input type="text" name="dimensions" placeholder="np. 20x30x10 cm">
                    </div>
                    
                    <div class="admin-form-group">
                        <label>Link do zdjęcia (URL):</label>
                        <input type="text" name="image_url" placeholder="http://...">
                    </div>

                    <div class="admin-form-group">
                        <label>Kategoria (ID):</label>
                        <input type="number" name="category_id" placeholder="ID z działu Kategorii">
                    </div>

                    <div class="admin-actions">
                         <input type="submit" class="btn btn-primary" value="Dodaj Produkt">
                    </div>

                </form>
              </div>';

        echo '</div>'; // End container
    }

    /**
     * Dodawanie nowego produktu do bazy
     */
    function DodajProdukt() {
        global $conn;
        
        $title = htmlspecialchars($_POST['title']);
        $desc = $_POST['description'];
        $net_price = floatval($_POST['net_price']);
        $vat = floatval($_POST['vat_tax']);
        $stock = intval($_POST['stock_quantity']);
        $status = intval($_POST['availability_status']);
        $dims = htmlspecialchars($_POST['dimensions']);
        $img = htmlspecialchars($_POST['image_url']);
        $cat_id = intval($_POST['category_id']);
        
        $expires = !empty($_POST['expires_at']) ? $_POST['expires_at'] : NULL;

        $stmt = $conn->prepare("INSERT INTO products (title, description, net_price, vat_tax, stock_quantity, availability_status, dimensions, image_url, category_id, expires_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssddiissss", $title, $desc, $net_price, $vat, $stock, $status, $dims, $img, $cat_id, $expires);
        
        if ($stmt->execute()) {
             // Przekierowanie po sukcesie
             header("Location: ?idp=-9");
             exit;
        } else {
             echo '<div class="login-message msg-error">Błąd dodawania: '.$stmt->error.'</div>';
        }
    }

    /**
     * Usuwanie produktu
     */
    function UsunProdukt($id) {
        global $conn;
        $id = intval($id);
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
             header("Location: ?idp=-9");
             exit;
        }
    }

    /**
     * Formularz edycji produktu
     */
    function FormularzEdycji($id) {
        global $conn;
        $id = intval($id);
        
        $result = $conn->query("SELECT * FROM products WHERE id = $id LIMIT 1");
        if ($result && $row = $result->fetch_assoc()) {
            
            echo '<div class="admin-container centered">';
            echo '<div class="admin-nav"><div class="admin-nav-links"><a href="?idp=-9">Wróć do listy produktów</a></div></div>';
            
            echo '<h3 class="admin-header">Edytuj Produkt #'.$id.'</h3>';
            echo '<form method="post" action="?idp=-9&action=edit&id='.$id.'">
                    
                     <div class="admin-form-group">
                        <label>Tytuł:</label>
                        <input type="text" name="title" value="'.htmlspecialchars($row['title']).'" required>
                    </div>
                    
                    <div class="admin-form-group">
                        <label>Opis:</label>
                        <textarea name="description" rows="5">'.htmlspecialchars($row['description']).'</textarea>
                    </div>

                     <div style="display:flex; gap:15px;">
                        <div class="admin-form-group" style="flex:1;">
                            <label>Cena Netto:</label>
                            <input type="number" step="0.01" name="net_price" value="'.$row['net_price'].'" required>
                        </div>
                        <div class="admin-form-group" style="flex:1;">
                            <label>VAT:</label>
                            <input type="number" step="0.01" name="vat_tax" value="'.$row['vat_tax'].'">
                        </div>
                    </div>

                    <div style="display:flex; gap:15px;">
                         <div class="admin-form-group" style="flex:1;">
                            <label>Magazyn (szt):</label>
                            <input type="number" name="stock_quantity" value="'.$row['stock_quantity'].'">
                        </div>
                        <div class="admin-form-group" style="flex:1;">
                            <label>Status (1=OK, 0=Stop):</label>
                            <input type="number" name="availability_status" value="'.$row['availability_status'].'">
                        </div>
                    </div>

                    <div class="admin-form-group">
                        <label>Data wygaśnięcia (YYYY-MM-DD HH:MM):</label>
                        <input type="datetime-local" name="expires_at" value="'.$row['expires_at'].'">
                    </div>
                    
                    <div class="admin-form-group">
                        <label>Gabaryt:</label>
                        <input type="text" name="dimensions" value="'.htmlspecialchars($row['dimensions']).'">
                    </div>

                    <div class="admin-form-group">
                        <label>Zdjęcie URL:</label>
                        <input type="text" name="image_url" value="'.htmlspecialchars($row['image_url']).'">
                    </div>

                    <div class="admin-actions">
                         <a href="?idp=-9" class="btn btn-delete">Anuluj</a>
                         <input type="submit" class="btn btn-success" value="Zapisz zmiany">
                    </div>
                  </form>';
            echo '</div>';
        }
    }

    /**
     * Zapisuje zmiany w edytowanym produkcie
     */
    function ZapiszEdycje($id) {
        global $conn;
        $id = intval($id);
        
        $title = htmlspecialchars($_POST['title']);
        $desc = $_POST['description'];
        $net_price = floatval($_POST['net_price']);
        $vat = floatval($_POST['vat_tax']);
        $stock = intval($_POST['stock_quantity']);
        $status = intval($_POST['availability_status']);
        $dims = htmlspecialchars($_POST['dimensions']);
        $img = htmlspecialchars($_POST['image_url']);
        
        $expires = !empty($_POST['expires_at']) ? $_POST['expires_at'] : NULL;

        $stmt = $conn->prepare("UPDATE products SET title=?, description=?, net_price=?, vat_tax=?, stock_quantity=?, availability_status=?, dimensions=?, image_url=?, expires_at=? WHERE id=?");
        $stmt->bind_param("ssddiisssi", $title, $desc, $net_price, $vat, $stock, $status, $dims, $img, $expires, $id);
        
        if ($stmt->execute()) {
             header("Location: ?idp=-9");
             exit;
        } else {
             echo '<div class="login-message msg-error">Błąd aktualizacji: '.$stmt->error.'</div>';
        }
    }
}
?>

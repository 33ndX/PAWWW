<?php
/**
 * ============================================================================
 * KLASA KOSZYKA ZAKUPOWEGO - cart.php
 * ============================================================================
 * 
 * Opis:    Klasa obsługująca koszyk zakupowy oparty o $_SESSION.
 *          Umożliwia dodawanie, usuwanie, edycję ilości produktów
 *          oraz obliczanie wartości brutto (netto + VAT).
 * Wersja:  v1.8
 * Data:    2026
 * 
 * ============================================================================
 */

class Cart {
    
    /**
     * Inicjalizacja koszyka w sesji (jeśli nie istnieje)
     */
    function __construct() {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }
    
    /**
     * Dodaje produkt do koszyka
     * @param int $productId - ID produktu z bazy danych
     * @param int $quantity - ilość sztuk do dodania
     */
    function addToCart($productId, $quantity = 1) {
        global $conn;
        
        $productId = intval($productId);
        $quantity = intval($quantity);
        
        if ($quantity <= 0) {
            return false;
        }
        
        // Sprawdź czy produkt już jest w koszyku
        foreach ($_SESSION['cart'] as $index => $item) {
            if ($item['product_id'] == $productId) {
                // Zwiększ ilość
                $_SESSION['cart'][$index]['quantity'] += $quantity;
                return true;
            }
        }
        
        // Pobierz dane produktu z bazy
        $stmt = $conn->prepare("SELECT id, title, net_price, vat_tax, image_url, stock_quantity FROM products WHERE id = ? AND availability_status = 1");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $row = $result->fetch_assoc()) {
            // Sprawdź dostępność w magazynie
            if ($row['stock_quantity'] < $quantity) {
                return false; // Brak wystarczającej ilości
            }
            
            // Dodaj nowy produkt do koszyka
            $_SESSION['cart'][] = [
                'product_id' => $row['id'],
                'title' => $row['title'],
                'net_price' => floatval($row['net_price']),
                'vat_tax' => floatval($row['vat_tax']),
                'quantity' => $quantity,
                'image_url' => $row['image_url']
            ];
            return true;
        }
        
        return false;
    }
    
    /**
     * Usuwa produkt z koszyka
     * TIP 3: Używamy unset() do usunięcia elementu
     * TIP 5: Reindeksujemy tablicę po usunięciu
     * 
     * @param int $index - indeks produktu w tablicy koszyka
     */
    function removeFromCart($index) {
        $index = intval($index);
        
        if (isset($_SESSION['cart'][$index])) {
            unset($_SESSION['cart'][$index]);
            // Reindeksacja tablicy - TIP 5
            $_SESSION['cart'] = array_values($_SESSION['cart']);
            return true;
        }
        
        return false;
    }
    
    /**
     * Aktualizuje ilość produktu w koszyku
     * 
     * @param int $index - indeks produktu w tablicy koszyka
     * @param int $quantity - nowa ilość
     */
    function updateQuantity($index, $quantity) {
        $index = intval($index);
        $quantity = intval($quantity);
        
        if (isset($_SESSION['cart'][$index])) {
            if ($quantity <= 0) {
                // Jeśli ilość 0 lub mniej - usuń produkt
                return $this->removeFromCart($index);
            }
            
            $_SESSION['cart'][$index]['quantity'] = $quantity;
            return true;
        }
        
        return false;
    }
    
    /**
     * Oblicza cenę brutto (netto + VAT)
     * 
     * @param float $netPrice - cena netto
     * @param float $vatTax - stawka VAT (np. 0.23 = 23%)
     * @return float - cena brutto
     */
    function getGrossPrice($netPrice, $vatTax) {
        return $netPrice * (1 + $vatTax);
    }
    
    /**
     * Oblicza całkowitą wartość koszyka (brutto)
     * 
     * @return float - suma wartości wszystkich produktów
     */
    function getTotalValue() {
        $total = 0;
        
        foreach ($_SESSION['cart'] as $item) {
            $grossPrice = $this->getGrossPrice($item['net_price'], $item['vat_tax']);
            $total += $grossPrice * $item['quantity'];
        }
        
        return $total;
    }
    
    /**
     * Zwraca liczbę produktów w koszyku
     * 
     * @return int - liczba różnych produktów
     */
    function getCartCount() {
        return count($_SESSION['cart']);
    }
    
    /**
     * Zwraca sumę wszystkich sztuk w koszyku
     * 
     * @return int - łączna liczba sztuk
     */
    function getTotalItems() {
        $total = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['quantity'];
        }
        return $total;
    }
    
    /**
     * Czyści cały koszyk
     */
    function clearCart() {
        $_SESSION['cart'] = [];
    }
    
    /**
     * Wyświetla zawartość koszyka
     */
    function showCart() {
        $html = '<div class="cart-container">';
        $html .= '<h1>Koszyk zakupowy</h1>';
        
        if (empty($_SESSION['cart'])) {
            $html .= '<div class="cart-empty">
                        <p>Twój koszyk jest pusty.</p>
                        <a href="?idp=-11" class="btn btn-primary">Przejdź do sklepu</a>
                      </div>';
        } else {
            $html .= '<table class="cart-table">
                        <thead>
                            <tr>
                                <th>Zdjęcie</th>
                                <th>Produkt</th>
                                <th>Cena netto</th>
                                <th>VAT</th>
                                <th>Cena brutto</th>
                                <th>Ilość</th>
                                <th>Wartość</th>
                                <th>Akcje</th>
                            </tr>
                        </thead>
                        <tbody>';
            
            foreach ($_SESSION['cart'] as $index => $item) {
                $grossPrice = $this->getGrossPrice($item['net_price'], $item['vat_tax']);
                $itemTotal = $grossPrice * $item['quantity'];
                $vatPercent = $item['vat_tax'] * 100;
                
                $imgHtml = $item['image_url'] 
                    ? '<img src="'.htmlspecialchars($item['image_url']).'" alt="'.htmlspecialchars($item['title']).'" class="cart-item-img">'
                    : '<span class="no-image">Brak zdjęcia</span>';
                
                $html .= '<tr>
                            <td>'.$imgHtml.'</td>
                            <td><strong>'.htmlspecialchars($item['title']).'</strong></td>
                            <td>'.number_format($item['net_price'], 2, ',', ' ').' PLN</td>
                            <td>'.$vatPercent.'%</td>
                            <td><strong>'.number_format($grossPrice, 2, ',', ' ').' PLN</strong></td>
                            <td>
                                <form method="post" action="?idp=-10&action=update" class="quantity-form">
                                    <input type="hidden" name="index" value="'.$index.'">
                                    <input type="number" name="quantity" value="'.$item['quantity'].'" min="0" class="quantity-input">
                                    <button type="submit" class="btn btn-small btn-primary">Aktualizuj</button>
                                </form>
                            </td>
                            <td><strong>'.number_format($itemTotal, 2, ',', ' ').' PLN</strong></td>
                            <td>
                                <a href="?idp=-10&action=remove&index='.$index.'" class="btn btn-small btn-delete" onclick="return confirm(\'Usunąć produkt z koszyka?\')">Usuń</a>
                            </td>
                          </tr>';
            }
            
            $html .= '</tbody></table>';
            
            // Podsumowanie
            $totalValue = $this->getTotalValue();
            $totalItems = $this->getTotalItems();
            
            $html .= '<div class="cart-summary">
                        <div class="cart-summary-row">
                            <span>Liczba produktów:</span>
                            <span><strong>'.$totalItems.' szt.</strong></span>
                        </div>
                        <div class="cart-summary-row cart-total">
                            <span>SUMA (brutto):</span>
                            <span><strong>'.number_format($totalValue, 2, ',', ' ').' PLN</strong></span>
                        </div>
                      </div>';
            
            $html .= '<div class="cart-actions">
                        <a href="?idp=-11" class="btn btn-primary">Kontynuuj zakupy</a>
                        <a href="?idp=-10&action=clear" class="btn btn-delete" onclick="return confirm(\'Wyczyścić cały koszyk?\')">Wyczyść koszyk</a>
                      </div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Wyświetla listę produktów do zakupu (widok sklepu)
     */
    function showShop() {
        global $conn;
        
        $html = '<div class="shop-container">';
        $html .= '<h1>Sklep</h1>';
        $html .= '<p class="shop-info">Przeglądaj nasze produkty i dodawaj je do koszyka.</p>';
        
        // Pobierz dostępne produkty
        $query = "SELECT * FROM products WHERE availability_status = 1 AND stock_quantity > 0 ORDER BY id DESC";
        $result = $conn->query($query);
        
        if ($result && $result->num_rows > 0) {
            $html .= '<div class="product-grid">';
            
            while ($row = $result->fetch_assoc()) {
                $grossPrice = $this->getGrossPrice($row['net_price'], $row['vat_tax']);
                $vatPercent = $row['vat_tax'] * 100;
                
                $imgHtml = $row['image_url'] 
                    ? '<img src="'.htmlspecialchars($row['image_url']).'" alt="'.htmlspecialchars($row['title']).'" class="product-img">'
                    : '<div class="no-image-placeholder">Brak zdjęcia</div>';
                
                $html .= '<div class="product-card">
                            <div class="product-image">'.$imgHtml.'</div>
                            <div class="product-info">
                                <h3>'.htmlspecialchars($row['title']).'</h3>
                                <p class="product-description">'.htmlspecialchars(substr($row['description'], 0, 100)).'...</p>
                                <div class="product-prices">
                                    <span class="net-price">Netto: '.number_format($row['net_price'], 2, ',', ' ').' PLN</span>
                                    <span class="vat-info">(+'.$vatPercent.'% VAT)</span>
                                    <span class="gross-price">Brutto: <strong>'.number_format($grossPrice, 2, ',', ' ').' PLN</strong></span>
                                </div>
                                <div class="product-stock">Dostępność: '.$row['stock_quantity'].' szt.</div>
                                <form method="post" action="?idp=-11&action=add" class="add-to-cart-form">
                                    <input type="hidden" name="product_id" value="'.$row['id'].'">
                                    <div class="quantity-row">
                                        <label>Ilość:</label>
                                        <input type="number" name="quantity" value="1" min="1" max="'.$row['stock_quantity'].'" class="quantity-input">
                                    </div>
                                    <button type="submit" class="btn btn-cart">Dodaj do koszyka</button>
                                </form>
                            </div>
                          </div>';
            }
            
            $html .= '</div>';
        } else {
            $html .= '<p class="no-products">Brak dostępnych produktów w sklepie.</p>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Obsługuje akcje koszyka (POST/GET)
     */
    function handleAction() {
        if (!isset($_GET['action'])) {
            return null;
        }
        
        $action = $_GET['action'];
        $message = null;
        
        switch ($action) {
            case 'add':
                if (isset($_POST['product_id'])) {
                    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
                    if ($this->addToCart($_POST['product_id'], $quantity)) {
                        $message = ['type' => 'success', 'text' => 'Produkt został dodany do koszyka!'];
                    } else {
                        $message = ['type' => 'error', 'text' => 'Nie udało się dodać produktu do koszyka.'];
                    }
                }
                break;
                
            case 'remove':
                if (isset($_GET['index'])) {
                    if ($this->removeFromCart($_GET['index'])) {
                        $message = ['type' => 'success', 'text' => 'Produkt został usunięty z koszyka.'];
                    }
                }
                break;
                
            case 'update':
                if (isset($_POST['index']) && isset($_POST['quantity'])) {
                    if ($this->updateQuantity($_POST['index'], $_POST['quantity'])) {
                        $message = ['type' => 'success', 'text' => 'Ilość została zaktualizowana.'];
                    }
                }
                break;
                
            case 'clear':
                $this->clearCart();
                $message = ['type' => 'success', 'text' => 'Koszyk został wyczyszczony.'];
                break;
        }
        
        return $message;
    }
    
    /**
     * Formatuje komunikat
     */
    function formatMessage($message) {
        if (!$message) return '';
        
        $class = $message['type'] == 'success' ? 'msg-success' : 'msg-error';
        return '<div class="cart-message '.$class.'">'.$message['text'].'</div>';
    }
}
?>

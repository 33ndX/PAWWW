<?php
/**
 * ============================================================================
 * FORMULARZ KONTAKTOWY - contact.php
 * ============================================================================
 * 
 * Opis:    Klasa Contact obsługuje formularz kontaktowy oraz funkcję
 *          odzyskiwania hasła przez email.
 * Autor:   Paweł Milanowski
 * Data:    2025
 * 
 * Funkcjonalności:
 * - Wyświetlanie formularza kontaktowego
 * - Wysyłanie wiadomości email
 * - Przypomnienie hasła przez email
 * 
 * ============================================================================
 */


/**
 * ============================================================================
 * KLASA: Contact
 * ============================================================================
 * 
 * Obsługuje formularz kontaktowy i funkcje związane z wysyłaniem emaili.
 * 
 * ============================================================================
 */
class Contact {


    /**
     * ========================================================================
     * METODA: PokazKontakt
     * ========================================================================
     * 
     * Generuje formularz HTML do wysyłania wiadomości kontaktowych.
     * Formularz zawiera pola: email, tytuł, zawartość.
     * 
     * @return  string  Kod HTML formularza kontaktowego
     * 
     * ========================================================================
     */
    function PokazKontakt() {
        
        $wynik = '
            <div class="contact">
                <form method="post" name="ContactForm" enctype="multipart/form-data" action="' . $_SERVER['REQUEST_URI'] . '">
                    <table class="contact">
                        <tr>
                            <td class="zaw">Email: </td>
                            <td><input type="email" name="email" class="contact" required /></td>
                        </tr>
                        <tr>
                            <td class="zaw">Tytuł: </td>
                            <td><input type="text" name="temat" class="contact" required /></td>
                        </tr>
                        <tr>
                            <td>Zawartość:</td>
                            <td class="zaw"><textarea name="tresc" required></textarea></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td><input type="submit" class="send-button" value="wyślij" /></td>
                        </tr>
                    </table>
                </form>
            </div>
        ';
        
        return $wynik;
    }


    /**
     * ========================================================================
     * METODA: PokazHaslo
     * ========================================================================
     * 
     * Generuje formularz HTML do odzyskiwania hasła.
     * Formularz zawiera pole: email.
     * 
     * @return  string  Kod HTML formularza odzyskiwania hasła
     * 
     * ========================================================================
     */
    function PokazHaslo() {
        
        $wynik = '
            <div class="passrecov">
                <div class="passrecov">
                    <form method="post" name="RecoveryForm" enctype="multipart/form-data" action="' . $_SERVER['REQUEST_URI'] . '">
                        <table class="passrecov">
                            <tr>
                                <td class="log4_t">Email: </td>
                                <td><input type="email" name="email_recovery" class="passrecov" required /></td>
                            </tr>
                            <tr>
                                <td></td>
                                <td><input type="submit" name="x1_submit" class="passrecov" value="wyślij" /></td>
                            </tr>
                        </table>
                    </form>
                </div>
            </div>
        ';
        
        return $wynik;
    }


    /**
     * ========================================================================
     * METODA: WyslijMailKontakt
     * ========================================================================
     * 
     * Obsługuje wysyłanie wiadomości z formularza kontaktowego.
     * Sprawdza czy formularz został wypełniony i wysyła email.
     * 
     * BEZPIECZEŃSTWO:
     * - Walidacja email przez filter_var() z FILTER_VALIDATE_EMAIL
     * - Sanityzacja danych przez htmlspecialchars()
     * - Zabezpieczenie nagłówków przed Email Injection
     * 
     * @param   string  $odbiorca   Adres email odbiorcy wiadomości
     * @return  void                Wyświetla formularz lub komunikat przez echo
     * 
     * ========================================================================
     */
    function WyslijMailKontakt($odbiorca) {
        
        // Sprawdzenie czy formularz został wypełniony
        if (empty($_POST['email']) || empty($_POST['temat']) || empty($_POST['tresc'])) {
            
            // Wyświetlenie formularza kontaktowego
            echo $this->PokazKontakt();
            
        } else {
            
            // -----------------------------------------------------------------
            // WALIDACJA I SANITYZACJA DANYCH Z FORMULARZA
            // -----------------------------------------------------------------
            
            // Walidacja adresu email - ochrona przed Email Injection
            $senderEmail = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
            
            if (!$senderEmail) {
                echo "Błąd: Nieprawidłowy adres email.";
                return;
            }
            
            // Sanityzacja danych - ochrona przed XSS i Injection
            $mail['subject']   = htmlspecialchars($_POST['temat']);
            $mail['body']      = htmlspecialchars($_POST['tresc']);
            $mail['sender']    = $senderEmail;
            $mail['recipient'] = $odbiorca;
            
            // -----------------------------------------------------------------
            // BUDOWANIE NAGŁÓWKÓW EMAIL
            // -----------------------------------------------------------------
            
            $header  = "From: Formularz kontaktowy <" . $mail['sender'] . ">\n";
            $header .= "MIME-Version: 1.0\n";
            $header .= "Content-Type: text/plain; charset=utf-8\n";
            $header .= "Content-Transfer-Encoding: 8bit\n";
            $header .= "X-Sender: <" . $mail['sender'] . ">\n";
            $header .= "X-Mailer: prapwww mail 1.2\n";
            $header .= "X-Priority: 3\n";
            $header .= "Return-Path: <" . $mail['sender'] . ">\n";

            // -----------------------------------------------------------------
            // WYSŁANIE WIADOMOŚCI EMAIL
            // -----------------------------------------------------------------
            // Funkcja mail() wysyła email przez serwer SMTP
            // -----------------------------------------------------------------
            
            mail($mail['recipient'], $mail['subject'], $mail['body'], $header);

            echo 'Wiadomość została wysłana pomyślnie.';
        }
    }
    

    /**
     * ========================================================================
     * METODA: PrzypomnijHaslo
     * ========================================================================
     * 
     * Obsługuje funkcję odzyskiwania hasła przez email.
     * Wysyła email z hasłem na podany adres.
     * 
     * BEZPIECZEŃSTWO:
     * - Walidacja email przez filter_var() z FILTER_VALIDATE_EMAIL
     * - Zabezpieczenie nagłówków przed Email Injection
     * 
     * UWAGA: W produkcji należy użyć tokenów resetowania hasła zamiast
     *        wysyłania hasła w plaintext!
     * 
     * @param   string  $odbiorca   Adres email odbiorcy wiadomości
     * @return  void                Wyświetla formularz lub komunikat przez echo
     * 
     * ========================================================================
     */
    function PrzypomnijHaslo($odbiorca) {
        
        // Sprawdzenie czy email został podany
        if (empty($_POST['email_recovery'])) {
            
            // Wyświetlenie formularza odzyskiwania hasła
            echo $this->PokazHaslo();
            
        } else {
            
            // -----------------------------------------------------------------
            // WALIDACJA ADRESU EMAIL
            // -----------------------------------------------------------------
            
            $senderEmail = filter_var($_POST['email_recovery'], FILTER_VALIDATE_EMAIL);
            
            if (!$senderEmail) {
                echo "Błąd: Nieprawidłowy adres email.";
                return;
            }
            
            // -----------------------------------------------------------------
            // PRZYGOTOWANIE DANYCH EMAIL
            // -----------------------------------------------------------------
            
            $mail['sender']    = $senderEmail;
            $mail['subject']   = "Password Recovery";
            $mail['body']      = "Password = haslo";  // W produkcji: token resetowania!
            $mail['recipient'] = $odbiorca;
            
            // -----------------------------------------------------------------
            // BUDOWANIE NAGŁÓWKÓW EMAIL
            // -----------------------------------------------------------------
            
            $header  = "From: Formularz kontaktowy <" . $mail['sender'] . ">\n";
            $header .= "MIME-Version: 1.0\n";
            $header .= "Content-Type: text/plain; charset=utf-8\n";
            $header .= "Content-Transfer-Encoding: 8bit\n";
            $header .= "X-Sender: <" . $mail['sender'] . ">\n";
            $header .= "X-Mailer: prapwww mail 1.2\n";
            $header .= "X-Priority: 3\n";
            $header .= "Return-Path: <" . $mail['sender'] . ">\n";
            
            // -----------------------------------------------------------------
            // WYSŁANIE WIADOMOŚCI EMAIL Z HASŁEM
            // -----------------------------------------------------------------
            
            mail($mail['recipient'], $mail['subject'], $mail['body'], $header);
            
            echo 'Hasło zostało wysłane na podany adres email.';
        }
    }
}

?>
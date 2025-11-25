<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Projekt 1">
    <meta name="keywords" content="HTML5, CSS3, JavaScript">
    <meta name="author" content="Paweł Milanowski">
    <title>Największe budynki świata</title>
    
    <link rel="stylesheet" href="./css/style.css">
    <script src="scripts/kolorujtlo.js"></script>
    <script src="scripts/timedate.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

</head >
<body onload='startClock()'>
    <?php
    error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
    if ($_GET['idp'] == '') {
        $strona = 'html/main.html';
    } elseif ($_GET['idp'] == 'main') {
        $strona = 'html/main.html';
    } elseif ($_GET['idp'] == 'budynki') {
        $strona = 'html/budynki.html';
    } elseif ($_GET['idp'] == 'ranking') {
        $strona = 'html/ranking.html';
    } elseif ($_GET['idp'] == 'architektura') {
        $strona = 'html/architektura.html';
    } elseif ($_GET['idp'] == 'kontakt') {
        $strona = 'html/kontakt.html';
    } elseif ($_GET['idp'] == 'skryptyJS') {
        $strona = 'html/SkryptyJS.html';
    } elseif ($_GET['idp'] == 'jquary') {
        $strona = 'html/JQuary.html';
    } elseif ($_GET['idp'] == 'filmy') {
        $strona = 'html/filmy.html';
    } elseif ($_GET['idp'] == 'lab4') {
        $strona = 'html/labor_175281_ISI2.php';    
    } else {
        $strona = 'html/404.html';  // Plik z informacją o błędzie
    }
    if (!file_exists($strona)) {
    $page = './html/404.html';
    }
    ?>
    <nav>
        <div>
            <a>
                <img src="./pics/logo.png" alt="logo" class="logo">        
            </a>
        </div>
        <ul>
            <li><a href="index.php?idp=main" >Strona główna</a></li>
            <li><a href="index.php?idp=budynki">Budynki</a></li>
            <li><a href="index.php?idp=ranking">Ranking</a></li>
            <li><a href="index.php?idp=architektura">Architektura</a></li>
            <li><a href="index.php?idp=kontakt">Kontakt</a></li>
            <li><a href="index.php?idp=filmy">Filmy</a></li>
            <li><a href="index.php?idp=jquary">JQuary</a></li>
            <li><a href="index.php?idp=lab4">Lab4</a></li>
            <li><a href="index.php?idp=skryptyJS">SkryptyJS</a></li>
        </ul>
        <div class="nav-clock">
            <div id='zegarek'></div>
            <div id='data'></div>
        </div>
    </nav>

    <main>
        <?php
        if(file_exists($strona)){
            include($strona);
        } else {
            echo "<p>Podstrona jest pusta.</p>";
        }
        ?>    
    </main>
    <?php
    $nr_indeksu = '175281';
    $nrGrupy = 'ISI2';
    ?>


    <footer>
        <p>&copy; 2025 Największe budynki na świecie - <?php echo "Paweł Milanowski $nr_indeksu, $nrGrupy" ?></p>             
    </footer>
</body>
</html>
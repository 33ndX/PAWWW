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
    <?php
    session_start();
    if (!isset($_GET['idp'])) {
        $_GET['idp'] = '1';
    }
    ?>

</head>
<body onload='startClock()'>
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
            <li><a href="index.php?idp=4">Kontakt</a></li>
            <li><a href="index.php?idp=6">Filmy</a></li>
            <li><a href="index.php?idp=8">JQuary</a></li>
            <li><a href="index.php?idp=10">Lab4</a></li>
            <li><a href="index.php?idp=7">SkryptyJS</a></li>
            <li><a href="index.php?idp=-1">Panel Admina</a></li>
        </ul>
        <div class="nav-clock">
            <div id='zegarek'></div>
            <div id='data'></div>
        </div>
    </nav>

    <main>
    <?php
    include('cfg.php');
    include('showpage.php');
    include('admin/admin.php');
    
    $id = htmlspecialchars($_GET['idp']);
  
    static $Admin = null;

    switch($id) {
        case -1:
            if($Admin === null) {
                $Admin = new Admin();
            }
            echo $Admin->LoginAdmin();
            break;
        
        case -2:
            if($Admin === null) {
                $Admin = new Admin();
            }
            echo $Admin->EditPage();
            break;
        
        case -3:
            if($Admin === null) {
                $Admin = new Admin();
            }
            echo $Admin->DeletePage();
            break;
        case -4:
            if($Admin === null) {
                $Admin = new Admin();
            }
            echo $Admin->CreatePage();
            break;

        default:
            echo PokazStrone($id);
            break;
        
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
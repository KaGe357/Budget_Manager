<?php
session_start();

if (session_status() === PHP_SESSION_NONE) {
    echo "Sesja nie została poprawnie zainicjowana.";
    exit();
}



ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_SESSION['zalogowany']) && $_SESSION['zalogowany'] === true) {
    header('Location: home.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['login']) || !isset($_POST['haslo'])) {
        $_SESSION['blad'] = 'Wypełnij wszystkie pola!';
        header('Location: log-in.php');
        exit();
    }

    require_once "connect.php";
    $polaczenie = new mysqli($host, $db_user, $db_password, $db_name);

    if ($polaczenie->connect_errno != 0) {
        echo "Błąd połączenia: " . $polaczenie->connect_errno;
        exit();
    } else {
        $login = htmlentities($_POST['login'], ENT_QUOTES, "UTF-8");
        $haslo = $_POST['haslo'];

        $sql = sprintf("SELECT * FROM users WHERE email='%s'", 
            mysqli_real_escape_string($polaczenie, $login)
        );

        if ($result = $polaczenie->query($sql)) {
            $ilu_userow = $result->num_rows;
            if ($ilu_userow > 0) {
                $wiersz = $result->fetch_assoc();


                if (password_verify($haslo, $wiersz['pass'])) {
                    echo "Hasło poprawne!<br>";

                    $_SESSION['zalogowany'] = true;
                    $_SESSION['id'] = $wiersz['id'];
                    $_SESSION['user'] = $wiersz['user'];
                    unset($_SESSION['blad']);
                    $result->close();
                    $polaczenie->close();

                    echo "Przekierowanie do home.php";
                    header('Location: home.php');
                    exit();
                } else {
                    $_SESSION['blad'] = 'Nieprawidłowy login lub hasło!';
                    header('Location: log-in.php');
                    exit();
                }
            } else {
                $_SESSION['blad'] = 'Nieprawidłowy login lub hasło!';
                header('Location: log-in.php');
                exit();
            }
        } else {
            echo "Błąd zapytania SQL.";
            exit();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Budget Manager - Logowanie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="./img/favicon.svg" type="image/png">
</head>
<body>

<header>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto justify-content-center">
                    <li class="nav-item">
                        <a href="./index.html" class="nav-link active px-1" aria-current="page">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-house mx-2" viewBox="0 0 16 16">
                                <path d="M8.707 1.5a1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.708L2 8.207V13.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V8.207l.646.647a.5.5 0 0 0 .708-.708L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293zM13 7.207V13.5a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5V7.207l5-5z"/>
                            </svg>Strona główna
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="./log-in.php" class="nav-link">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-door-open mx-2" viewBox="0 0 16 16">
                                <path d="M8.5 10c-.276 0-.5-.448-.5-1s.224-1 .5-1 .5.448.5 1-.224 1-.5 1"/>
                                <path d="M10.828.122A.5.5 0 0 1 11 .5V1h.5A1.5 1.5 0 0 1 13 2.5V15h1.5a.5.5 0 0 1 0 1h-13a.5.5 0 0 1 0-1H3V1.5a.5.5 0 0 1 .43-.495l7-1a.5.5 0 0 1 .398.117M11.5 2H11v13h1V2.5a.5.5 0 0 0-.5-.5M4 1.934V15h6V1.077z"/>
                            </svg>Zaloguj
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="./register.php" class="nav-link">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-pencil-square mx-2" viewBox="0 0 16 16">
                                <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
                                <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/>
                            </svg>Rejestracja
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>

<section>
    <div class="container center fs-4">
        <main class="form-signin w-50 m-auto bordered">
            <form action="log-in.php" method="POST">
                <h1 class="h3 mb-3 fw-normal">Zaloguj się</h1>
                <?php
                if (isset($_SESSION['blad'])) {
                    echo '<div class="alert alert-danger">' . $_SESSION['blad'] . '</div>';
                    unset($_SESSION['blad']);
                }
                ?>
                <div class="form">
                    <input type="email" name="login" class="form-control my-2 p-1" placeholder="Email" required>
                </div>
                <div class="form">
                    <input type="password" name="haslo" class="form-control  my-2 p-1" placeholder="Hasło" required>
                </div>
                <button class="btn btn-primary w-100 py-2" type="submit">Sign in</button>
            </form>
        </main>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="./script.js"></script>
</body>
</html>
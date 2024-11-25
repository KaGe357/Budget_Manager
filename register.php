<?php
session_start();

if (isset($_POST['email'])) {
    $wszystko_OK = true;

    // Sprawdzanie poprawności imienia
    $name = $_POST['name'];
    if (strlen($name) < 3 || strlen($name) > 20) {
        $wszystko_OK = false;
        $_SESSION['e_name'] = 'Imie musi posiadać od 3 do 20 znaków!';
    }
    if (!ctype_alnum($name)) {
        $wszystko_OK = false;
        $_SESSION['e_name'] = 'Imie może składać się tylko z liter i cyfr (bez polskich znaków)';
    }

    // Sprawdzanie poprawności e-maila
    $email = $_POST['email'];
    $emailB = filter_var($email, FILTER_SANITIZE_EMAIL);
    if (!filter_var($emailB, FILTER_VALIDATE_EMAIL) || $emailB != $email) {
        $wszystko_OK = false;
        $_SESSION['e_email'] = 'Podaj poprawny adres email!';
    }

    // Sprawdzanie poprawności hasła
    $haslo1 = $_POST['haslo1'];
    $haslo2 = $_POST['haslo2'];
    if (strlen($haslo1) < 8 || strlen($haslo1) > 20) {
        $wszystko_OK = false;
        $_SESSION['e_haslo'] = 'Hasło musi posiadać od 8 do 20 znaków';
    }
    if ($haslo1 !== $haslo2) {
        $wszystko_OK = false;
        $_SESSION['e_haslo'] = 'Podane hasła nie są identyczne';
    }
    $haslo_hash = password_hash($haslo1, PASSWORD_DEFAULT);

    // Czy zaakceptowano regulamin?
    if (!isset($_POST['regulamin'])) {
        $wszystko_OK = false;
        $_SESSION['e_regulamin'] = 'Potwierdź akceptację regulaminu';
    }

    // Walidacja reCAPTCHA
    $sekret = "6Lf1r3YqAAAAAAxXD66U_yDg2vGXQfXkY9lOZBbD";
    $sprawdz = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=" . $sekret . '&response=' . $_POST["g-recaptcha-response"]);
    $odpowiedz = json_decode($sprawdz);
    if (!$odpowiedz->success) {
        $wszystko_OK = false;
        $_SESSION['e_bot'] = 'Potwierdź, że nie jesteś botem!';
    }

    // Zapamiętaj wprowadzone dane
    $_SESSION['fr_name'] = $name;
    $_SESSION['fr_email'] = $email;
    $_SESSION['fr_haslo1'] = $haslo1;
    $_SESSION['fr_haslo2'] = $haslo2;
    if (isset($_POST['regulamin'])) {
        $_SESSION['fr_regulamin'] = true;
    }

    require_once "connect.php";
    mysqli_report(MYSQLI_REPORT_STRICT);

    try {
        $polaczenie = new mysqli($host, $db_user, $db_password, $db_name);
        if ($polaczenie->connect_errno != 0) {
            throw new Exception(mysqli_connect_errno());
        } else {
            // Sprawdzenie, czy e-mail już istnieje
            $rezultat = $polaczenie->query("SELECT id FROM users WHERE email='$email'");
            if (!$rezultat) {
                throw new Exception($polaczenie->error);
            }
            $ile_takich_maili = $rezultat->num_rows;
            if ($ile_takich_maili > 0) {
                $wszystko_OK = false;
                $_SESSION["e_email"] = "Istnieje już konto przypisane do tego adresu e-mail!";
            }




            
            if ($wszystko_OK) {
                // Wstawienie użytkownika do bazy
                if ($polaczenie->query("INSERT INTO users VALUES(NULL, '$name', '$haslo_hash', '$email')")) {
                    $userId = $polaczenie->insert_id;
                    if (!$userId) {
                        throw new Exception("Nie udało się uzyskać ID nowo dodanego użytkownika.");
                    }

                    // Kopiowanie z tabel domyślnych
                    $sqlIncomes = "INSERT INTO incomes_category_assigned_to_users (user_id, name) 
                                   SELECT '$userId', name FROM incomes_category_default";
                    $sqlExpenses = "INSERT INTO expenses_category_assigned_to_users (user_id, name) 
                                    SELECT '$userId', name FROM expenses_category_default";
                    $sqlPaymentMethods = "INSERT INTO payment_methods_assigned_to_users (user_id, name) 
                                          SELECT '$userId', name FROM payment_methods_default";

                    if (!$polaczenie->query($sqlIncomes)) {
                        throw new Exception("Błąd w zapytaniu incomes: " . $polaczenie->error);
                    }
                    if (!$polaczenie->query($sqlExpenses)) {
                        throw new Exception("Błąd w zapytaniu expenses: " . $polaczenie->error);
                    }
                    if (!$polaczenie->query($sqlPaymentMethods)) {
                        throw new Exception("Błąd w zapytaniu payment methods: " . $polaczenie->error);
                    }

                    $_SESSION['udanarejestracja'] = true;
                    header('Location: welcome.php');
                } else {
                    throw new Exception("Błąd podczas rejestracji użytkownika: " . $polaczenie->error);
                }
            }
            $polaczenie->close();
        }
    } catch (Exception $e) {
        echo '<span style="color:red">Błąd serwera! Przepraszamy za niedogodności i prosimy o rejestrację w innym terminie!</span>';
        echo '<br>Informacja developerska: ' . $e;
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BudgetManager - załóż darmowe konto!</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <link rel="stylesheet" href="style.css">
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
            <li class="nav-item"><a href="./index.html" class="nav-link active px-1" aria-current="page">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-house mx-2" viewBox="0 0 16 16">
                <path d="M8.707 1.5a1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.708L2 8.207V13.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V8.207l.646.647a.5.5 0 0 0 .708-.708L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293zM13 7.207V13.5a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5V7.207l5-5z"/>
              </svg>Strona główna</a></li>
            <li class="nav-item"><a href="./log-in.php" class="nav-link">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-door-open mx-2" viewBox="0 0 16 16">
                <path d="M8.5 10c-.276 0-.5-.448-.5-1s.224-1 .5-1 .5.448.5 1-.224 1-.5 1"/>
                <path d="M10.828.122A.5.5 0 0 1 11 .5V1h.5A1.5 1.5 0 0 1 13 2.5V15h1.5a.5.5 0 0 1 0 1h-13a.5.5 0 0 1 0-1H3V1.5a.5.5 0 0 1 .43-.495l7-1a.5.5 0 0 1 .398.117M11.5 2H11v13h1V2.5a.5.5 0 0 0-.5-.5M4 1.934V15h6V1.077z"/>
              </svg>Zaloguj</a></li>
            <li class="nav-item"><a href="./register.php" class="nav-link">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-pencil-square mx-2" viewBox="0 0 16 16">
                <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
                <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/>
              </svg>Rejestracja</a></li>
          </ul>
        </div>
      </div>
    </nav>
  </header>
<main class="form-signin w-100 m-auto center">
    <div class="container bordered p-5">
        <h1 class="fs-4">Zarejestruj się</h1>
        <form method="post" class="">
            Imię: <br> <input type="text" class="form-control" id="name" value="<?php
            if (isset($_SESSION['fr_name'])) {
                echo $_SESSION['fr_name'];
                unset($_SESSION['fr_name']);
            }
            ?>" name="name">

            <?php
            if (isset($_SESSION['e_name'])) {
                echo '<div class="error">' . $_SESSION['e_name'] . '</div>';
                unset($_SESSION['e_name']);
            }
            ?>

            E-mail: <br> <input type="email" class="form-control" id="email" value="<?php
            if (isset($_SESSION['fr_email'])) {
                echo $_SESSION['fr_email'];
                unset($_SESSION['fr_email']);
            }
            ?>" name="email">
            <?php
            if (isset($_SESSION['e_email'])) {
                echo '<div class="error">' . $_SESSION['e_email'] . '</div>';
                unset($_SESSION['e_email']);
            }
            ?>

            Hasło: <br> <input type="password" class="form-control" id="haslo1" value="<?php
            if (isset($_SESSION['fr_haslo1'])) {
                echo $_SESSION['fr_haslo1'];
                unset($_SESSION['fr_haslo1']);
            }
            ?>" name="haslo1">
            <?php
            if (isset($_SESSION['e_haslo'])) {
                echo '<div class="error">' . $_SESSION['e_haslo'] . '</div>';
                unset($_SESSION['e_haslo']);
            }
            ?>

            Powtórz hasło: <br> <input type="password" class="form-control" id="haslo2" value="<?php
            if (isset($_SESSION['fr_haslo2'])) {
                echo $_SESSION['fr_haslo2'];
                unset($_SESSION['fr_haslo2']);
            }
            ?>" name="haslo2">

            <label>
                <input type="checkbox" name="regulamin" <?php
                if (isset($_SESSION['fr_regulamin'])) {
                    echo "checked";
                    unset($_SESSION["fr_regulamin"]);
                }
                ?>> Akceptuję regulamin
            </label><br>
            <?php
            if (isset($_SESSION['e_regulamin'])) {
                echo '<div class="error">' . $_SESSION['e_regulamin'] . '</div>';
                unset($_SESSION['e_regulamin']);
            }
            ?>

            <div class="g-recaptcha" data-sitekey="6Lf1r3YqAAAAAAqR2uLmng6dGdtTzdCE7HjbA-X8"></div><br>
            <?php
            if (isset($_SESSION['e_bot'])) {
                echo '<div class="error">' . $_SESSION['e_bot'] . '</div>';
                unset($_SESSION['e_bot']);
            }
            ?> 


            <input type="submit" value="Zarejestruj się">
        </form>
    </div>
</main>
<script src="./register.js"></script>
</body>

</html>
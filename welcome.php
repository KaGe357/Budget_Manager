<?php
session_start();

if((!isset($_SESSION["udanarejestracja"])))
{
    header('Location: index.html');
    exit();
} else
{
    unset($_SESSION['udanarejestracja']);
}

if(isset($_SESSION['fr_nick'])) unset($_SESSION['fr_nick']);
if(isset($_SESSION['fr_email'])) unset($_SESSION['fr_email']);
if(isset($_SESSION['fr_haslo1'])) unset($_SESSION['fr_haslo1']);
if(isset($_SESSION['fr_haslo2'])) unset($_SESSION['fr_haslo2']);
if(isset($_SESSION['fr_regulamin'])) unset($_SESSION['fr_regulamin']);

if(isset($_SESSION['e_nick'])) unset($_SESSION['e_nick']);
if(isset($_SESSION['e_email'])) unset($_SESSION['e_email']);
if(isset($_SESSION['e_haslo'])) unset($_SESSION['e_haslo']);
if(isset($_SESSION['e_regulamin'])) unset($_SESSION['e_regulamin']);
if(isset($_SESSION['e_bot'])) unset($_SESSION['e_bot']);
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BudgetManager - Dziękujemy za rejestrację</title>
    <link rel="stylesheet" href="style.css">
    <!-- Przekierowanie po 5 sekundach -->
    <meta http-equiv="refresh" content="5;url=index.html">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>
    <div class="container">
        <p class="text-center">Dziękujemy za rejestrację w serwisie! Możesz już zalogować się na swoje konto.<br></p>
        <p class="text-center"><a href="index.html">Zaloguj się na swoje konto! </a>Za chwilę nastąpi przekierowanie do strony głównej.</p>
        <br><br>
    </div>


</body>
</html>

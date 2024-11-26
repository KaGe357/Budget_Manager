<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['id'])) {
    header('Location: log-in.php');
    exit();
}

require_once "connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['id'];
    $amount = $_POST['amount'];
    $date = $_POST['date'];
    $category_id = $_POST['category_id'];
    $payment_method_id = $_POST['payment_method_id'];
    $comment = isset($_POST['comment']) ? $_POST['comment'] : null;

    $polaczenie = new mysqli($host, $db_user, $db_password, $db_name);

    if ($polaczenie->connect_errno != 0) {
        echo "<script>alert('Błąd połączenia: " . $polaczenie->connect_errno . "');</script>";
        echo "<script>window.location.href = 'add-expense.php';</script>";
        exit();
    } else {

        $stmt = $polaczenie->prepare("INSERT INTO expenses (user_id, date_of_expense, amount, expense_category_assigned_to_user_id, payment_method_assigned_to_user_id, expense_comment) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("isdiis", $user_id, $date, $amount, $category_id, $payment_method_id, $comment);
            if ($stmt->execute()) {
                echo "<script>alert('Wydatek został dodany pomyślnie!');</script>";
                echo "<script>window.location.href = 'add-expense.php';</script>";
            } else {
                echo "<script>alert('Błąd podczas dodawania wydatku: " . $stmt->error . "');</script>";
                echo "<script>window.location.href = 'add-expense.php';</script>";
            }
            $stmt->close();
        } else {
            echo "<script>alert('Błąd przygotowania zapytania: " . $polaczenie->error . "');</script>";
            echo "<script>window.location.href = 'add-expense.php';</script>";
        }
        $polaczenie->close();
    }
}
?>

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
    $comment = isset($_POST['comment']) ? $_POST['comment'] : null;

    $polaczenie = new mysqli($host, $db_user, $db_password, $db_name);

    if ($polaczenie->connect_errno != 0) {
        echo "<script>alert('Błąd połączenia: " . $polaczenie->connect_errno . "');</script>";
        echo "<script>window.location.href = 'add-income.php';</script>";
        exit();
    } else {
        $stmt_check_users_table = $polaczenie->prepare("SELECT COUNT(*) FROM incomes_category_assigned_to_users WHERE user_id = ?");
        $categories_source = 'assigned_to_users'; 
        if ($stmt_check_users_table) {
            $stmt_check_users_table->bind_param("i", $user_id);
            $stmt_check_users_table->execute();
            $stmt_check_users_table->bind_result($count);
            $stmt_check_users_table->fetch();
            $stmt_check_users_table->close();

            if ($count == 0) {
                $categories_source = 'default';
            }
        }

        if ($categories_source === 'assigned_to_users') {
            $stmt_check_category = $polaczenie->prepare("SELECT id FROM incomes_category_assigned_to_users WHERE id = ? AND user_id = ?");
        } else {
            $stmt_check_category = $polaczenie->prepare("SELECT id FROM incomes_category_default WHERE id = ?");
        }

        if ($stmt_check_category) {
            if ($categories_source === 'assigned_to_users') {
                $stmt_check_category->bind_param("ii", $category_id, $user_id);
            } else {
                $stmt_check_category->bind_param("i", $category_id);
            }
            $stmt_check_category->execute();
            $stmt_check_category->store_result();

            if ($stmt_check_category->num_rows == 0) {
                echo "<script>alert('Nieprawidłowa kategoria.');</script>";
                echo "<script>window.location.href = 'add-income.php';</script>";
                $stmt_check_category->close();
                $polaczenie->close();
                exit();
            }
            $stmt_check_category->close();
        }

        $stmt = $polaczenie->prepare("INSERT INTO incomes (user_id, date_of_income, amount, income_category_assigned_to_user_id, income_comment) VALUES (?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("isdss", $user_id, $date, $amount, $category_id, $comment);
            if ($stmt->execute()) {
                echo "<script>alert('Dochód został dodany pomyślnie!');</script>";
                echo "<script>window.location.href = 'add-income.php';</script>";
            } else {
                echo "<script>alert('Błąd podczas dodawania dochodu: " . $stmt->error . "');</script>";
                echo "<script>window.location.href = 'add-income.php';</script>";
            }
            $stmt->close();
        } else {
            echo "<script>alert('Błąd przygotowania zapytania: " . $polaczenie->error . "');</script>";
            echo "<script>window.location.href = 'add-income.php';</script>";
        }
        $polaczenie->close();
    }
}
?>

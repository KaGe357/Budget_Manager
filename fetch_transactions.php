<?php
session_start();



require_once 'connect.php';

$userId = $_SESSION['id'];

try {
    $connection = new mysqli($host, $db_user, $db_password, $db_name);
    if ($connection->connect_errno) {
        throw new Exception("Database connection error: " . $connection->connect_error);
    }



    // ostatnie przychody
    $incomeQuery = "
        SELECT date_of_income AS date, 
               icatu.name AS category, 
               amount, 
               income_comment 
        FROM incomes i
        JOIN incomes_category_assigned_to_users icatu 
        ON i.income_category_assigned_to_user_id = icatu.id
        WHERE i.user_id = ?
        ORDER BY i.date_of_income DESC
        LIMIT 10
    ";

    $stmt = $connection->prepare($incomeQuery);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $incomes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // ostatnie wydatki
    $expenseQuery = "
        SELECT date_of_expense AS date, 
               ecatu.name AS category, 
               amount, 
               expense_comment 
        FROM expenses e
        JOIN expenses_category_assigned_to_users ecatu 
        ON e.expense_category_assigned_to_user_id = ecatu.id
        WHERE e.user_id = ?
        ORDER BY e.date_of_expense DESC
        LIMIT 10
    ";

    $stmt = $connection->prepare($expenseQuery);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $expenses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => true, 'incomes' => $incomes, 'expenses' => $expenses]);
} catch (Exception $e) {
    error_log("Fetch transactions error: " . $e->getMessage());
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($connection)) $connection->close();
}

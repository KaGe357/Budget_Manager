<?php
session_start();
if (!isset($_SESSION['id'])) {
    header('Location: log-in.php');
    exit();
}

$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

if (!$startDate || !$endDate) {
    exit("Nieobsługiwany zakres dat.");
}

require_once 'connect.php';

$userId = $_SESSION['id'];
$incomeCategories = [];
$expenseCategories = [];

$connection = new mysqli($host, $db_user, $db_password, $db_name);
if ($connection->connect_errno) {
    exit("Błąd połączenia z bazą danych: " . $connection->connect_error);
}

$queryIncomeCategories = "
    SELECT 
        icatu.name AS income_category_name, 
        COALESCE(SUM(i.amount), 0) AS total_incomes
    FROM incomes_category_assigned_to_users icatu
    LEFT JOIN incomes i 
        ON i.income_category_assigned_to_user_id = icatu.id 
        AND i.user_id = ? 
        AND i.date_of_income BETWEEN ? AND ?
    WHERE icatu.user_id = ?
    GROUP BY icatu.name
    ORDER BY icatu.name
";
$stmt = $connection->prepare($queryIncomeCategories);
$stmt->bind_param('issi', $userId, $startDate, $endDate, $userId);
$stmt->execute();
$result = $stmt->get_result();

$totalIncomes = 0;
while ($row = $result->fetch_assoc()) {
    $incomeCategories[] = $row;
    $totalIncomes += $row['total_incomes'];
}

$queryExpenseCategories = "
    SELECT 
        ecatu.name AS expense_category_name, 
        COALESCE(SUM(e.amount), 0) AS total_expenses
    FROM expenses_category_assigned_to_users ecatu
    LEFT JOIN expenses e 
        ON e.expense_category_assigned_to_user_id = ecatu.id 
        AND e.user_id = ? 
        AND e.date_of_expense BETWEEN ? AND ?
    WHERE ecatu.user_id = ?
    GROUP BY ecatu.name
    ORDER BY ecatu.name
";
$stmt = $connection->prepare($queryExpenseCategories);
$stmt->bind_param('issi', $userId, $startDate, $endDate, $userId);
$stmt->execute();
$result = $stmt->get_result();

$totalExpenses = 0;
while ($row = $result->fetch_assoc()) {
    $expenseCategories[] = $row;
    $totalExpenses += $row['total_expenses'];
}

$stmt->close();
$connection->close();

$balance = $totalIncomes - $totalExpenses;

?>

<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Budget Manager - Bilans</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="./img/favicon.svg" type="image/png">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <main class="container">
        <header>
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <div class="container-fluid">

                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                        aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <a href="./home.php" class="nav-link active" aria-current="page">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor"
                                        class="bi bi-house mx-2" viewBox="0 0 16 16">
                                        <path
                                            d="M8.707 1.5a1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.708L2 8.207V13.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V8.207l.646.647a.5.5 0 0 0 .708-.708L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293zM13 7.207V13.5a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5V7.207l5-5z" />
                                    </svg>Strona główna
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="./add-income.php" class="nav-link">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor"
                                        class="bi bi-cash mx-2" viewBox="0 0 16 16">
                                        <path d="M8 10a2 2 0 1 0 0-4 2 2 0 0 0 0 4" />
                                        <path
                                            d="M0 4a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H1a1 1 0 0 1-1-1zm3 0a2 2 0 0 1-2 2v4a2 2 0 0 1 2 2h10a2 2 0 0 1 2-2V6a2 2 0 0 1-2-2z" />
                                    </svg>Dodaj przychód
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="./add-expense.php" class="nav-link">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor"
                                        class="bi bi-wallet2 mx-2" viewBox="0 0 16 16">
                                        <path
                                            d="M12.136.326A1.5 1.5 0 0 1 14 1.78V3h.5A1.5 1.5 0 0 1 16 4.5v9a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 0 13.5v-9a1.5 1.5 0 0 1 1.432-1.499zM5.562 3H13V1.78a.5.5 0 0 0-.621-.484zM1.5 4a.5.5 0 0 0-.5.5v9a.5.5 0 0 0 .5.5h13a.5.5 0 0 0 .5-.5v-9a.5.5 0 0 0-.5-.5z" />
                                    </svg>Dodaj wydatek
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="./balance.php" class="nav-link">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor"
                                        class="bi bi-bar-chart-line-fill mx-2" viewBox="0 0 16 16">
                                        <path
                                            d="M11 2a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v12h.5a.5.5 0 0 1 0 1H.5a.5.5 0 0 1 0-1H1v-3a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3h1V7a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v7h1z" />
                                    </svg>Przeglądaj Bilans
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="./settings.php" class="nav-link">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor"
                                        class="bi bi-gear-fill mx-2" viewBox="0 0 16 16">
                                        <path
                                            d="M9.405 1.05c-.413-1.4-2.397-1.4-2.81 0l-.1.34a1.464 1.464 0 0 1-2.105.872l-.31-.17c-1.283-.698-2.686.705-1.987 1.987l.169.311c.446.82.023 1.841-.872 2.105l-.34.1c-1.4.413-1.4 2.397 0 2.81l.34.1a1.464 1.464 0 0 1 .872 2.105l-.17.31c-.698 1.283.705 2.686 1.987 1.987l.311-.169a1.464 1.464 0 0 1 2.105.872l.1.34c.413 1.4 2.397 1.4 2.81 0l.1-.34a1.464 1.464 0 0 1 2.105-.872l.31.17c1.283.698 2.686-.705 1.987-1.987l-.169-.311a1.464 1.464 0 0 1-.872-2.105l.34-.1c1.4-.413 1.4-2.397 0-2.81l-.34-.1a1.464 1.464 0 0 1-.872-2.105l.17-.31c.698-1.283-.705-2.686-1.987-1.987l-.311.169a1.464 1.464 0 0 1-2.105-.872zM8 10.93a2.929 2.929 0 1 1 0-5.86 2.929 2.929 0 0 1 0 5.858z" />
                                    </svg>Ustawienia
                                </a>
                            </li>
                            <li class="nav-item"><a href="./log-out.php" class="nav-link"><svg
                                        xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor"
                                        class="bi bi-box-arrow-right mx-2" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd"
                                            d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0z" />
                                        <path fill-rule="evenodd"
                                            d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708z" />
                                    </svg>Wyloguj się</a></li>
                        </ul>
                    </div>
                </div>
            </nav>
        </header>

        <section class="my-5">
            <h2 class="text-center">Bilans</h2>

            <div class="text-center my-3">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#balanceModal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor"
                        class="bi bi-calendar mx-2" viewBox="0 0 16 16">
                        <path
                            d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 1 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z" />
                    </svg>Wybierz zakres dat
                </button>
            </div>

            <div class="text-center my-4">
                <a id="goToHistoryButton" href="./history.php" class="btn btn-info">Histora transakcji</a>
            </div>

            <div class="modal fade" id="balanceModal" tabindex="-1" aria-labelledby="balanceModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="balanceModalLabel">Wybierz zakres dat</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">


                            <div class="mb-3">
                                <label for="dateRangeSelect" class="form-label">Wybierz zakres dat</label>
                                <select id="dateRangeSelect" class="form-select">
                                    <option value="">Wybierz zakres</option>
                                    <option value="thisMonth">Bieżący miesiąc</option>
                                    <option value="lastMonth">Poprzedni miesiąc</option>
                                    <option value="allTime">Wszystkie</option>
                                    <option value="custom">Niestandardowy</option>
                                </select>
                            </div>


                            <div class="mb-3" id="customStartDateRange" style="display: none;">
                                <label for="startDateInput" class="form-label">Data początkowa</label>
                                <input type="date" id="startDateInput" class="form-control">
                            </div>
                            <div class="mb-3" id="customEndDateRange" style="display: none;">
                                <label for="endDateInput" class="form-label">Data końcowa</label>
                                <input type="date" id="endDateInput" class="form-control">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" id="applyDateRange">Zastosuj</button>
                        </div>
                    </div>
                </div>
            </div>


            <div class="row">
                <div class="col-12 d-flex justify-content-center align-items-center flex-column bordered">
                    <p class="fs-3">Twój bilans:</p>
                    <p class="fs-3"><span id="balance"
                            class="text-center"><?php echo number_format($balance, 2, ',', ' ') . " zł"; ?></span></p>
                    <br>
                    <p class="fs-3"><span id="text">
                            <?php echo $balance > 0
                                ? "Świetnie zarządzasz swoimi finansami!"
                                : ($balance < 0
                                    ? "Twój bilans jest na minusie: " . abs($balance) . " zł"
                                    : "Bilans wynosi zero."); ?>
                        </span></p>
                </div>

                <!-- Dochody -->
                <div class="col-md-6 d-flex align-items-center flex-column bordered">
                    <h4><br>Dochody</h4>
                    <table class="table table-bordered table-incomes">
                        <thead>
                            <tr>
                                <th scope="col">Kategoria</th>
                                <th scope="col">Kwota</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($incomeCategories as $category): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($category['income_category_name']); ?></td>
                                    <td class="fw-bold">
                                        <?php echo number_format($category['total_incomes'], 2, ',', ' ') . " PLN"; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <canvas id="incomeChart" width="200" height="200"></canvas>
                </div>

                <!-- Wydatki -->
                <div class="col-md-6 d-flex align-items-center flex-column bordered">
                    <br>
                    <h4>Wydatki</h4>
                    <table class="table table-bordered table-expenses">
                        <thead>
                            <tr>
                                <th scope="col">Kategoria</th>
                                <th scope="col">Kwota</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($expenseCategories as $category): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($category['expense_category_name']); ?></td>
                                    <td class="fw-bold">
                                        <?php echo number_format($category['total_expenses'], 2, ',', ' ') . " PLN"; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <canvas id="expenseChart" width="200" height="200"></canvas>
                </div>
            </div>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="balance-script.js"></script>
    <script src="transaction-charts.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="script.js"></script>

</body>

</html>
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

$polaczenie = new mysqli($host, $db_user, $db_password, $db_name);

if ($polaczenie->connect_errno != 0) {
    echo "Błąd połączenia: " . $polaczenie->connect_errno;
    exit();
}

$user_id = $_SESSION['id'];
$categories = [];
$payment_methods = [];

// kategorie przypisane do użytkownika
$query = "SELECT id, name FROM expenses_category_assigned_to_users WHERE user_id = ?";
$stmt = $polaczenie->prepare($query);
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
    }

    $stmt->close();
}

// metody płatności przypisane do użytkownika
$query = "SELECT id, name FROM payment_methods_assigned_to_users WHERE user_id = ?";
$stmt = $polaczenie->prepare($query);
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $payment_methods[] = $row;
        }
    }

    $stmt->close();
}

$polaczenie->close();
?>



<!DOCTYPE html>
<html lang="pl">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Budget Manager - Dodaj Wydatek</title>
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      rel="stylesheet"
      integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
      crossorigin="anonymous"
    />
    <link rel="stylesheet" href="style.css" />
    <link rel="icon" href="./img/favicon.svg" type="image/png" />
  </head>
  <body>
    <main>
      <div class="container">
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

        <div class="d-flex justify-content-center border py-4">
          <form action="save_expense.php" method="POST">
            <section>
              <div class="mb-3">
                <label for="amountInput" class="form-label">Kwota</label>
                <input
                  type="number"
                  class="form-control resize"
                  id="amountInput"
                  name="amount"
                  step="0.01"
                  placeholder="Wprowadź kwotę"
                  required
                />
              </div>
              <div class="mb-3">
                <label for="dateInput" class="form-label">Wybierz datę</label>
                <input
                  type="date"
                  class="form-control resize"
                  id="dateInput"
                  name="date"
                  value="<?= date('Y-m-d'); ?>"
                  required
                />
              </div>
              <div class="mb-3">
                <label for="categorySelect" class="form-label">Wybierz kategorię</label>
                <select
                  class="form-select resize"
                  id="categorySelect"
                  name="category_id"
                  required
                >
                  <option value="" disabled selected>Wybierz...</option>
                  <?php foreach ($categories as $category): ?>
                    <option value="<?= htmlspecialchars($category['id']) ?>">
                      <?= htmlspecialchars($category['name']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="mb-3">
                <label for="methodSelect" class="form-label">Metoda płatności</label>
                <select
                  class="form-select resize"
                  id="methodSelect"
                  name="payment_method_id"
                  required
                >
                  <option value="" disabled selected>Wybierz...</option>
                  <?php foreach ($payment_methods as $method): ?>
                    <option value="<?= htmlspecialchars($method['id']) ?>">
                      <?= htmlspecialchars($method['name']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="mb-3">
                <label for="commentTextarea" class="form-label"
                  >Komentarz (opcjonalnie)</label
                >
                <textarea
                  class="form-control resize"
                  id="commentTextarea"
                  name="comment"
                  rows="3"
                  placeholder="Wpisz swój komentarz tutaj..."
                ></textarea>
              </div>
              <div class="text-center">
                <button type="submit" class="btn btn-success">Dodaj</button>
                <a href="./home.html" class="btn btn-danger">Anuluj</a>
              </div>
            </section>
          </form>
        </div>
      </div>
    </main>

    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
      crossorigin="anonymous"
    ></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="./script.js"></script>
  </body>
</html>
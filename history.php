<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Budget Manager - Historia Transakcji</title>
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      rel="stylesheet"
      integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
      crossorigin="anonymous"
    />
    <link rel="stylesheet" href="style.css" />
  </head>
  <body>
    <main>
      <div class="container my-4">
        <header class="d-flex py-3">
          <a href="./balance.php" class="btn btn-secondary">Powrót</a>
          <h1 class="ms-3">Historia Transakcji</h1>
        </header>

        <div class="recent-transactions-container d-flex gap-3 justify-content-center flex-wrap">
          <section class="recent-transactions fs-5">
            <h2>Ostatnie Przychody</h2>
            <div class="table-responsive">
              <table class="transactions-table table table-striped table-bordered">
                <thead>
                  <tr>
                    <th>Data</th>
                    <th>Kategoria</th>
                    <th>Kwota</th>
                    <th>Komentarz</th>
                  </tr>
                </thead>
                <tbody id="incomeTransactionsList">
                </tbody>
              </table>
            </div>
            <button id="refreshIncomeTransactions" class="btn btn-primary mt-2">Odśwież Przychody</button>
          </section>

          <section class="recent-transactions fs-5">
            <h2>Ostatnie Wydatki</h2>
            <div class="table-responsive">
              <table class="transactions-table table table-striped table-bordered">
                <thead>
                  <tr>
                    <th>Data</th>
                    <th>Kategoria</th>
                    <th>Kwota</th>
                    <th>Komentarz</th>
                  </tr>
                </thead>
                <tbody id="expenseTransactionsList">
                </tbody>
              </table>
            </div>
            <button id="refreshExpenseTransactions" class="btn btn-primary mt-2">Odśwież Wydatki</button>
          </section>
        </div>
      </div>

      <script src="last-transactions.js"></script>
    </main>
  </body>
</html>

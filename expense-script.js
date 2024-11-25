document.addEventListener("DOMContentLoaded", () => {
  const ctx = document.getElementById("expenseChart").getContext("2d");
  let chart;

  const createChart = (labels, data) => {
    if (chart) chart.destroy(); 
    chart = new Chart(ctx, {
      type: "pie", 
      data: {
        labels: labels,
        datasets: [
          {
            label: "Wydatki na kategorie",
            data: data,
            backgroundColor: ["#FF6384", "#36A2EB", "#FFCE56", "#4BC0C0"],
            borderColor: ["#FF6384", "#36A2EB", "#FFCE56", "#4BC0C0"],
            borderWidth: 1,
          },
        ],
      },
    });
  };


  const fetchExpenseData = () => {
    fetch("get-expense-data.php")
      .then((response) => {
        if (!response.ok) {
          throw new Error("Błąd podczas pobierania danych.");
        }
        return response.json();
      })
      .then((data) => {
        const labels = data.map((item) => item.category);
        const values = data.map((item) => parseFloat(item.total));
        createChart(labels, values);
      })
      .catch((error) => {
        console.error("Błąd:", error);
        alert("Nie udało się załadować danych do wykresu.");
      });
  };

  fetchExpenseData();
});

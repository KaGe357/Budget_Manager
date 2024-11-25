$(document).ready(function() {
    const today = new Date().toISOString().split("T")[0];
    $('#dateInput').val(today);

    $('#addButton').on('click', function() {
        const kwota = parseFloat($('#numberInput').val()) || 0;
        const data = $('#dateInput').val();
        const kategoria = $('#categorySelect option:selected').text();
        const komentarz = $('#commentTextarea').val();

        if (!kwota || !data || !kategoria) {
            alert("Proszę wypełnić wszystkie wymagane pola (kwota, data, kategoria).");
            return;
        }

        let dochody = JSON.parse(localStorage.getItem('dochody')) || [];
        dochody.push({ kwota, data, kategoria, komentarz });
        localStorage.setItem('dochody', JSON.stringify(dochody));

        if (window.incomeChart) {
            const categoryIndex = window.incomeChart.data.labels.indexOf(kategoria);

            if (categoryIndex === -1) {
                window.incomeChart.data.labels.push(kategoria);
                window.incomeChart.data.datasets[0].data.push(kwota);
            } else {
                window.incomeChart.data.datasets[0].data[categoryIndex] += kwota;
            }

            window.incomeChart.update();
        }

        $('#numberInput').val('');
        $('#dateInput').val(today);
        $('#categorySelect').prop('selectedIndex', 0);
        $('#commentTextarea').val('');

        alert("Dochód został dodany!");
    });
});

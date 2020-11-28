/*
Denne fil indeholder klient kode som køres på aktivitetssiden
Side: /activity-center
Layout: activity-center.php
 */

let selectedLogTypeIDs = [];

let logTable = document.getElementById('logs');
let logs = Array.from(logTable.querySelectorAll('tbody tr'));
let filteredLogs = filterLogs();

let limit = 10; // Antal logs pr. side
let page = 1; // Nuværende side
let pages = Math.ceil(filteredLogs.length / limit); // Antal sider

updatePagination();

let temp = filteredLogs.slice((page - 1) * limit, limit + (page - 1) * limit);

logs.forEach(log => {
    if (!temp.includes(log) || !filterLogs().includes(log)) {
        log.style.display = 'none';
    }
});

/**
 * Indsætter links i bunden af tabellen til at skifte side
 */
function updatePagination() {
    document.querySelector('.log-pagination').innerHTML = '';

    for (let i = 1; i <= pages; i++) {
        let link = ` <a onclick="page = ${i}; updateTable()" href="javascript:void(0)">${i}</a> `;
        document.querySelector('.log-pagination').insertAdjacentHTML('beforeend', link);
    }
}

/**
 * Opdaterer array'et med valgte log typer
 * @param context
 */
function updateSelectedTypes(context) {
    if (context.classList.contains('selected')) {
        context.classList.remove('selected');
        selectedLogTypeIDs = selectedLogTypeIDs.filter(typeID => typeID !== +context.getAttribute('data-log-type-id'));
    } else {
        context.classList.add('selected');
        selectedLogTypeIDs.push(+context.getAttribute('data-log-type-id'));
    }

    page = 1;

    updateTable();
}

/**
 * Returnerer et array med logs som er filtreret efter hvilke log typer der er valgt
 * @return {[]}
 */
function filterLogs() {
    return logs.filter(log => selectedLogTypeIDs.includes(+log.getAttribute('data-log-type-id')));
}

/**
 * Skjuler eller viser logs i tabellen, alt efter hvilken side man er på, og hvilke log typer der er valgt
 */
function updateTable() {
    let total = filterLogs();
    let toShow = total.slice((page - 1) * limit, limit + (page - 1) * limit);

    logs.forEach(log => {
        if (toShow.includes(log)) {
            log.style.display = 'table-row';
        } else {
            log.style.display = 'none';
        }
    });

    pages = Math.ceil(total.length / limit);

    updatePagination();
}
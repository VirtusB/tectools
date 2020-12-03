/*Denne fil indeholder det klient kode som køres først på sidenDen står bl.a. for at initialisere burger-menuen på mobile enheder og indeholder funktioner som bruges til RCMSTables pluginet *//** * "DOMContentLoaded" er et event som JavaScript udsender, når HTML strukturen, elementerne og indholdet er loadet på siden */document.addEventListener('DOMContentLoaded', function () {    window.timeago.register('da_DK', localeFunc); // Sæt timeago sprog til dansk    window.timeagoInstance = new window.timeago();    $('select.mat-select').formSelect();    $('.materialboxed').materialbox({        'onCloseEnd': function () {            $('.RCMSTable img').css('max-height', '53.75px')        }    });    initMobileSidenav();});/** * "load" er et event som JavaScript udsender, når siden er helt færdig med at loade * Det udsendes først når alle JavaScript filer, CSS filer og billeder er loadet på siden */window.addEventListener('load', function () {    makeTablesResponsive();    // Biblioteket "timeago.js" formaterer datoer som f.eks. "2020-01-01" til "1. januar 2020"    window.timeagoInstance.render(document.querySelectorAll('.render-datetime'), 'da_DK');    // Tjek om tabeller skal gøres responsive når størrelsen på browservinduet ændrer sig    var resizeTimer;    window.addEventListener('resize', function (e) {        clearTimeout(resizeTimer);        resizeTimer = setTimeout(function () {            makeTablesResponsive();        }, 250);    });});/** * Initialiser burger menu for tablets og smartphone * @returns {undefined} */function initMobileSidenav() {    $('.sidenav').sidenav();}/** * Viser en loading gif som snurrer rundt, i elementet som hører til elemSelector * @param elemSelector */function showLoader(elemSelector) {    document.querySelector(elemSelector).innerHTML = `        <img class="loader-gif" src="/template/tectools/images/loader.gif" style="max-height: 30px; vertical-align: middle;">    `;}/** * Viser et success ikon (et flueben) i elementet som hører til elemSelector * @param elemSelector * @param text */function showSuccessIcon(elemSelector, text = '') {    let c = text === '' ? '' : 'right';    document.querySelector(elemSelector).innerHTML = `        ${text} <i class="fal fa-check ${c}"></i>    `;}/** * Viser et fejl ikon (et kryds) i elementet som hører til elemSelector * @param elemSelector * @param text */function showErrorIcon(elemSelector, text = '') {    let c = text === '' ? '' : 'right';    document.querySelector(elemSelector).innerHTML = `        ${text} <i class="fal fa-times ${c}"></i>    `;}/** * Denne funktion sørger for at RCMSTables tabeller ser godt ud på mobiler */function makeTablesResponsive() {    if (window.innerWidth < 600) {        document.querySelectorAll('table.RCMSTable').forEach(table => {            const thEls = table.querySelectorAll('.table-head th');            const tdLabels = Array.from(thEls).map(el => el.innerText);            table.querySelectorAll('tbody tr:not(.table-head):not(.search-tr):not(.pagination-tr)').forEach(tr => {                Array.from(tr.children).forEach(                    (td, ndx) => td.setAttribute('label', tdLabels[ndx])                );            });        });    }}/** * Returnerer det "i" HTML element i "table" element som p.t. har "currentsort" klassen * @param {string} tableID * @return {Element} */function findCurrentSortIcon(tableID) {    let table = document.getElementById(tableID);    return table.querySelector('i.currentsort');}/** * Returnerer det "<i>" HTML element i et "thead" element som p.t. har "sort-desc-icon" klassen * @param {Element} th * @return {Element} */function findSortIconDESC(th) {    return th.querySelector('i.sort-desc-icon');}/** * Returnerer det "<i>" HTML element i et "thead" element som p.t. har "sort-asc-icon" klassen * @param {Element} th * @return {Element} */function findSortIconASC(th) {    return th.querySelector('i.sort-asc-icon');}/** * Returnerer enten DESC eller ASC, alt efter hvad den indledende sorteringsretning er * @param {string} tableID * @return {string} */function getInitialSortDir(tableID) {    let table = document.getElementById(tableID);    let th = table.querySelector('th[data-initial-sort-dir]');    if (th === null) {        return 'DESC'; // DESC er default    }    return th.getAttribute('data-initial-sort-dir');}$(document).ready(function () {    // Opsætning af RCMSTable ajax sortering    var sortDESC = {dir: 'DESC', icon: '<i class="material-icons sort-desc-icon">arrow_drop_down</i>'};    var sortASC = {dir: 'ASC', icon: '<i class="material-icons sort-asc-icon">arrow_drop_up</i>'};    let tableSorting = {};    let rcmsTables = document.querySelectorAll('table.RCMSTable');    rcmsTables.forEach(table => {        if (!table.classList.contains('has-sorting')) {            return;        }        let id = $(table).attr('id');        tableSorting[id] = {            currentPageNum: 1,            currentSortDir: getInitialSortDir(id),            previousClickedSortTh: null,            sortKey: null        };        // Indsæt ikoner til sortering (små pile)        let thEls = table.querySelectorAll('th');        thEls.forEach(th => {            if (!th.classList.contains('th-can-sort')) {                return;            }            let isInitialSort = th.getAttribute('data-initial-sort-dir') !== null;            let iconDiv = document.createElement('div');            iconDiv.classList.add('rcmstable-sort-icons-container');            if (isInitialSort) {                iconDiv.insertAdjacentHTML('beforeend', sortASC.icon);                iconDiv.insertAdjacentHTML('beforeend', sortDESC.icon);                th.insertAdjacentElement('beforeend', iconDiv);                th.querySelector('i.sort-desc-icon').classList.add('currentsort');            } else {                iconDiv.insertAdjacentHTML('beforeend', sortASC.icon);                iconDiv.insertAdjacentHTML('beforeend', sortDESC.icon);                th.insertAdjacentElement('beforeend', iconDiv);            }        });    });    // RCMSTable AJAX sortering    $(document).on('click', 'th.th-can-sort', function (e) {        var table_id = $(this).closest('table').attr('id');        var th = $(this)[0];        // Hvis data-initial-sort-dir er sat, så ved vi hvilken kolonne der startes med at sortere på (inden bruger interaktion) og om det er DESC eller ASC        if ($(this).attr('data-initial-sort-dir')) {            tableSorting[table_id].previousClickedSortTh = $(this).attr('data-sort-key');            $(this).removeAttr('data-initial-sort-dir');        }        findCurrentSortIcon(table_id).classList.remove('currentsort');        if (tableSorting[table_id].previousClickedSortTh === $(this).attr('data-sort-key')) {            // Toggle rækkefølge. Hvis rækkefølgen var DESC bliver den ændret til ASC og omvendt            if (tableSorting[table_id].currentSortDir === sortDESC.dir) {                tableSorting[table_id].currentSortDir = sortASC.dir;                let sortIcon = findSortIconASC(th);                sortIcon.classList.add('currentsort');            } else if (tableSorting[table_id].currentSortDir === sortASC.dir) {                tableSorting[table_id].currentSortDir = sortDESC.dir;                let sortIcon = findSortIconDESC(th);                sortIcon.classList.add('currentsort');            }        } else {            let initialSortDir = getInitialSortDir(table_id);            if (initialSortDir === 'ASC') {                let sortIcon = findSortIconASC(th);                sortIcon.classList.add('currentsort');            } else {                let sortIcon = findSortIconDESC(th);                sortIcon.classList.add('currentsort');            }            tableSorting[table_id].currentSortDir = initialSortDir;        }        tableSorting[table_id].previousClickedSortTh = $(this).attr('data-sort-key');        tableSorting[table_id].sortKey = $(this).attr('data-sort-key');        var searchText = $(this).closest('table').find('.searchbar').val();        RCMSTableLoad(table_id, tableSorting[table_id].currentPageNum, searchText, tableSorting[table_id].sortKey, tableSorting[table_id].currentSortDir);    });    // RCMSTable dropdown rækker, vis/skjul    // Hvis et <tr> element har "hidedtrow" klassen, så er det en dropdown række    $(document).on('click', '.RCMSTableExpand', function (e) {        $(this).next(".hidedtrow").slideToggle();        if ($(this).next(".hidedtrow").is(":visible")) {            $(this).next(".hidedtrow").css("display", "table-row");        }    });    // RCMSTable sideskift    $(document).on('click', '.pagestd a', function (e) {        e.preventDefault();        var table_id = $(this).closest('table').attr('id');        var pageNumber = $(this).attr("href");        var searchText = $(this).closest('table').find('.searchbar').val();        if (typeof tableSorting[table_id] !== 'undefined') {            tableSorting[table_id].currentPageNum = pageNumber;            RCMSTableLoad(table_id, tableSorting[table_id].currentPageNum, searchText, tableSorting[table_id].sortKey, tableSorting[table_id].currentSortDir);            return;        }        RCMSTableLoad(table_id, pageNumber, searchText);    });    // RCMSTable søgefelt debounce    // debounce begrænser hvor tit data for tabellen skal loades igen    // Hvis der er gået 300 ms siden sidste data load, når brugeren søger, så load igen    // Giver effekten af realtime søgning    var changeTimer = false;    $(document).on('keyup', ".searchbar", function () {        var table_id = $(this).closest('table').attr('id');        var searchText = $(this).val();        if (changeTimer !== false) {            clearTimeout(changeTimer);        }        changeTimer = setTimeout(function () {            RCMSTableLoad(table_id, 1, searchText);            changeTimer = false;        }, 300);    });});/** * Sender en AJAX request til serveren, loader og indsætter nyt data i en HTML tabel * På serveren håndterer loadAjax() funktionen, i RCMSTables.php filen, denne request * @param {string} table_id * @param {int|string} pageNumber * @param {string} searchText * @param {string|null} sortKey * @param {string|null} sortDir * @returns {undefined} */function RCMSTableLoad(table_id, pageNumber, searchText, sortKey, sortDir) {    let data = {RCMSTable: table_id, pageNum: pageNumber, searchTxt: searchText};    if (sortKey !== '' && sortKey !== undefined && sortKey !== null) {        data['sortKey'] = sortKey;    }    if (sortDir !== '' && sortDir !== undefined && sortDir !== null) {        data['sortDir'] = sortDir;    }    $.ajax({        type: 'POST',        dataType: 'TEXT',        data: data,        success: function (response) {            $("#" + table_id + " .dataRow").first().replaceWith('<tr class="tempRow"></tr>');            $("#" + table_id + " .dataRow").remove();            $("#" + table_id + " .tempRow").replaceWith(response);            JsBarcode(".barcode").init();            window.timeagoInstance.render(document.querySelectorAll('.render-datetime'), 'da_DK');            makeTablesResponsive();            handleExceededRentals();            $('.materialboxed').materialbox({                'onCloseEnd': function () {                    $('.RCMSTable img').css('max-height', '53.75px')                }            });            $('.RCMSTable img').css('max-height', '53.75px');        },        error: function (XMLHttpRequest, textStatus, errorThrown) {            alert("RCMSTable failure: " + textStatus);        }    });}
document.addEventListener('DOMContentLoaded', function () {    initMobileSidenav();});/** * Initialize side burger menu for mobile view */function initMobileSidenav() {    $('.sidenav').sidenav();}function insertRCMSTableStyle() {    let style = `        <style>            .RCMSTable .sort-desc-icon, .RCMSTable .sort-asc-icon {                display: block;                line-height: 0.4;            }                        .RCMSTable .sort-desc-icon.currentsort, .RCMSTable .sort-asc-icon.currentsort {                color: #039be5;            }                        .RCMSTable .rcmstable-sort-icons-container {                display: inline-block;                vertical-align: middle;            }        </style>    `;    document.body.insertAdjacentHTML('beforeend', style);}function findCurrentSortIcon(tableID) {    let table = document.getElementById(tableID);    return table.querySelector('i.currentsort');}function findSortIconDESC(th) {    return th.querySelector('i.sort-desc-icon');}function findSortIconASC(th) {    return th.querySelector('i.sort-asc-icon');}function getInitialSortDir(tableID) {    let table = document.getElementById(tableID);    let th = table.querySelector('th[data-initial-sort-dir]');    if (th === null) {        return 'DESC'; // DESC er default    }    return th.getAttribute('data-initial-sort-dir');}$(document).ready(function() {    insertRCMSTableStyle();    // Opsætning af RCMSTable ajax sortering    var sortDESC = {dir: 'DESC', icon: '<i class="material-icons sort-desc-icon">arrow_drop_down</i>'};    var sortASC = {dir: 'ASC', icon: '<i class="material-icons sort-asc-icon">arrow_drop_up</i>'};    let tableSorting = {};    let rcmsTables = document.querySelectorAll('table.RCMSTable');    rcmsTables.forEach(table => {        if (!table.classList.contains('has-sorting')) {            return;        }        let id = $(table).attr('id');        tableSorting[id] = {            currentPageNum: 1,            currentSortDir: getInitialSortDir(id),            previousClickedSortTh: null,            sortKey: null        };        // indsæt ikoner til sortering (små pile)        let thEls = table.querySelectorAll('th');        thEls.forEach(th => {            if (!th.classList.contains('th-can-sort')) {                return;            }            let isInitialSort = th.getAttribute('data-initial-sort-dir') !== null;            let iconDiv = document.createElement('div');            iconDiv.classList.add('rcmstable-sort-icons-container');            if (isInitialSort) {                iconDiv.insertAdjacentHTML('beforeend', sortASC.icon);                iconDiv.insertAdjacentHTML('beforeend', sortDESC.icon);                th.insertAdjacentElement('beforeend', iconDiv);                th.querySelector('i.sort-desc-icon').classList.add('currentsort');            } else {                iconDiv.insertAdjacentHTML('beforeend', sortASC.icon);                iconDiv.insertAdjacentHTML('beforeend', sortDESC.icon);                th.insertAdjacentElement('beforeend', iconDiv);            }        });    });    // RCMSTable ajax sortering    $(document).on('click', 'th.th-can-sort', function (e) {        var table_id = $(this).closest('table').attr('id');        var th = $(this)[0];        // hvis data-initial-sort-dir er sat, så ved vi hvilken kolonne der startes med at sortere på (inden bruger interaktion) og om det er DESC eller ASC        if ($(this).attr('data-initial-sort-dir')) {            tableSorting[table_id].previousClickedSortTh = $(this).attr('data-sort-key');            $(this).removeAttr('data-initial-sort-dir');        }        findCurrentSortIcon(table_id).classList.remove('currentsort');        if (tableSorting[table_id].previousClickedSortTh === $(this).attr('data-sort-key')) {            // toggle rækkefølge. hvis rækkefølgen var DESC bliver den ændret til ASC og omvendt            if (tableSorting[table_id].currentSortDir === sortDESC.dir) {                tableSorting[table_id].currentSortDir = sortASC.dir;                let sortIcon = findSortIconASC(th);                sortIcon.classList.add('currentsort');            } else if (tableSorting[table_id].currentSortDir === sortASC.dir) {                tableSorting[table_id].currentSortDir = sortDESC.dir;                let sortIcon = findSortIconDESC(th);                sortIcon.classList.add('currentsort');            }        } else {            let initialSortDir = getInitialSortDir(table_id);            if (initialSortDir === 'ASC') {                let sortIcon = findSortIconASC(th);                sortIcon.classList.add('currentsort');            } else {                let sortIcon = findSortIconDESC(th);                sortIcon.classList.add('currentsort');            }            tableSorting[table_id].currentSortDir = initialSortDir;        }        tableSorting[table_id].previousClickedSortTh = $(this).attr('data-sort-key');        tableSorting[table_id].sortKey = $(this).attr('data-sort-key');        var searchText = $(this).closest('table').find('.searchbar').val();        RCMSTableLoad(table_id, tableSorting[table_id].currentPageNum, searchText, tableSorting[table_id].sortKey, tableSorting[table_id].currentSortDir);    });    // RCMSTable dropdown rows    $(document).on('click', '.RCMSTableExpand', function (e) {        $(this).next(".hidedtrow").slideToggle();        if ($(this).next(".hidedtrow").is(":visible")) {            $(this).next(".hidedtrow").css("display","table-row");        }    });    // RCMSTable sideskift    $(document).on('click', '.pagestd a', function(e) {        e.preventDefault();        var table_id = $(this).closest('table').attr('id');        var pageNumber = $(this).attr("href");        var searchText = $(this).closest('table').find('.searchbar').val();        if (typeof tableSorting[table_id] !== 'undefined') {            tableSorting[table_id].currentPageNum = pageNumber;            RCMSTableLoad(table_id, tableSorting[table_id].currentPageNum, searchText, tableSorting[table_id].sortKey, tableSorting[table_id].currentSortDir);            return;        }        RCMSTableLoad(table_id, pageNumber, searchText);    });    // RCMSTable searchbar debounce    var changeTimer = false;    $(document).on('keyup', ".searchbar", function() {        var table_id = $(this).closest('table').attr('id');        var searchText = $(this).val();        if(changeTimer !== false) {            clearTimeout(changeTimer);        }        changeTimer = setTimeout(function() {            RCMSTableLoad(table_id, 1, searchText);            changeTimer = false;        },300);    });});function RCMSTableLoad(table_id, pageNumber, searchText, sortKey, sortDir) {    let data = {RCMSTable: table_id, pageNum: pageNumber, searchTxt: searchText};    if (sortKey !== '' && sortKey !== undefined && sortKey !== null) {        data['sortKey'] = sortKey;    }    if (sortDir !== '' && sortDir !== undefined && sortDir !== null) {        data['sortDir'] = sortDir;    }    $.ajax({        type: 'POST',        dataType: 'TEXT',        data: data,        success: function(response) {            $("#" + table_id + " .dataRow").first().replaceWith('<tr class="tempRow"></tr>');            $("#" + table_id + " .dataRow").remove();            $("#" + table_id + " .tempRow").replaceWith(response);            JsBarcode(".barcode").init();        },        error: function(XMLHttpRequest, textStatus, errorThrown) {            alert("RCMSTable failure: " + textStatus);        }    });}
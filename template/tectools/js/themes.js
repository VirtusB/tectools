document.addEventListener('DOMContentLoaded', e => {
    const themeSwitch = document.getElementById('dark-theme-input');

    if (themeSwitch) {
        initTheme();
    }

    addThemeSwitchListener();
});


/**
 * Tilføjer en EventListener til tema-kontakten
 * @returns {undefined}
 */
function addThemeSwitchListener() {
    const themeSwitch = document.getElementById('dark-theme-input');
    themeSwitch.addEventListener('change', toggleTheme);
}

/**
 * Første funktion der kører i themes.js
 * Tjekker om brugeren har valgt et tema og aktiverer/deaktiverer efterfølgende
 * @returns {undefined}
 */
function initTheme() {
    const themeSwitch = document.getElementById('dark-theme-input');
    const darkThemeSelected = (localStorage.getItem('LS_THEME') !== null && localStorage.getItem('LS_THEME') === 'dark');

    themeSwitch.checked = darkThemeSelected;

    if (darkThemeSelected) {
        enableDarkThemeStyle();
    } else {
        disableDarkThemeStyle();
    }
}

/**
 * Skifter mellem mørkt/lyst tema
 * Kaldes automatisk når tema-kontakten ændrer værdi, bør ikke kaldes manuelt
 * @returns {undefined}
 */
function toggleTheme() {
    const themeSwitch = document.getElementById('dark-theme-input');

    if(themeSwitch.checked) {
        enableDarkThemeStyle();
        localStorage.setItem('LS_THEME', 'dark');
    } else {
        disableDarkThemeStyle();
        localStorage.removeItem('LS_THEME');
    }
}

/**
 * Genindlæs et HTML element
 * @param {HTMLElement} element
 * @returns {undefined}
 */
function refreshElement(element) {
    const content = element.innerHTML;
    element.innerHTML = '';
    element.innerHTML = content;
}

/**
 * Aktiverer mørkt tema
 * @returns {undefined}
 */
function enableDarkThemeStyle() {
    const darkThemeStyleEl = document.getElementById('dark-theme-style');
    darkThemeStyleEl.removeAttribute('media');

    refreshElement(darkThemeStyleEl); // Nødvendig for ældre browsere

    document.querySelector('meta[name=msapplication-TileColor]').setAttribute('content', '#1d1d1d');
    document.querySelector('meta[name=theme-color]').setAttribute('content', '#1d1d1d');
}

/**
 * Deaktiverer mørkt tema
 * @returns {undefined}
 */
function disableDarkThemeStyle() {
    const darkThemeStyleEl = document.getElementById('dark-theme-style');
    darkThemeStyleEl.setAttribute('media', 'max-width: 1px');

    document.querySelector('meta[name=msapplication-TileColor]').setAttribute('content', '#8cc63e');
    document.querySelector('meta[name=theme-color]').setAttribute('content', '#8cc63e');
}
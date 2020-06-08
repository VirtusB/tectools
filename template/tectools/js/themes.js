document.addEventListener('DOMContentLoaded', e => {
    const themeSwitch = document.getElementById('dark-theme-input');

    if (themeSwitch) {
        initTheme();
    }

    addThemeSwitchListener()
});



function addThemeSwitchListener() {
    const themeSwitch = document.getElementById('dark-theme-input');
    themeSwitch.addEventListener('change', resetTheme);
}

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

function resetTheme() {
    const themeSwitch = document.getElementById('dark-theme-input');

    if(themeSwitch.checked) {
        enableDarkThemeStyle();
        localStorage.setItem('LS_THEME', 'dark');
    } else {
        disableDarkThemeStyle();
        localStorage.removeItem('LS_THEME');
    }
}

function refreshElement(element) {
    const content = element.innerHTML;
    element.innerHTML = '';
    element.innerHTML = content;
}

function enableDarkThemeStyle() {
    const darkThemeStyleEl = document.getElementById('dark-theme-style');
    darkThemeStyleEl.removeAttribute('media');

    // Needed for older browsers, like Safari 9
    refreshElement(darkThemeStyleEl);

    document.querySelector('meta[name=msapplication-TileColor]').setAttribute('content', '#1d1d1d');
    document.querySelector('meta[name=theme-color]').setAttribute('content', '#1d1d1d');
}

function disableDarkThemeStyle() {
    const darkThemeStyleEl = document.getElementById('dark-theme-style');
    darkThemeStyleEl.setAttribute('media', 'max-width: 1px');

    document.querySelector('meta[name=msapplication-TileColor]').setAttribute('content', '#8cc63e');
    document.querySelector('meta[name=theme-color]').setAttribute('content', '#8cc63e');
}
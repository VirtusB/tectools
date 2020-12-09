document.querySelector('div.contentArea.contentPadding').addEventListener('click', function (el) {
    $(el.target.nextElementSibling).css('transition', 'unset');
    $(el.target.nextElementSibling).slideToggle();
});
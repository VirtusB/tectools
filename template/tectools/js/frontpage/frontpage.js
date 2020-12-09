var lastEl;

document.addEventListener('DOMContentLoaded', function() {
    let categorySelect = document.getElementById('category-select');

    let instance = M.FormSelect.init(categorySelect);

    let height = $('#category-select-col').height();

    $('#category-select-col').next().height(height);

    $(document).ready(function(){
        $('.carousel').carousel({
            onCycleTo: function (el) {
                var currentName = el.querySelector('.tool-name');

                if (lastEl !== undefined) {
                    var lastName = lastEl.querySelector('.tool-name');

                    animateCSS(lastName, 'fadeOut', false, true);

                    el.querySelector('.tool-name').style.visibility = 'visible';
                    animateCSS(currentName, 'fadeIn', false);

                } else {
                    el.querySelector('.tool-name').style.visibility = 'visible';
                    animateCSS(currentName, 'fadeIn', false);
                }

                lastEl = el;

            }
        });
    });
});
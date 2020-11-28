/*
Denne fil indeholder klient kode som køres på "Rediger værktøj" og "Opret værktøj" siderne
Side: /tools/edit
Side /tools/create
Layout: /tools/edit.php
Layout: /tools/create.php
 */

/**
 * Quill er en rich-text editor
 * WYSIWYG
 * https://quilljs.com/
 */
var quill = new Quill('#des-editor', {
    placeholder: 'Indtast beskrivelse',
    theme: 'bubble',
    modules: {
        toolbar: [
            ['bold', 'italic', 'underline', 'link'],        // toggled buttons

            [{ 'list': 'ordered'}, { 'list': 'bullet' }],

            [{ 'header': [1, 2, 3, 4, 5, 6, false] }],

            [{ 'color': [] }, { 'background': [] }],          // dropdown with defaults from theme
            [{ 'align': [] }],

            ['clean']                                         // remove formatting button
        ]
    }
});

quill.on('text-change', function(delta, oldDelta, source) {
    console.log(quill.container.firstChild.innerHTML)
    $('#description').val(quill.container.firstChild.innerHTML);
});

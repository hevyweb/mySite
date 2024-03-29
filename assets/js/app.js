require('@fortawesome/fontawesome-free/js/all.js');
require('bootstrap');
const WOW = require('wowjs');

$(function($){
    let message = $('.toast');
    if (message.length) {
        message.toast({
            'autohide': false
        }).toast('show');
        message.find('.flash-close').click(function(){
            message.toast('dispose');
        });
    }

    window.wow = new WOW.WOW({ live: false });
    window.wow.init();

    tinymce.init({
        selector: '.html-editor',
        images_upload_url: '/administrator/images/upload',
        automatic_uploads: true,
        setup: function (editor) {
            editor.on('change', function () {
                editor.save();
            });
        },
        language: settings.language,
        plugins: 'image table code',
        toolbar_mode: 'wrap',
        toolbar:
            'undo redo ' +
            '| backcolor forecolor blocks fontfamily fontsize ' +
            '| bold italic underline ' +
            '| alignleft aligncenter alignjustify alignright' +
            '| allignment removeformat ' +
            '| table image ' +
            '| code ',

    });


    // Back to top button
    $(window).scroll(function () {
        if ($(this).scrollTop() > 200) {
            $('.back-to-top').fadeIn('slow');
        } else {
            $('.back-to-top').fadeOut('slow');
        }
    });

    $('.back-to-top').click(function () {
        $('html, body').animate({scrollTop: 0}, 1500, 'easeInOutExpo');
        return false;
    });

    (function () {
        'use strict'

        // Fetch all the forms we want to apply custom Bootstrap validation styles to
        var forms = document.querySelectorAll('.needs-validation')

        // Loop over them and prevent submission
        Array.prototype.slice.call(forms)
            .forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }

                    form.classList.add('was-validated')
                }, false)
            })
    })()
});
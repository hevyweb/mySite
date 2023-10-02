require('@fortawesome/fontawesome-free/js/all.js');
require('jquery-ui/ui/widgets/datepicker');
require('jquery-ui/ui/i18n/datepicker-uk');
require('bootstrap');

const WOW = require('wowjs');

jQuery(function($){
    /*$('.js-datepicker').datepicker({
        changeMonth: true,
        changeYear: true,
        yearRange: "1930:2021",
        dateFormat: settings.date_format,
        firstDay: 1,
    })
        .datepicker( $.datepicker.regional[ settings.language ] );
*/
    let message = $('.toast');
    if (message.length) {
        $(message).toast({
            'autohide': false
        }).toast('show');
        $(message).find('.flash-close').click(function(){
            $(message).toast('dispose');
        });
    }

    // loader
    var loader = function () {
        setTimeout(function () {
            if ($('#loader').length > 0) {
                $('#loader').removeClass('show');
            }
        }, 1);
    };
    loader();


    window.wow = new WOW.WOW({ live: false });
    window.wow.init();
    tinymce.init({
        selector: '.html-editor',
        setup: function (editor) {
            editor.on('change', function () {
                editor.save();
            });
        }
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
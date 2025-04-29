import CyrillicToTranslit from 'cyrillic-to-translit-js';

jQuery(function($){
    $('.slug-button').click(function(){
        const cyrillicToTranslit = new CyrillicToTranslit();

        $('.slug-input').val(
            convertToSlug(cyrillicToTranslit.transform($('.slug-source').val()))
        );
    });

    $('#delete-articles').click(function () {
        $('#article-list').submit();
    });
    
    setInterval(function(){
        let form = $('form').serialize();

        if ($('form').find('#form_article_slug').val()) {
            $.ajax({
                type: 'POST',
                url: $('#article-form-update').attr('data-url'),
                data: form,
                success: function (response, status, xhr) {
                    if (response.url) {
                        $('form').attr('action', response.action);
                        $('<div />').text(response.message).addClass('alert alert-success')
                            .click(function () {
                                $(this).remove();
                            })
                            .appendTo(
                                $('#article-form-update').attr('data-url', response.url)
                            ).animate({'opacity': 1}, 500, 'linear', function () {
                            $(this).animate({'opacity': 0}, 3000, 'linear', function () {
                                $(this).remove();
                            });
                        });
                    } else {
                        $('<div />').addClass('alert alert-danger').attr('role', 'alert').text('Unknown error')
                            .appendTo(
                                $('#article-form-update')
                            ).click(function () {
                            $(this).remove();
                        }).animate({'opacity': 1}, 500);
                    }
                },
                error: function (xhr) {
                    let error = 'Unknown error';
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        error = xhr.responseJSON.error;
                    }
                    $('<div />').addClass('alert alert-danger').attr('role', 'alert').text(error)
                        .appendTo(
                            $('#article-form-update')
                        ).click(function () {
                        $(this).remove();
                    }).animate({'opacity': 1}, 500);
                }
            });
        }
    }, settings.autosave);
});

function convertToSlug(Text) {
    return Text.toLowerCase()
        .replace(/ /g, "-")
        .replace(/[^\w\-]+/g, "");
}
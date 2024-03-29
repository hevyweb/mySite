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
});

function convertToSlug(Text) {
    return Text.toLowerCase()
        .replace(/ /g, "-")
        .replace(/[^\w\-]+/g, "");
}
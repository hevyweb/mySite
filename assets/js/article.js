import CyrillicToTranslit from 'cyrillic-to-translit-js';

jQuery(function($){
    $('#article_title').keyup(function(){
        const cyrillicToTranslit = new CyrillicToTranslit();

        $('#article_slug').val(
            convertToSlug(cyrillicToTranslit.transform($(this).val()))
        );
    });
});

function convertToSlug(Text) {
    return Text.toLowerCase()
        .replace(/ /g, "-")
        .replace(/[^\w-]+/g, "");
}
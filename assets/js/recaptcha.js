
window.reCaptchaRender = function()
{
    $('.g-recaptcha').each(function(){
        grecaptcha.render($(this).attr('id'), {
            'sitekey' : $(this).attr('data-sitekey'),
            'callback' : function (response) {
                let formId = $('.g-recaptcha').attr('data-control');
                $('#' + formId).val(response).siblings('.error').remove();
            }.bind(this),
        });
    });
}
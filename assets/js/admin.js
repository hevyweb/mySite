jQuery(function($){
    $('.delete').click(function(){
        if ($('.admin-panel table tbody input[type=checkbox]:checked').length) {
            if (confirm($(this).data('confirm'))) {
                $('.datagrid-form').submit();
            }
        }
    });

    $('.check-all').change(function(){
        $(this).parents('table').find('tbody input[type=checkbox]').prop('checked', $(this).prop('checked'));
    })

    $('#mark-read').click(function(e){
        if (confirm($(this).data('confirm'))){
            $('#message-form').attr('action', $(this).data('href')).submit();
        }
    });
});
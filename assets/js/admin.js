jQuery(function($){
    $('.delete').click(function(){
        if ($('.admin-panel table tbody input[type=checkbox]:checked').length) {
            if (confirm($(this).attr('data-confirm'))) {
                $('.datagrid-form').submit();
            }
        }
    });

    $('.check-all').change(function(){
        $(this).parents('table').find('tbody input[type=checkbox]').prop('checked', $(this).prop('checked'));
    })
});
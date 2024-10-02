jQuery(document).ready(function ($) {
    'use strict';
    $('.viw2s-clear-data-btn').on('click', function () {

        let button = $(this);
        let store_name = button.closest('table').find('.store_name_clear').val();
        let data_clear = button.data('clear');
        let data_clear_title = button.data('clear_title');
        button.addClass('loading');
        if (confirm("You definitely want to delete the data " + data_clear_title + "?")) {
            $.ajax({
                url: viw2s_clear_param.url,
                type: 'POST',
                dataType: 'JSON',
                data: {
                    action: 'action_clear_data',
                    data_clear: data_clear,
                    store_name: store_name,
                    viw2s_nonce: viw2s_clear_param.viw2s_nonce,
                },
                success: function (res) {
                    if (res.status === 'success') {
                        button.closest('td').find('.mess-clear-data').text(data_clear_title + ' data deleted');
                        alert("Remove data " + data_clear_title + " successful!");
                    }
                    button.removeClass('loading');
                },
                error: function (err) {
                    console.log(err);

                },

            });
        } else {
            button.removeClass('loading');
        }
        return false;
    });

});

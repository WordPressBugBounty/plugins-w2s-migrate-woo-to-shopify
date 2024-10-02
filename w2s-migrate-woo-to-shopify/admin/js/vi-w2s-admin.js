jQuery(document).ready(function ($) {

    'use strict';
    /*
    global viw2s_i18n_params
    */
    $('.vi-ui.accordion').vi_accordion();
    $('.vi-ui.dropdown').dropdown();
    $('.vi-ui.checkbox').unbind().checkbox();

    /*Add new row store info */
    $(document).on('click', '.viw2s-add-store-options', function () {

        alert("This feature is available on the premium version");
        return false;
    });
    /*remove row store info */
    $(document).on('click', '.remove-store', function () {

        alert("This feature is available on the premium version");
        return false;
    });

    /*Check all*/
    $(document).on('change', '.viw2s-import-element-enable-bulk', function () {
        $('.viw2s-import-element-enable').prop('checked', $(this).prop('checked'));
    });

    /*Selector is $('.viw2s-import-store-enable-bulk') */
    function __viw2s_import_store_enable_bulk(selector) {
        selector.on('change', function () {
            let t = $(this),
                tChecked = t.prop('checked');

            $('.viw2s-choose-store').each(function (e) {
                let th = $(this);
                if (th.prop('disabled')) {
                    return;
                } else {
                    th.prop('checked', tChecked);
                }
            }).trigger('change');
        });
    }

    // __viw2s_import_store_enable_bulk($('.viw2s-import-store-enable-bulk'));
    // $('.viw2s-import-store-enable-bulk').trigger('change');
    /*remove row store info */
    $(document).on('click', '.viw2s-next-back-btn', function () {
        let $this = $(this),
            next_atr = $this.attr('data-target-step');
        __viw2s_next_back_btn($this, next_atr);
        return false;
    });

    function __viw2s_next_back_btn(selector, next_atr) {
        let list_content_steps = $('.viw2s-step-import-settings'),
            list_steps = $('.viw2s-list-steps-import.steps .step');
        list_content_steps.removeClass('active');
        list_steps.removeClass('active');
        list_steps.each(function () {
            let th = $(this),
                current_step = th.attr('data-step');
            if (current_step === next_atr) {
                th.addClass('active');
            }
        });
        list_content_steps.each(function () {
            let th = $(this),
                current_step = th.attr('data-step');
            if (current_step === next_atr) {
                th.addClass('active');
            }
        });
    }

    $('#viw2s_product_collection_id').select2({
        // theme: "classic",
        minimumInputLength: 2,
        dropdownParent: $('#viw2s_product_collection_id').parent(),
        placeholder: viw2s_i18n_params.i18n_search_product_placeholder,
        closeOnSelect: false,
        ajax: {
            type: 'post',
            url: viw2s_i18n_params.ajaxurl,
            data: function (params) {
                let exclude_product_ids = $('#viw2s_product_exclude_id').val();

                console.log(exclude_product_ids)
                return {
                    keysearch: params.term,
                    exclude_product_ids: exclude_product_ids,
                    action: 'viw2s_ajax_search_product'
                };
            },
            processResults: function (data) {
                // Transforms the top-level key of the response object from 'items' to 'results'

                let newOption = new Option(data.text, data.id, false, false);

                $('#viw2s_product_collection_id').append(newOption);
                return {
                    results: data
                };
            }
        }
    });
    $('#viw2s_product_exclude_id').select2({
        // theme: "classic",
        minimumInputLength: 2,
        dropdownParent: $('#viw2s_product_exclude_id').parent(),
        placeholder: viw2s_i18n_params.i18n_search_product_placeholder,
        closeOnSelect: false,
        ajax: {
            type: 'post',
            url: viw2s_i18n_params.ajaxurl,
            data: function (params) {
                let exclude_product_ids = $('#viw2s_product_collection_id').val();

                console.log(exclude_product_ids)
                return {
                    keysearch: params.term,
                    exclude_product_ids: exclude_product_ids,
                    action: 'viw2s_ajax_search_product'
                };
            },
            processResults: function (data) {
                // Transforms the top-level key of the response object from 'items' to 'results'

                let newOption = new Option(data.text, data.id, false, false);

                $('#viw2s_product_exclude_id').append(newOption);
                return {
                    results: data
                };
            }
        }
    });
    $('#viw2s_product_categories_include_id').select2({
        // theme: "classic",
        minimumInputLength: 2,
        dropdownParent: $('#viw2s_product_categories_include_id').parent(),
        closeOnSelect: false,
        ajax: {
            type: 'post',
            url: viw2s_i18n_params.ajaxurl,
            data: function (params) {
                return {
                    keysearch: params.term,
                    action: 'viw2s_ajax_search_product_cat'
                };
            },
            processResults: function (data) {
                // Transforms the top-level key of the response object from 'items' to 'results'

                let newOption = new Option(data.text, data.id, false, false);

                $('#viw2s_product_categories_include_id').append(newOption);
                return {
                    results: data
                };
            }
        }
    });


    $(document).on('click', '.viw2s-save-settings', function () {
        let $this = $(this);
        let validate = __validate_input_setting();

        if (validate) {
            $this.addClass('loading');
            $('#viw2s_setting_form').addClass('loading');
            $('.viw2s-settings-import-container').addClass('loading');

        } else {
            return false;
        }

        // $this.removeClass('loading');
        // $this.data('changed', false);

    });

    // $("form#viw2s_setting_form :input, form#viw2s_setting_form select").on('change', function () {
    //     $(this).closest('form').data('changed', true);
    // });

    let progress_bars = {};
    let selected_elements = [];

    function get_selected_elements() {
        selected_elements = [];
        progress_bars = [];
        $('.viw2s-import-element-enable').map(function () {
            if ($(this).prop('checked')) {
                let element_name = $(this).data('element_name');
                selected_elements.push(element_name);
                progress_bars[element_name] = $('#viw2s-' + element_name.replace('_', '-') + '-progress');
            }
        });
    }

    function __validate_input_setting() {
        let store_address = $('.viw2s_store_domain'),
            store_api_key = $('.viw2s_store_api_key'),
            store_api_secret = $('.viw2s_store_api_secret'),
            validate = true,
            validate_store_address = true,
            validate_store_api_key = true,
            validate_store_api_secret = true,
            error_message = '';

        store_address.each(function () {
            let th = $(this);
            if (th.val() === '') {
                validate = false;
                validate_store_address = false;
            }
        });
        store_api_key.each(function () {
            let th = $(this);
            if (th.val() === '') {
                validate = false;
                validate_store_api_key = false;
            }
        });
        store_api_secret.each(function () {
            let th = $(this);
            if (th.val() === '') {
                validate = false;
                validate_store_api_secret = false;
            }
        });
        if (!validate_store_address) {
            error_message += viw2s_i18n_params.i18n_empty_store_address_error;
        }
        if (!validate_store_api_key) {
            error_message += viw2s_i18n_params.i18n_empty_store_api_key_error;
        }
        if (!validate_store_api_secret) {
            error_message += viw2s_i18n_params.i18n_empty_store_api_secret_error;
        }
        if (!validate) {
            alert(error_message);
        }
        return validate;
    }

    function __validate_input_import() {
        let store_import = $('.viw2s-choose-store'),
            data_import = $('.viw2s-import-element-enable'),
            validate = true,
            validate_store_import = false,
            validate_data_import = false,

            error_message = '';
        store_import.each(function () {
            let th = $(this);
            if (th.is(':checked')) {
                validate_store_import = true;
            }
        });
        data_import.each(function () {
            let th = $(this);
            if (th.is(':checked')) {
                validate_data_import = true;
            }
        });

        if (!validate_store_import) {
            error_message += viw2s_i18n_params.i18n_empty_choose_store_import_error;
            validate = false;
        }
        if (!validate_data_import) {
            error_message += viw2s_i18n_params.i18n_empty_choose_data_import_error;
            validate = false;
        }

        if (!validate) {
            alert(error_message);
        }
        return validate;
    }

    function __update_attr_input_row(selector = '') {
        if (selector === '') {
            return;
        }

        let count_row = 0;
        selector.each(function () {

            let node = $(this);
            $(node).find('.viw2s_store_number').html(count_row + 1);
            $(node).find('.viw2s_store_domain').attr("name", "viw2s_store_setting[" + count_row + "][domain]");
            $(node).find('.viw2s_store_domain').attr("id", "viw2s_domain-" + count_row);
            $(node).find('.viw2s_store_api_key').attr("name", "viw2s_store_setting[" + count_row + "][api_key]");
            $(node).find('.viw2s_store_api_key').attr("id", "viw2s_api_key-" + count_row);
            $(node).find('.viw2s_store_api_secret').attr("name", "viw2s_store_setting[" + count_row + "][api_secret]");
            $(node).find('.viw2s_store_api_secret').attr("id", "viw2s_api_secret" + count_row);

            count_row++;
        });
    }

    let save_active = false,
        import_complete = false,
        total_products = 0,
        total_categories = 0,
        product_index = 0,
        categories_index = 0,
        current_import_product_id = 0,
        current_import_product_category_id = 0,
        current_import_product_type = '',
        error_log = '',
        _viw2s_action_import_nonce = '',
        import_active = false;

    $(document).on('click', '.viw2s-import-btn', function () {

        let $this = $(this),
            form_import = $('.viw2s-settings-import-container'),
            next_atr = $this.attr('data-target-step');
        let validate = __validate_input_import();


        if ($('#viw2s_setting_form').data('changed')) {
            alert('Save Form be changed');
        }
        get_selected_elements();
        if (validate) {
            let imported = [];
            for (let i in selected_elements) {
                let element = selected_elements[i];

                imported.push(element);
            }
            _viw2s_action_import_nonce = $('#_viw2s_action_import_nonce').val();
            // __viw2s_next_back_btn($this, next_atr);

            form_import.addClass('loading');
            $.ajax({
                url: viw2s_i18n_params.ajaxurl,
                type: 'post',
                data: 'action=viw2s_ajax_active_import&' + form_import.serialize(),
                success: function (response) {

                    form_import.removeClass('loading');
                    $('.viw2s_wrap_logs').show();
                    if (response.status === 'success') {
                        if (response.total_products > 0) {
                            $('.viw2s-import-progress').css({'visibility': 'hidden'});
                            for (let ele in progress_bars) {
                                progress_bars[ele].css({'visibility': 'visible'});
                                progress_bars[ele].progress('set label', 'Waiting...').progress('set percent', 0);
                            }
                            total_products = parseInt(response.total_products);
                            total_categories = parseInt(response.total_categories);
                            current_import_product_id = response.current_import_product_id;
                            current_import_product_type = response.current_import_product_type;
                            product_index = 0;
                            categories_index = 0;
                            vis2w_import_element();
                        } else {
                            if (response.logs) {
                                $('.viw2s-logs').append(response.logs).scrollTop($('.viw2s-logs')[0].scrollHeight);
                            }
                        }

                    } else if (response.status === 'store_setting_error') {
                        alert('Store setting error');
                    } else if (response.status === 'error_nonce') {
                        alert('Error nonce, please reload and try again!');
                    } else if (response.status === 'permission_not_correct') {
                        if (response.logs) {
                            $('.viw2s-logs').append(response.logs).scrollTop($('.viw2s-logs')[0].scrollHeight);
                        }
                    }

                },
                error: function (response) {
                    console.log(response)
                }

            });
        }

        return false;
    });

    function viw2s_import_products() {

        if (total_products === 0) {
            return;
        }

        $.ajax({
            url: viw2s_i18n_params.ajaxurl,
            type: 'POST',
            dataType: 'JSON',
            data: {
                action: 'viw2s_ajax_import_action',
                step: 'products',
                total_products: total_products,
                product_index: product_index,
                current_import_product_id: current_import_product_id,
                current_import_product_type: current_import_product_type,
                _viw2s_action_import_nonce: _viw2s_action_import_nonce,

            },
            success: function (response) {
                if (response.status === 'retry') {
                    total_products = parseInt(response.total_products);
                    let try_current_import_product_id = response.current_import_product_id,
                        try_current_import_product_type = parseInt(response.current_import_product_type);
                    viw2s_import_products();
                } else if (response.status === 'error_store_setting') {
                    if (response.logs) {
                        $('.viw2s-logs').append(response.logs).scrollTop($('.viw2s-logs')[0].scrollHeight);
                    }
                    import_complete = true;
                    progress_bars['products'].progress('set label', response.message.toString());
                    progress_bars['products'].progress('set error');
                } else {
                    error_log = '';
                    progress_bars['products'].progress('set label', response.message.toString());
                    console.log(response.message.toString());
                    if (response.status === 'error') {
                        if (response.code === 'no_data') {
                            import_complete = true;
                            progress_bars['products'].progress('set error');
                            vis2w_import_element();
                        } else if (parseInt(response.code) < 400) {
                            setTimeout(function () {
                                let try_current_import_product_id = response.current_import_product_id,
                                    try_current_import_product_type = parseInt(response.current_import_product_type);
                                viw2s_import_products();
                            }, 1000)
                        }
                    } else {
                        if (response.current_import_product_id !== '') {
                            product_index = parseInt(response.imported_products);
                            current_import_product_id = parseInt(response.current_import_product_id);
                            current_import_product_type = response.current_import_product_type;
                            let imported_products = parseInt(response.imported_products);
                            let percent = Math.ceil(imported_products * 100 / total_products);
                            if (percent > 100) {
                                percent = 100;
                            }
                            progress_bars['products'].progress('set percent', percent);
                            if (response.logs) {
                                $('.viw2s-logs').append(response.logs).scrollTop($('.viw2s-logs')[0].scrollHeight);
                            }
                            if (response.status === 'successful') {
                                if (response.imported_products <= total_products) {

                                    viw2s_import_products();
                                } else {
                                    import_complete = true;
                                    progress_bars['products'].progress('complete');
                                    if (!import_complete) {
                                        selected_elements.unshift('products');
                                    }
                                    $('.viw2s-logs').append('/*============================================================================*/').scrollTop($('.viw2s-logs')[0].scrollHeight);

                                    vis2w_import_element();
                                }
                            } else if (response.status === 'error_access_scope' || response.status === 'error_none') {
                                import_complete = true;
                                progress_bars['products'].progress('set label', response.message.toString());
                                progress_bars['products'].progress('set error');
                            } else {
                                import_complete = true;

                                console.log('finish');
                                progress_bars['products'].progress('set label', response.message.toString());
                                progress_bars['products'].progress('complete');
                                vis2w_import_element();
                            }
                        } else {
                            import_complete = true;
                            progress_bars['products'].progress('set label', response.message.toString());
                            progress_bars['products'].progress('set error');
                        }

                    }
                }
            },
            error: function (err) {
                error_log = 'error ' + err.status + ' : ' + err.statusText;
                progress_bars['products'].progress('set error');
                if (!import_complete) {
                    selected_elements.unshift('products');
                }
                setTimeout(function () {
                    vis2w_import_element();
                }, 3000);
            }
        })
    }

    function viw2s_import_product_categories() {

        $.ajax({
            url: viw2s_i18n_params.ajaxurl,
            type: 'POST',
            dataType: 'JSON',
            data: {
                action: 'viw2s_ajax_import_action',
                step: 'product_categories',
                categories_index: categories_index,
                current_import_product_category_id: current_import_product_category_id,
                total_categories: total_categories,
                _viw2s_action_import_nonce: _viw2s_action_import_nonce,
            },
            success: function (response) {
                if (response.logs) {
                    $('.viw2s-logs').append(response.logs).scrollTop($('.viw2s-logs')[0].scrollHeight);
                }
                if (response.status === 'retry') {
                    categories_index = parseInt(response.categories_index);
                    total_categories = parseInt(response.total_categories);
                    current_import_product_category_id = parseInt(response.current_import_product_category_id);
                    viw2s_import_product_categories();
                } else if (response.status === 'success') {
                    categories_index = parseInt(response.categories_index);
                    total_categories = parseInt(response.total_categories);
                    current_import_product_category_id = parseInt(response.current_import_product_category_id);
                    let percent = categories_index * 100 / total_categories;
                    progress_bars['product_categories'].progress('set percent', percent);
                    progress_bars['product_categories'].progress('set label', response.message.toString());
                    viw2s_import_product_categories();
                } else if (response.status === 'exist') {
                    categories_index = parseInt(response.categories_index);
                    total_categories = parseInt(response.total_categories);
                    current_import_product_category_id = parseInt(response.current_import_product_category_id);
                    let percent = categories_index * 100 / total_categories;
                    progress_bars['product_categories'].progress('set percent', percent);
                    progress_bars['product_categories'].progress('set label', response.message.toString());
                    viw2s_import_product_categories();
                } else if (response.status === 'error') {
                    categories_index = parseInt(response.categories_index);
                    total_categories = parseInt(response.total_categories);
                    current_import_product_category_id = parseInt(response.current_import_product_category_id);
                    let percent = categories_index * 100 / total_categories;
                    progress_bars['product_categories'].progress('set percent', percent)
                    progress_bars['product_categories'].progress('set label', response.message.toString());
                    progress_bars['product_categories'].progress('set error');
                    setTimeout(function () {
                        viw2s_import_product_categories();
                    }, 1000);

                } else if (response.status === 'error_none' || response.status === 'error_store_setting') {

                    import_complete = true;
                    progress_bars['products'].progress('set label', response.message.toString());
                    progress_bars['products'].progress('set error');

                } else {
                    categories_index = parseInt(response.categories_index);
                    total_categories = parseInt(response.total_categories);
                    current_import_product_category_id = parseInt(response.current_import_product_category_id);
                    progress_bars['product_categories'].progress('set label', response.message.toString());
                    progress_bars['product_categories'].progress('complete');
                    vis2w_import_element();
                }
            },
            error: function (err) {


                error_log = 'error ' + err.status + ' : ' + err.statusText;
                progress_bars['product_categories'].progress('set error');
                if (!import_complete) {
                    selected_elements.unshift('product_categories');
                }
                setTimeout(function () {
                    vis2w_import_element();
                }, 3000);
            }
        })
    }

    function vis2w_import_element() {
        if (selected_elements.length) {
            let element = selected_elements.shift();
            progress_bars[element].progress('set label', 'Importing...');
            progress_bars[element].progress('set active');
            switch (element) {
                case 'products':
                    viw2s_import_products();
                    break;
                case 'product_categories':
                    viw2s_import_product_categories();
                    break;

            }
        } else {
            vis2w_unlock_buttons();
            import_active = false;
            $('.viw2s-sync').removeClass('loading');
            setTimeout(function () {
                alert('Import completed.');
            }, 400);
        }
    }

    function vis2w_lock_buttons() {
        $('.viw2s-import-element-enable').prop('readonly', true);
    }

    function vis2w_unlock_buttons() {
        $('.viw2s-import-element-enable').prop('readonly', false);
    }
    $( '.viw2s-help-tip' ).tipTip( {
        'attribute': 'data-tip',
        'fadeIn': 50,
        'fadeOut': 50,
        'delay': 200,
        'keepAlive': true
    } );
});


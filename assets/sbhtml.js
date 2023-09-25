jQuery(function ($) {

    var ajaxurl = window.location.origin + '/wp-admin/admin-ajax.php';
    // console.log(ajaxurl);

    /* ********** */
    /* BACK/ADMIN */
    /* ********** */

    // find table
    var table = '.sbhtml_tab_pane.active .sbhtml_size_table';
    // get number of rows
    var num_rows = $(table).find('tr').length - 1;
    // get number of columns
    var num_cols = $(table).find('th').length - 1;
    // some of other hidden input
    var h_table_input = $('#sbhtml_table_hidden');

    // build col
    build_col = function (cell_id) {
        var tmp_col_btn = '<th><a href="javascript:void(0);" class="sbhtml_add_col">+</a><a href="javascript:void(0);" class="sbhtml_del_col">-</a></th>';
        var tmp_col = '<td contenteditable="true" class="core-data" data-unit_cm="click to edit" data-unit_in="">click to edit</td>';
        $(table).find('thead tr').find('th:eq(' + cell_id + ')').after(tmp_col_btn);
        $(table).find('tbody tr').each(function (i_tr, e_tr) {
            $(e_tr).find('td').each(function (i_td, e_td) {
                if ($(e_td).attr('colspan')) {
                    cell_id -= $(e_td).attr('colspan') - 1;
                }
            });
            if (cell_id <= 0) {
                $(e_tr).find('td:eq(' + cell_id + ')').before(tmp_col);
            } else {
                $(e_tr).find('td:eq(' + cell_id + ')').after(tmp_col);
            }
        });
    };

    // remove col
    remove_col = function (cell_id) {
        $(table).find('thead tr').find('th:eq(' + cell_id + ')').remove();
        $(table).find('tbody tr').each(function () {
            $(this).find('td:eq(' + cell_id + ')').remove();
        });
    };

    // build row
    build_row = function (num_cols) {
        var tmp_row = '<tr>';
        for (var i = 0; i < num_cols; i++) {
            tmp_row += '<td contenteditable="true" class="core-data" data-unit_cm="click to edit" ' + (i >= 2 ? "data-unit_in=''" : "") + '>click to edit</td>';
        }
        tmp_row += '<td class="sbhtml_table_btn_container"><a href="javascript:void(0);" class="sbhtml_add_row">+</a><a href="javascript:void(0);" class="sbhtml_del_row">-</a></td>';
        tmp_row += '</tr>';
        return tmp_row;
    };

    // create matrix from table (not sure what this does)
    create_matrix_from_table = function () {
        var tmp_matrix = [];

        $(table).find('tbody tr').each(function () {
            var cols = [];
            var all_td = $(this).find('td');

            all_td.each(function () {
                if (!$(this).is('.sbhtml_table_btn_container')) {
                    var tmp_value = $(this).find('input').val();
                    cols.push(tmp_value);
                }
            });
            tmp_matrix.push(cols);
        });

        h_table_input.val(JSON.stringify(tmp_matrix));
    };

    // table / add row on click
    $(document).on('click', '.sbhtml_add_row', function () {
        var this_cell = $(this).closest('td');
        var this_row = this_cell.closest('tr');
        // num_rows++;

        var num_rows = 0;
        this_row.find('.core-data').each(function (i, e) {
            var colsp = $(e).attr('colspan');
            num_rows += parseInt(colsp ? colsp : 1);
        });

        this_row.after(build_row(num_rows));
        create_matrix_from_table();
    });

    // table / del row on click
    $(document).on('click', '.sbhtml_del_row', function () {
        var num_rows = $(this).closest('tbody').find('tr').length;

        if (num_rows < 2) {
            return;
        }
        var this_cell = $(this).closest('td');
        var this_row = this_cell.closest('tr');
        num_rows--;
        this_row.remove();
        create_matrix_from_table();
    });

    // table / add col on click
    $(document).on('click', '.sbhtml_add_col', function () {
        var this_cell = $(this).closest('th');
        var cell_id = this_cell.index();
        num_cols++;
        build_col(cell_id);

        create_matrix_from_table();
    });

    // table / del col on click
    $(document).on('click', '.sbhtml_del_col', function () {
        var num_cols = $(this).closest('.sbhtml_size_table').find('tbody > tr:first > td.core-data').length;

        if (num_cols < 2) {
            return;
        }
        var this_cell = $(this).closest('th');
        var cell_id = this_cell.index();
        num_cols--;
        remove_col(cell_id);
        create_matrix_from_table();
    });

    // table on keyup/input
    $(document).on('keyup', 'input', function (event) {
        var this_input = $(event.target);
        var value = this_input.val();

        //alert(value);
        // remove html tags
        if (value.search(/<[^>]+>/ig) > 0 || value.search('<>') > 0) {
            this_input.val(value.replace(/<[^>]+>/ig, '').replace('<>', ''));
        }
        create_matrix_from_table();
    });

    // replace core chart data with translated strings via ajax on page load
    // $('td.core-data').each(function () {
    //     var wer_string = $(this).text().trim();
    //     if (wer_string != 'click to edit') {
    //         var data = {
    //             'action': 'sbhtmlc_save_data',
    //             'pll_translate': wer_string
    //         };

    //         $.post(ajaxurl, data, function (response) {
    //             // console.log(response);
    //         });
    //     }
    // });

    // change conversion
    $(document).on('change', '#sbhtml_backend_btn_conv input[name=unit_conversion]', function (e) {
        var unit_type = $(this).val();

        $("#sbhtml_chart_data_body > tr td.core-data").each(function (c_i, c_e) {
            let val_u = $(c_e).attr('data-unit_' + unit_type);
            if (val_u) {
                $(c_e).text(val_u);
            }
        });
    });

    // change input td
    $(document).on("keyup", "#sbhtml_chart_data_body > tr td.core-data", function (e) {
        var unit_type = $('#sbhtml_backend_btn_conv input[name=unit_conversion]:checked').val();

        var value = $(e.target).text();
        $(e.target).attr('data-unit_' + unit_type, value);

        // convert cm - inch 
        if (!$(e.target).hasClass('highlight') && !isNaN(value)) {
            if (unit_type == 'cm') {
                $(e.target).attr('data-unit_in', convertUnit(value, 'in'));
            } else if (unit_type == 'in') {
                $(e.target).attr('data-unit_cm', convertUnit(value, 'cm'));
            }
        }
    });

    // func convert cm -> in <-
    function convertUnit(unit_value, toType) {
        if (toType == 'in') {
            return (unit_value * 0.39370079).toFixed(1);
        }
        if (toType == 'cm') {
            return (unit_value * 2.54).toFixed(0);
        }
        else {
            return false;
        }
    }

    /* SAVE CHART */
    var saveChart = $('#sbhtml_save');

    $(saveChart).on('click', function (e) {
        e.preventDefault();


        var chart_array = {};
        $('#sbhtml_table_wrapper .sbhtml_tab_pane').each(function (i, e) {
            var tab_locale = $(this).attr('data-name');

            // chart array
            var tr_body = {};
            $(this).find('.sbhtml_size_table > tbody tr').each(function (i_tr, el_tr) {

                var td_body = {};
                $(el_tr).find('td').each(function (i_td, el_td) {

                    if ($(el_td).hasClass("sbhtml_table_btn_container")) {
                        return;
                    }

                    var el_cm = $(el_td).attr('data-unit_cm') || !isNaN($(el_td).attr('data-unit_cm')) ? $(el_td).attr('data-unit_cm') : convertUnit($(el_td).attr('data-unit_in'), 'cm');
                    var el_in = $(el_td).attr('data-unit_in') || !isNaN($(el_td).attr('data-unit_in')) ? $(el_td).attr('data-unit_in') : convertUnit($(el_td).attr('data-unit_cm'), 'in');

                    td_body[i_td] = {
                        class: $(el_td).attr('class'),
                        colspan: $(el_td).attr('colspan'),
                        value: el_cm,
                        unit_in: el_in
                    };

                });

                tr_body[i_tr] = td_body;
            });

            chart_array[tab_locale] = tr_body;

        });

        // save
        var chart = $('.sbhtml_size_table').html();
        // remove line breaks
        chart = chart.replace(/(\r\n|\n|\r)/gm, "");
        // remove unnecessary spaces
        chart = chart.replace(/\s+/g, " ");

        var postId = $(this).attr('post');
        var chdata = {
            'action': 'sbhtmlc_save_data',
            'save_chart': chart,
            'chart_array': chart_array,
            'product_id': postId
        };

        $.post(ajaxurl, chdata, function (response) {
            alert(response);
            // location.reload();
            // console.log(response);
        });

    });

    /* DELETE CHART */
    var deleteChart = $('#sbhtml_delete');

    $(deleteChart).on('click', function (e) {
        e.preventDefault();

        var postId = $(this).attr('post');
        var deldata = {
            'action': 'sbhtmlc_save_data',
            'del_chart_id': postId
        };

        $.post(ajaxurl, deldata, function (response) {
            alert(response);
            location.reload();
        });
    });

    /* CHANGE TEXT TO BOLD/ADD BACKGROUND/MERGE CELLS */
    var chartbody = $('#sbhtml_chart_data_body');

    $(chartbody).on('click', 'td', function () {

        // add highlight
        if (window.event.ctrlKey) {
            $(this).toggleClass('highlight');
        }

        // add merge highlight
        if (window.event.shiftKey) {
            $(this).toggleClass('merge');
        }
    });

    // MERGE CELLS
    $('button#sbhtml_merge').on('click', function (e) {
        e.preventDefault();
        var mergeCount = $('.merge').size();
        $('.merge').first().attr('colspan', mergeCount);
        $('.merge').first().removeClass('merge').addClass('merged');
        $('.merge').each(function () {
            $(this).remove();
        });
    });

    // GATHER CHART DATA PRIOR TO SAVING
    $('#sbhtml_save').on('mousenter', function () {
        var chartdata = $('tbody#sbhtml_chart_data_body').html();
        chartdata = JSON.stringify(chartdata);
        $('#sbhtml_chart_data').val(chartdata);
    });


    // UPLOAD CHART IMAGE
    $('button#sbhtml_ul_image').on('click', function (e) {

        e.preventDefault();

        /* create new form data instance - need this to send image data across ajax */
        var formData = new FormData();

        /* get supplied file data and append to form data var */
        var prodId = $('button#sbhtml_ul_image').attr('post');
        var imgInput = document.getElementById('sbhtml_image');
        var imgFile = imgInput.files[0];
        formData.append('sbhtml_image', imgFile);

        /* append wp_ajax action to form data, otherwise send won't work */
        formData.append('action', 'sbhtml_save_chart_img');

        /* append product id to form data so that we update the correct product's post meta */
        formData.append('product_id', prodId);

        /* throw error if img not supplied, else send ajax request and update product meta */
        if (!imgFile) {
            alert('Please upload valid chart image!');
        } else {
            $('button#sbhtml_ul_image').text('UPLOADING...');
            $.ajax({
                url: ajaxurl,
                cache: false,
                contentType: false,
                processData: false,
                data: formData,
                type: 'POST',
                success: function (result) {

                    $('button#sbhtml_ul_image').text('UPLOADED');
                    /* parse returned data to var */
                    var returnData = $.parseJSON(result);

                    /* get attached img url and display img */
                    var uploaded = returnData.uploaded;

                    if (uploaded == 'yes') {
                        window.alert('Size chart image successfully uploaded');
                        window.location.reload();
                    }
                },
                error: function (error) {
                    console.log(error);
                }
            });
        }
    });

    // GLOBAL CHART IMAGE SHORTCODE ADD/UPDATE
    $('button#sbhtml_img_global_sc_save').on('click', function (e) {
        e.preventDefault();
        // get size chart img SHORTCODE
        var shortcode = $('#sbhtml_img_global_sc').val();

        // data
        var data = {
            'action': 'sbhtmlc_save_data',
            'gshortcode': shortcode
        };

        // ajax
        $.post(ajaxurl, data, function (response) {
            alert(response);
        });
    });

    // DELETE CHART IMAGE
    $('#sbhtml_img_del').on('click', function (e) {
        e.preventDefault();

        var prodid = $(this).attr('post');

        var data = {
            'action': 'sbhtmlc_save_data',
            'remove_img': prodid
        };

        $.post(ajaxurl, data, function (response) {
            alert(response);
            location.reload();
        });

    });

    // DELETE GLOBAL CHART IMAGE
    $('#sbhtml_img_del_global').on('click', function (e) {
        e.preventDefault();

        var data = {
            'action': 'sbhtmlc_save_data',
            'remove_global_img': true
        };

        $.post(ajaxurl, data, function (response) {
            alert(response);
            location.reload();
        });

    });

    // UPDATE GLOBAL NOTE
    $('#sbhtml_update_global_note').on('click', function (e) {
        e.preventDefault();

        var note = $('#sbhtml_global_note').val()

        var data = {
            'action': 'sbhtmlc_save_data',
            'global_note': note
        };

        $.post(ajaxurl, data, function (response) {
            alert(response);
            location.reload();
        });

    });

    // UPDATE PER PRODUCT SETTINGS (DISPLAY OF GLOBAL CHART IMAGE AND UNIT CONVERSION FUNCTIONALITY)

    // unit conversion enable/disable
    var conversion = $('input#sbhtml_unit_conv');

    if ($(conversion).val() == 'yes') {
        $(conversion).prop('checked', true);
    }

    $(conversion).click(function () {
        if ($(this).is(':checked')) {
            $(this).val('yes');
        } else {
            $(this).val('no');
        }
    });

    // global chart image enable/disable
    var global_img = $('input#sbhtml_gci_de');

    if ($(global_img).val() == 'yes') {
        $(global_img).prop('checked', true);
    }

    $(global_img).click(function () {
        if ($(this).is(':checked')) {
            $(this).val('yes');
        } else {
            $(this).val('no');
        }
    });

    // global remarks enable/disable
    var global_rem = $('input#sbhtml_g_remarks_disable');

    if ($(global_rem).val() == 'yes') {
        $(global_rem).prop('checked', true);
    }

    $(global_rem).click(function () {
        if ($(this).is(':checked')) {
            $(this).val('yes');
        } else {
            $(this).val('no');
        }
    });

    $('button#sbhtml_save_dbs').on('click', function (e) {
        e.preventDefault();
        var product_id = $(this).attr('post');
        var global_img = $('input#sbhtml_gci_de').val();
        var conversion = $('input#sbhtml_unit_conv').val();
        var remarks = $('input#sbhtml_g_remarks_disable').val();

        var data = {
            'action': 'sbhtmlc_save_data',
            'product_id': product_id,
            'conversion': conversion,
            'global_img': global_img,
            'global_remarks': remarks
        };

        $.post(ajaxurl, data, function (response) {
            alert(response);
        });

    });


    // tab
    $('li.sbhtml_nav_item').on('click', function (e) {
        $('li.sbhtml_nav_item').removeClass('active');
        $(this).addClass('active');
        var unit_type = $(this).attr('data-target');

        $("#sbhtml_chart_data_body > tr td.core-data").each(function (c_i, c_e) {
            let val_u = $(c_e).attr('data-unit_' + unit_type);
            if (val_u) {
                $(c_e).text(val_u);
            }
        });
    });


    /* ******************** */
    /* FRONT/PRODUCT SINGLE */
    /* ******************** */

    // show chart modal link
    //check for presence of pa_size

    // check if size chart is set for current product
    var check = $('.attribute-swatch').find('[selectid="pa_size"]');

    if (!check.length) {
        check = $('.attribute-swatch').find('[selectid="pa_shoe-size"]');
        if (!check.length) {
            check = $('.attribute-swatch:last').find('.wcvaswatchlabel');

            // theme riode
            if (!check.length) {
                check = $('.pa_size');
                if (!check.length) {
                    check = $('.pa_shoe-size');
                }
            }
        }
    }

    // check if size chart is set for current product
    var table_append = $(check).closest('table.variations');
    var chart_set = $('#sbhtml-show-chart').val();

    if (check.length && chart_set) {

        var shtml_label_text = $('#sbhtml_text_label').val();
        var sbhtml_link_text = $('#sbhtml_text_open_modal').val();

        var label_text_content = '<div class="sbhtml_label_wrap">' + shtml_label_text + ' <span class="sbhtml_link_text">' + sbhtml_link_text + '</span></div>';

        // $('table.variations').find('td.label:contains("Size")').append('<span>blah</span>');

        $('<td class="label size-chart-label"><svg style="margin-right:10px;" data-v-6b417351="" width="24" viewBox="0 -4 34 30" xmlns="http://www.w3.org/2000/svg"><path d="M32.5 11.1c-.6 0-1 .4-1 1v11.8h-1.9v-5.4c0-.6-.4-1-1-1s-1 .4-1 1v5.4h-3.7v-3.6c0-.6-.4-1-1-1s-1 .4-1 1v3.6h-3.7v-3.6c0-.6-.4-1-1-1s-1 .4-1 1v3.6h-4.1v-3.6c0-.6-.4-1-1-1s-1 .4-1 1v3.6H6.4v-5.4c0-.6-.4-1-1-1s-1 .4-1 1v5.4H2.5V12.1c0-.6-.4-1-1-1s-1 .4-1 1v12.8c0 .6.4 1 1 1h31c.6 0 1-.4 1-1V12.1c0-.6-.4-1-1-1z" fill="#666666"></path><path d="M27.1 12.4v-.6c0-.1-.1-.1-.1-.2l-2.6-3c-.4-.6-1-.6-1.5-.3-.4.4-.5 1-.1 1.4L24 11H10l1.2-1.3c.4-.4.3-1-.1-1.4-.5-.3-1.1-.3-1.5.1l-2.6 3s0 .1-.1.1l-.1.1c0 .1-.1.1-.1.2v.2c0 .1 0 .1.1.2 0 .1.1.1.1.1s0 .1.1.1l2.6 3c.2.2.5.3.8.3.2 0 .5-.1.7-.2.4-.4.5-1 .1-1.4l-1.2-1h14l-1.2 1.3c-.4.4-.3 1 .1 1.4.2.2.4.2.7.2.3 0 .6-.1.8-.3l2.6-3c0-.1.1-.1.1-.2v-.1z" fill="#666666"></path></svg>' + sbhtml_link_text + '</td>').insertAfter('table.variations td.label:contains("Size")');

        // table_append.after(label_text_content);
    }

    // remove unneeded elements
    $('.sbhtml_table_front > thead').remove();
    $('.sbhtml_table_front .sbhtml_table_btn_container').remove();

    // hide modal and overlay
    $('div#sbhtml_chart_overlay, span#sbhtml_modal_close').on('click', function (e) {
        e.preventDefault();
        $('div#sbhtml_chart_overlay, div#sbhtml_chart_modal').hide();
    });

    // show modal and overlay
    $('a#sbhtml_view_size_chart, .size-chart-label, a#sbhtml_single_size_chart').on('click', function (e) {
        e.preventDefault();
        $('div#sbhtml_chart_overlay, div#sbhtml_chart_modal').show();
    });

    // stop modal
    $('div#sbhtml_chart_modal').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
    });

    // disable content editing capabilities
    $('.sbhtml_table_front td').attr('contenteditable', false);

    // convert to inches
    var convcounterin = 0;
    $('button#sbhtml_imp_units').on('click', function (e) {
        conv_unit_in();
    });
    function conv_unit_in() {
        $('button#sbhtml_met_units').removeClass('sbhtml_active');
        $(this).addClass('sbhtml_active');

        var cells = $('tbody#sbhtml_chart_data_body td');

        $.each(cells, function () {
            var value = $(this).text();
            var float = parseFloat(value);
            var converter = 0.39370079;

            if (!isNaN(float) && convcounterin == 0) {
                $(this).text((float * converter).toFixed(1));
            }

        });
        convcounterin++;
        convcountercm = 0;
    }

    // convert to centimetres
    var convcountercm = 0;
    $('button#sbhtml_met_units').on('click', function (e) {
        conv_unit_cm();
    });
    function conv_unit_cm() {

        $('button#sbhtml_imp_units').removeClass('sbhtml_active');
        $(this).addClass('sbhtml_active');

        var cells = $('tbody#sbhtml_chart_data_body td');

        $.each(cells, function () {
            var value = $(this).text();
            var float = parseFloat(value);
            var converter = 2.54;

            if (!isNaN(float) && convcountercm == 0) {
                $(this).text((float * converter).toFixed(0));
            }

        });
        convcountercm++;
        convcounterin = 0;
    }
    // change rario conv cm|in
    $('#sbhtml_front_btn_cont input[name="unit_conversion"]').change(function (e) {
        var unit_type = $(this).val();

        $("#sbhtml_chart_data_body > tr td.core-data").each(function (c_i, c_e) {
            let val_u = $(c_e).attr('data-unit_' + unit_type);
            if (val_u) {
                $(c_e).text(val_u);
            }
        });
    })

    // convert to inches -> SHORTCODE
    var convcounterin_emb = 0;
    $('button#sbhtml_imp_units_emb').on('click', function (e) {

        $('button#sbhtml_met_units_emb').removeClass('sbhtml_active');
        $(this).addClass('sbhtml_active');

        e.preventDefault();

        var cells = $('tbody#sbhtml_chart_data_body td');

        $.each(cells, function () {
            var value = $(this).text();
            var float = parseFloat(value);
            var converter = 0.39370079;

            if (!isNaN(float) && convcounterin_emb == 0) {
                $(this).text((float * converter).toFixed(1));
            }

        });
        convcounterin_emb++;
        convcountercm_emb = 0;
    });

    // convert to centimetres
    var convcountercm_emb = 0;
    $('button#sbhtml_met_units_emb').on('click', function (e) {
        e.preventDefault();

        $('button#sbhtml_imp_units_emb').removeClass('sbhtml_active');
        $(this).addClass('sbhtml_active');

        var cells = $('tbody#sbhtml_chart_data_body td');

        $.each(cells, function () {
            var value = $(this).text();
            var float = parseFloat(value);
            var converter = 2.54;

            if (!isNaN(float) && convcountercm_emb == 0) {
                $(this).text((float * converter).toFixed(0));
            }

        });
        convcountercm_emb++;
        convcounterin_emb = 0;
    });
});
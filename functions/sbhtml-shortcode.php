<?php

/**
 * Chart shortcode
 */
function sbhtml_shortcode()
{
    // as per Tony
    $dom = new DOMDocument();

    $dom->loadHTML('<html>...</html>');

    $finder = new DOMXPath($dom);
    $classname = "highlight";
    $nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");

    foreach ($nodes as $node) {
        echo $node->nodeValue . "<br />";
    }

    // get user country code
    $user_country_code = $_SERVER['HTTP_CF_IPCOUNTRY'];

    // countries which still use imperial measurements (officially)
    $imp_countries = ['US', 'LR', 'MM'];

    // determine default measurement system
    if (in_array($user_country_code, $imp_countries)) :
        $msystem = 'imperial';
    endif;

    // get product id
    $product_id = get_the_ID();

    // get chart data
    // $chart_data = get_post_meta($product_id, 'sbhtml_chart_data', true);
    $chart_data = get_post_meta($product_id, 'sbarray_chart_data', true);

    // echo '<pre>';
    // print_r(get_post_meta($product_id));
    // echo '</pre>';

    if ($chart_data) : ?>



        <!-- chart data table actual -->
        <div id="sbhtml_table_wrapper" class="sbhtml-shortcode" style="display: none;">

            <?php
            // display unit conversion buttons if enable for product
            if (get_post_meta($product_id, 'sbhtml_unit_conv', true) != 'yes') : ?>

                <!-- unit buttons -->
                <div id="sbhtml_front_btn_cont">
                    <button id="sbhtml_met_units" class="sbhtml_active" title="<?php pll_e('View metric units'); ?>">CM</button>
                    <button id="sbhtml_imp_units" title="<?php pll_e('View imperial units'); ?>">IN</button>
                </div>

                <!-- auto select size chart measurement -->
                <script>
                    jQuery(function($) {
                        var munit = '<?php echo $msystem; ?>';
                        if (munit == 'imperial') {
                            $('#sbhtml_imp_units').trigger('click');
                            $('#sbhtml_imp_units_emb').trigger('click');
                        }
                    });
                </script>

            <?php endif;
            ?>

            <!-- table actual -->
            <table id="sbhtml_size_table" class="sbhtml_table_front shortcode">
                <?php if (is_object(json_decode($chart_data))) :
                    echo json_decode($chart_data);
                else :
                    $chart_data = stripcslashes($chart_data);
                    echo $chart_data; ?>
                    <script>
                        jQuery(function($) {
                            $('table#sbhtml_size_table > tbody').attr('id', 'sbhtml_chart_data_body');
                            $('table#sbhtml_size_table > tbody').attr('pll-lang', '<?php echo pll_current_language(); ?>');
                            $('table#sbhtml_size_table>tbody>tr>td:last-child').attr('class', 'sbhtml_table_btn_container');
                        });
                    </script>
                <?php endif; ?>
            </table>

            <!-- polylang string translations -->
            <?php

            $dom = new DOMDocument();

            $dom->loadHTML('<html>...</html>');

            $finder = new DOMXPath($dom);
            $classname = "highlight";
            $nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");

            foreach ($nodes as $node) {
                $node_text = $node->nodeValue;
                pll_e($node_text) . "<br />";
            }

            ?>

        </div>



    <?php endif;
}

add_shortcode('sbhtml_size_chart', 'sbhtml_shortcode');

/*********************
 * JS for size chart
 *********************/
add_action('wp_footer', function () {

    // is is not product single or post type 'offer' or 'landing' then return
    if (!is_product() || !get_post_type() == 'offer' || !get_post_type() == 'landing') :
        return;
    endif;

    ?>

    <script id="sbhtml-chart-prod-js-shortcode">
        jQuery(document).ready(function($) {

            // check for presence of product-variations with data-attr value of pa_shoes_size or pa_size
            let hasSizes = false;

            // check for presence of attribute select fields with ids of pa_shoes-size or pa_size
            $('table.variations tr').each(function() {

                let selects = $(this).find('select');

                selects.each(function() {

                    if ($(this).attr('id') === 'pa_shoes-size' || $(this).attr('id') === 'pa_size') {
                        hasSizes = true;
                        return false; // exit the loop if found
                    }

                });

            });

            // check if chart can be shown
            let show_chart = $('#sbhtml-show-chart').val();

            // DEBUG
            // if (show_chart === 'true' && hasSizes) {
            //     console.log('chart can be shown');
            // } else {
            //     console.log('chart cannot be shown');
            // }

            if (show_chart === 'true' && hasSizes) {

                var sbhtml_link_text = $('#sbhtml_text_open_modal').val();

                // DEBUG
                // console.log(sbhtml_link_text);

                let label_text_content = '<a href="#" class="show-size-chart"><svg style="margin-right:10px;" data-v-6b417351="" width="24" viewBox="0 -4 34 30" xmlns="http://www.w3.org/2000/svg"><path d="M32.5 11.1c-.6 0-1 .4-1 1v11.8h-1.9v-5.4c0-.6-.4-1-1-1s-1 .4-1 1v5.4h-3.7v-3.6c0-.6-.4-1-1-1s-1 .4-1 1v3.6h-3.7v-3.6c0-.6-.4-1-1-1s-1 .4-1 1v3.6h-4.1v-3.6c0-.6-.4-1-1-1s-1 .4-1 1v3.6H6.4v-5.4c0-.6-.4-1-1-1s-1 .4-1 1v5.4H2.5V12.1c0-.6-.4-1-1-1s-1 .4-1 1v12.8c0 .6.4 1 1 1h31c.6 0 1-.4 1-1V12.1c0-.6-.4-1-1-1z" fill="#666666"></path><path d="M27.1 12.4v-.6c0-.1-.1-.1-.1-.2l-2.6-3c-.4-.6-1-.6-1.5-.3-.4.4-.5 1-.1 1.4L24 11H10l1.2-1.3c.4-.4.3-1-.1-1.4-.5-.3-1.1-.3-1.5.1l-2.6 3s0 .1-.1.1l-.1.1c0 .1-.1.1-.1.2v.2c0 .1 0 .1.1.2 0 .1.1.1.1.1s0 .1.1.1l2.6 3c.2.2.5.3.8.3.2 0 .5-.1.7-.2.4-.4.5-1 .1-1.4l-1.2-1h14l-1.2 1.3c-.4.4-.3 1 .1 1.4.2.2.4.2.7.2.3 0 .6-.1.8-.3l2.6-3c0-.1.1-.1.1-.2v-.1z" fill="#666666"></path></svg>' + sbhtml_link_text + '</a>';

                // append to label with for attribute of pa_size or pa_shoes-size
                if ($('table.variations').find('label[for="pa_size"]').length) {

                    // DEBUG
                    // console.log('pa_size found');

                    $(label_text_content).insertAfter($('table.variations').find('label[for="pa_size"]').parent('.label'));
                    // $('table.variations').find('label[for="pa_size"]').insertAfter(label_text_content);
                } else if ($('table.variations').find('label[for="pa_shoes-size"]').length) {

                    // DEBUG
                    // console.log('pa_shoes-size found');

                    $(label_text_content).insertAfter($('table.variations').find('label[for="pa_shoes-size"]').parent('.label'));
                    // $('table.variations').find('label[for="pa_shoes-size"]').append(label_text_content);

                }
            }

            // remove unneeded elements
            $('.sbhtml_table_front > thead').remove();
            $('.sbhtml_table_front .sbhtml_table_btn_container').remove();

            // hide modal and overlay
            $('div#sbhtml_chart_overlay, span#sbhtml_modal_close').on('click', function(e) {
                e.preventDefault();
                $('div#sbhtml_chart_overlay, div#sbhtml_chart_modal').hide();
            });

            // show modal and overlay
            $('a#sbhtml_view_size_chart, .show-size-chart, a#sbhtml_single_size_chart').on('click', function(e) {
                e.preventDefault();
                $('div#sbhtml_chart_overlay, div#sbhtml_chart_modal').show();
            });

            // stop modal
            $('div#sbhtml_chart_modal').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
            });

            // disable content editing capabilities
            $('.sbhtml_table_front td').attr('contenteditable', false);

            // convert to inches
            var convcounterin = 0;
            $('button#sbhtml_imp_units').on('click', function(e) {
                conv_unit_in();
            });

            function conv_unit_in() {
                $('button#sbhtml_met_units').removeClass('sbhtml_active');
                $(this).addClass('sbhtml_active');

                var cells = $('tbody#sbhtml_chart_data_body td');

                $.each(cells, function() {
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
            $('button#sbhtml_met_units').on('click', function(e) {
                conv_unit_cm();
            });

            function conv_unit_cm() {

                $('button#sbhtml_imp_units').removeClass('sbhtml_active');
                $(this).addClass('sbhtml_active');

                var cells = $('tbody#sbhtml_chart_data_body td');

                $.each(cells, function() {
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
            $('#sbhtml_front_btn_cont input[name="unit_conversion"]').change(function(e) {
                var unit_type = $(this).val();

                $("#sbhtml_chart_data_body > tr td.core-data").each(function(c_i, c_e) {
                    let val_u = $(c_e).attr('data-unit_' + unit_type);
                    if (val_u) {
                        $(c_e).text(val_u);
                    }
                });
            })

            // convert to inches -> SHORTCODE
            var convcounterin_emb = 0;
            $('button#sbhtml_imp_units_emb').on('click', function(e) {

                $('button#sbhtml_met_units_emb').removeClass('sbhtml_active');
                $(this).addClass('sbhtml_active');

                e.preventDefault();

                var cells = $('tbody#sbhtml_chart_data_body td');

                $.each(cells, function() {
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
            $('button#sbhtml_met_units_emb').on('click', function(e) {
                e.preventDefault();

                $('button#sbhtml_imp_units_emb').removeClass('sbhtml_active');
                $(this).addClass('sbhtml_active');

                var cells = $('tbody#sbhtml_chart_data_body td');

                $.each(cells, function() {
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
    </script>

    <style>
        tr.list-type {
            position: relative;
        }

        a.show-size-chart {
            position: absolute;
            right: 0px;
            top: 8px;
            font-size: 20px;
        }
    </style>


<?php })

?>
<?php

/**
 * DISPLAY CHART ON FRONTEND
 */

add_action('woocommerce_before_add_to_cart_button', 'sbhtml_front');

if (defined('MWCVersion')) {
    add_action('mwc_size_chart', 'sbhtml_front');
}

function sbhtml_front($p_id)
{

    // if current page has shortcode 'sbhtml_size_chart' present, return
    if (has_shortcode(get_the_content(), 'sbhtml_size_chart')) :
        return;
    endif;

    // get user country code
    $user_country_code = $_SERVER['HTTP_CF_IPCOUNTRY'];

    // countries which still use imperial measurements (officially)
    $imp_countries = ['US', 'LR', 'MM'];

    // determine default measurement system
    if (in_array($user_country_code, $imp_countries)) :
        $msystem = 'imperial';
    endif;

    // if product id has been passed to function, assign to variable, else check if is product page and get id, else return
    if ($p_id) :
        $product_id = $p_id;
    elseif (is_product()) :
        $product_id = get_the_ID();
    else :
        return;
    endif;

    // get product type and attribute data
    $product_data = wc_get_product($product_id);
    $attr_data    = $product_data->attributes;
    $attr_keys    = array_keys($attr_data);

    // get chart data
    $chart_data       = get_post_meta($product_id, 'sbhtml_chart_data', true);
    $chart_data_array = get_post_meta($product_id, 'sbarray_chart_data', true);

    // bail if no chart data for product id
    if (!$chart_data && !$chart_data_array) :
        return;
    endif;

    // get pll option
    $pll_strings = get_option('sbhtml_pll_strings');

    if ($chart_data_array) :

        if ($product_data->has_child()) : ?>
            <input type="hidden" id="sbhtml-show-chart" value="true">
        <?php endif; ?>

        <script id="sbhtm-prod-js-hooked">
            jQuery(document).ready(function($) {
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

                    console.log(sbhtml_link_text);

                    var label_text_content = '<div class="sbhtml_label_wrap">' + shtml_label_text + ' <span class="sbhtml_link_text">' + sbhtml_link_text + '</span></div>';

                    // $('table.variations').find('td.label:contains("Size")').append('<span>blah</span>');
                    try {
                        $('<td class="label size-chart-label"><svg style="margin-right:10px;" data-v-6b417351="" width="24" viewBox="0 -4 34 30" xmlns="http://www.w3.org/2000/svg"><path d="M32.5 11.1c-.6 0-1 .4-1 1v11.8h-1.9v-5.4c0-.6-.4-1-1-1s-1 .4-1 1v5.4h-3.7v-3.6c0-.6-.4-1-1-1s-1 .4-1 1v3.6h-3.7v-3.6c0-.6-.4-1-1-1s-1 .4-1 1v3.6h-4.1v-3.6c0-.6-.4-1-1-1s-1 .4-1 1v3.6H6.4v-5.4c0-.6-.4-1-1-1s-1 .4-1 1v5.4H2.5V12.1c0-.6-.4-1-1-1s-1 .4-1 1v12.8c0 .6.4 1 1 1h31c.6 0 1-.4 1-1V12.1c0-.6-.4-1-1-1z" fill="#666666"></path><path d="M27.1 12.4v-.6c0-.1-.1-.1-.1-.2l-2.6-3c-.4-.6-1-.6-1.5-.3-.4.4-.5 1-.1 1.4L24 11H10l1.2-1.3c.4-.4.3-1-.1-1.4-.5-.3-1.1-.3-1.5.1l-2.6 3s0 .1-.1.1l-.1.1c0 .1-.1.1-.1.2v.2c0 .1 0 .1.1.2 0 .1.1.1.1.1s0 .1.1.1l2.6 3c.2.2.5.3.8.3.2 0 .5-.1.7-.2.4-.4.5-1 .1-1.4l-1.2-1h14l-1.2 1.3c-.4.4-.3 1 .1 1.4.2.2.4.2.7.2.3 0 .6-.1.8-.3l2.6-3c0-.1.1-.1.1-.2v-.1z" fill="#666666"></path></svg>' + sbhtml_link_text + '</td>').insertAfter('table.variations td:has(label[for="pa_size"])');
                    } catch (error) {
                        console.error(error);
                    }

                    // table_append.after(label_text_content);
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
                $('a#sbhtml_view_size_chart, .size-chart-label, a#sbhtml_single_size_chart').on('click', function(e) {
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

        <!-- Text open popup size  -->
        <?php
        $link_text = pll__(SBHTML_TEXT_LINK);
        $label_text = pll__(SBHTML_TEXT_LABEL);
        ?>
        <input type="hidden" id="sbhtml_text_open_modal" value="<?php echo apply_filters('sbhtml_text_open_modal', $link_text); ?>">
        <input type="hidden" id="sbhtml_text_label" value="<?php echo apply_filters('sbhtml_text_label', $label_text); ?>">

        <?php if ($p_id) { ?>
            <!-- chart modal overlay -->
            <div class="sbhtml_chart_overlay" id="sbhtml_chart_overlay-<?php echo $product_id ?>" style="display: none;" data-sizechart-id="<?php echo $product_id ?>">
                <!-- chart modal -->
                <div class="sbhtml_chart_modal" id="sbhtml_chart_modal-<?php echo $product_id ?>" style="display: none;" data-sizechart-id="<?php echo $product_id ?>">
                    <span class="sbhtml_modal_close" id="sbhtml_modal_close-<?php echo $product_id ?>" data-sizechart-id="<?php echo $product_id ?>" title="<?php pll_e('close'); ?>">x</span>
                <?php } else { ?>
                    <!-- chart modal overlay -->
                    <div id="sbhtml_chart_overlay" style="display: none;">
                        <!-- chart modal -->
                        <div id="sbhtml_chart_modal" style="display: none;">
                            <span id="sbhtml_modal_close" title="<?php pll_e('close'); ?>">x</span>
                        <?php } ?>

                        <!-- chart data table actual -->
                        <div id="sbhtml_table_wrapper">

                            <?php
                            $has_conversion = get_post_meta($product_id, 'sbhtml_unit_conv', true);
                            ?>

                            <?php if (isset($chart_data_array['us'])) : ?>
                                <ul class="sbhtml_nav_tabs">
                                    <!-- <li class="sbhtml_nav_item active" data-target="#sbhtml_tab_us">US</li> -->
                                    <li class="sbhtml_nav_item active" data-target="cm">CM</li>
                                    <li class="sbhtml_nav_item" data-target="in">IN</li>
                                    <!--                     
                    <?php if (isset($chart_data_array['eu'])) : ?>
                    <li class="sbhtml_nav_item" data-target="#sbhtml_tab_eu">EU</li>
                    <?php endif ?>

                    <?php if (isset($chart_data_array['eu'])) : ?>
                    <li class="sbhtml_nav_item" data-target="#sbhtml_tab_ja">JA</li>
                    <?php endif ?> -->
                                </ul>
                            <?php endif ?>
                            <div class="sbhtml_tab_content">
                                <?php
                                // display unit conversion buttons if enable for product
                                if ($has_conversion != 'yes') : ?>
                                    <!-- unit buttons -->
                                    <!-- <div id="sbhtml_front_btn_cont">
                            <label class="checkbox">
                                <input class="input-checkbox" name="unit_conversion" type="radio" value="cm" checked="true"> <span>CM</span>
                            </label>
                            <label class="checkbox">
                                <input class="input-checkbox" name="unit_conversion" type="radio" value="in"> <span>IN</span>
                            </label>
                        </div> -->
                                <?php
                                endif; ?>

                                <!-- BEGIN: Tab US -->
                                <div class="sbhtml_tab_pane active" id="sbhtml_tab_us" data-name="us">

                                    <!-- table actual -->
                                    <table id="sbhtml_size_table" class="sbhtml_table_front">

                                        <?php
                                        $chart_array_us = isset($chart_data_array['us']) ? $chart_data_array['us'] : $chart_data_array;
                                        if (!empty($chart_array_us)) { ?>

                                            <tbody id="sbhtml_chart_data_body" pll-lang="<?php echo pll_current_language() ?>">
                                                <?php
                                                foreach ($chart_array_us as $tr_key => $tr_value) { ?>
                                                    <tr>
                                                        <?php
                                                        foreach ($tr_value as $td_key => $td_value) { ?>
                                                            <td contenteditable="false" class="<?php echo $td_value['class'] ?>" colspan="<?php echo $td_value['colspan'] ?>" data-unit_cm="<?= (isset($td_value['value']) ? $td_value['value'] : '') ?>" data-unit_in="<?= (isset($td_value['unit_in']) ? $td_value['unit_in'] : '') ?>">
                                                                <?php echo pll__(trim($td_value['value'])) ?>
                                                            </td>
                                                        <?php
                                                        } ?>
                                                    </tr>
                                                <?php
                                                } ?>
                                            </tbody>
                                        <?php
                                        } else { ?>

                                            <?php
                                            if (is_object(json_decode($chart_data))) :
                                                echo json_decode($chart_data);
                                            else :
                                                if (strstr($chart_data, '\n')) {
                                                    $chart_data = json_decode($chart_data);
                                                }

                                                foreach ($pll_strings as $pll_string) {
                                                    $translated_pll_strings[] = pll__($pll_string);
                                                }

                                                echo str_replace($pll_strings, $translated_pll_strings, $chart_data);

                                            ?>
                                                <script>
                                                    jQuery(function($) {
                                                        $('table#sbhtml_size_table > tbody').attr('id', 'sbhtml_chart_data_body');
                                                        $('table#sbhtml_size_table > tbody').attr('pll-lang', '<?php echo pll_current_language(); ?>');
                                                        $('table#sbhtml_size_table>tbody>tr>td:last-child').attr('class', 'sbhtml_table_btn_container');
                                                    });
                                                </script>
                                        <?php
                                            endif;
                                        } ?>
                                    </table>
                                </div>
                                <!-- END: Tab US -->

                                <!-- BEGIN: Tab EU -->
                                <?php if (isset($chart_data_array['eu'])) : ?>
                                    <div class="sbhtml_tab_pane" id="sbhtml_tab_eu" data-name="eu">

                                        <!-- table actual -->
                                        <table id="sbhtml_size_table" class="sbhtml_table_front">

                                            <?php
                                            $chart_array_us = isset($chart_data_array['eu']) ? $chart_data_array['eu'] : $chart_data_array;
                                            if (!empty($chart_array_us)) { ?>

                                                <tbody id="sbhtml_chart_data_body" pll-lang="<?php echo pll_current_language() ?>">
                                                    <?php
                                                    foreach ($chart_array_us as $tr_key => $tr_value) { ?>
                                                        <tr>
                                                            <?php
                                                            foreach ($tr_value as $td_key => $td_value) { ?>
                                                                <td contenteditable="false" class="<?php echo $td_value['class'] ?>" colspan="<?php echo $td_value['colspan'] ?>" data-unit_cm="<?= (isset($td_value['value']) ? $td_value['value'] : '') ?>" data-unit_in="<?= (isset($td_value['unit_in']) ? $td_value['unit_in'] : '') ?>">
                                                                    <?php echo pll__(trim($td_value['value'])) ?>
                                                                </td>
                                                            <?php
                                                            } ?>
                                                        </tr>
                                                    <?php
                                                    } ?>
                                                </tbody>
                                            <?php
                                            } else { ?>

                                                <?php
                                                if (is_object(json_decode($chart_data))) :
                                                    echo json_decode($chart_data);
                                                else :
                                                    if (strstr($chart_data, '\n')) {
                                                        $chart_data = json_decode($chart_data);
                                                    }

                                                    foreach ($pll_strings as $pll_string) {
                                                        $translated_pll_strings[] = pll__($pll_string);
                                                    }

                                                    echo str_replace($pll_strings, $translated_pll_strings, $chart_data);

                                                ?>
                                                    <script>
                                                        jQuery(function($) {
                                                            $('table#sbhtml_size_table > tbody').attr('id', 'sbhtml_chart_data_body');
                                                            $('table#sbhtml_size_table > tbody').attr('pll-lang', '<?php echo pll_current_language(); ?>');
                                                            $('table#sbhtml_size_table>tbody>tr>td:last-child').attr('class', 'sbhtml_table_btn_container');
                                                        });
                                                    </script>
                                            <?php
                                                endif;
                                            } ?>
                                        </table>
                                    </div>
                                <?php endif ?>
                                <!-- END: Tab EU -->

                                <!-- BEGIN: Tab JA -->
                                <?php if (isset($chart_data_array['ja'])) : ?>
                                    <div class="sbhtml_tab_pane" id="sbhtml_tab_ja" data-name="ja">

                                        <!-- table actual -->
                                        <table id="sbhtml_size_table" class="sbhtml_table_front">

                                            <?php
                                            $chart_array_us = isset($chart_data_array['ja']) ? $chart_data_array['ja'] : $chart_data_array;
                                            if (!empty($chart_array_us)) { ?>

                                                <tbody id="sbhtml_chart_data_body" pll-lang="<?php echo pll_current_language() ?>">
                                                    <?php
                                                    foreach ($chart_array_us as $tr_key => $tr_value) { ?>
                                                        <tr>
                                                            <?php
                                                            foreach ($tr_value as $td_key => $td_value) { ?>
                                                                <td contenteditable="false" class="<?php echo $td_value['class'] ?>" colspan="<?php echo $td_value['colspan'] ?>" data-unit_cm="<?= (isset($td_value['value']) ? $td_value['value'] : '') ?>" data-unit_in="<?= (isset($td_value['unit_in']) ? $td_value['unit_in'] : '') ?>">
                                                                    <?php echo pll__(trim($td_value['value'])) ?>
                                                                </td>
                                                            <?php
                                                            } ?>
                                                        </tr>
                                                    <?php
                                                    } ?>
                                                </tbody>
                                            <?php
                                            } else { ?>

                                                <?php
                                                if (is_object(json_decode($chart_data))) :
                                                    echo json_decode($chart_data);
                                                else :
                                                    if (strstr($chart_data, '\n')) {
                                                        $chart_data = json_decode($chart_data);
                                                    }

                                                    foreach ($pll_strings as $pll_string) {
                                                        $translated_pll_strings[] = pll__($pll_string);
                                                    }

                                                    echo str_replace($pll_strings, $translated_pll_strings, $chart_data);

                                                ?>
                                                    <script>
                                                        jQuery(function($) {
                                                            $('table#sbhtml_size_table > tbody').attr('id', 'sbhtml_chart_data_body');
                                                            $('table#sbhtml_size_table > tbody').attr('pll-lang', '<?php echo pll_current_language(); ?>');
                                                            $('table#sbhtml_size_table>tbody>tr>td:last-child').attr('class', 'sbhtml_table_btn_container');
                                                        });
                                                    </script>
                                            <?php
                                                endif;
                                            } ?>
                                        </table>
                                    </div>
                                <?php endif ?>
                                <!-- END: Tab JA -->


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

                            </div>
                        </div>

                        <?php
                        // if chart remarks present
                        if (get_post_meta($product_id, 'sbhtml_remarks', true)) : ?>

                            <div id="sbhtml_chart_remarks_cont">
                                <p><?php pll_e(get_post_meta($product_id, 'sbhtml_remarks', true)); ?></p>
                            </div>

                            <!-- pll -->
                            <script>
                                jQuery(function($) {
                                    var text = $('#sbhtml_chart_remarks_cont > p').text();
                                    var pll_text = '<?php pll_e("'+text+'"); ?>';
                                    $('#sbhtml_chart_remarks_cont > p').text(pll_text);
                                });
                            </script>

                            <?php endif;

                        // if global note present AND set to on for product
                        $global_note = get_post_meta($product_id, 'sbhtml_g_remarks_disable', true);
                        if (get_option('sbhtml_global_note')) :
                            if ($global_note && $global_note == 'no') : ?>
                                <div id="sbhtml_global_note_cont">
                                    <p><?php pll_e(get_option('sbhtml_global_note')); ?></p>
                                </div>
                            <?php elseif (!$global_note) : ?>
                                <div id="sbhtml_global_note_cont">
                                    <p><?php pll_e(get_option('sbhtml_global_note')); ?></p>
                                </div>
                            <?php endif; ?>

                            <!-- pll -->
                            <script>
                                jQuery(function($) {
                                    var text = $('#sbhtml_global_note_cont > p').text();
                                    var pll_text = '<?php pll_e("'+text+'"); ?>';
                                    $('#sbhtml_global_note_cont > p').text(pll_text);
                                });
                            </script>
                        <?php endif;

                        // if chart image present
                        if (get_post_meta($product_id, 'sbhtml_img_url', true)) : ?>

                            <div id="sbhtml_img_cont_front">
                                <img src="<?php echo get_post_meta($product_id, 'sbhtml_img_url', true); ?>">
                            </div>

                        <?php
                        // if global chart image present && global chart image set to show for product
                        elseif (!get_post_meta($product_id, 'sbhtml_gci_de', true) || get_post_meta($product_id, 'sbhtml_gci_de', true) == 'no') : ?>
                            <div id="sbhtml_img_cont_front">
                                <?php
                                // get product id
                                $product_id = get_the_ID();

                                // get product terms
                                $terms = wp_get_post_terms($product_id, 'product_cat');

                                // check if terms are children or parents and if either have shortcodes assigned to them
                                $child_shortcode = '';
                                $parent_shortcode = '';

                                foreach ($terms as $term) :
                                    if ($term->parent > 0) :
                                        $term_id = $term->term_id;
                                        if (get_term_meta($term_id, 'sbhtml_cat_shortcode', true)) :
                                            $child_shortcode = get_term_meta($term_id, 'sbhtml_cat_shortcode', true);
                                        endif;
                                    else :
                                        $term_id = $term->term_id;
                                        if (get_term_meta($term_id, 'sbhtml_cat_shortcode', true)) :
                                            $parent_shortcode = get_term_meta($term_id, 'sbhtml_cat_shortcode', true);
                                        endif;
                                    endif;
                                endforeach;

                                // if child cat shortcode present, display that, else display parent cat shortcode if present, else display global shortcode if present
                                if (!empty($child_shortcode)) :
                                    echo do_shortcode($child_shortcode);
                                elseif (!empty($parent_shortcode)) :
                                    echo do_shortcode($parent_shortcode);
                                elseif (!empty($gshortcode = stripslashes(get_option('sbhtml_img_global_sc')))) :
                                    echo do_shortcode($gshortcode);
                                endif;

                                ?>
                            </div>
                        <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- end chart modal -->
            <?php } //function end

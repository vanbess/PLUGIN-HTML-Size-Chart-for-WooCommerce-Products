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
        <div id="sbhtml_table_wrapper" style="display: none;">

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
            <table id="sbhtml_size_table" class="sbhtml_table_front">
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
<?php endif;
    endif;
}

add_shortcode('sbhtml_size_chart', 'sbhtml_shortcode');

?>
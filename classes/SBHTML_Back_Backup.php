<?php

/**
 * Renders admin area for HTML chart
 */

class SBHTML_Back
{
    /**
     * Class init
     */
    public static function init()
    {
        // custom tab in product edit screen
        add_filter('woocommerce_product_data_tabs', [__CLASS__, 'html_chart_tab']);

        // custom tab content
        add_action('woocommerce_product_data_panels', [__CLASS__, 'html_chart_tab_content']);

        // save tab data via ajax
        add_action('wp_ajax_sbhtmlc_save_data', [__CLASS__, 'sbhtmlc_save_data']);
        add_action('wp_ajax_nopriv_sbhtmlc_save_data', [__CLASS__, 'sbhtmlc_save_data']);

        // upload chart image via ajax
        add_action('wp_ajax_sbhtml_save_chart_img', [__CLASS__, 'sbhtml_save_chart_img']);
        add_action('wp_ajax_nopriv_sbhtml_save_chart_img', [__CLASS__, 'sbhtml_save_chart_img']);

        // global chart image override page
        add_action('admin_menu', [__CLASS__, 'sbhtml_register_settings_page'], 99);

        // css and js
        wp_enqueue_style('sbhtml-css', SBHTML_URL . 'assets/sbhtml.css');
        wp_enqueue_script('sbhtml-js', SBHTML_URL . 'assets/sbhtml.js', ['jquery'], '1.0.0', true);
    }

    /**
     * Custom meta tab in product edit screen for adding HTML size chart
     */
    public static function html_chart_tab($tabs)
    {
        $tabs['sbhtmlsc'] = [
            'label' => 'HTML Size Chart',
            'target' => 'sbhtml_size_chart',
            'class' => '',
            'priority' => 21
        ];
        return $tabs;
    }

    /**
     * Register settings page
     */
    public static function sbhtml_register_settings_page()
    {
        add_submenu_page('edit.php?post_type=product', 'HTML Size Chart', 'HTML Size Chart', 'manage_options', 'sbhtml-global-img-settings', [__CLASS__, 'sbhtml_global_img_settings']);
    }

    /**
     * Add global menu page to override HTML size chart image if not defined per product
     */
    public static function sbhtml_global_img_settings()
    { ?>

        <div id="sbhtml_chart_global">

            <h3><?php pll_e('HTML Table Image Global Override '); ?></h3>

            <p><?php pll_e('NOTE: If a global sizing chart image shortcode is defined here it will be displayed for each corresponding product which DOES NOT have its own size chart image defined.'); ?></p>

            <!-- global image shortcode -->
            <label id="sbhtml_img_global_sc_label" for="sbhtml_img_global_sc"><?php pll_e('Global chart image shortcode:'); ?></label>
            <input type="text" id="sbhtml_img_global_sc" value='<?php echo wp_unslash(get_option('sbhtml_img_global_sc')); ?>' placeholder="<?php pll_e('add shortcode here'); ?>">
            <button id="sbhtml_img_global_sc_save"><?php pll_e('Save global chart image shortcode'); ?></button>

            <!-- global note -->
            <div id="sbhtml_global_note_div">
                <label for="sbhtml_global_note"><?php pll_e('Note to display below global chart image'); ?></label><br>
                <?php
                if (get_option('sbhtml_global_note')) :
                    $global_note = get_option('sbhtml_global_note'); ?>
                    <input id="sbhtml_global_note" value="<?php echo $global_note; ?>"><br>
                <?php else :
                    $global_note = 'add global note here'; ?>
                    <input id="sbhtml_global_note" placeholder="<?php pll_e($global_note); ?>"><br>
                <?php endif;
                ?>
                <a id="sbhtml_update_global_note" href="javascript:void(0)"><?php pll_e('Update global note'); ?></a>
            </div>
        </div>
    <?php }

    /**
     * Custom meta tab content
     */
    public static function html_chart_tab_content()
    {
        $chart_data = get_post_meta(get_the_ID(), 'sbhtml_chart_data', true);
    ?>
        <div id="sbhtml_size_chart" class="panel woocommerce_options_panel hidden">

            <!-- instructions -->
            <p class="sbhtml_instructions">
                <em><?php pll_e('<u>NOTE:</u> All units of measurement should be in centimetres (cm). Conversion will be done to imperial inches (in) automatically. You can also use the following shortcode to display the size chart: [sbhtml_size_chart]'); ?></em>
            </p>

            <!-- chart -->
            <div class="sbhtml_content">
                <div id="sbhtml_table_wrapper">
                    <table id="sbhtml_size_table">
                        <?php if ($chart_data) :
                            echo json_decode($chart_data);
                        else :
                        ?>
                            <thead>
                                <tr>
                                    <th class="sbhtml_add_remove_cols">
                                        <!-- add col -->
                                        <a href="javascript:void(0);" title="<?php pll_e('add column'); ?>" class="sbhtml_add_col">+</a>
                                        <!-- del col -->
                                        <a href="javascript:void(0);" title="<?php pll_e('delete column'); ?>" class="sbhtml_del_col">-</a>
                                    </th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="sbhtml_chart_data_body" pll-lang="<?php echo pll_current_language(); ?>">
                                <tr>
                                    <td contenteditable="true" class="core-data">
                                        click to edit
                                    </td>
                                    <td class="sbhtml_table_btn_container">
                                        <!-- add row -->
                                        <a href="javascript:void(0);" title="<?php pll_e('add row'); ?>" class="sbhtml_add_row">+</a>
                                        <!-- del row -->
                                        <a href="javascript:void(0);" title="<?php pll_e('delete row'); ?>" class="sbhtml_del_row">-</a>
                                    </td>
                                </tr>
                            </tbody>
                        <?php endif; ?>
                    </table>
                </div>
            </div>

            <!-- set current language -->
            <script>
                jQuery(function($) {
                    $('#sbhtml_chart_data_body').attr('pll-lang', '<?php echo pll_current_language(); ?>');
                });
            </script>

            <!-- save and delete chart -->
            <div id="sbhtml_content">
                <button id="sbhtml_save" post="<?php echo get_the_ID(); ?>" title="<?php pll_e('click to save'); ?>"><?php pll_e('Save chart'); ?></button>
                <button id="sbhtml_delete" post="<?php echo get_the_ID(); ?>" title="<?php pll_e('click to delete'); ?>"><?php pll_e('Delete chart'); ?></button>
                <button id="sbhtml_merge" title="<?php pll_e('Merge selected cells'); ?>"><?php pll_e('Merge Cells'); ?></button>
            </div>

            <!-- disable/enable unit conversion and global chart image -->
            <div id="sbhtml_content">

                <!-- disable unit conversion -->
                <label class="sbhtml_cb_label" for="sbhtml_unit_conv">
                    <input type="checkbox" id="sbhtml_unit_conv" value="<?php echo get_post_meta(get_the_ID(), 'sbhtml_unit_conv', true); ?>">
                    <?php pll_e('Disable unit conversion for this product?'); ?>
                </label>

                <!-- disable global chart image -->
                <label class="sbhtml_cb_label" for="sbhtml_gci_de">
                    <input type="checkbox" id="sbhtml_gci_de" value="<?php echo get_post_meta(get_the_ID(), 'sbhtml_gci_de', true); ?>">
                    <?php pll_e('Disable global chart image for this product?'); ?>
                </label>

                <!-- disable global remarks -->
                <label class="sbhtml_cb_label" for="sbhtml_g_remarks_disable">
                    <?php pll_register_string('sbhtml-size-charts', 'Disable global size chart remarks for this product?'); ?>
                    <input type="checkbox" id="sbhtml_g_remarks_disable" value="<?php echo get_post_meta(get_the_ID(), 'sbhtml_g_remarks_disable', true); ?>">
                    <?php pll_e('Disable global size chart remarks for this product?'); ?>
                </label>
                <button id="sbhtml_save_dbs" post="<?php echo get_the_ID(); ?>"><?php pll_e('Save Settings'); ?></button>
            </div>

            <!-- chart remarks -->
            <div id="sbhtml_content">
                <label for="sbhtml_remarks"><?php pll_e('Remarks:'); ?></label>
                <textarea id="sbhtml_remarks"><?php echo get_post_meta(get_the_ID(), 'sbhtml_remarks', true); ?></textarea>
                <button id="sbhtml_save_remarks" post="<?php echo get_the_ID(); ?>"><?php pll_e('Save Remarks'); ?></button>
            </div>

            <!-- upload image -->
            <div id="sbhtml_content">
                <label for="sbhtml_image"><?php pll_e('Chart image:'); ?></label>
                <input type="file" id="sbhtml_image" name="sbhtml_image">
                <button id="sbhtml_ul_image" post="<?php echo get_the_ID(); ?>"><?php pll_e('Upload Chart Image'); ?></button>

                <!-- img actual -->
                <?php
                if (get_post_meta(get_the_ID(), 'sbhtml_img_url', true)) : ?>
                    <div id="sbhtml_img_div">
                        <span id="sbhtml_img_del" post="<?php echo get_the_ID(); ?>" title="<?php pll_e('Delete this image'); ?>">x</span>
                        <img id="sbhtml_img" src="<?php echo get_post_meta(get_the_ID(), 'sbhtml_img_url', true); ?>">
                    </div>
                <?php endif;
                ?>
            </div>
        </div>
        <!-- sbhtml size chart container ends -->
<?php }

    /**
     * Save custom meta panel data via AJAX
     */
    public static function sbhtmlc_save_data()
    {
        // save chart data
        if (isset($_POST['save_chart'])) :

            $chart_data = $_POST['save_chart'];
            $product_id = $_POST['product_id'];
            $pll_strings = $_POST['pll_strings'];

            $pll_strings_saved = update_option('sbhtml_pll_strings', $pll_strings);
            $data_added = update_post_meta($product_id, 'sbhtml_chart_data', $chart_data);

            if ($data_added && $pll_strings_saved) :
                pll_e('Chart data successfully saved.');
            else :
                pll_e('Chart data could not be saved. Please reload the page and try again.');
            endif;

        endif;

        // chart core data strings to pll strings
        if (isset($_POST['string_to_pll'])) :

            $string = $_POST['string_to_pll'];
            $curr_lang = $_POST['curr_lang'];
            $pll_string = pll_translate_string($string, $curr_lang);
            print $pll_string;

        endif;

        // delete chart data
        if (isset($_POST['del_chart_id'])) :

            $product_id = $_POST['del_chart_id'];
            $chart_data_deleted = delete_post_meta($product_id, 'sbhtml_chart_data');

            if ($chart_data_deleted) :
                pll_e('Chart data successfully deleted.');
            else :
                pll_e('Chart data could not be deleted. Please reload the page and try again.');
            endif;
        endif;

        // save/delete chart remarks
        if (isset($_POST['remarks'])) :

            $remarks = $_POST['remarks'];
            $product_id = $_POST['product_id'];
            pll_register_string('sbhtml-size-charts', $remarks);

            if ($remarks == '') {
                $remarks_deleted = delete_post_meta($product_id, 'sbhtml_remarks');
                if ($remarks_deleted) :
                    pll_e('Remark saved');
                else :
                    pll_e('Could not save remark. Please reload the page and try again.');
                endif;
            } else {
                $remarks_added = update_post_meta($product_id, 'sbhtml_remarks', $remarks);
                if ($remarks_added) :
                    pll_e('Remark saved');
                else :
                    pll_e('Could not save remark. Please reload the page and try again.');
                endif;
            }

        endif;

        // delete/remove image
        if (isset($_POST['remove_img'])) :

            $product_id = $_POST['remove_img'];
            $img_deleted = delete_post_meta($product_id, 'sbhtml_img_url');

            if ($img_deleted) :
                pll_e('Image deleted.');
            else :
                pll_e('Image could not be deleted. Please reload the page and try again.');
            endif;

        endif;

        // delete/remove GLOBAL image
        if (isset($_POST['remove_global_img'])) :

            $img_deleted = delete_option('sbhtml_img_url_global');

            if ($img_deleted) :
                pll_e('Global chart image deleted.');
            else :
                pll_e('Global chart image could not be deleted. Please reload the page and try again.');
            endif;

        endif;

        // add/update global note
        if (isset($_POST['global_note'])) :

            $global_note_set = update_option('sbhtml_global_note', $_POST['global_note']);
            pll_register_string('sbhtml-size-charts', $_POST['global_note']);

            if ($global_note_set) :
                pll_e('Global note saved.');
            else :
                pll_e('Global note could not be saved. Please reload the page and try again.');
            endif;

        endif;

        // Update per product global image/unit conversion/remarks
        if (isset($_POST['conversion'])) :

            $product_id = $_POST['product_id'];
            $convert_units = $_POST['conversion'];
            $global_img = $_POST['global_img'];
            $global_remarks = $_POST['global_remarks'];

            // unit conversion
            if ($convert_units == '' || $convert_units == 'no') {
                $cupdated = update_post_meta($product_id, 'sbhtml_unit_conv', 'no');
            } elseif ($convert_units == 'yes') {
                $cupdated = update_post_meta($product_id, 'sbhtml_unit_conv', 'yes');
            }

            // global image
            if ($global_img == '' || $global_img == 'no') {
                $giupdated = update_post_meta($product_id, 'sbhtml_gci_de', 'no');
            } elseif ($global_img == 'yes') {
                $giupdated = update_post_meta($product_id, 'sbhtml_gci_de', 'yes');
            }

            // global remarks
            if ($global_remarks == '' || $global_remarks == 'no') {
                $grupdated = update_post_meta($product_id, 'sbhtml_g_remarks_disable', 'no');
            } elseif ($global_remarks == 'yes') {
                $grupdated = update_post_meta($product_id, 'sbhtml_g_remarks_disable', 'yes');
            }

            if ($cupdated || $giupdated || $grupdated) {
                pll_e('Product settings updated');
            }

        endif;

        // save global shortcode
        if (isset($_POST['gshortcode'])) :

            $shortcode = $_POST['gshortcode'];
            $gshortcode_saved = update_option('sbhtml_img_global_sc', $shortcode);

            if ($gshortcode_saved) :
                pll_e('Global shortcode saved.');
            else :
                pll_e('Global shortcode could not be saved. Please reload the page and try again.');
            endif;

        endif;

        wp_die();
    }

    /**
     * Save chart image via Ajax
     */
    public static function sbhtml_save_chart_img()
    {
        if (isset($_POST)) :

            /* get wp upload directory path and urls */
            $uploadDir = wp_upload_dir();
            $targetDir = $uploadDir['path'];
            $targetUrl = $uploadDir['url'];

            /* set target file name */
            $targetFile = $targetDir . basename($_FILES['sbhtml_image']['name']);

            /* set target file url */
            $targetFileUrl = $targetUrl . basename($_FILES['sbhtml_image']['name']);

            /* move uploaded file to uploads directory */
            $moved = move_uploaded_file($_FILES["sbhtml_image"]["tmp_name"], $targetFile);

            /* if file moved successfully, return response */
            if ($moved) {

                $updateSizeUrl    = update_post_meta($_POST['product_id'], 'sbhtml_img_url', $targetFileUrl);

                if ($updateSizeUrl) :
                    $response              = [];
                    $response['file_path'] = $targetFile;
                    $response['file_url']  = $targetFileUrl;
                    $response['uploaded']  = 'yes';

                    $responseJson = json_encode($response);

                    echo $responseJson;
                endif;
            }

        endif;
        wp_die();
    }
}
SBHTML_Back::init();

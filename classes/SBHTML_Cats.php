<?php

/**
 * Adds size chart shortcode input fields to category add/edit pages
 */

class SBHTML_Cats
{

    // class init
    public static function init()
    {
        // product category custom meta
        add_filter('product_cat_add_form_fields', [__CLASS__, 'sbhtml_add_cat_shortcode_input']);
        add_filter('product_cat_edit_form_fields', [__CLASS__, 'sbhtml_add_cat_edit_shortcode_input']);

        // save category based shortcode
        add_action('edited_product_cat', [__CLASS__, 'sbhtml_save_cat_shortcode_data']);
        add_action('create_product_cat', [__CLASS__, 'sbhtml_save_cat_shortcode_data']);
    }

    /**
     * Add input to category add screen
     *
     * @return void
     */
    public static function sbhtml_add_cat_shortcode_input()
    { ?>

        <tr class="form-field">
            <th scope="row"><label for="sbhtml_cat_shortcode"><?php pll_e('Size chart image shortcode'); ?></label></th>
            <td>
                <input style="width: 90%;" id="sbhtml_cat_shortcode" name="sbhtml_cat_shortcode" placeholder="<?php pll_e('add shortcode here'); ?>">
                <p class="description"><?php pll_e('If you want to use a specific size chart image shortcode for this category it should be added here.'); ?></p>
            </td>
        </tr>

    <?php }

    /**
     * Add input to category edit screen
     *
     * @return void
     */
    public static function sbhtml_add_cat_edit_shortcode_input($term)
    {
        // get term id
        $term_id = $term->term_id;

        // retrieve existing values
        $cat_shortcode_val = get_term_meta($term_id, 'sbhtml_cat_shortcode', true);
    ?>

        <tr class="form-field">
            <th scope="row"><label for="sbhtml_cat_shortcode"><?php pll_e('Size chart image shortcode'); ?></label></th>
            <td>
                <input  style="width: 90%;" id="sbhtml_cat_shortcode" name="sbhtml_cat_shortcode" placeholder="<?php pll_e('add shortcode here'); ?>" value='<?php echo wp_unslash($cat_shortcode_val); ?>'>
                <p class="description"><?php pll_e('If you want to use a specific size chart image shortcode for this category it should be added here.'); ?></p>
            </td>
        </tr>

<?php }

/**
 * Save term meta
 *
 * @param [integer] $term_id
 * @return void
 */
    public static function sbhtml_save_cat_shortcode_data($term_id)
    {
        // get submitted val
        $sbhtml_shortcode_val = $_POST['sbhtml_cat_shortcode'];

        // save submitted val
        update_term_meta($term_id, 'sbhtml_cat_shortcode', $sbhtml_shortcode_val);
    }
}

SBHTML_Cats::init();

?>
<?php

/**
 * Plugin Name: Silverback HTML Size Charts [MWC Compatible - RIODE Theme]
 * Author: WCB
 * Description: HTML size charts for WooCommerce products. Compatible with latest version of Multi Woo Checkout plugin (adds size chart to package products via action hook)
 * Version: 2.2
 */

if (!defined('ABSPATH')) :
    exit();
endif;

define('SBHTML_VERSION', '2.2');

// load
function sbhtmlc()
{
    // constants 
    define('SBHTML_PATH', plugin_dir_path(__FILE__));
    define('SBHTML_URL', plugin_dir_url(__FILE__));
    define('SBHTML_TEXT_LABEL', 'Like wearing socks or prefer a roomier feel? Consider sizing up for the perfect fit.');
    define('SBHTML_TEXT_LINK', 'Size Chart');

    // classes
    require_once SBHTML_PATH . 'classes/SBHTML_Back.php';

    // front
    require_once SBHTML_PATH . 'functions/sbhtml-front.php';

    // shortcode
    require_once SBHTML_PATH . 'functions/sbhtml-shortcode.php';

    // category based shortcodes
    require_once SBHTML_PATH . 'classes/SBHTML_Cats.php';

    // register global note
    $global_note = get_option('sbhtml_global_note');

    if($global_note):
        pll_register_string('sbhtml_gl_note_'.$global_note, $global_note, 'sbhtml_chart_global_note');
    endif;

    // register pll strings as needed
    $pll_strings = get_option('sbhtml_pll_strings');

    if ($pll_strings) :
        foreach ($pll_strings as $key => $string) :
            pll_register_string('sbhtml_'.$string, $string, 'sbhtml_chart_data_strings');
            pll_register_string('sbhtml_text_label', SBHTML_TEXT_LABEL);
            pll_register_string('sbhtml_text_link', SBHTML_TEXT_LINK);
        endforeach;
    endif;

}

//plugins loaded
add_action('init', 'sbhtmlc');

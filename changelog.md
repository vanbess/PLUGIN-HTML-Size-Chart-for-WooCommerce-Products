# 24 November 2023
- Moved front-end JS to wp_footer
- Fixed issue where the chart view link would not show on the product single page for shortcode
- Added check for shortcode to woocommerce_before_add_to_cart_button hook to avoid loading chart twice
- Disabled code which was causing fatal error via infinite loop until I can figure out why it's happening
- Backed up original shortcode function for future debugging reference

- version bumped to 2.3
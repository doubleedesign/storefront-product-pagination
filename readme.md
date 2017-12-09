# Storefront Product Pagination - sans Storefront

## Description

A simple plugin that displays next and previous links on single product pages, with the option to restrict to only products in the same category. This is a fork of Storefront Product Pagination designed to be used by non-Storefront themes.

## Installation

1. Upload `storefront-product-pagination-sans-storefront` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure the display in the Customizer (look for the 'Product Pagination' section).
4. Integrate into your theme (see below)
5. Styling (see below)
 
## Theme Integration

By default, the plugin will insert the pagination after the product data tabs, before upsells and related products, by adding the action to `woocommerce_after_single_product_summary` at priority `12`.

You can modify this by adding the below code to your functions file, where the 30 in the last line is your desired priority. You could also add it to a different action hook by replacing `woocommerce_after_single_product_summary` with your desired action hook.
```php
$plugin_instance = Storefront_Product_Pagination::instance();
remove_action('init', array($plugin_instance, 'spp_template_position'));
add_action( 'woocommerce_after_single_product_summary', array( $plugin_instance, 'spp_single_product_pagination' ), 30 );
```

## Styling

I have not included the CSS from the original plugin, or added my own CSS here, as I do not want to enqueue an extra stylesheet for this. I add styling in my own _woocommerce.scss on a per-theme basis. 

## Changelog

= 1.0.0 - 09.12.2017 =
Initial release.
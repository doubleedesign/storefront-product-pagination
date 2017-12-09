<?php
/**
 * Plugin Name:			Storefront Product Pagination without Storefront
 * Plugin URI:			http://woothemes.com/storefront/
 * Description:			Add unobstrusive links to next/previous products on your WooCommerce single product pages. Forked from Storefront Product Pagination version 1.2.2.
 * Version:				1.0
 * Author:				Leesa Ward
 * Author URI:			https://www.github.com/doubleedesign
 * Requires at least:	4.0.0
 * Tested up to:		4.8.2
 *
 * Text Domain: storefront-product-pagination
 * Domain Path: /languages/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


/**
 * Returns the main instance of Storefront_Product_Pagination to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object Storefront_Product_Pagination
 */
function storefront_product_pagination() {
	return Storefront_Product_Pagination::instance();
} // End storefront_product_pagination()

storefront_product_pagination();

/**
 * Main Storefront_Product_Pagination Class
 *
 * @class Storefront_Product_Pagination
 * @version	1.0.0
 * @since 1.0.0
 * @package	Storefront_Product_Pagination
 */
final class Storefront_Product_Pagination {
    
	/**
	 * Storefront_Product_Pagination The single instance of Storefront_Product_Pagination.
	 *
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * The token.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $token;

	/**
	 * The version number.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $version;

	/**
	 * The admin object.
	 *
	 * @var     object
	 * @access  public
	 * @since   1.0.0
	 */
	public $admin;

	/**
	 * Constructor function.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function __construct() {
		$this->token 			= 'storefront-product-pagination';
		$this->plugin_url 		= plugin_dir_url( __FILE__ );
		$this->plugin_path 		= plugin_dir_path( __FILE__ );
		$this->version 			= '1.2.2';
		register_activation_hook( __FILE__, array( $this, 'install' ) );
		add_action( 'init', array( $this, 'spp_load_plugin_textdomain' ) );
		add_action( 'init', array( $this, 'spp_setup' ) );
        add_action( 'init', array( $this, 'spp_template_position' ) );    
	}

	/**
	 * Main Storefront_Product_Pagination Instance
	 *
	 * Ensures only one instance of Storefront_Product_Pagination is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see Storefront_Product_Pagination()
	 * @return Main Storefront_Product_Pagination instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	} // End instance()

	/**
	 * Load the localisation file.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function spp_load_plugin_textdomain() {
		load_plugin_textdomain( 'storefront-product-pagination', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, esc_attr( __( 'Cheatin&#8217; huh?' ) ), '1.0.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_attr( __( 'Cheatin&#8217; huh?' ) ), '1.0.0' );
	}

	/**
	 * Installation.
	 * Runs on activation. Logs the version number and assigns a notice message to a WordPress option.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function install() {
		$this->_log_version_number();

		// Get theme customizer url.
		$url = admin_url() . 'customize.php?';
		$url .= 'url=' . urlencode( site_url() . '?storefront-customizer=true' );
		$url .= '&return=' . urlencode( admin_url() . 'plugins.php' );
		$url .= '&storefront-customizer=true';

		$notices 		= get_option( 'spp_activation_notice', array() );
		$notices[]		= sprintf( __( '%sThanks for installing the Storefront Product Pagination extension. Configure the settings in the %sCustomizer%s.%s %sOpen the Customizer%s', 'storefront-product-pagination' ), '<p>', '<a href="' . esc_url( $url ) . '">', '</a>', '</p>', '<p><a href="' . esc_url( $url ) . '" class="button button-primary">', '</a></p>' );

		update_option( 'spp_activation_notice', $notices );
	}

	/**
	 * Log the plugin version number.
	 *
	 * @access  private
	 * @since   1.0.0
	 * @return  void
	 */
	private function _log_version_number() {
		// Log the version number.
		update_option( $this->token . '-version', $this->version );
	}

	/**
	 * Setup all the things.
	 *
	 * @return void
	 */
	public function spp_setup() {
		add_action( 'customize_register', array( $this, 'spp_customize_register' ) );
		add_action( 'customize_preview_init', array( $this, 'spp_customize_preview_js' ) );
		add_action( 'admin_notices', array( $this, 'spp_customizer_notice' ) );	
		add_filter( 'storefront_customizer_more', '__return_false' ); // Hide the 'More' section in the customizer
	}
    
    public function spp_template_position() {
        add_action( 'woocommerce_after_single_product_summary', array( $this, 'spp_single_product_pagination' ), 12 ); 
    }

	/**
	 * Admin notice
	 * Checks the notice setup in install(). If it exists display it then delete the option so it's not displayed again.
	 *
	 * @since   1.0.0
	 * @return  void
	 */
	public function spp_customizer_notice() {
		$notices = get_option( 'spp_activation_notice' );

		if ( $notices = get_option( 'spp_activation_notice' ) ) {

			foreach ( $notices as $notice ) {
				echo '<div class="updated">' . wp_kses_post( $notice ) . '</div>';
			}

			delete_option( 'spp_activation_notice' );
		}
	}

	/**
	 * Customizer Controls and settings
	 *
	 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
	 */
	public function spp_customize_register( $wp_customize ) {
		/**
		 * Add a new section
		 */
		$wp_customize->add_section( 'spp_section' , array(
			'title'      	=> __( 'Product Pagination', 'storefront-extention-boilerplate' ),
			'priority'   	=> 55,
		) );

		/**
		 * Same category
		 */
		$wp_customize->add_setting( 'spp_same_cat', array(
			'default'   => false,
		) );

		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'spp_same_cat', array(
			'label'       => __( 'Display products in same category', 'storefront-product-pagination' ),
			'description' => __( 'When enabled, pagination will only display links to products in the same category as the one currently being viewed.', 'storefront-product-pagination' ),
			'section'     => 'spp_section',
			'settings'    => 'spp_same_cat',
			'type'        => 'checkbox',
			'priority'    => 40,
		) ) );
	}

	/**
	 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
	 *
	 * @since  1.0.0
	 */
	public function spp_customize_preview_js() {
		wp_enqueue_script( 'spp-customizer', plugins_url( '/assets/js/customizer.min.js', __FILE__ ), array( 'customize-preview' ), '1.0.0', true );
	}

	/**
	 * Single product pagination
	 * Display links to the next/previous products on the single product page
	 *
	 * @since   1.0.0
	 * @return  void
	 * @uses    previous_post_link(), next_post_link()
	 */
	function spp_single_product_pagination() {
		$placeholder 				= '<img src="' . wc_placeholder_img_src() . '" />';
		$same_cat 					= get_theme_mod( 'spp_same_cat', false );
		$taxonomy 		            = 'category';
		$in_same_term 	            = false;

		if ( true === $same_cat ) {
			$in_same_term 	= true;
			$taxonomy 		= 'product_cat';
		}

		$previous_product 			= get_previous_post( $same_cat, '', $taxonomy );
		$next_product 				= get_next_post( $same_cat, '', $taxonomy );

		$previous_product_thumbnail	= '';
		$next_product_thumbnail		= '';

		$previous_product_data = '';
		$next_product_data     = '';

		// If a next/previous product exists, get the thumbnail (or place holder).
		if ( $previous_product ) {
			$previous_product_data      = new WC_Product( $previous_product->ID );
			$previous_product_thumbnail = get_the_post_thumbnail( $previous_product->ID, 'shop_catalog' );

			if ( ! $previous_product_thumbnail ) {
				$previous_product_thumbnail = $placeholder;
			}
		}

		if ( $next_product ) {
			$next_product_data      = new WC_Product( $next_product->ID );
			$next_product_thumbnail = get_the_post_thumbnail( $next_product->ID, 'shop_catalog' );

			if ( ! $next_product_thumbnail ) {
				$next_product_thumbnail = $placeholder;
			}
		}

		// Output the links.
		if ( $next_product || $previous_product ) {

			echo '<nav class="single-product-pagination">';

			if ( $previous_product && $previous_product_data->is_visible() ) {
				previous_post_link( '%link', $previous_product_thumbnail . '<span class="title">%title</span>', $in_same_term, '', $taxonomy );
			}

			if ( $next_product && $next_product_data->is_visible() ) {
				next_post_link( '%link', $next_product_thumbnail . '<span class="title">%title</span>', $in_same_term, '', $taxonomy );
			}

			echo '</nav>';

		}
	}
} // End Class
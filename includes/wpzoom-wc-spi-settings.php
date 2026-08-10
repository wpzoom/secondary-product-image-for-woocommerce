<?php

//Exit if accessed directly
if( ! defined( 'ABSPATH' ) )
	exit;

/**
 * WPZOOM_WC_SPI_Settings Class
 *
 * Stores the plugin options and renders them as a section under
 * WooCommerce > Settings > Products.
 *
 * @since 1.1.0
 */

if ( ! class_exists( 'WPZOOM_WC_SPI_Settings' ) ) {

	class WPZOOM_WC_SPI_Settings {

		/**
		 * The settings section slug.
		 *
		 * @var string
		 */
		const SECTION = 'wpzoom-secondary-image';

		/**
		 * Option name prefix.
		 *
		 * @var string
		 */
		const PREFIX = 'wpzoom_wc_spi_';

		/**
		 * Runtime cache for option lookups.
		 *
		 * @var array
		 */
		protected static $cache = array();

		public function __construct() {

			if ( is_admin() ) {
				add_filter( 'woocommerce_get_sections_products', array( $this, 'add_section' ) );
				add_filter( 'woocommerce_get_settings_products', array( $this, 'add_settings' ), 10, 2 );
				add_action( 'woocommerce_update_options_products', array( __CLASS__, 'flush_cache' ) );
				// Priority 5 puts the panel inside the settings form but ahead of the
				// fields, so it can be moved into its own column with CSS.
				add_action( 'woocommerce_settings_products', array( $this, 'render_preview' ), 5 );
				add_filter( 'admin_body_class', array( $this, 'add_body_class' ) );
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_preview_assets' ) );
			}
		}

		/**
		 * Default value for every option.
		 *
		 * The defaults reproduce the behaviour of version 1.0.3, so updating the
		 * plugin never changes how an existing store looks.
		 *
		 * @return array
		 */
		public static function defaults() {
			return array(
				'effect'     => 'fade',
				'duration'   => 450,
				'mode'       => 'single',
				'interval'   => 1200,
				'max_images' => 5,
				'fallback'          => 'first',
				'lightbox'          => 'no',
				'lightbox_position' => 'center',
				'touch'             => 'no',
			);
		}

		/**
		 * Get a single option.
		 *
		 * @param string $key Option key, without the prefix.
		 * @return mixed
		 */
		public static function get( $key ) {

			if ( isset( self::$cache[ $key ] ) ) {
				return self::$cache[ $key ];
			}

			$defaults = self::defaults();

			if ( ! isset( $defaults[ $key ] ) ) {
				return null;
			}

			$value = get_option( self::PREFIX . $key, $defaults[ $key ] );

			if ( is_numeric( $defaults[ $key ] ) ) {
				$value = absint( $value );

				if ( ! $value && 'duration' !== $key ) {
					$value = $defaults[ $key ];
				}
			}

			/**
			 * Filters a single plugin option.
			 *
			 * @since 1.1.0
			 * @param mixed  $value The stored value.
			 * @param string $key   The option key.
			 */
			$value = apply_filters( 'wpzoom_wc_spi_option', $value, $key );

			self::$cache[ $key ] = $value;

			return $value;
		}

		/**
		 * Whether a yes/no option is enabled.
		 *
		 * @param string $key Option key.
		 * @return bool
		 */
		public static function enabled( $key ) {
			return 'yes' === self::get( $key );
		}

		/**
		 * Clear the runtime cache after the options are saved.
		 */
		public static function flush_cache() {
			self::$cache = array();
		}

		/**
		 * Whether the current screen is our settings section.
		 *
		 * @return bool
		 */
		protected function is_settings_screen() {

			if ( ! isset( $_GET['page'], $_GET['tab'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				return false;
			}

			return 'wc-settings' === $_GET['page'] // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				&& 'products' === $_GET['tab'] // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				&& isset( $_GET['section'] ) && self::SECTION === $_GET['section']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		/**
		 * The sample product shown in the preview.
		 *
		 * The images ship with the plugin rather than being pulled from the store,
		 * so every shop gets the same predictable sample, and from a local URL: the
		 * admin never depends on a remote server to render this screen.
		 *
		 * @return array
		 */
		protected function preview_product() {

			$base = WPZOOM_WC_SPI_URL . 'includes/assets/images/';

			return array(
				'title'    => 'Circle Rotation T-Shirt',
				'price'    => function_exists( 'wc_price' ) ? wp_strip_all_tags( wc_price( 39 ) ) : '39',
				'featured' => $base . 'preview-1.jpg',
				'images'   => array(
					$base . 'preview-2.jpg',
					$base . 'preview-3.jpg',
					$base . 'preview-4.jpg',
				),
			);
		}

		/**
		 * Shape one bundled image for the preview script.
		 *
		 * @param string $url
		 * @return array
		 */
		protected function preview_image_data( $url ) {

			return array(
				'src'    => $url,
				'width'  => 760,
				'height' => 507,
				'srcset' => '',
				'sizes'  => '',
				'full'   => $url,
				'alt'    => '',
			);
		}

		/**
		 * Load the real front-end styles and script, so the preview behaves exactly
		 * like the shop rather than being a separate implementation.
		 */
		public function enqueue_preview_assets() {

			if ( ! $this->is_settings_screen() ) {
				return;
			}

			$preview = $this->preview_product();

			wp_enqueue_style(
				'wpzoom-wc-spi-style',
				WPZOOM_WC_SPI_URL . 'assets/css/secondary-product-image-for-woocommerce.css',
				array(),
				WPZOOM_WC_SPI_VER
			);

			wp_enqueue_style(
				'wpzoom-wc-spi-preview',
				WPZOOM_WC_SPI_URL . 'includes/assets/css/wpzoom-wc-spi-preview.css',
				array( 'wpzoom-wc-spi-style' ),
				WPZOOM_WC_SPI_VER
			);

			wp_enqueue_script(
				'wpzoom-wc-spi-script',
				WPZOOM_WC_SPI_URL . 'assets/js/secondary-product-image-for-woocommerce.js',
				array(),
				WPZOOM_WC_SPI_VER,
				true
			);

			wp_localize_script(
				'wpzoom-wc-spi-script',
				'wpzoomWcSpi',
				array(
					'touch' => 'no',
					'i18n'  => array(
						'previous' => esc_html__( 'Previous image', 'secondary-product-image-for-woocommerce' ),
						'next'     => esc_html__( 'Next image', 'secondary-product-image-for-woocommerce' ),
						'expand'   => esc_html__( 'Open images in a lightbox', 'secondary-product-image-for-woocommerce' ),
						'close'    => esc_html__( 'Close', 'secondary-product-image-for-woocommerce' ),
						/* translators: 1: current image number 2: total number of images */
						'counter'  => esc_html__( 'Image %1$s of %2$s', 'secondary-product-image-for-woocommerce' ),
					),
				)
			);

			wp_enqueue_script(
				'wpzoom-wc-spi-preview',
				WPZOOM_WC_SPI_URL . 'includes/assets/js/wpzoom-wc-spi-preview.js',
				array( 'wpzoom-wc-spi-script' ),
				WPZOOM_WC_SPI_VER,
				true
			);

			$images = array();

			foreach ( $preview['images'] as $url ) {
				$images[] = $this->preview_image_data( $url );
			}

			wp_localize_script(
				'wpzoom-wc-spi-preview',
				'wpzoomWcSpiPreview',
				array(
					'prefix'  => self::PREFIX,
					'primary' => $this->preview_image_data( $preview['featured'] ),
					'images'  => $images,
					'product' => array(
						'title' => $preview['title'],
						// Decoded because the script sets it as text, not as markup.
						'price' => html_entity_decode( $preview['price'], ENT_QUOTES, get_bloginfo( 'charset' ) ),
					),
					'i18n'    => array(
						'nothing' => esc_html__( 'Nothing is revealed on hover with these settings.', 'secondary-product-image-for-woocommerce' ),
					),
				)
			);
		}

		/**
		 * Flag the screen so the stylesheet can lay the form out in two columns.
		 *
		 * @param string $classes
		 * @return string
		 */
		public function add_body_class( $classes ) {

			if ( $this->is_settings_screen() ) {
				$classes .= ' wpzoom-wc-spi-settings';
			}

			return $classes;
		}

		/**
		 * Output the live preview panel.
		 */
		public function render_preview() {

			if ( ! $this->is_settings_screen() ) {
				return;
			}

			echo '<div class="wpzoom-wc-spi-preview-panel">';
			echo '<h3>' . esc_html__( 'Live preview', 'secondary-product-image-for-woocommerce' ) . '</h3>';
			echo '<div class="wpzoom-wc-spi-preview" id="wpzoom-wc-spi-preview"></div>';
			echo '<p class="description">';
			esc_html_e( 'Hover the image to try your settings. Changes are previewed straight away, but still need saving.', 'secondary-product-image-for-woocommerce' );
			echo '</p>';
			echo '</div>';
		}

		/**
		 * Register the section under the Products tab.
		 *
		 * @param array $sections
		 * @return array
		 */
		public function add_section( $sections ) {
			$sections[ self::SECTION ] = esc_html__( 'Secondary Image', 'secondary-product-image-for-woocommerce' );

			return $sections;
		}

		/**
		 * Output the settings fields for our section.
		 *
		 * @param array  $settings
		 * @param string $current_section
		 * @return array
		 */
		public function add_settings( $settings, $current_section ) {

			if ( self::SECTION !== $current_section ) {
				return $settings;
			}

			$defaults = self::defaults();

			return array(

				array(
					'title' => esc_html__( 'Secondary Product Image', 'secondary-product-image-for-woocommerce' ),
					'type'  => 'title',
					'desc'  => esc_html__( 'Choose how the secondary image behaves when a shopper hovers a product in your listings.', 'secondary-product-image-for-woocommerce' ),
					'id'    => self::PREFIX . 'options',
				),

				array(
					'title'    => esc_html__( 'On hover show', 'secondary-product-image-for-woocommerce' ),
					'id'       => self::PREFIX . 'mode',
					'type'     => 'select',
					'class'    => 'wc-enhanced-select',
					'default'  => $defaults['mode'],
					'desc_tip' => esc_html__( 'Rotating and sliding use the product gallery. Extra images are only downloaded once a product is hovered.', 'secondary-product-image-for-woocommerce' ),
					'options'  => array(
						'single' => esc_html__( 'A single secondary image', 'secondary-product-image-for-woocommerce' ),
						'rotate' => esc_html__( 'All gallery images, rotating automatically', 'secondary-product-image-for-woocommerce' ),
						'slider' => esc_html__( 'All gallery images, with previous / next arrows', 'secondary-product-image-for-woocommerce' ),
					),
				),

				array(
					'title'    => esc_html__( 'Transition effect', 'secondary-product-image-for-woocommerce' ),
					'id'       => self::PREFIX . 'effect',
					'type'     => 'select',
					'class'    => 'wc-enhanced-select',
					'default'  => $defaults['effect'],
					'options'  => array(
						'fade' => esc_html__( 'Fade', 'secondary-product-image-for-woocommerce' ),
						'slide' => esc_html__( 'Slide', 'secondary-product-image-for-woocommerce' ),
						'zoom' => esc_html__( 'Zoom', 'secondary-product-image-for-woocommerce' ),
					),
				),

				array(
					'title'             => esc_html__( 'Transition speed', 'secondary-product-image-for-woocommerce' ),
					'id'                => self::PREFIX . 'duration',
					'type'              => 'number',
					'default'           => $defaults['duration'],
					'desc'              => esc_html__( 'milliseconds', 'secondary-product-image-for-woocommerce' ),
					'custom_attributes' => array( 'min' => 0, 'max' => 3000, 'step' => 50 ),
				),

				array(
					'title'             => esc_html__( 'Rotation interval', 'secondary-product-image-for-woocommerce' ),
					'id'                => self::PREFIX . 'interval',
					'type'              => 'number',
					'default'           => $defaults['interval'],
					'desc'              => esc_html__( 'milliseconds each image stays visible while rotating', 'secondary-product-image-for-woocommerce' ),
					'custom_attributes' => array( 'min' => 300, 'max' => 10000, 'step' => 100 ),
				),

				array(
					'title'             => esc_html__( 'Maximum images per product', 'secondary-product-image-for-woocommerce' ),
					'id'                => self::PREFIX . 'max_images',
					'type'              => 'number',
					'default'           => $defaults['max_images'],
					'desc'              => esc_html__( 'Keeps large galleries from slowing down your shop pages.', 'secondary-product-image-for-woocommerce' ),
					'desc_tip'          => true,
					'custom_attributes' => array( 'min' => 1, 'max' => 20, 'step' => 1 ),
				),

				array(
					'title'    => esc_html__( 'When no secondary image is set', 'secondary-product-image-for-woocommerce' ),
					'id'       => self::PREFIX . 'fallback',
					'type'     => 'select',
					'class'    => 'wc-enhanced-select',
					'default'  => $defaults['fallback'],
					'options'  => array(
						'first' => esc_html__( 'Use the first gallery image', 'secondary-product-image-for-woocommerce' ),
						'last'  => esc_html__( 'Use the last gallery image', 'secondary-product-image-for-woocommerce' ),
						'none'  => esc_html__( 'Show nothing', 'secondary-product-image-for-woocommerce' ),
					),
				),

				array(
					'title'   => esc_html__( 'Lightbox', 'secondary-product-image-for-woocommerce' ),
					'id'      => self::PREFIX . 'lightbox',
					'type'    => 'checkbox',
					'default' => $defaults['lightbox'],
					'desc'    => esc_html__( 'Add a button that opens the images in a lightbox', 'secondary-product-image-for-woocommerce' ),
				),

				array(
					'title'    => esc_html__( 'Lightbox button position', 'secondary-product-image-for-woocommerce' ),
					'id'       => self::PREFIX . 'lightbox_position',
					'type'     => 'select',
					'class'    => 'wc-enhanced-select',
					'default'  => $defaults['lightbox_position'],
					'desc_tip' => esc_html__( 'Where the button sits on the product image. Move it off the corners if your theme shows a sale badge there.', 'secondary-product-image-for-woocommerce' ),
					'options'  => array(
						'center'       => esc_html__( 'Centre', 'secondary-product-image-for-woocommerce' ),
						'top-left'     => esc_html__( 'Top left', 'secondary-product-image-for-woocommerce' ),
						'top-right'    => esc_html__( 'Top right', 'secondary-product-image-for-woocommerce' ),
						'bottom-left'  => esc_html__( 'Bottom left', 'secondary-product-image-for-woocommerce' ),
						'bottom-right' => esc_html__( 'Bottom right', 'secondary-product-image-for-woocommerce' ),
					),
				),

				array(
					'title'   => esc_html__( 'Touch devices', 'secondary-product-image-for-woocommerce' ),
					'id'      => self::PREFIX . 'touch',
					'type'    => 'checkbox',
					'default' => $defaults['touch'],
					'desc'    => esc_html__( 'Also show the secondary image on phones and tablets', 'secondary-product-image-for-woocommerce' ),
					'desc_tip' => esc_html__( 'Off by default, because a tap on a touch screen is meant to open the product.', 'secondary-product-image-for-woocommerce' ),
				),

				array(
					'type' => 'sectionend',
					'id'   => self::PREFIX . 'options',
				),
			);
		}

	}

	new WPZOOM_WC_SPI_Settings;
}

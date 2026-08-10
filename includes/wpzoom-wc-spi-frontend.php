<?php

//Exit if accessed directly
if( ! defined( 'ABSPATH' ) )
	exit;

/**
 * WPZOOM_WC_Secondary_Image_Frontend Class
 *
 * Frontend output class
 *
 * @since 1.0.0
 */

if ( ! class_exists( 'WPZOOM_WC_Secondary_Image_Frontend' ) ) {

	class WPZOOM_WC_Secondary_Image_Frontend {

		/**
		 * Instance of this class.
		 *
		 * @var object
		 */
		protected static $instance = null;

		/**
		 * The meta key holding the secondary image attachment ID.
		 *
		 * @var string
		 */
		const META_KEY = 'product_wpzoom-product-secondary-image_thumbnail_id';

		public function __construct() {

			if ( ! is_admin() ) {

				add_action( 'wp_enqueue_scripts', array( $this, 'load_frontend_scripts' ), 99 );
				add_action( 'wp_head', array( $this, 'noscript_fallback' ), 99 );
				add_action( 'woocommerce_before_shop_loop_item_title', array( $this, 'output_secondary_product_thumbnail' ), 15 );
				add_filter( 'post_class', array( $this, 'set_product_post_class' ), 21, 3 );

				add_filter( 'wpzoom_wc_spi_secondary_product_thumbnail', array( $this, 'add_image_wrapper') );

				// Product Collection / Products blocks, which don't fire the classic loop hooks.
				add_filter( 'render_block_woocommerce/product-image', array( $this, 'render_product_image_block' ), 10, 3 );
			}

		}

		/**
		 * Return an instance of this class.
		 *
		 * @return object A single instance of this class.
		 */
		public static function get_instance() {
			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		/**
		 * Whether the hover effect should run at all.
		 *
		 * @since 1.0.3
		 * @return bool
		 */
		public function is_enabled() {
			return (bool) apply_filters( 'wpzoom_wc_spi_enabled', true );
		}

		/**
		 * Enqueue WCSPT front-end styles and scripts.
		 */
		public function load_frontend_scripts() {

			if ( ! $this->is_enabled() ) {
				return;
			}

			wp_enqueue_style(
				'wpzoom-wc-spi-style',
				WPZOOM_WC_SPI_URL . 'assets/css/secondary-product-image-for-woocommerce.css',
				array(),
				WPZOOM_WC_SPI_VER
			);

			wp_enqueue_script(
				'wpzoom-wc-spi-script',
				WPZOOM_WC_SPI_URL . 'assets/js/secondary-product-image-for-woocommerce.js',
				array(),
				WPZOOM_WC_SPI_VER,
				true
			);
		}

		/**
		 * Keep the hover effect working when JavaScript is unavailable.
		 *
		 * @since 1.0.3
		 */
		public function noscript_fallback() {

			if ( ! $this->is_enabled() ) {
				return;
			}

			echo '<noscript><style>@media (hover: hover) and (pointer: fine){.wpzoom-secondary-image-container{display:block}}</style></noscript>' . "\n";
		}

		public function output_secondary_product_thumbnail() {
			// Not escaped on output: the markup comes from wp_get_attachment_image(),
			// and wp_kses_post() would strip srcset/sizes from the image.
			echo $this->add_secondary_product_thumbnail(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		public function add_image_wrapper( $image_html ) {

			global $product;

			//Check if the theme is a block theme
			$is_theme_block = wp_is_block_theme();

			if( $is_theme_block && $product instanceof WC_Product ) {
				$image_html = '<a href="' . esc_url( $product->get_permalink() ) . '">' . $image_html . '</a>';
			}

			return '<div class="wpzoom-secondary-image-container">' . $image_html . '</div>';
		}

		/*
		* Output the secondary product thumbnail.
		*
		* @param string $size (default: 'woocommerce_thumbnail').
		* @param int    $deprecated1 Deprecated since WooCommerce 2.0 (default: 0).
		* @param int    $deprecated2 Deprecated since WooCommerce 2.0 (default: 0).
		* @return string
		*/
		public function add_secondary_product_thumbnail( $size = 'woocommerce_thumbnail', $deprecated1 = 0, $deprecated2 = 0 ) {

			global $product;

			if ( ! $this->is_enabled() || ! $product instanceof WC_Product ) {
				return '';
			}

			//Check if the theme is a block theme
			$is_theme_block = wp_is_block_theme();
			if( $is_theme_block ) {
				$size = 'woocommerce_single';
			}

			$image_size = apply_filters( 'single_product_archive_thumbnail_size', $size );

			$secondary_img_id = $this->get_secondary_image_id( $product );

			if ( ! $secondary_img_id ) {
				return '';
			}

			$image_html = $this->get_image_html( $secondary_img_id, $image_size );

			return apply_filters( 'wpzoom_wc_spi_secondary_product_thumbnail', $image_html, $secondary_img_id, $image_size, $product );
		}

		/**
		 * Render the secondary image on top of the WooCommerce Product Image block.
		 *
		 * The block-based Product Collection and Products blocks never fire
		 * `woocommerce_before_shop_loop_item_title`, so the image is injected into the
		 * block markup instead. Wrapping the block output also anchors the overlay to
		 * the image itself rather than to the whole product card.
		 *
		 * @since 1.0.3
		 * @param string   $block_content The rendered block HTML.
		 * @param array    $block         The parsed block.
		 * @param WP_Block $instance      The block instance.
		 * @return string
		 */
		public function render_product_image_block( $block_content, $block, $instance ) {

			if ( ! $this->is_enabled() || empty( $block_content ) ) {
				return $block_content;
			}

			// The same block renders the single product image, which has no hover state.
			if ( ! empty( $block['attrs']['isDescendentOfSingleProductBlock'] ) ) {
				return $block_content;
			}

			$product_id = isset( $instance->context['postId'] ) ? absint( $instance->context['postId'] ) : 0;

			if ( ! $product_id ) {
				return $block_content;
			}

			$product = wc_get_product( $product_id );

			if ( ! $product instanceof WC_Product ) {
				return $block_content;
			}

			$secondary_img_id = $this->get_secondary_image_id( $product );

			if ( ! $secondary_img_id ) {
				return $block_content;
			}

			// Match the size the block itself renders, so both images stay equally sharp.
			$sizing     = isset( $block['attrs']['imageSizing'] ) ? $block['attrs']['imageSizing'] : 'single';
			$image_size = 'thumbnail' === $sizing ? 'woocommerce_thumbnail' : 'woocommerce_single';

			$image_size = apply_filters( 'single_product_archive_thumbnail_size', $image_size );
			$image_html = $this->get_image_html( $secondary_img_id, $image_size );

			// Blocks render outside the loop, so point the global at this product
			// before handing off to filters that expect it.
			$previous_product   = isset( $GLOBALS['product'] ) ? $GLOBALS['product'] : null;
			$GLOBALS['product'] = $product;

			// Already returns the .wpzoom-secondary-image-container wrapper.
			$image_html = apply_filters( 'wpzoom_wc_spi_secondary_product_thumbnail', $image_html, $secondary_img_id, $image_size, $product );

			$GLOBALS['product'] = $previous_product;

			return sprintf(
				'<div class="wpzoom-wc-spi-block-image wpzoom-wc-spi-has-enabled">%1$s%2$s</div>',
				$block_content,
				$image_html
			);
		}

		/**
		 * Build the <img> markup for the secondary image.
		 *
		 * @since 1.0.3
		 * @param int    $attachment_id Attachment ID.
		 * @param string $image_size    Registered image size.
		 * @return string
		 */
		public function get_image_html( $attachment_id, $image_size ) {

			$classes = 'attachment-' . $image_size . ' wpzoom-wc-spi-secondary-img wpzoom-wc-spi-transition';
			$classes = apply_filters( 'wpzoom_wc_spi_image_class', $classes, $attachment_id, $image_size );

			return wp_get_attachment_image( $attachment_id, $image_size, false, array( 'class' => $classes ) );
		}

		/**
		 * Get the ID of the image to reveal on hover.
		 *
		 * Falls back to the first (or last) product gallery image when no dedicated
		 * secondary image has been set.
		 *
		 * @since 1.0.3
		 * @param WC_Product $product
		 * @return int Attachment ID, or 0 when the product has no secondary image.
		 */
		public function get_secondary_image_id( $product ) {

			if ( ! $product instanceof WC_Product ) {
				return 0;
			}

			$secondary_img_id = get_post_meta( $product->get_id(), self::META_KEY, true );

			if ( ! empty( $secondary_img_id ) ) {
				return absint( $secondary_img_id );
			}

			$image_ids = $this->get_gallery_img_ids( $product );

			if ( ! empty( $image_ids ) ) {
				return absint( apply_filters( 'wpzoom_wc_spi_reveal_last_img', false ) ? end( $image_ids ) : reset( $image_ids ) );
			}

			return 0;
		}

		/**
		 * Returns the gallery image ids.
		 *
		 * @param WC_Product $product
		 * @return array
		 */
		public function get_gallery_img_ids( $product ) {
			if ( method_exists( $product, 'get_gallery_image_ids' ) ) {
				$image_ids = $product->get_gallery_image_ids();
			} else {
				// Deprecated in WC 3.0.0
				$image_ids = $product->get_gallery_attachment_ids();
			}

			return $image_ids;
		}

		/**
		 * Add wcspt-has-gallery class to products that have at least one gallery image.
		 *
		 * @param array $classes
		 * @param array $class
		 * @param int $post_id
		 * @return array
		 */
		public function set_product_post_class( $classes, $class, $post_id ) {

			if ( ! $post_id || ! $this->is_enabled() || get_post_type( $post_id ) !== 'product' ) {
				return $classes;
			}

			// Resolve the product from the post being rendered instead of the global,
			// which can point at a different product inside nested loops.
			$product = wc_get_product( $post_id );

			if ( $product instanceof WC_Product && $this->get_secondary_image_id( $product ) ) {
				$classes[] = 'wpzoom-wc-spi-has-enabled';
			}

			return $classes;
		}

	}
	new WPZOOM_WC_Secondary_Image_Frontend;
}

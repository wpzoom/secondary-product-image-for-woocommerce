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
		 * Read a plugin option, falling back to the defaults when the settings
		 * class is unavailable.
		 *
		 * @since 1.1.0
		 * @param string $key
		 * @return mixed
		 */
		protected function option( $key ) {

			if ( class_exists( 'WPZOOM_WC_SPI_Settings' ) ) {
				return WPZOOM_WC_SPI_Settings::get( $key );
			}

			$defaults = array(
				'effect'     => 'fade',
				'duration'   => 450,
				'mode'       => 'single',
				'interval'   => 1200,
				'max_images' => 5,
				'fallback'   => 'first',
				'lightbox'   => 'no',
				'touch'      => 'no',
			);

			return isset( $defaults[ $key ] ) ? $defaults[ $key ] : null;
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

			wp_localize_script(
				'wpzoom-wc-spi-script',
				'wpzoomWcSpi',
				array(
					'touch' => $this->option( 'touch' ),
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

			return sprintf(
				'<div class="wpzoom-secondary-image-container %1$s"%2$s>%3$s</div>',
				esc_attr( 'wpzoom-wc-spi-effect-' . $this->option( 'effect' ) ),
				$this->container_attributes(),
				$image_html
			);
		}

		/**
		 * Data attributes the script reads off the container.
		 *
		 * @since 1.1.0
		 * @return string
		 */
		protected function container_attributes() {

			return sprintf(
				' data-mode="%1$s" data-interval="%2$d" data-lightbox="%3$s" style="--wpzoom-wc-spi-duration:%4$dms"',
				esc_attr( $this->option( 'mode' ) ),
				absint( $this->option( 'interval' ) ),
				'yes' === $this->option( 'lightbox' ) ? '1' : '0',
				absint( $this->option( 'duration' ) )
			);
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

			$image_ids = $this->get_secondary_image_ids( $product );

			if ( ! $image_ids ) {
				return '';
			}

			$image_html = $this->get_images_html( $image_ids, $image_size );

			return apply_filters( 'wpzoom_wc_spi_secondary_product_thumbnail', $image_html, reset( $image_ids ), $image_size, $product );
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

			$image_ids = $this->get_secondary_image_ids( $product );

			if ( ! $image_ids ) {
				return $block_content;
			}

			$secondary_img_id = reset( $image_ids );

			// Match the size the block itself renders, so both images stay equally sharp.
			$sizing     = isset( $block['attrs']['imageSizing'] ) ? $block['attrs']['imageSizing'] : 'single';
			$image_size = 'thumbnail' === $sizing ? 'woocommerce_thumbnail' : 'woocommerce_single';

			$image_size = apply_filters( 'single_product_archive_thumbnail_size', $image_size );
			$image_html = $this->get_images_html( $image_ids, $image_size );

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

			return wp_get_attachment_image(
				$attachment_id,
				$image_size,
				false,
				array(
					'class'               => $classes . ' is-active',
					'data-wpzoom-full'    => wp_get_attachment_image_url( $attachment_id, 'large' ),
				)
			);
		}

		/**
		 * Markup for every image that follows the first one.
		 *
		 * The source is held back in data attributes so a shop page never pays for
		 * gallery images the visitor may not look at. The script fills them in the
		 * first time the product is hovered.
		 *
		 * @since 1.1.0
		 * @param int    $attachment_id
		 * @param string $image_size
		 * @return string
		 */
		public function get_deferred_image_html( $attachment_id, $image_size ) {

			$image = wp_get_attachment_image_src( $attachment_id, $image_size );

			if ( ! $image ) {
				return '';
			}

			$classes = 'attachment-' . $image_size . ' wpzoom-wc-spi-secondary-img wpzoom-wc-spi-transition';
			$classes = apply_filters( 'wpzoom_wc_spi_image_class', $classes, $attachment_id, $image_size );

			$srcset = wp_get_attachment_image_srcset( $attachment_id, $image_size );
			$sizes  = wp_get_attachment_image_sizes( $attachment_id, $image_size );

			return sprintf(
				'<img class="%1$s" alt="%2$s" width="%3$d" height="%4$d" decoding="async" data-wpzoom-src="%5$s"%6$s%7$s data-wpzoom-full="%8$s" />',
				esc_attr( $classes ),
				esc_attr( (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ),
				absint( $image[1] ),
				absint( $image[2] ),
				esc_url( $image[0] ),
				$srcset ? ' data-wpzoom-srcset="' . esc_attr( $srcset ) . '"' : '',
				$sizes ? ' sizes="' . esc_attr( $sizes ) . '"' : '',
				esc_url( (string) wp_get_attachment_image_url( $attachment_id, 'large' ) )
			);
		}

		/**
		 * Build the images, plus the arrows and lightbox button when enabled.
		 *
		 * @since 1.1.0
		 * @param array  $ids        Attachment IDs, first one visible on hover.
		 * @param string $image_size
		 * @return string
		 */
		public function get_images_html( $ids, $image_size ) {

			if ( empty( $ids ) ) {
				return '';
			}

			$html = '';

			foreach ( array_values( $ids ) as $index => $id ) {
				$html .= 0 === $index
					? $this->get_image_html( $id, $image_size )
					: $this->get_deferred_image_html( $id, $image_size );
			}

			$controls = '';
			$mode     = $this->option( 'mode' );

			if ( 'slider' === $mode && count( $ids ) > 1 ) {
				$controls .= '<button type="button" class="wpzoom-wc-spi-nav wpzoom-wc-spi-prev" data-wpzoom-action="prev"></button>';
				$controls .= '<button type="button" class="wpzoom-wc-spi-nav wpzoom-wc-spi-next" data-wpzoom-action="next"></button>';
			}

			if ( 'yes' === $this->option( 'lightbox' ) ) {
				$position = $this->option( 'lightbox_position' );
				$allowed  = array( 'center', 'top-left', 'top-right', 'bottom-left', 'bottom-right' );

				if ( ! in_array( $position, $allowed, true ) ) {
					$position = 'center';
				}

				$controls .= sprintf(
					'<button type="button" class="wpzoom-wc-spi-nav wpzoom-wc-spi-expand wpzoom-wc-spi-expand-%s" data-wpzoom-action="lightbox"></button>',
					esc_attr( $position )
				);
			}

			if ( $controls ) {
				// The script moves this out of the product link before it is usable,
				// so the buttons are never nested inside an anchor.
				$html .= '<div class="wpzoom-wc-spi-controls" hidden>' . $controls . '</div>';
			}

			return $html;
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

			$ids = $this->get_secondary_image_ids( $product );

			return $ids ? absint( reset( $ids ) ) : 0;
		}

		/**
		 * Every image to reveal on hover, in display order.
		 *
		 * In `single` mode this is the dedicated secondary image, or one gallery
		 * image as a fallback. The rotating and sliding modes return the gallery.
		 *
		 * @since 1.1.0
		 * @param WC_Product $product
		 * @return array Attachment IDs.
		 */
		public function get_secondary_image_ids( $product ) {

			if ( ! $product instanceof WC_Product ) {
				return array();
			}

			$mode      = $this->option( 'mode' );
			$gallery   = $this->get_gallery_img_ids( $product );
			$secondary = absint( get_post_meta( $product->get_id(), self::META_KEY, true ) );

			$ids = array();

			if ( $secondary ) {
				$ids[] = $secondary;
			}

			if ( 'single' === $mode ) {

				$fallback = $this->option( 'fallback' );

				/**
				 * Kept for back-compatibility: overrides the fallback setting.
				 *
				 * @param bool $use_last Whether to reveal the last gallery image.
				 */
				$use_last = apply_filters( 'wpzoom_wc_spi_reveal_last_img', 'last' === $fallback );

				if ( ! $ids && $gallery && 'none' !== $fallback ) {
					$ids[] = absint( $use_last ? end( $gallery ) : reset( $gallery ) );
				}

			} else {

				foreach ( $gallery as $gallery_id ) {
					$ids[] = absint( $gallery_id );
				}
			}

			// Never repeat the image the shopper is already looking at.
			$featured = method_exists( $product, 'get_image_id' ) ? absint( $product->get_image_id() ) : 0;

			$ids = array_filter(
				array_unique( $ids ),
				function( $id ) use ( $featured ) {
					return $id && $id !== $featured;
				}
			);

			$max = 'single' === $mode ? 1 : max( 1, (int) $this->option( 'max_images' ) );
			$ids = array_slice( array_values( $ids ), 0, $max );

			/**
			 * Filters the images revealed on hover.
			 *
			 * @since 1.1.0
			 * @param array      $ids     Attachment IDs.
			 * @param WC_Product $product
			 */
			return apply_filters( 'wpzoom_wc_spi_image_ids', $ids, $product );
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

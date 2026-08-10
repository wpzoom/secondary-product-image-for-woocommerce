/**
 * Live preview for the Secondary Product Image settings.
 *
 * Builds a real product card from the store's own images and re-renders it
 * whenever a setting changes. The card uses the same markup, stylesheet and
 * script as the shop, so what you see here is what the shop will do.
 */
( function () {
	'use strict';

	var data = window.wpzoomWcSpiPreview || {};
	var root = document.getElementById( 'wpzoom-wc-spi-preview' );

	if ( ! root || ! data.primary || ! data.images || ! data.images.length ) {
		return;
	}

	var prefix = data.prefix || 'wpzoom_wc_spi_';

	var FIELDS = [
		'mode',
		'effect',
		'duration',
		'interval',
		'max_images',
		'fallback',
		'lightbox',
		'lightbox_position'
	];

	function field( name ) {
		return document.getElementById( prefix + name );
	}

	function value( name ) {
		var element = field( name );

		if ( ! element ) {
			return '';
		}

		return 'checkbox' === element.type ? element.checked : element.value;
	}

	function image( item, active ) {
		var img = document.createElement( 'img' );

		img.className = 'attachment-woocommerce_thumbnail wpzoom-wc-spi-secondary-img wpzoom-wc-spi-transition' + ( active ? ' is-active' : '' );
		img.src = item.src;
		img.alt = item.alt || '';

		if ( item.srcset ) {
			img.srcset = item.srcset;
		}

		if ( item.sizes ) {
			img.sizes = item.sizes;
		}

		img.setAttribute( 'data-wpzoom-full', item.full || item.src );

		return img;
	}

	/**
	 * Which images the current settings would reveal, mirroring the PHP side.
	 */
	function selected() {
		var mode = value( 'mode' );
		var images = data.images.slice();

		if ( 'single' !== mode ) {
			return images.slice( 0, Math.max( 1, parseInt( value( 'max_images' ), 10 ) || 1 ) );
		}

		var fallback = value( 'fallback' );

		if ( 'none' === fallback ) {
			// Only a dedicated secondary image would show, which this sample has not
			// necessarily got, so show the honest result: nothing.
			return [];
		}

		return [ 'last' === fallback ? images[ images.length - 1 ] : images[ 0 ] ];
	}

	function build() {
		var mode = value( 'mode' );
		var images = selected();

		var card = document.createElement( 'div' );
		card.className = 'wpzoom-wc-spi-preview-card product wpzoom-wc-spi-has-enabled';

		var primary = document.createElement( 'img' );
		primary.className = 'wpzoom-wc-spi-preview-primary wp-post-image';
		primary.src = data.primary.src;
		primary.alt = '';

		if ( data.primary.srcset ) {
			primary.srcset = data.primary.srcset;
		}

		card.appendChild( primary );

		if ( images.length ) {

			var container = document.createElement( 'div' );

			container.className = 'wpzoom-secondary-image-container wpzoom-wc-spi-effect-' + value( 'effect' );
			container.setAttribute( 'data-mode', mode );
			container.setAttribute( 'data-interval', parseInt( value( 'interval' ), 10 ) || 1200 );
			container.setAttribute( 'data-lightbox', value( 'lightbox' ) ? '1' : '0' );
			container.style.setProperty( '--wpzoom-wc-spi-duration', ( parseInt( value( 'duration' ), 10 ) || 0 ) + 'ms' );

			images.forEach( function ( item, index ) {
				container.appendChild( image( item, 0 === index ) );
			} );

			var controls = '';

			if ( 'slider' === mode && images.length > 1 ) {
				controls += '<button type="button" class="wpzoom-wc-spi-nav wpzoom-wc-spi-prev" data-wpzoom-action="prev"></button>';
				controls += '<button type="button" class="wpzoom-wc-spi-nav wpzoom-wc-spi-next" data-wpzoom-action="next"></button>';
			}

			if ( value( 'lightbox' ) ) {
				controls += '<button type="button" class="wpzoom-wc-spi-nav wpzoom-wc-spi-expand wpzoom-wc-spi-expand-' +
					( value( 'lightbox_position' ) || 'center' ) + '" data-wpzoom-action="lightbox"></button>';
			}

			if ( controls ) {
				var wrapper = document.createElement( 'div' );
				wrapper.className = 'wpzoom-wc-spi-controls';
				wrapper.hidden = true;
				wrapper.innerHTML = controls;
				container.appendChild( wrapper );
			}

			card.appendChild( container );
		}

		var meta = document.createElement( 'div' );
		meta.className = 'wpzoom-wc-spi-preview-meta';
		meta.innerHTML = '<span class="wpzoom-wc-spi-preview-title"></span><span class="wpzoom-wc-spi-preview-price"></span>';
		meta.querySelector( '.wpzoom-wc-spi-preview-title' ).textContent = ( data.product && data.product.title ) || '';
		meta.querySelector( '.wpzoom-wc-spi-preview-price' ).textContent = ( data.product && data.product.price ) || '';
		card.appendChild( meta );

		// Tell the shop script the old card is no longer hovered, so any rotation
		// timer it started is cleared before the node is thrown away.
		document.body.dispatchEvent( new PointerEvent( 'pointerover', { bubbles: true } ) );

		root.innerHTML = '';
		root.appendChild( card );

		if ( ! images.length ) {
			var note = document.createElement( 'p' );
			note.className = 'wpzoom-wc-spi-preview-note';
			note.textContent = ( data.i18n && data.i18n.nothing ) || '';
			root.appendChild( note );
		}
	}

	FIELDS.forEach( function ( name ) {
		var element = field( name );

		if ( ! element ) {
			return;
		}

		element.addEventListener( 'change', build );
		element.addEventListener( 'input', build );
	} );

	build();
} )();

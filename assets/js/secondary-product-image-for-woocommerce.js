/**
 * Secondary Product Image for WooCommerce
 *
 * Reveals the secondary images once a hover-capable pointer is present, and
 * matches each overlay to the box of the product image it covers. Themes crop
 * product images to their own ratio (Blocksy uses 4:3, others 1:1 or natural),
 * so the overlay is measured from the primary image rather than assumed.
 */
( function () {
	'use strict';

	if ( ! window.matchMedia || ! window.matchMedia( '(hover: hover) and (pointer: fine)' ).matches ) {
		return;
	}

	var CONTAINER = '.wpzoom-secondary-image-container';
	var ITEM = '.wpzoom-wc-spi-has-enabled';

	function measure( item, container, primary ) {
		var box = primary.getBoundingClientRect();

		if ( ! box.width || ! box.height ) {
			return;
		}

		var itemBox = item.getBoundingClientRect();

		// Offsets are relative to the item's padding box, which is what an
		// absolutely positioned child is placed against.
		container.style.top = ( box.top - itemBox.top - item.clientTop ) + 'px';
		container.style.left = ( box.left - itemBox.left - item.clientLeft ) + 'px';
		container.style.width = box.width + 'px';
		container.style.height = box.height + 'px';

		var secondary = container.querySelector( 'img' );

		if ( secondary ) {
			var primaryStyle = window.getComputedStyle( primary );

			// `fill` would stretch an image whose ratio differs from the primary.
			secondary.style.objectFit = 'fill' === primaryStyle.objectFit ? 'cover' : primaryStyle.objectFit;
			secondary.style.objectPosition = primaryStyle.objectPosition;
		}
	}

	function sync( item ) {
		if ( ! item || item.dataset.wpzoomWcSpi ) {
			return;
		}

		var container = item.querySelector( CONTAINER );
		var primary = item.querySelector( 'img:not(.wpzoom-wc-spi-secondary-img)' );

		if ( ! container || ! primary ) {
			return;
		}

		item.dataset.wpzoomWcSpi = '1';

		var apply = function () {
			measure( item, container, primary );
		};

		apply();

		// Keeps the overlay aligned through responsive reflows and lazy loading.
		if ( window.ResizeObserver ) {
			var observer = new ResizeObserver( apply );
			observer.observe( primary );
			observer.observe( item );
		}

		if ( ! primary.complete ) {
			primary.addEventListener( 'load', apply, { once: true } );
		}
	}

	function syncAll() {
		document.querySelectorAll( ITEM ).forEach( sync );
	}

	// Waiting for the first pointer movement keeps the images out of the initial
	// page load, and gives them time to decode before the first product is hovered.
	document.addEventListener(
		'pointerover',
		function ( event ) {
			if ( ! document.documentElement.classList.contains( 'wpzoom-wc-spi-ready' ) ) {
				document.documentElement.classList.add( 'wpzoom-wc-spi-ready' );
				syncAll();
				return;
			}

			// Products added after load, e.g. by AJAX filters or infinite scroll.
			if ( event.target.closest ) {
				sync( event.target.closest( ITEM ) );
			}
		},
		{ passive: true }
	);
} )();

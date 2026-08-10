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

	var measurers = new WeakMap();
	var ready = false;
	var hovered = null;

	function measurer( item ) {
		var container = item.querySelector( CONTAINER );
		var primary = item.querySelector( 'img:not(.wpzoom-wc-spi-secondary-img)' );

		if ( ! container || ! primary ) {
			return null;
		}

		return function () {
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
		};
	}

	/**
	 * Measure an item, setting it up on first sight.
	 *
	 * Measuring again on every hover is what keeps the overlay correct after a
	 * viewport change, a late web font, or a grid rebuilt by AJAX filters.
	 */
	function measure( item ) {
		if ( ! item ) {
			return;
		}

		var apply = measurers.get( item );

		if ( ! apply ) {
			apply = measurer( item );

			if ( ! apply ) {
				return;
			}

			measurers.set( item, apply );

			// Handles reflows that happen while the product is already hovered.
			if ( window.ResizeObserver ) {
				var observer = new ResizeObserver( apply );
				observer.observe( item );
			}

			// A lazy-loaded product image has no box to measure yet.
			var primary = item.querySelector( 'img:not(.wpzoom-wc-spi-secondary-img)' );

			if ( primary && ! primary.complete ) {
				primary.addEventListener( 'load', apply, { once: true } );
			}
		}

		apply();
	}

	// Waiting for the first pointer movement keeps the images out of the initial
	// page load, and gives them time to decode before the first product is hovered.
	document.addEventListener(
		'pointerover',
		function ( event ) {
			if ( ! ready ) {
				ready = true;
				document.documentElement.classList.add( 'wpzoom-wc-spi-ready' );
				document.querySelectorAll( ITEM ).forEach( measure );
			}

			if ( ! event.target.closest ) {
				return;
			}

			var item = event.target.closest( ITEM );

			// pointerover repeats for every child, so only act on a new product.
			// Tracking null too means leaving the grid and coming back re-measures.
			if ( item !== hovered ) {
				hovered = item;

				if ( item ) {
					measure( item );
				}
			}
		},
		{ passive: true }
	);
} )();

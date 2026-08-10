/**
 * Secondary Product Image for WooCommerce
 *
 * Flags the page as hover-capable so the CSS can reveal the secondary images.
 * Everything else is handled in CSS, which keeps the effect working with any
 * theme markup and with product grids loaded over AJAX.
 */
( function () {
	'use strict';

	if ( ! window.matchMedia || ! window.matchMedia( '(hover: hover) and (pointer: fine)' ).matches ) {
		return;
	}

	// Waiting for the first pointer movement keeps the images out of the initial
	// page load, and gives them time to decode before the first product is hovered.
	document.addEventListener(
		'pointerover',
		function () {
			document.documentElement.classList.add( 'wpzoom-wc-spi-ready' );
		},
		{ once: true, passive: true }
	);
} )();

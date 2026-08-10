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

	var config = window.wpzoomWcSpi || {};
	var i18n = config.i18n || {};

	var CONTAINER = '.wpzoom-secondary-image-container';
	var ITEM = '.wpzoom-wc-spi-has-enabled';
	var IMAGE = '.wpzoom-wc-spi-secondary-img';

	var hoverable = window.matchMedia && window.matchMedia( '(hover: hover) and (pointer: fine)' ).matches;

	if ( ! hoverable && 'yes' !== config.touch ) {
		return;
	}

	var products = new WeakMap();
	var ready = false;
	var hovered = null;

	/* --------------------------------------------------------------------- *
	 * Product
	 * --------------------------------------------------------------------- */

	function setup( item ) {

		var container = item.querySelector( CONTAINER );
		var primary = item.querySelector( 'img:not(.wpzoom-wc-spi-secondary-img)' );

		if ( ! container || ! primary ) {
			return null;
		}

		var product = {
			item: item,
			container: container,
			primary: primary,
			images: Array.prototype.slice.call( container.querySelectorAll( IMAGE ) ),
			controls: container.querySelector( '.wpzoom-wc-spi-controls' ),
			index: 0,
			timer: null
		};

		// Buttons rendered inside the product link would be interactive content
		// nested in an anchor, so they are moved up to the product itself.
		if ( product.controls ) {
			item.appendChild( product.controls );
			product.controls.hidden = false;
			label( product.controls );
			product.controls.addEventListener( 'click', function ( event ) {
				onControlClick( event, product );
			} );
		}

		product.measure = function () {
			measure( product );
		};

		if ( window.ResizeObserver ) {
			new ResizeObserver( product.measure ).observe( item );
		}

		if ( ! primary.complete ) {
			primary.addEventListener( 'load', product.measure, { once: true } );
		}

		products.set( item, product );

		return product;
	}

	function label( controls ) {
		var labels = {
			prev: i18n.previous,
			next: i18n.next,
			lightbox: i18n.expand
		};

		controls.querySelectorAll( '[data-wpzoom-action]' ).forEach( function ( button ) {
			var text = labels[ button.getAttribute( 'data-wpzoom-action' ) ];

			if ( text ) {
				button.setAttribute( 'aria-label', text );
				button.setAttribute( 'title', text );
			}
		} );
	}

	/**
	 * Size the overlay to the product image it covers.
	 *
	 * Measuring again on every hover is what keeps it correct after a viewport
	 * change, a late web font, or a grid rebuilt by AJAX filters.
	 */
	function measure( product ) {

		var box = product.primary.getBoundingClientRect();

		if ( ! box.width || ! box.height ) {
			return;
		}

		var itemBox = product.item.getBoundingClientRect();

		// Offsets are relative to the item's padding box, which is what an
		// absolutely positioned child is placed against.
		var top = ( box.top - itemBox.top - product.item.clientTop ) + 'px';
		var left = ( box.left - itemBox.left - product.item.clientLeft ) + 'px';

		[ product.container, product.controls ].forEach( function ( element ) {
			if ( ! element ) {
				return;
			}

			element.style.top = top;
			element.style.left = left;
			element.style.width = box.width + 'px';
			element.style.height = box.height + 'px';
		} );

		var primaryStyle = window.getComputedStyle( product.primary );

		product.images.forEach( function ( image ) {
			// `fill` would stretch an image whose ratio differs from the primary.
			image.style.objectFit = 'fill' === primaryStyle.objectFit ? 'cover' : primaryStyle.objectFit;
			image.style.objectPosition = primaryStyle.objectPosition;
		} );
	}

	/**
	 * Load the images that were held back from the initial page load.
	 */
	function hydrate( product ) {

		product.images.forEach( function ( image ) {
			var src = image.getAttribute( 'data-wpzoom-src' );

			if ( ! src ) {
				return;
			}

			var srcset = image.getAttribute( 'data-wpzoom-srcset' );

			if ( srcset ) {
				image.srcset = srcset;
			}

			image.src = src;
			image.removeAttribute( 'data-wpzoom-src' );
			image.removeAttribute( 'data-wpzoom-srcset' );
		} );
	}

	function show( product, index ) {

		var total = product.images.length;

		if ( ! total ) {
			return;
		}

		product.index = ( index + total ) % total;

		product.images.forEach( function ( image, position ) {
			image.classList.toggle( 'is-active', position === product.index );
		} );
	}

	function startRotating( product ) {

		var interval = parseInt( product.container.getAttribute( 'data-interval' ), 10 );

		if ( product.images.length < 2 || ! interval ) {
			return;
		}

		stopRotating( product );

		product.timer = window.setInterval( function () {
			show( product, product.index + 1 );
		}, interval );
	}

	function stopRotating( product ) {

		if ( product.timer ) {
			window.clearInterval( product.timer );
			product.timer = null;
		}
	}

	function onControlClick( event, product ) {

		var button = event.target.closest( '[data-wpzoom-action]' );

		if ( ! button ) {
			return;
		}

		// The controls sit on top of the product link.
		event.preventDefault();
		event.stopPropagation();

		var action = button.getAttribute( 'data-wpzoom-action' );

		if ( 'prev' === action ) {
			stopRotating( product );
			show( product, product.index - 1 );
		} else if ( 'next' === action ) {
			stopRotating( product );
			show( product, product.index + 1 );
		} else if ( 'lightbox' === action ) {
			stopRotating( product );
			lightbox.open( product, button );
		}
	}

	function enter( item ) {

		var product = products.get( item ) || setup( item );

		if ( ! product ) {
			return;
		}

		hydrate( product );
		measure( product );

		if ( 'rotate' === product.container.getAttribute( 'data-mode' ) ) {
			startRotating( product );
		}
	}

	function leave( item ) {

		var product = products.get( item );

		if ( ! product ) {
			return;
		}

		stopRotating( product );
		show( product, 0 );
	}

	/* --------------------------------------------------------------------- *
	 * Lightbox
	 * --------------------------------------------------------------------- */

	var lightbox = {

		element: null,
		sources: [],
		index: 0,
		opener: null,

		build: function () {

			if ( this.element ) {
				return this.element;
			}

			var root = document.createElement( 'div' );

			root.className = 'wpzoom-wc-spi-lightbox';
			root.setAttribute( 'role', 'dialog' );
			root.setAttribute( 'aria-modal', 'true' );
			root.hidden = true;

			root.innerHTML =
				'<div class="wpzoom-wc-spi-lightbox-backdrop" data-wpzoom-close></div>' +
				'<div class="wpzoom-wc-spi-lightbox-frame">' +
					'<img class="wpzoom-wc-spi-lightbox-image" alt="" />' +
					'<p class="wpzoom-wc-spi-lightbox-counter" aria-live="polite"></p>' +
				'</div>' +
				'<button type="button" class="wpzoom-wc-spi-lightbox-close" data-wpzoom-close></button>' +
				'<button type="button" class="wpzoom-wc-spi-lightbox-prev"></button>' +
				'<button type="button" class="wpzoom-wc-spi-lightbox-next"></button>';

			this.element = root;
			this.image = root.querySelector( '.wpzoom-wc-spi-lightbox-image' );
			this.counter = root.querySelector( '.wpzoom-wc-spi-lightbox-counter' );
			this.closeButton = root.querySelector( '.wpzoom-wc-spi-lightbox-close' );
			this.prevButton = root.querySelector( '.wpzoom-wc-spi-lightbox-prev' );
			this.nextButton = root.querySelector( '.wpzoom-wc-spi-lightbox-next' );

			this.closeButton.setAttribute( 'aria-label', i18n.close || 'Close' );
			this.prevButton.setAttribute( 'aria-label', i18n.previous || 'Previous' );
			this.nextButton.setAttribute( 'aria-label', i18n.next || 'Next' );

			var self = this;

			root.addEventListener( 'click', function ( event ) {
				if ( event.target.hasAttribute( 'data-wpzoom-close' ) ) {
					self.close();
				} else if ( event.target === self.prevButton ) {
					self.go( self.index - 1 );
				} else if ( event.target === self.nextButton ) {
					self.go( self.index + 1 );
				}
			} );

			document.body.appendChild( root );

			return root;
		},

		open: function ( product, opener ) {

			this.sources = product.images
				.map( function ( image ) {
					return image.getAttribute( 'data-wpzoom-full' ) || image.currentSrc || image.src;
				} )
				.filter( Boolean );

			if ( ! this.sources.length ) {
				return;
			}

			this.build();
			this.opener = opener;
			this.item = product.item;

			// The pointer has usually left the product by the time the lightbox is
			// closed. Keeping the item marked as active leaves the controls visible,
			// so focus has somewhere to return to.
			this.item.classList.add( 'wpzoom-wc-spi-active' );

			this.element.hidden = false;
			document.documentElement.classList.add( 'wpzoom-wc-spi-lightbox-open' );

			var single = this.sources.length < 2;

			this.prevButton.hidden = single;
			this.nextButton.hidden = single;

			this.go( product.index );
			this.closeButton.focus();

			document.addEventListener( 'keydown', this.onKeydown );
		},

		go: function ( index ) {

			var total = this.sources.length;

			this.index = ( index + total ) % total;
			this.image.src = this.sources[ this.index ];

			var template = i18n.counter || 'Image %1$s of %2$s';

			this.counter.textContent = template
				.replace( '%1$s', this.index + 1 )
				.replace( '%2$s', total );
		},

		close: function () {

			if ( ! this.element || this.element.hidden ) {
				return;
			}

			this.element.hidden = true;
			document.documentElement.classList.remove( 'wpzoom-wc-spi-lightbox-open' );
			document.removeEventListener( 'keydown', this.onKeydown );

			var opener = this.opener;

			this.opener = null;

			if ( opener ) {
				// Flush pending style changes so the button counts as visible, and
				// therefore focusable, at this point.
				void opener.offsetWidth;

				opener.focus();

				// A hidden button can't take focus; fall back to the product link
				// rather than dropping the shopper back at the top of the document.
				if ( document.activeElement !== opener ) {
					var item = opener.closest( ITEM );
					var link = item && item.querySelector( 'a[href]' );

					if ( link ) {
						link.focus();
					}
				}
			}

			if ( this.item ) {
				this.item.classList.remove( 'wpzoom-wc-spi-active' );
				this.item = null;
			}
		},

		onKeydown: function ( event ) {

			if ( 'Escape' === event.key ) {
				lightbox.close();
			} else if ( 'ArrowLeft' === event.key ) {
				lightbox.go( lightbox.index - 1 );
			} else if ( 'ArrowRight' === event.key ) {
				lightbox.go( lightbox.index + 1 );
			} else if ( 'Tab' === event.key ) {
				// Keep focus inside the dialog.
				var focusable = [ lightbox.closeButton, lightbox.prevButton, lightbox.nextButton ].filter( function ( button ) {
					return ! button.hidden;
				} );

				var position = focusable.indexOf( document.activeElement );
				var next = event.shiftKey ? position - 1 : position + 1;

				event.preventDefault();
				focusable[ ( next + focusable.length ) % focusable.length ].focus();
			}
		}
	};

	/* --------------------------------------------------------------------- *
	 * Wiring
	 * --------------------------------------------------------------------- */

	// Waiting for the first pointer movement keeps the images out of the initial
	// page load, and gives them time to decode before the first product is hovered.
	document.addEventListener(
		'pointerover',
		function ( event ) {

			if ( ! ready ) {
				ready = true;
				document.documentElement.classList.add( 'wpzoom-wc-spi-ready' );
				document.querySelectorAll( ITEM ).forEach( function ( item ) {
					var product = products.get( item ) || setup( item );

					if ( product ) {
						measure( product );
					}
				} );
			}

			if ( ! event.target.closest ) {
				return;
			}

			var item = event.target.closest( ITEM );

			// pointerover repeats for every child, so only act on a new product.
			// Tracking null too means leaving the grid and coming back re-measures.
			if ( item !== hovered ) {

				if ( hovered ) {
					leave( hovered );
				}

				hovered = item;

				if ( item ) {
					enter( item );
				}
			}
		},
		{ passive: true }
	);
} )();

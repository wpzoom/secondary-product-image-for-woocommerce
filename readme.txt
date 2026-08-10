=== Secondary Product Image for WooCommerce ===
Contributors:       wpzoom
Donate link:        http://paypal.me/wpzm/10usd
Author URI:         https://www.wpzoom.com/
Requires at least:  6.5
Tested up to:       7.1
Stable tag:         1.1.0
Requires PHP:       7.4
Requires Plugins:   woocommerce
License:            GPLv2 or later
License URI:        https://www.gnu.org/licenses/gpl-2.0.html
Tags:               woocommerce, product image, hover, flip, shop

Adds a hover effect to your WooCommerce product listings that reveals a secondary product image.

== Description ==

Secondary Product Image for WooCommerce adds a hover effect that will reveal a secondary product thumbnail to product images on your WooCommerce product listings

[VIEW DEMO](https://demo.wpzoom.com/balance/shop/)


== Features ==

* Allows to upload a secondary Featured Image for WooCommerce products
* Uses the first image from product gallery as fallback if there's no second Featured Image
* Works with Classic & Block Themes, including the Product Collection block
* Works with 99% themes
* Compatible with WooCommerce High-Performance Order Storage (HPOS)
* Supports Lazy Loading Images
* The secondary image is loaded only when hovering a product so it doens't affect the loading speed of pages
* Skipped on touch devices, so it never gets in the way of tapping a product

== Recommended Themes & DEMO ==

[**Balance**](https://www.wpzoom.com/themes/balance/)
[**Inspiro**](https://www.wpzoom.com/themes/inspiro/)
[**Inspiro PRO**](https://www.wpzoom.com/themes/inspiro-pro/) - *NEW*


= Additional Resources =

* [Grab a free theme](https://profiles.wordpress.org/wpzoom/#content-themes)
* [WPZOOM website](https://www.wpzoom.com)
* [GitHub repository](https://github.com/wpzoom/secondary-product-image-for-woocommerce)


== Installation ==

This section describes how to install the plugin and get it working.

1. Go to Plugins > Add New
2. Search for plugin name
3. Install & Activate.


== Screenshots ==

1. Secondary Product Image for WooCommerce
2. Add a 2nd Featured Image for your products


== Changelog ==

= 1.0.3 =
* Declared compatibility with WooCommerce High-Performance Order Storage (HPOS)
* Added support for the Product Collection and Products blocks
* Fixed the hover effect requiring the product title to be hovered first on some themes, including Divi
* Fixed the secondary image overflowing the product image on themes that crop thumbnails to their own ratio, such as Blocksy
* Sale badges and similar overlays now stay above the secondary image
* Fixed the secondary image appearing faded on themes that dim product images on hover
* The effect now follows the device's pointer instead of user agent detection, so it no longer breaks on cached pages
* The secondary image is now revealed for keyboard users and respects reduced motion preferences
* Fixed stale image data being left behind when an attachment was deleted from the Media Library

= 1.0.2 =
* Fixed an issue with the secondary image preloading
* Added compatibility with Block Themes
* The secondary image is now hidden on mobile devices to make it easier to navigate to products

= 1.0.1 =
* Minor bug fix

= 1.0.0 =
* Initial plugin release
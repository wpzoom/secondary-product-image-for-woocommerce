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
Tags:               woocommerce, product image, hover, flip, product gallery

Show a second product image on hover in your WooCommerce shop: fade, slide or zoom, gallery rotation, arrows and a lightbox.

== Description ==

Shoppers decide from the catalog page. **Secondary Product Image for WooCommerce** gives them a second look without a single click: hover a product in your shop, and a second image fades in over the first.

Set a dedicated hover image per product, or let the plugin use the product gallery you have already uploaded. It works on the shop page, category and tag archives, search results, related products, and anywhere else your theme lists products.

[VIEW DEMO](https://demo.wpzoom.com/balance/shop/)

= Pick a second image, or use the gallery you already have =

Every product gets a **Secondary Product Image** box next to the featured image, so you can choose exactly which photo appears on hover: the back of a shirt, a different colourway, the product being worn, a detail shot.

Not set one? The plugin falls back to the product gallery automatically, so an existing store gets the hover effect on every product without any editing. You choose whether the fallback is the first or the last gallery image, or whether such products simply show nothing.

= Three hover behaviours =

* **A single second image** — the classic image swap. One photo fades in, one fades out.
* **Rotate the gallery** — cycle through every gallery image automatically for as long as the shopper hovers, at a speed you set.
* **Previous / next arrows** — let shoppers step through the gallery themselves, right in the product grid, without opening the product.

= Fade, slide or zoom =

Choose the transition that suits your shop and set its speed in milliseconds. Prefer something instant? Set the speed to zero for a hard swap.

= Built-in lightbox =

Turn on the optional lightbox and a button appears on the product image. Click it and the images open full size over the page, with previous and next arrows, keyboard navigation and a counter. Place the button in the centre of the image or in any corner, so it never clashes with your theme's sale badge.

= Fast by default =

Extra gallery images are not downloaded when the page loads. They are fetched the first time a shopper hovers that particular product, so a shop page full of products stays as light as it was before.

The effect is also skipped entirely on phones and tablets, where a tap is meant to open the product rather than swap its picture. Those visitors never download the extra images at all. You can switch it on for touch devices if you prefer.

= Works with your theme =

Rather than assuming your product images are square, the plugin measures the image it is covering and matches it exactly, so the hover image cannot spill over your titles and prices. It has been built and tested against themes that crop thumbnails to their own ratio.

* Classic themes and block themes
* The Products and Product Collection blocks
* Product grids loaded by AJAX filters or infinite scroll
* Sale badges and other overlays stay on top of the hover image
* Keyboard users get the effect too, and it respects "reduce motion" system settings
* Compatible with WooCommerce High-Performance Order Storage (HPOS)

= Set it up in a minute =

All options live under **WooCommerce → Settings → Products → Secondary Image**, with a live preview so you can try every effect, speed and position before you save anything.

= Recommended Themes & DEMO =

[**Balance**](https://www.wpzoom.com/themes/balance/)
[**Inspiro**](https://www.wpzoom.com/themes/inspiro/)
[**Inspiro PRO**](https://www.wpzoom.com/themes/inspiro-pro/) - *NEW*

= Additional Resources =

* [Grab a free theme](https://profiles.wordpress.org/wpzoom/#content-themes)
* [WPZOOM website](https://www.wpzoom.com)
* [GitHub repository](https://github.com/wpzoom/secondary-product-image-for-woocommerce)

== Installation ==

1. Go to Plugins > Add New
2. Search for "Secondary Product Image for WooCommerce"
3. Install & Activate
4. Open WooCommerce > Settings > Products > Secondary Image to choose the effect
5. Edit any product and use the "Secondary Product Image" box to pick the image shown on hover

WooCommerce needs to be installed and active.

== Frequently Asked Questions ==

= How do I choose the image that appears on hover? =

Edit a product and look for the **Secondary Product Image** box, below the main product image box. Click it, pick an image from your media library, and update the product.

= Do I have to set an image for every product? =

No. If a product has no secondary image, the plugin uses its product gallery instead, so an existing shop works straight away. Under WooCommerce > Settings > Products > Secondary Image you can pick whether that fallback is the first or the last gallery image, or turn the fallback off so only products with a chosen image get the effect.

= Can I show more than one image on hover? =

Yes. Set "On hover show" to rotate through all gallery images automatically, or to show previous / next arrows so shoppers can browse the gallery themselves from the product grid.

= Will this slow down my shop page? =

The second image is fetched only when a shopper actually hovers that product, and only on devices with a mouse. Extra gallery images are held back the same way, so the initial page load is unaffected.

= Does it work on phones and tablets? =

By default the effect is switched off on touch devices, because a tap should open the product rather than change its image, and those visitors never download the extra images. There is a setting to enable it if you want it anyway.

= Does it work with my theme? =

It works with the vast majority of WooCommerce themes, classic and block-based. The hover image measures the product image underneath and matches its exact size and crop, so it fits themes that display square thumbnails as well as those that don't.

= Does it work with block themes and the Product Collection block? =

Yes. Both the classic shop templates and the Products / Product Collection blocks are supported.

= Is it compatible with HPOS? =

Yes. The plugin is declared compatible with WooCommerce High-Performance Order Storage. It only stores an image ID against your products and never touches order data.

= Can I change the animation, or how fast it is? =

Yes. Choose fade, slide or zoom, and set the duration in milliseconds. There is a live preview on the settings screen so you can see each option before saving.

= What is the lightbox, and is it the same as WooCommerce's? =

It is an optional button on the product image in your listings that opens the images full size over the page. WooCommerce's own lightbox works on the single product page; this one lets shoppers take a closer look without leaving the shop page. It can be turned off completely.

= Can I use my own styling or change the behaviour in code? =

Yes. The plugin exposes filters including `wpzoom_wc_spi_enabled`, `wpzoom_wc_spi_image_ids`, `wpzoom_wc_spi_secondary_product_thumbnail`, `wpzoom_wc_spi_image_class` and `wpzoom_wc_spi_option`, plus the CSS custom properties `--wpzoom-wc-spi-duration`, `--wpzoom-wc-spi-control-color` and `--wpzoom-wc-spi-control-background`.

= Where do I report a bug or request a feature? =

Use the support forum here, or open an issue on the [GitHub repository](https://github.com/wpzoom/secondary-product-image-for-woocommerce).

== Screenshots ==

1. A second product image revealed on hover in the shop
2. Choosing the secondary image on the product edit screen

== Changelog ==

= 1.1.0 =
* New settings screen under WooCommerce > Settings > Products > Secondary Image
* New live preview on the settings screen, so you can try every option before saving
* New Settings shortcut in the Plugins list
* New transition effects: fade, slide and zoom, with a configurable speed
* New hover mode that rotates through all gallery images automatically
* New hover mode with previous / next arrows
* New optional lightbox, opened from a button on the product image, which you can place in the centre or in any corner
* Gallery images are only downloaded once a product is hovered, so shop pages stay fast
* You can now choose whether the fallback is the first or the last gallery image, or nothing at all
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

== Upgrade Notice ==

= 1.1.0 =
Adds a settings screen with a live preview, three transition effects, gallery rotation, hover arrows and an optional lightbox. Your shop keeps behaving exactly as before until you change a setting.

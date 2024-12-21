=== WooCommerce Discount Manager ===
Contributors: barn2media
Tags: woocommerce, product, discount, coupons, pricing
Requires at least: 6.1
Tested up to: 6.6.2
Requires PHP: 7.4
Stable tag: 1.2.2
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Add advanced pricing rules and discounts to WooCommerce.

== Description ==

Add advanced pricing rules and discounts to WooCommerce.

== Installation ==

1. Go to Plugins -> Add New -> Upload and select the plugin ZIP file (see link in Purchase Confirmation Email).
2. Activate the plugin.
3. Follow the setup wizard.

== Frequently Asked Questions ==

Please refer to [the documentation](https://barn2.com/kb-categories/discount-manager-kb/).

== Changelog ==

= 1.2.2 =
Release date 11 November 2024

 * Fix: Compatibility issue with the Event Tickets Plus plugin.
 * Fix: WooCommerce Product Table - Crossed out price not displaying when using a lazy loaded table.
 * Fix: Divi theme - Unable to use the theme builder while the WooCommerce Wholesale Pro plugin is active.
 * Fix: Divi theme - Crossed out price not displaying correctly in the cart due to Divi's own override.

= 1.2.1 =
Release date 23 October 2024

 * Fix: Selection of products in the editor may not load products correctly if a variations related setting is missing.

= 1.2.0 =
Release date 23 October 2024

 * New: Added ability to discount products at variation level.
 * Fix: Crossed out price not displaying for individual variations when a discount is restricted to specific products.
 * Fix: "Buy X for Y Discount" discount type unable to properly handle large quantities of products while using specific pricing conditions.
 * Fix: WooCommerce Wholesale Pro - Sale price not being displayed correctly for cross-sells.
 * Fix: WooCommerce Quick View Pro - Discount content displaying twice inside the modal when specific settings are used.
 * Fix: "Fixed price" for the "Buy X products for a fixed price" discount type not being validated correctly.
 * Dev: Tested up to WooCommerce 9.3.3

= 1.1.8 =
Release date 20 September 2024

 * Tweak: Added ability to remove plugin's data on uninstall.
 * Fix: "Buy X for Y Discount" discount not applied when multiple variations are added to the cart under specific pricing conditions.
 * Fix: Unable to use mini cart due to a conflict with WooCommerce.
 * Dev: Tested up to WooCommerce 9.3.2 and WordPress 6.6.2

= 1.1.7 =
Release date 05 September 2024

 * Tweak: Minor UI improvements to the tinyMCE editor.
 * Fix: Pricing input field in the admin editor not using the correct currency format when the decimal separator is a comma.
 * Fix: "Discount type" setting not preselecting the correct value when switching between other discounts while an unsaved discount exists.

= 1.1.6 =
Release date 28 August 2024

 * Tweak: Users with the "manage_woocommerce" capability can now manage discounts.
 * Fix: Admin editor stylesheet loading on all pages instead of just the discounts editor page.
 * Fix: Divi - Fatal error when using the builder tool on pages using the "Woo Products" module.

= 1.1.5 =
Release date 12 August 2024

 * Fix: "Free Products" type not looping correctly under certain conditions.
 * Fix: Mini Cart not showing crossed out prices when WooCommerce Wholesale Pro is active.
 * Tweak: Expand the newly added discount after duplication.
 * Dev: Updated internal dependencies.

= 1.1.4 =
Release date 29 July 2024

 * Fix: Discounts editor not loading while using an RTL language due to a bug in WooCommerce core.
 * Fix: "Free products" type not working correctly under certain conditions.
 * Fix: Product page content defaulting to the wrong hook when the input field is left unchanged during creation of a discount.
 * Fix: Typo in the "sale badge" setting description.
 * Fix: Sale badge is hidden when WooCommerce Product Options is active.
 * Fix: Crossed out price for variable products is not displayed correctly when WooCommerce Wholesale Pro is active.
 * Tweak: updated how the pricing input field is rendered in the admin panel.
 * Tweak: added a workaround for Themes using outdated WooCommerce templates.
 * Dev: Tested up to WooCommerce 9.1.4
 * Dev: Updated internal dependencies.

= 1.1.3 =
Release date 08 July 2024

 * Fix: "Buy X products for Y discount" and "Free products" types behaving incorrectly when WooCommerce is configured to have prices with taxes excluded.
 * Fix: Calculation of subtotal taking in consideration all products instead of just relevant ones under certain conditions.
 * Fix: Ampersand escaped in category inclusions and exclusions input fields.
 * Tweak: Added compatibility with the Object Cache Pro plugin.
 * Tweak: The bulk table shortcode no longer has mandatory parameters and supports autodiscovery.
 * Dev: Tested up to WooCommerce 9.0.2
 * Dev: Updated internal dependencies.

= 1.1.2 =
Release date 13 June 2024

 * Tweak: Added new filter to discount entities when checking eligibility for categories.
 * Dev: Tested up to WooCommerce 8.9.3
 * Dev: Updated internal dependencies.

= 1.1.1 =
Release date 21 May 2024

 * Fix: Unable to complete checkout when using the "Buy X products for Y discount" with certain settings.
 * Fix: Search list component prefetching only 10 of all selected items on page load.

= 1.1.0 =
Release date 14 May 2024

 * New: Added name of discount used by a product on the admin order page.
 * New: Added note regarding original price to discounted price on the admin order page.
 * New: Added "Total savings" section on order receipt page.
 * New: Added "Total savings" section on the "Order details" and "New Order" email.
 * Dev: Added new hooks and filters.
 * Dev: Tested up to WooCommerce 8.8.3

= 1.0.3 =
Release date 11 April 2024

 * Fix: Discount date availability not taking in consideration the WordPress timezone.
 * Dev: Tested up to WordPress 6.5.2

= 1.0.2 =
Release date 04 April 2024

 * Fix: Check for discount availability between two dates was not taking in consideration today's date as a full day.
 * Fix: Free Products - calculation of subtotal taking in consideration all products instead of just the relevant ones.
 * Fix: Sale badge compatibility issue with themes and plugins returning unexpected content.
 * Tweak: Added order metadata to track discounted products and which discounts have been used.
 * Tweak: Added new hooks and filters for developers.
 * Dev: Tested up to WordPress 6.5.

= 1.0.1 =
Release date 28 February 2024

 * Fix: Free products - subtotal calculation taking in consideration all products instead of just relevant ones.
 * Fix: Free products - wrong discount amount when taxes enabled.
 * Tweak: Updated design of certain elements in Admin UI.
 * Tweak: Changed default value for the "Content location" setting.

= 1.0.0 =

 * New: Initial release.

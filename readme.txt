=== WC Bulk Edit & Category Tools ===
Contributors: rcmanuel
Tags: woocommerce, bulk edit, quick edit, categories, product categories
Requires at least: 5.2
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.2
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Native WooCommerce tools: taxonomy checklist search for Quick Edit and Bulk Edit, plus a default category cleaner.

== Description ==

This plugin combines two utilities for the WooCommerce product catalog in a single extension.

**1. Quick Edit and Bulk Edit search**

Adds a search field above the hierarchical taxonomy checklists
(categories, brands, tags, and any registered taxonomy) displayed by WooCommerce
in Quick Edit and Bulk Edit for products.

* Filters terms by text in real time with debounce.
* Keeps parent and child terms related to the match visible.
* Displays the result count and a button to clear the search.
* Works with nested checklists of any depth.

**2. Default category cleaner**

Adds a tool under *Products → Clean Default Category* that removes the default
WooCommerce category from simple products that also have other categories assigned.

* Simulates the process first, showing how many products can be cleaned and how many will remain unchanged.
* Executes the cleanup in batches with a progress bar.
* Never leaves a product without a category: it only acts when additional categories exist.
* Adds a shortcut to the top toolbar of the product list.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/` or install it from the
  WordPress plugin repository.
2. Activate the plugin from the *Plugins* menu.
3. Make sure WooCommerce is installed and active.

== Frequently Asked Questions ==

= Does it modify products that only have the default category? =

No. Only simple products that have the default category together with at least
one additional category are cleaned.

= Which product types are processed? =

Only products of type `simple`.

= Can I undo the cleanup? =

Not automatically. It is recommended to back up the database before running the
cleanup.

== Changelog ==

= 1.0.2 =
* Added local search to filter selected products during Bulk Edit without changing
  the native WordPress selection.

= 1.0.1 =
* Fixed Quick Edit and Bulk Edit search by delegating event handlers so they
  continue working when WordPress and WooCommerce clone complete product-list rows.

= 1.0.0 =
* Initial unified release. Includes taxonomy search in Quick Edit and Bulk Edit,
  plus the default category cleaner.

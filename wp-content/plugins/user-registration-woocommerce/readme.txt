=== User Registration WooCommerce ===
Contributors: WPEverest
Tags: user registration, addon, woocommerce, user registration woocommerce
Requires at least: 5.2
Requires PHP: 7.2
Tested up to: 6.5.3
Stable tag: 1.5.4
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

User Registration addon for WooCommerce

== Description ==

WooCommerce synchronization for user registration plugin.

Get [free support](https://wpeverest.com/support-ticket/)


### Features And Options:
* Show order,shipping address, billing address and downloadable products on user registration my account page
* Vertical and Horizontal tabs features
* Incredible Support
* Well Documented
* Translation ready

== Installation ==


== Frequently Asked Questions ==

= What is the plugin license? =

* This plugin is released under a GPL license.

= Does the plugin work with any WordPress themes?

Yes, the plugin is designed to work with any themes that have been coded following WordPress guidelines.

== Screenshots ==

== Changelog ==

= 1.5.4    - 31/05/2024 =
* Enhance - Template made overridable from Theme.

= 1.5.3    - 15/04/2024 =
* Feature  - Added smart phone number in billing phone number.
* Enhance  - File upload field sync on Product Page.
* Enhance  - Addon dependency on User Registration Pro.
* Tweak    - Addon update check triggered from User Registration Pro.
* Tweak    - Error message consistent with User Registration and Woocommerce.
* Fix      - WooCommerce my account edit profile sync.
* Fix      - Fatal error when woocommerce session not set
* Fix      - Backend and Frontend Validation on synced fields on Product Page.

= 1.5.2    - 12/02/2024
* Fix      - Billing Address and Shipping Address not saved.
* Fix      - WooCommerce Memberships undefined function error.
* Fix      - Add user with customer role to customer list of WooCommerce.
* Fix      - Placeholder not working in country state of billing and shiping.
* Fix      - Backend validation not working on Invite Codes fields on product page.
* Fix 	   - Country and file upload field not working in woocommerce checkout page.

= 1.5.1    - 13/12/2023 =
* Fix      - Conditional logic not working on checkout page.
* Fix      - Parse billing and shipping country name instead of country code.
* Fix      - Range field error message not being displayed in WooCommerce's my account page.

= 1.5.0    - 13/10/2023 =
* Feature  - Form fields integration on WooCommerce product page.
* Dev      - Declared HPOS feature compatibility.
* Tweak    - Changed hook to trigger for checking updates.

= 1.4.1    - 10/08/2023 =
* Fix      - Validation error when registering from checkout.
* Fix      - Translation and critical issue for WooCommerce My Account.
* Fix      - Undefined variable wp_button_class in Orders tab in my account page.

= 1.4.0    - 31/05/2023 =
* Enhance  - Changed checkbox option to toggle in global settings.
* Dev      - Compatibility with search global settings.
* Tweak    - Compatibility with new global settings design.
* Fix      - Edit address form not loading.
* Fix      - Translation Issue for Extra Information Text.
* Fix      - Error on WooCommerce My Account when syncing our form fields.
* Fix      - Critical issue due to array not supplied in in_array as parameter.

= 1.3.2    - 26/04/2023 =
* Fix      - Form Id not found in WooCommerce checkout.
* Fix      - Required validation in shipping fields when empty.

= 1.3.1    - 02/01/2023 =
* Enhance  - Hide payment fields from sync table.​
* Fix      - Billing phone field accepting invalid characters.​
* Fix      - Tooltip not showing in WooCommerce fields in frontend.

= 1.3.0    - 10/11/2022 =
* Feature  - Support Conditional logic in WooCommerce My Account.
* Feature  - Implement login options for WooCommerce checkout registration.
* Dev      - Check learndash function exists.

= 1.2.9    - 01/06/2022 =
* Enhance  - Learndash compatibility in checkout page.
* Fix 	   - Value checked issue while switching form.

= 1.2.8    - 14/03/2022 =
* Fix      - Required issue in checkout when conditionally hide.

= 1.2.7    - 13/12/2021 =
* Tweak    - User Registration Pro compatibility.

= 1.2.6    - 19/10/2021 =
* Dev      - Woocommerce v5.8.0 and later compatibility.

= 1.2.5    - 02/08/2021 =
* Feature  - Form field icons in user registration.
* Refactor - Settings page design and parsing.
* Refactor - JS codes.
* Fix      - Sync Firstname Lastname with WooCommerce Checkout Page.
* Fix      - Conditional logic error in checkout page.

= 1.2.4 - 31/05/2021 =
* Fix - Placeholder and hide label issue in state field.
* Fix - Invite Code not appear in checkout page.
* Dev - Replaced deprecated events.

= 1.2.3 - 18/11/2020 =
* Fix - Conditional logic not working in WooCommerce checkout page.
* Tweak - Deprecate old admin notice function for compatibility with hide notice.

= 1.2.2 - 13/10/2020 =
* Feature - User Registration login form in checkout page.
* Enhance - Specify Woocommerce Field Type while exporting user.

= 1.2.1 - 09/09/2020 =
* Fix - Woocommerce sync inconsistency with wp-admin edit user.
* Fix - Seperate shipping address field not working due to WordPress 5.5.

= 1.2.0 - 12/08/2020 =
* Tweak - Enqueue user registration my account scripts in woocommerce my account page.
* Tweak - UI in Woocommerce my account page to load registration forms filed.
* Tweak - Tooltip added in the form builder.
* Fix - Mailchimp sync with woocommerce my account and checkout page.
* Fix - Empty Registration Field data value of logged in user after the checkout.
* Fix - Enqueue scripts in only UR pages

= 1.1.7 - 13/07/2020 =
* Tweak - Order update and status added on view order page.
* Fix - Membership content not showing on UR Dashboard.
* Fix - Fields loading in multiple forms.
* Fix - Enqueue user-registration on checkout page.

= 1.1.6 - 15/05/2020 =
* Feature - Add custom CSS class field.
* Fix - View subscription page rendering.
* Fix - List states at initial load.

= 1.1.5 - 17/04/2020 =
* Dev - Woocommerce v4.0 later compatibility.
* Fix - State field loading on change of country field.
* Fix - Error on updating billing and shipping address.
* Fix - user-registration field not saved while checkout.

= 1.1.4 - 12/02/2020 =
* Enhancement - Invite code while checkout.
* Enhancement - Selective countries.
* Tweak 	  - Default shipping address unchecked.

= 1.1.3 - 08/11/2019 =
* Fix   - Added form_id parameter in profile details hook.
* Fix   - Separate shipping field hide show.
* Fix   - Profile extra fields empty when checkout.

= 1.1.2 - 02/10/2019 =
* Fix - Remove unused locale variable.

= 1.1.1 - 05/09/2019 =
* Tweak - Fields Icon change.
* Fix   - State field label change.

= 1.1.0 - 04/05/2019 =
* Feature - Sync profile details in WooCommerce's Account Page
* Feature - Sync fields with checkout page
* Feature - Replace WooCommerce Login and Registration form

= 1.0.6 - 04/01/2019 =
* Fix - Disable myaccount tab if respective enpoint is left empty in WooCommerce

= 1.0.5 - 31/12/2018 =
* Fix - Responsive orders and downloads tab in myaccount section

= 1.0.4 - 27/09/2018 =
* Feature - Support for WooCommerce Subscriptions extension
* Feature - Support for Woocommerce memberships extension
* Fix - Account page layout issue

= 1.0.3 - 03/08/2018 =
* Fix - Copy billing address issue
* Fix - Shipping title hide on uncheck ship to different address

= 1.0.2 - 11/07/2018 =
* Remove - WooCommerce tab in settings
* Fix - Copy billing address to shipping only if ship to different address selected

= 1.0.1 - 26/06/2017 =
* Feature - WooCommerce fields sync

= 1.0.0 - 22/09/2017 =
* Initial release

=== User Registration Advanced Fields ===
Contributors: WPEverest
Tags: user registration, addon, advanced fields, extra fields
Requires at least: 5.2
Requires PHP: 7.2
Tested up to: 6.3.1
Stable tag: 1.6.5
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

User Registration addon for Advanced Fields

== Description ==

Advanced Fields addition for user registration plugin.

Get [free support](https://wpeverest.com/support-ticket/)


### Features And Options:
* Extra Advanced fields
* WYSIWYG, Time picker, Phone Number, HTML, Section title support
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

= 1.6.5    - 13/10/2023
* Refactor - Frontend field structure of all fields for compatibility with conversational forms.
* Tweak    - Changed hook to trigger for checking updates.
* Fix      - Invalid file types on profile picture field.
* Fix      - Delete profile picture file from directory when removed while updating profile.

= 1.6.4    - 04/09/2023
* Enhance  - Option Enable/Disable taking picture from webcam.
* Fix      - Reset profile picture field after edit profile ajax complete.

= 1.6.3    - 10/08/2023
* Fix      - Vulnerability issue to arbitrary file upload.

= 1.6.2    - 29/06/2023
* Fix      - Large-size picture going out of cropping area.
* Fix      - Security Vulnerability issue from arbitrary file upload.

= 1.6.1    - 14/06/2023
* Tweak    - Create global constant for upload dir path and url.

= 1.6.0    - 31/05/2023
* Feature  - Hidden Field.
* Enhance  - Input mask template for phone field.
* Enhance  - Enabling/Disabling cropping in the profile picture field.
* Enhance  - Changed checkbox and yes/no select field option to toggle.
* Tweak    - Compatibility with new form builder setting design.

= 1.5.8    - 16/03/2023
* Refactor - Backend validation for form data.
* Tweak    - Do not initiate form submission while uploading profile picture.
* Fix      - Profile picture required issue.
* Fix      - Phone field validation error on smart format.
* Fix      - Disable wysisyg field in form builder preview.
* Fix      - Take snapshot button not appearing when file type validation set.
* Fix      - Profile picture filetype validation being applied on take snapshot.

= 1.5.7    - 20/02/2023
* Refactor - Profile picture upload method.
* Tweak    - Server side phone field mask validation.
* Tweak    - Change element tag from span to div for html field.
* Enhance  - Server side validation for multi-select2 choice limit.
* Fix      - Undefined index issue while updating user via admin side.

= 1.5.6    - 06/01/2023
* Feature  - Multi-Draggable URL Field.​
* Feature  - Bulk options add to the Select2 and Multi Select2 fields.​
* Enhance  - GD extension check.
* Tweak    - Description added for HTML field.​
* Fix      - Invalid file type error while uploading from edit profile.
* Fix      - Upload max file size not working.
* Fix      - Proper validation for deleting self uploaded file.
* Fix      - Files other than images being sent to cropping area.
* Fix      - Image not being uploaded in edit profile
* Fix      - Undefined index PHP notices for the Range field in the Account details of WooCommerce.​
* Fix      - Section title label not rendering properly on frontend as selected from field's setting option.
* Fix      - Removed input mask option from Time Picker and WYSIWYG. And Added placeholder option to the Time Picker field.​

= 1.5.5    - 14/11/2022
* Fix      - Security issue in profile picture upload.

= 1.5.4    - 27/09/2022 =
* Fix      - Enable payment slider option value not saving dynamically​.
* Fix      - Image overlap from profile picture popup with FSE supported theme.

= 1.5.3    - 06/09/2022 =
* Feature  - Add tooltip to advanced fields.
* Tweak    - Tooltip texts made more meaningful.​
* Tweak    - Replace incremental file name function with WordPress default​
* Dev      - Profile picture filename extracted appropriately.
* Dev      - Quantity field support for range field.​
* Dev      - Add payment slider enabled class for range field.​
* Fix      - Select All Translation issue.​
* Fix      - Conditional Logic enabled required field issue. ​
* Fix      - Remove uploaded profile picture if the user doesnot register.​

= 1.5.2    - 28/07/2022 =
* Fix      - File upload path permission denied due to no existence  of file directory.

= 1.5.1    - 14/07/2022 =
* Fix      - Profile picture required issue even if uploaded.​
* Fix      - Profile Picture removed when updated.

= 1.5.0 	- 01/06/2022 =
* Enhance  - Remove profile image file from directory when user is deleted.
* Enhance  - Profile Picture upload to `user_registration_uploads/profile-pictures` folder.
* Enhance  - Remove profile image file from directory when profile image is removed by user.
* Tweak    - Profile picture upload method.
* Tweak    - Incremental file name for duplicate files.
* Tweak    - Update `user_registration_profile_pic_url` user meta from url to attachment id.

= 1.4.9 	- 26/01/2022 =
* Fix       - Html display in visual switch of editor.
* Fix       - Profile Picture default max size changed to ini value.

= 1.4.8 	- 07/01/2022 =
* Dev       - Added custom filter for profile picture resolution.
* Dev       - Custom hooks added for prepopulate.

= 1.4.7 	- 13/12/2021 =
* Tweak 	- User Registration Pro compatibility.

= 1.4.6 	- 19/10/2021 =
* Enhance   - Readonly option in WYSIWYG field.
* Fix		- Required issue in form builder.

= 1.4.5 	- 22/09/2021 =
* Enhance   - Current time as default in timepicker field.
* Tweak		- Custom time interval in timepicker field.

= 1.4.4 - 02/08/2021 =
* Enhance  - Select all option in multiple choice field.
* Refactor - JS libraries.
* Refactor - JS Codes.
* Fix      - Class translation issue.

= 1.4.3 - 30/06/2021 =
* Feature - Field visibility in range field.
* Fix     - Range field error while Payment deactivated.

= 1.4.2 - 31/05/2021 =
* Enhance - Payment slider in range field.
* Enhance - Valid file types in profile picture.
* Fix     - Profile picture update after removval

= 1.4.1 - 15/03/2021 =
* Tweak 	- Handle deprecated jQuery methods.
* Fix 		- Profile picture strech in smaller screen devices.
* Fix 		- Advance fields load on elementor popup.
* Fix 		- WYSIWYG field value save and sanitization in edit-profile.

= 1.4.0 - 12/02/2021 =
* Feature - Range field.
* Enhance - Limit Choice in multi-select2 field.

= 1.3.5.1 - 02/12/2020 =
* Fix - Properly reinitialize advanced fields to resolve smart phone conflict.

= 1.3.5 - 18/11/2020 =
* Tweak - Fields re-initialized on full page load.
* Tweak - Deprecate old admin notice function for compatibility with hide notice.

= 1.3.4 - 09/09/2020 =
* Tweak - Profile field UI in form builder.
* Tweak - Select2 and Multi-select2 value and default value change and update.
* Fix - Select2 and Multi-select2 compatibility with WordPress 5.5

= 1.3.3 - 12/08/2020 =
* Tweak - Tooltip added in the form builder.
* Fix - Profile picture preview display in woocommerce my account.

= 1.3.2 - 13/07/2020 =
* Dev - WYSIWYG and Phone Field Compatibility in ajax form submission.
* Fix - Multiple forms load and submit.
* Dev - WPML Compatibility in My Account.
* Fix - Restrict advanced fields script equeued in non UR Page.
* Fix - Duplicated profile picture field.

= 1.3.1 - 14/05/2020 =
* Feature - Add custom CSS class field.
* Tweak - Profile picture field string translations.
* Fix - Advanced field profile data format make backend compatible.

= 1.3.0 - 12/02/2020 =
* Feature - Profile picture upload field.
* Fix 	  - Phone field input mask validation.

= 1.2.4 - 30/12/2019 =
* Fix - Advanced Field update issue.
* Enhance - Introduced smart format in phone field.

= 1.2.3 - 02/10/2019 =
* Fix - Undefined function.

= 1.2.2 - 05/09/2019 =
* Tweak - Fields Icon change.

= 1.2.1 - 26/08/2019 =
* Fix - Section Title header undefined.

= 1.2.0 - 19/08/2019 =
* Feature - Introducing Select2 and Multi-select2 field.
* Feature - Header ( H1 to H6 ) option for Section Title field.
* Fix     - Hide label field option.

= 1.0.5 - 23/07/2019 =
* Fix - Phone input mask in profile details.

= 1.0.4 - 11/10/2018 =
* Fix - Escaping attribute on conditional rules

= 1.0.3 - 09/10/2018 =
* Fix - Headers already sent issue

= 1.0.2 - 10/08/2018 =
* Feature - Customizable input mask for phone number
* Fix - Remove placeholder for unnecessary fields

= 1.0.1 - 11/07/2018 =
* Add - Conditions to advanced fields
* Fix - Repeating html and section title on my account page

= 1.0.0 - 25/06/2018 =
* Initial release

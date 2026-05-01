=== Group Registration for LearnDash ===
Contributors: WisdmLabs
Tags: Group Registration LearnDash, LearnDash Bulk Purchase, LearnDash multiple Courses
Requires WordPress version at least: 5.0
Tested up to: 5.2.4
LearnDash Version: 3.0.7.1
WooCommerce Version: 3.7.1
WooCommerce Subscriptions: 2.6.2
LD-WooCommerce Version: 1.6.0
Easy Digital Downloads Version : 2.9.17
LD-EDD Version : 1.1.1
License: GPL2


The Group Registration plugin for LearnDash allows Group leaders to purchase a course (or courses) on behalf of multiple students, and then enroll them to the course (or courses), by adding them as group members.

== Description ==
1. Group Leaders and Users have an option to purchase multiple copies/licenses of a single course during a single checkout.
2. A group is automatically created for the course which has been purchased in bulk, and the user making the purchase is set as the Group Leader.
3. Group Leaders have the option to enroll and unenroll multiple students for the courses they purchase, right from the front-end
4. Using the Pro Panel extension for LearnDash, Group Leaders can track user progress.
5. Admins can add multiple users as Group Leaders for a group created and can add or remove students from the group as well.

== Installation ==
Kindly note: The Group Registration plugin is an extension to LearnDash and WooCommerce. You will need to install and activate both these plugins along with the LD-WooCommerce Integration plugin.

1. Upon purchasing the Group Registration plugin, an email will be sent to the registered email id, with the download link for the plugin and a purchase receipt id. Download the plugin using the download link.
2. Go to Plugin-> Add New menu in your dashboard and click on the ‘Upload’ tab. Choose the downloaded plugin file to be uploaded and click on ‘Install Now’.
3. After the plugin has installed successfully, click on the Activate Plugin link or activate the plugin from your Plugins page.
4. An Group Registration for LearnDash License sub-menu will be created under Plugins menu in your dashboard. Click on this menu and enter your purchased product’s license key. Click on Activate License. If license is valid, an ‘Active’ status message will be displayed, else ‘Inactive’ will be displayed.
5. Upon entering a valid license key, and activating the license, you will find a ‘Course Author’ user role created, and ‘Course Creation Settings’ menu added to LearnDash settings.


== Frequently Asked Questions ==

Does the LD Group Registration plugin have any prerequisites?
Yes, you would need the WooCommerce plugin along with the LD-WooCommerce integration plugin to use the Group Registration plugin.

What will happen if my license expires?
Every purchased license is valid for one year from the date of purchase. During this time you will receive free updates and support. If the license expires, you will still be able to use the plugin, but you will not receive any support or updates.

I was using the plugin on my test site. Now, I need to activate the license on the live site. What do I do?
Don’t stress. Deactivate the license key from the staging site, by going to the Group Registration License sub-menu, and clicking on ‘Deactivate License’. You can then activate it on your live site and it should work fine.

Do you have a refund policy?
Please refer to our Terms and Conditions (https://wisdmlabs.com/terms-conditions/) to know more about our refund policy.

== Changelog ==
= 4.1.5 =
Fix: Removed display animation for product single page causing issues with quantity box.
Fix: Fixed escaping slashes for single quotes when entering group names.
Fix: Removed utf8 encoding while reading CSV data during CSV uploads.
Update: Added new filter hooks for group course list and group code form validation.
Update: Updated plugin language files.
= 4.1.4 =
Update: Updated quantity box animation to simple transition.
Update: Updated seat count updation for unlimited groups.
Update: Updated scrolling animation for notifications
Update: Added additional hidden order item meta to track group purchases to avoid translation conflicts.
Update: Added a new filter for tab users and fixed indentation issues.
Fix: Fixed date format issue causing wrong date displayed for group codes and also updated mailing to allow additional filters.
= 4.1.3 =
New Feature: Added new settings to support GDPR for the group code registration form.
Fix: Fixed group code date validation to check for start of the day for the from and end of day for the to date.
Fix: Styling fixes for group registration notifications.
Fix: Updated group code notification animations to cancel current animation if another is queued
Fix: Fix to clear group code registration form after successful submission.
= 4.1.2 =
Update: Updated the 'ldgr_send_group_mails' function to take in additional parameters to identify type of email and get related group ID.
Update: Updated report class template callbacks from include to ldgr_get_template and added filter to customize ajax course report data.
Update: Added a fix to support the quick view feature from the neve theme.
Update: Updated 'ldgr_get_template' function to add a new filter for arguments and updated action hooks arguments.
Update: Updated group name feature to work with package option.
Update: Added new action hook to allow adding custom fields before the group code field.
Fix: Fixed the infinite loading icon on group reports page while loading student statistics.
Fix: Fixed a bug with a static recaptcha test key being used instead of the configured one causing recaptcha issues.
Fix: Fixed an admin privilege check for sending reinvite emails.
Fix: Fixed group code image and updated the reports image for mobile view.

= 4.1.1 =
Update: Select whether to redirect users enrolled from group code to a page or display a custom message.
Fix: Removed the default seat limit when creating a group code instead added a filter to it.

= 4.1.0 =
New Feature : Group Enrollment Code - Users can add themselves to the groups using enrollment code. Secure, easy to create and manage with expiry dates to the code.
New Feature : A new [ldgr-group-code-registration-form] shortcode to enroll new as well as existing users using group code. A new user can register on the same page using the email address.
New Feature : An option to allow unlimited seats in the group.
Update: A placeholder to use in the registration email notification to reset the password.
Fix : An issue with default or empty emails being set for emails configured in group registration email settings.
Fix : An issue with individual and group checkbox selected for variable products.
Tweak : Styling improvements in the group management page.
Tweak : A toggle to enable/disable group registration email notifications.

= 4.0.3-beta =
Fix : Added fixes for group emails settings for reinvite email subject and group removal email body.
Fix : Fixed admin privileges for performing group registration functions.

= 4.0.2 =
Tweak : Added additional hooks and filters to the user registration and enrollment flow.

= 4.0.1 =
Tweak : Implemented ajax based batch processing for bulk user csv uploads.
Tweak : Added additional filters hooks.
Fix : Group name support for variable products.

= 4.0 =
Update : Updated the entire plugin structure and also updated coding standards to WordPress.
Update : Optimized the bulk user upload process on group registration page.

= 3.8.3 =
New Feature : Added functionality for bulk user removal request accept and remove for admin
Fixes : Updated survey form code and email setting templates and placeholders

= 3.8.2 =
New Feature : Custom group name feature with compatibility with some themes.
New Feature : Ajax based bulk student removal
Update : Updated 'wdm_modify_total_number_of_registrations' hook for additional parameters for order ID.
Update : Updated 'wdm_change_group_quantity' hook for additional parameters for order item details.
Fixes : Added fixes for hidden group name and description field on group edit page on admin end
Fixes : Code optimization for checking empty course lists fetches.


= 3.8.1 =
New Feature : Ajax based user CSV upload
New Feature : Show detailed course progress report to group leaders
New Feature : Settings in the backend to redirect users on certain pages after login. Role based login redirection.
Update : Added default messages in email settings
Update : Added settings link on plugins page
Update : Improve user removal alert message
Update : Updated the group registration settings on woocommerce product edit page.
Fixes : Changed text ‘select product’ word to Group’ on leader page
Fixes : Remove uppercase transform for header tags

= 3.8.0 =
New Feature : Display course wise report of group users on Group Registration Page
Support : Added support for resubscribe when a group leader cancels and resubscribes.

= 3.7.1 =
Bug Fix : For the Paid Course when subscription gets activated Group Leader gets access of courses.
Minor Fixes

= 3.7 =
Update : New Layout with responsiveness
Fixes : MAC users not able to work with CSV enrollment in group

= 3.6.4 =
Minor fixes related license
Update : Security Update for CSV enrollment
Bug Fix : Related Course selection gets removed on Product save
Update : Add filter to display the Remove button to Group Leader for user

= 3.6.3 =
Minor Fixes
Bug Fix : Enroll user into course(s) for variable product for individual purchase
New Feature : Allow Group Leader to reinvite Group Users
New Feature : Display Courses associated with Group on Group Registration Page
Update : POT File
Update : Compatible with LearnDash WooCommerce Integration version 1.5.0

= 3.6.2 =
Minor Fixes
Bug Fix : For variable products Group Leader was able to access the course even if he has not paid for it.
Compatibility of License code as per GDPR

= 3.6.1 =
Minor Fixes

= 3.6.0 =
Update Feature : Added setting for admin to avail the group's courses to Group Leader
Update Feature : Added setting for admin to select default option on Product Page for Group Registration
New Feature : Fixed Price packages for Group Purchase (WooCommerce Only
Compatibility : Make compatible with WooCommerce Variable Product type
Minor Fixes
Update : .pot files


= 3.5.2 =
Improvement : Updated .pot file
Minor Fixes

= 3.5.1 =
Improvement : Make Group Registration page Responsive
Update : Added all Group Registration setting under one page
Update : Change the sequence of parameters for the 'wdm_group_registration_label_below_product_name' filter
Update : Added missing strings for translation ready in /languages folder
Minor Fixes

= 3.5.0 =
New Feature : Modify email templates from learndash admin menu

= 3.4.0 =
New Feature : Restrict Product Quantity if Purchased for Single User
New Feature : Allow Group Leader to Remove User without Admin Approval
New Feature : Fix the Group Limit on User Removal
Fixes : Multiple Subscription Issue

= 3.3.0 =

Improvement: Admin can add multiple group leaders and these group leaders will be able to access the group registration page.

= 3.2.0 =
Bug Fixes

= 3.1.1 =

Bug Fix: Resolved issue with function check for subscriptions function

= 3.1.0 =
Compatibility : Compatible with WooCommerce Zapier
Bug Fix: Resolved issue with subscription renewals

= 3.0.0 =
New Feature : Group Purchase for WooCommerce Subscription Products
Improvement: WordPress 4.8 compatible

= 2.1.0 =
Compatibility: Compatible with LearnDash 2.4.2 & WooCommerce 3.0.1

= 2.0.2 =

Improvement: Updated License Integration
Bug Fix: Resolved plugin dependency issue

= 2.0.1 =
Bug Fixes

= 2.0.0 =
Made plugin integration with EDD plugin.

= 1.2.0 =
Made plugin compatible with LearnDash version 2.2.1.1.
Added new feature for enabling group registration checkbox on frontend.

= 1.0.1 =
PSR2 Standards compatible and code optimization to pass through all PHPMD checks.

= 1.0.0 =
*Plugin Released

== Upgrade Notice ==

= 4.1.0 =
<strong>Notice: </strong>We will be removing support for Easy Digital Downloads from the next update ( i.e. version 4.2.0 )

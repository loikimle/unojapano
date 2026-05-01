=== LearnDash Quiz Import/Export ===
Contributors: wooninjas
Tags: import, export, import-quizzes, export-quizzes, import-export-quizzes, excel-import, import-quizzes-using excel, learndash, learndash quiz, learndash quiz import export, learndash quiz questions
Requires at least: 4.0
Tested up to: 5.3.2
Stable tag: 3.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

LearnDash Quiz Import/Export is an addon which enables LearnDash to offer the flexibility to enable admins
to import quizzes from excel file or export quizzes to excel file.

== Description ==

LearnDash Quiz Import/Export is an addon which enables LearnDash to offer the flexibility to enable admins
to import quizzes from excel file or export quizzes to excel file. Admin can set default quiz values from plugin's setting page.

= Prerequisites: =

* Wordpress
* LearnDash (version 2.6.1 or greater)

= Features: =

* Import quizzes from excel file
* Export quizzes to excel file
* Admin can set default quiz values
* All learndash question type supported
* Option to select existing questions while importing
* Latex text can be imported/exported to the quizzes

= Action / Hooks: =

* ldqie_before_quiz_import ( Called, before importing the quiz )
* ldqie_after_quiz_import ( Called, After importing the quiz )

== Installation ==

Before installation please make sure you have latest LearnDash installed.

1. Upload the plugin files to the `/wp-content/plugins/` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress

== Frequently Asked Questions ==

= Can I select prerequisite quizzes before importing?

Yes, you can select prequisite quizzes from plugin's setting page.

== Screenshots ==

1. assets/import.png
2. assets/import-settings.png

== Changelog ==

= 3.2 =
* New: Added option to share existing question with imported quiz
* New: Made the import ajax compatible
* Fix: Made the add-on compatible with Latest version of LearnDash and WordPress

= 3.1 =
* Fix: Made the add-on compatible with Rename WPProQuiz DB Tables
* Fix: Fixed the question category title format issue

= 3.0 =
* Fix: Fixed question sorting issue on import

= 2.9 =
* Fix: Fixed PHP compatibility issues
* Fix: Fixed special charater issue while exporting the quiz
* Fix: Revamp LearnDash plugin dependency logic
* Fix: Fixed essay answer duplication issue on quiz builder

= 2.8 =
* Fix: Fixed PHP 7.0 or greater compatibility issue
* Fix: Fixed total points calculation issue
* Fix: Fixed empty column issue for matrix sort types questions

= 2.7 =
* Fix: Allowed 0 value as answer

= 2.6 =
* Fix: Allowed empty quiz content
* New: Display result after each submitted answer issue

= 2.5 =
* New: Made the add-on compatible with LearnDash version 3.0.5.1
* New: Added option to associate course/lesson/topic

= 2.4 =
* Fix: Fixed matrix sorting issue with Latex text
* Fix: Renamed auto loader library class name to avoid conflicts

= 2.3 =
* New: Added option to import/export images with Correct/incorrect answer messages
* New: Added option to import/export images with hint messages
* New: Moved import upload folder to WP uploads folder
* Fix: Fixed quiz random ordering issue
* Fix: Made the add-on compatible with LearnDash 2.6.4

= 2.2 =
* Fix: Fixed backslash issue with latex text

= 2.1 =
* New: Added support of images with latex text
* New: Added new column in the sheet for quiz title

= 2.0 =
* Fix: Questions ordering issue on importing

= 1.9 =
* New: Added option to select quiz title from Excel file name or excel sheet name
* Fix: Export file sheet name character limitation issue

= 1.8 =
* New: Made the add-on compatible with LearnDash latest verion (2.6.3)
* New: Added a new tab named status where the user can see how many quizzes have been imported/exported.
* New: Added a new tab named plugins options where existing questions can be kept or discarded.
* New: Addded tab to add custom field for form that renders before/after the quizzes (New in LearnDash)
* New: The name of the sheet will be taken as the quiz title upon importing. Previously file name was being added as quiz title.
* New: Added support to add single/double quotes in quiz title.
* New: Added option to display images on quiz questions along with the text.
* Fix: On Martix sorting, if answer type is set as html, then it will be applied to both fields i.e element and criterion.
* Fix: You can display your answers with images and texts by setting the value as html for the column.
* Fix: If there is not any category selected for questions then it renders errors on exporting the quiz

= 1.7 =
* Fix: true/false question importing issue
* Fix: Answer Type text/html issue for single, multiple and sort_answer question types
* Fix: overriding trash quizzes issue while importing

= 1.6 =
* New: Improve license system integrated to the addon.
* New: You can now deactivate your license from LD Quiz Import - License Settings screen
* Remove: Email field from activation screen

= 1.5 =
* Fix: Image display issue on frontend
* Fix: Import form redirection issue

= 1.4 =
* Fix: Quiz title conflict with other post type title on import

= 1.3 =
* Fix: Column’s title spelling issue on export

= 1.2 =
* New: Removed answer type image value from import excel file. From now you can add answer type html in the excel file if you want to add html answer to the questions.
* New: To add image as an answer, set answer type to html and add [image_complete_url] to the answer column.
* Fix: Warning issue on export
* Fix: Header already sent issue on export
* Fix: Empty answer column issue for martix_sorting type question on export
* Fix: Quiz description default value issue on import

= 1.1 =
* Fix: Quiz questions override issue
* Fix: Default quiz option issue on import / export

= 1.0 =
* Initial
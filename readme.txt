=== Make magic ===
Contributors: hakhakob
Requires at least: 4.6
Tested up to: 6.5.5
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Simple plugin to have a shortcode to display a form that accept user input
and have a shortcode to display the data with search functionality.
* Have a custom table in WordPress DB, and able to do Read and Write operations with it.
* Have a shortcode to display a form that accept user input, and then insert it into the custom table
* Have a shortcode to display the data from the custom table, with search functionality.

== Installation ==

Install Make magic plugin by uploading the ZIP file from the WordPress dashboard.
To install, go to your WordPress dashboard, click on 'Plugins', and choose 'Add New Plugin' then "Upload Plugin".
Choose the plugin ZIP file and click 'Install Now', and after installation, activate the plugin.

Use "Import and export users and customers" WordPress plugin to import users data from "user-export.csv" which
is included in the package.

After activation, you can add the [MAKEMAGICFORM] shortcode for showing the form and allowing users to submit the form
with their data. And add the [MAKEMAGICLIST] shortcode to show the submitted data with search functional.
All submitted data will be saved in the custom table named {your_db_prefix}_make_magic_things.
Used REST API to register rout to write tha data to the custom table and read tha data.
Note that this is a straightforward solution to meet all requirements. However, there is room for
improvement, including refining the code structure, implementing separate classes for logic,
adopting the MVC pattern, integrating pagination into the list, applying pleasing styles, and
incorporating various enhancements to optimize the solution.

== Changelog ==

= 1.0.0 =
* Initial version.
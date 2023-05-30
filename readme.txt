=== Auto Assign Role by Email Domain ===

Contributors: Shahid Hussain
Author URI: https://www.upwork.com/freelancers/~01304587883757d540
Author: Shahid Hussain
Tags:   User Role Management, Email Domain Rules, Automatic Role Assignment, User Registration, WordPress Plugin
Requires PHP: 7.3
Requires at least: 5.0
Tested up to: 6.2
Stable tag: 1.0.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Automatically assigns user role based on email domain at the time of user registration.

== Description ==

The "Auto Assign Role by Email Domain" plugin allows you to assign user roles based on their email addresses during registration. If a user's role has expired according to the specified time set in the plugin's settings, their role will be automatically changed to "Subscriber". Additionally, if the administrator deletes a domain rule in the plugin's settings page, all users with that specific domain name will have their roles changed to "Subscriber".

Instructions:

    Add New Domain Registration Rule:
        To add a new rule, locate the "Add New Domain Registration Rule" section above the table.
        Enter the email domain for which you want to assign a specific role.
        Select the desired user role from the dropdown menu.
        Specify the number of days for the role assignment to expire.
        Locate and click on the "Add" button after filling all the fields.

    Existing Domain Registration Rules
        This section displays a table containing the existing rules for assigning user roles based on email domains.
        Locate the "Delete" button under action column to delete previously added rule.

    Existing Domain Registration Rules
        This section displays a table containing the existing rules for assigning user roles based on email domains.
        Locate the "Delete" button under action column to delete previously added rule.

    Restrict registration to specific domain names:
        Users with the added domain name specified in the rule will be allowed to register on the website.
        All other domain registrations will be prohibited. Only users with the specified domain name will be able to successfully register.


Official Developer: [Shahid Hussain](https://www.upwork.com/freelancers/~01304587883757d540).

== Features ==

* Role Assignment based on Email Domain: The plugin allows administrators to automatically assign user roles based on the email domain used during user registration. This feature eliminates the need for manual role assignment and ensures consistency in role allocation.

* Flexible Rule Configuration: Administrators can define custom rules for role assignment using a simple format of "domain|role." This flexible rule configuration allows for precise control over role assignments based on specific email domains.

* Easy Settings Management: The plugin provides a user-friendly settings page in the WordPress admin area. Administrators can conveniently manage and update the email domain rules through a textarea input, making it easy to modify role assignments as needed.

* Multiple Rules Support: The plugin supports the definition of multiple rules, allowing for different role assignments based on various email domains. Administrators can add as many rules as required to cover different scenarios and user types.

* Error Logging: The plugin includes error handling capabilities, ensuring that any issues encountered during role assignment are logged for troubleshooting purposes. Error logs help administrators identify and address any problems that may arise during the automatic role assignment process.

* Seamless Integration: The plugin seamlessly integrates with the existing user registration process in WordPress. It hooks into the "user_register" action to trigger the automatic role assignment based on the defined email domain rules.

* Customizable Role Options: Administrators have full control over the roles available for assignment. They can choose from the default WordPress roles or use custom roles created using other plugins or themes. This flexibility ensures compatibility with various role management setups.

* Efficient Role Management: By automating role assignment based on email domains, the plugin streamlines the user role management process. Administrators can save time and effort that would otherwise be spent manually assigning roles to users based on their email addresses.

* Plugin Compatibility: The "Auto Assign Role by Email Domain" plugin is designed to work with the latest version of WordPress and is compatible with other popular plugins and themes. It maintains compatibility to ensure smooth functionality alongside other essential WordPress components.

* Regular Updates and Support: The plugin is actively maintained and supported by its author, Shahid Hussain. Regular updates ensure compatibility with new versions of WordPress and provide ongoing improvements and bug fixes.


== Usage ==

1. Install and activate the plugin.
2. Access the settings page.
3. Define email domain rules (domain|role).
4. Save settings.
5. User roles will be assigned automatically during registration based on email domain rules.

Languages: English.

== Screenshots ==

1. screenshot-1

== Changelog ==

= 1.0.0 =
* Initial release on wordpress.org
=== User Menus - Nav Menu Visibility ===
Contributors: codeatlantic, danieliser
Author URI:  https://code-atlantic.com/
Plugin URI:  https://wordpress.org/plugins/user-menus/
Donate link: https://code-atlantic.com/donate/
Tags: menu, menus, user-menu, logout, nav-menu, nav-menus, user, user-role, user-roles
Requires at least: 4.6
Tested up to: 5.5
Stable tag: 1.2.4
Requires PHP: 5.6
Freemius: 2.4.0.1
License: GPLv3 or Any Later Version

Show/hide menu items to logged in users, logged out users or specific user roles. Display logged in user details in menu. Add a logout link to menu.


== Description ==

User Menus is the perfect plugin for websites which have logged in users.

The plugin gives you more control over your nav menu by allowing you to apply visibility controls to menu items e.g who can see each menu item (everyone, logged out users, logged in users, specific user roles).

It also enables you to display logged in user information in the navigation menu e.g “Hello, John Doe”.

Lastly, the plugin allows you to add login, register, and logout links to your menu.

= Full Feature List =

User Menus allows you to do the following:

* Display menu items to everyone
* Display menu items to only logged out users
* Display menu items to only logged in users
* Display menu item to users with or without a specific user role.
* Show a logged in user’s {avatar} in a menu item with custom size option.
* Show a logged in user’s {username} in a menu item
* Show a logged in user’s {first_name} in a menu item
* Show a logged in user’s {last_name} in a menu item
* Show a logged in user’s {display_name} in a menu item
* Show a logged in user’s nickname} in a menu item
* Show a logged in user’s {email} in a menu item
* Add a logout link to menu (optional redirect settings)
* Add a register link to menu (optional redirect settings)
* Add a login link to menu (optional redirect settings)

** Includes a custom Menu Importer that will allow migrating User Menus data with the normal menu export/import.

= Created by Code Atlantic =

User Menus is built by the [Code Atlantic][codeatlantic] team. We create high-quality WordPress plugins that help you grow your WordPress sites.

Check out some of our most popular plugins:

* [Popup Maker][popupmaker] - #1 Popup & Marketing Plugin for WordPress
* [Content Control][contentcontrol] - Restrict Access to Pages and Posts

**Requires WordPress 4.6 and PHP 5.6**

[codeatlantic]: https://code-atlantic.com "Code Atlantic - High Quality WordPress Plugins"

[popupmaker]: https://wppopupmaker.com "#1 Popup & Marketing Plugin for WordPress"

[contentcontrol]: https://wordpress.org/plugins/content-control/ "Control Who Can Access Content"

== Installation ==

= Minimum Requirements =

* WordPress 4.6 or greater
* PHP version 5.6 or greater

= Installation =

* Install User Menus either via the WordPress.org plugin repository or by uploading the files to your server.
* Activate User Menus.
* Go to wp-admin > Appearance > Menus and edit your menu

If you need help getting started with User Menus please see [FAQs][faq page] which explains how to use the plugin.


[faq page]: https://wordpress.org/plugins/user-menus/faq/ "User Menus FAQ"


== Frequently Asked Questions ==

= How do I setup this plugin? =

* To setup the plugin, go to wp-admin > appearance > menu
* Once a menu item has been added to the menu, expand the menu item and select which user group (everyone (default option), logged out users, logged in users (all logged in users or select specific user roles) can see the menu item
* To show a logged in user’s information in a menu item, make a menu item only visible to logged in users and then click the grey arrow button to add a user tag (username, first_name, last_name, nickname, display_name, email) to the menu item label.
* To add a logout/login link to menu, expand the "User Links" menu item type and then add the logout and/or login link to the menu.

= Where can I get support? =

If you get stuck, you can ask for help in the [User Menu Plugin Forum](https://wordpress.org/support/plugin/user-menus).

= Where can I report bugs or contribute to the project? =

Bugs can be reported either in our support forum or preferably on the [User Menu GitHub repository](https://github.com/jungleplugins/user-menus/issues).


== Screenshots ==

1. Limit menu item visibility based on logged in status, user role etc.
2. Display user information such as username, first name etc in your menu text.
3. Quickly insert login/logout links & choose where users will be taken afterwards.


== Changelog ==

= v1.2.4 - 08/20/2020 =
* Improvement: Removed class that could cause links to be disabled with some themes.
* Tweak: Update Freemius sdk to v2.4.0.1.
* Fix: Compatibility issue with some sites where duplicate fields were shown in the menu editor.

= v1.2.3 - 3/23/2020 =
* Tweak: Add compatibility fix for WP 5.4 menu walker

= v1.2.2 - 12/17/2019 =
* Improvement: Login, Register & Logout menu links now hint at who they will be visible for.
* Fix: Deprecation notice for sites using PHP 7.4

= v1.2.1 - 10/20/2019 =
* Fix: Bug in some sites where Menu Editor Description field was not shown.

= v1.2.0 - 10/10/2019 =
* Feature: Added option to *show* or *hide* the menu item for chosen roles.
* Feature: Added Register user link navigation menu type with optional redirect.
* Improvement: Added Freemius integration to allow for future premium offerings
* Tweak: Updates brand from Jungle Plugins to Code Atlantic (nothing has changed, just the name).
* Tweak: Minor text and design changes.
* Fix: Bug where missing data in menu items caused an error to be thrown in edge cases.

= v1.1.3 =
* Improvement: Corrected usage of get_avatar to ensure compatibility with 3rd party avatar plugins.

= v1.1.2 =
* Improvement: Made changes to the nav menu editor to make it more compatible with other plugins.

= v1.1.1 =
* Fix: Forgot to add new files during commit. Correcting this issue.

= v1.1.0 =
* Feature: Added ability to insert user avatar in menu items with size option to match your needs.
* Improvement: Added accessibility enhancements to menu editor. Includes keyboard support, proper focus, tabbing & titles.
* Improvement: Added proper labeling to the user code dropdown.
* Tweak: Restyled user code insert elements to better resemble default WP admin.

= v1.0.0 =
* Initial Release

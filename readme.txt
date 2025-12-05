=== User Menus ===
Contributors: codeatlantic
Tags: menu, menus, user-menu, user-menus, logout, nav-menu, nav-menus, user, user-role, user-roles, navigation, visibility, roles, login, user
Requires at least: 6.0
Tested up to: 6.4
Stable tag: 2.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Show or hide menu items to logged in users, logged out users or specific user roles. Display logged in user details in menu. Add a logout link to menu.

== Description ==

User Menus allows you to control who sees what in your navigation menus with a beautiful, intuitive interface.

User Menus is the perfect plugin for websites that have logged in users and are using non-block based themes.

It gives you more control over your nav menu by allowing you to apply visibility controls to menu items e.g., who can see each menu item (everyone, logged out users, logged in users, specific user roles).

It also enables you to display logged in user information in the navigation menu e.g., “Hello, John Doe”.

Lastly, User Menus allows you to add login, register, and logout links to your menu.

= Features =

* **Visibility Control** - Show or hide menu items based on login status
* **Role-Based Access** - Restrict menu items to specific WordPress user roles
* **User Codes** - Display dynamic user information in menu titles (avatar, name, email)
* **Login/Logout Links** - Easy-to-add login, logout, and registration links
* **Custom Redirects** - Control where users go after login/logout
* **Modern Interface** - Built with React for a smooth, responsive experience

= How It Works =

1. Go to Appearance → Menus
2. Add or edit a menu item
3. Expand the item to see visibility options
4. Choose who can see the link: Everyone, Logged In, or Logged Out
5. For logged-in users, optionally select specific roles

= User Codes =

Insert these codes in menu item titles to display dynamic user information:

* `{avatar}` - User's avatar image
* `{first_name}` - User's first name
* `{last_name}` - User's last name
* `{display_name}` - User's display name
* `{username}` - User's login username
* `{nickname}` - User's nickname
* `{email}` - User's email address
* `{role}` - User's role

You can also add fallback values: `{first_name||Guest}` displays "Guest" for logged-out users.

= User Links =

Add login, logout, and registration links from the "User Links" metabox in the menu editor. These links automatically handle visibility (login/register for logged-out users, logout for logged-in users) and support custom redirect URLs.

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/user-menus/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Appearance → Menus to start using the visibility controls

== Frequently Asked Questions ==

= Does this work with block themes? =

User Menus is designed for classic themes that use the traditional Appearance → Menus system. Block themes using the Site Editor navigation blocks are not currently supported and can use our Content Control plugin instead.

= Can I show a menu item only to administrators? =

Yes! Set the visibility to "Logged In Users", then select only the "Administrator" role from the role options.

= How do I display the user's name in a menu item? =

Use the user code `{display_name}` in the menu item title. You can also use `{first_name}`, `{last_name}`, or other codes.

= What happens if a user code is empty? =

You can specify a fallback: `{first_name||Friend}` will display "Friend" if the first name is empty or the user is logged out.

== Screenshots ==

1. Menu item visibility settings
2. Role selection for logged-in users
3. User Links metabox with login/logout options
4. Settings page overview

== Changelog ==

= 2.0.0 =

* Improvement: Complete rebuild with React and responsive interface
* Fix: Add compatibility for Astra theme.
* Full compatibility with WordPress 6.0+
* Remove Freemius

= v1.3.2 - 07/19/2023 =

* Security: Fixes from the freemius library, notice can be seen [here](https://freemius.com/blog/freemius-wordpress-sdk-security-vulnerability/)

= v1.3.1 - 11/04/2022 =

* Tweak: Patch file mismatches in freemius/core/svn in last version.

= v1.3.0 - 09/13/2022 =

* Tweak: Upgrade freemius sdk to v2.4.5 for PHP 8.1 compatibility.

= v1.2.9 - 03/02/2022 =

* Tweak: Downgrade freemius sdk to the latest stable (previously version was Release Candidate).

= v1.2.8 - 03/02/2022 =

* Tweak: Update freemius sdk to the latest version.

= v1.2.7 - 07/21/2021 =

* Fix: Bug due to variable type mismatch which caused children of protected items to be rendered.

= v1.2.6 - 07/20/2021 =

* Improvement: Update Freemius to 2.4.2
* Improvement: Code styling clean up.
* Improvement: Compatibility with jQuery v3.

= v1.2.5 - 12/31/2020 =

* Improvement:Update Freemius to 2.4.1

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

=== cpcompatibility ===
Plugin Name:        CPcompatibility
Description:        Tweaks for working with CP: wpcli compatibility, plugin checks.
Version:            0.7.2
Text Domain:        cpc
Domain Path:        /languages
Requires PHP:       5.6
Requires:           1.0.0
Tested:             4.9.99
Author:             Gieffe edizioni
Author URI:         https://www.gieffeedizioni.it
Plugin URI:         https://software.gieffeedizioni.it
Download link:      https://github.com/xxsimoxx/cpcompatibility/releases/download/v0.7.2/cpcompatibility-0.7.2.zip
License:            GPLv2
License URI:        https://www.gnu.org/licenses/gpl-2.0.html

This plugin is for fixing WPCLI and alerting some compatibility issues for ClassicPress.
== Description ==
**Notices on plugin compatibility**
* Mark plugins not compatible with WP version 4.9 in plugins admin page
* Add a menu under "tools" that displays top 200 plugins from wp.org and their compatibility

**Fix wp-cli** 
* Fix `wp core check-update`

To help us know the number of active installations of this plugin, we collect and store anonymized data when the plugin check in for updates. The date and unique plugin identifier are stored as plain text and the requesting URL is stored as a non-reversible hashed value. This data is stored for up to 28 days.

== Screenshots ==

1. Plugin Page
2. Most popular plugins and their compatibility

== Changelog ==
= 0.7.2 =
* Speed up plugin notices.

= 0.7.1 =
* Removed unused lodash lib.

= 0.7.0 =
* Don't show warning if plugin version is bumped
* Code restyling

= 0.6.0 =
* Removed compatibility hacks for plugins deprecated in v. 0.4.0
* Recoded change_menu_page.php

= 0.5.1 =
* Don't link plugin without link

= 0.5.0 =
* Properly read options for wp core check updates fix

= 0.4.0 =
* Bring in scope $cp_version in WP-CLI
* Deprecated SEO by Rank Math and Caldera Forms fixes

= 0.3.0 =
* Rewritten wp core check-update so now it's as the original
* WP API is called just once when checking plugins
* Removed wp plugin latestgit

= 0.2.0 =
* New UI for plugin list
* Speed up plugin page loading
* Enforced coding standards

= 0.1.7 =
* Updated Update Manager

= 0.1.6 =
* Updated Update Manager

= 0.1.5 =
* Fixed a bug when in certain conditions a file was included twice

= 0.1.4 =
* Updated Update Manager

= 0.1.3 =
* Minor fixes

= 0.1.2 =
* Updated Update Manager

= 0.1.1 =
* wpapi.org used for plugin information replaced with plugins_api()
* Nicer plugins page with links and ordered
* Icon in plugin_action_links
* Code cleanup

= 0.1.0 =
* Add autoupdate code
* removed GitHub updater

= 0.0.12 =
* bugfix: PHP 7.4 compatibility

= 0.0.11 =
* bugfix: wp core check-update returned an extra line
* adds Caldera Forms > 1.8.4 (missing wp-components style)

= 0.0.10 =
* added wp plugin latestgit to install from GitHub
* function cpcompatibility_fixed_plugin() return an array of slugs of fixed plugins
* wp core check-update now supports --fields and --format options
* support for GitHub Updater (https://github.com/afragen/github-updater)
* self-checking for new releases

= 0.0.9 =
* split file/directory structure
* better CSS
* i18n l10n

= v0.0.7 =
* wp core check-update response is closer at the original behaviour

= v0.0.6 =
* Fixed SEO by Rank Math (v. 1.0.30.2)
* moved to "Tools" menu
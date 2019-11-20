=== vars ===
Plugin Name:        CPcompatibility
Description:        Tweaks for working with CP: wpcli compatibility, plugin checks.
Version:            0.1.0
Text Domain:        cpc
Domain Path:        /languages
Requires PHP:       5.6
Requires:           1.0.0
Tested:             4.9.99
Author:             Gieffe edizioni
Author URI:         https://www.gieffeedizioni.it
Plugin URI:         https://software.gieffeedizioni.it
Download link:      https://github.com/xxsimoxx/cpcompatibility/releases/download/v0.1.0/cpcompatibility.zip
License:            GPLv2
License URI:        https://www.gnu.org/licenses/gpl-2.0.html

This plugin is for fixing some compatibility issues for ClassicPress.
== Description ==

**Fix compatibility with plugins**
* SEO by Rank Math (v. 1.0.30.2)
* Caldera Forms > 1.8.4

**Fix wp-cli** 
* Fix `wp core check-update` (only supports --fields and --format options)
* Add `wp plugin latestgit <user> <repo>` command to install the latest GitHub release of a plugin.

**Notices on plugin compatibility**
* Mark plugins not compatible with WP version 4.9 in plugins admin page
* Add a menu under "tools" that displays top 200 plugins from wp.org and their compatibility

== Screenshots ==

1. Plugin Page
2. Most popular plugins and their compatibility

== Changelog ==

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
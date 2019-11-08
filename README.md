# cpcompatibility
This plugin is for fixing some compatibility issues for ClassicPress.

## Functions
### Fix compatibility with plugins
* SEO by Rank Math (v. 1.0.30.2)
* Caldera Forms > 1.8.4

### Fix wp-cli 
* Fix `wp core check-update` (only supports --fields and --format options)
* Add `wp plugin latestgit <user> <repo>` command to install the latest GitHub release of a plugin.

### Notices on plugin compatibility
* Mark plugins not compatible with WP version 4.9 in plugins admin page
* Add a menu under "tools" that displays top 200 plugins from wp.org and their compatibility

### Updating
This plugin supports [GitHub Updater](https://github.com/afragen/github-updater).
This allows to upgrade to the latest code.
If not installed, you will be noticed about new releases on the plugin page,
and you'll need to install the new version manually.

### Screenshots
![plugin page](assets/plugin-page.jpg)
![pupolar page](assets/popular-page.jpg)

## Changelog
* 2019/10/XX v0.0.13
* 2019/10/13 v0.0.12
	* bugfix: PHP 7.4 compatibility
* 2019/10/02 v0.0.11
	* bugfix: wp core check-update returned an extra line
	* adds Caldera Forms > 1.8.4 (missing wp-components style)
* 2019/09/25 v0.0.10
	* added `wp plugin latestgit` to install from GitHub
	* function `cpcompatibility_fixed_plugin()` return an array of slugs of fixed plugins
	* wp core check-update now supports --fields and --format options
	* support for GitHub Updater (https://github.com/afragen/github-updater)
	* self-checking for new releases
* 2019/09/05 v0.0.9
   * split file/directory structure
   * better CSS
   * i18n 
* 2019/09/02 v0.0.8
   * bugfix
* 2019/08/29 v0.0.7
   * wp core check-update response is closer at the original behaviour
* 2019/07/29 v0.0.6
   * Fixed SEO by Rank Math (v. 1.0.30.2)
   * moved to "Tools" menu
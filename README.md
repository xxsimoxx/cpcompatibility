# cpcompatibility
This plugin is for fixing some compatibility issues for ClassicPress.

## Functions
### Fix compatibility with plugins
* SEO by Rank Math (v. 1.0.30.2)

### Fix wp-cli 
* Fix `wp core check-update` (only supports --fields and --format options)
* Add `wp plugin latestgit <user> <repo>` command to install the latest GitHub release of a plugin.

### Notices on plugin compatibility
* Mark plugins not compatible with WP version 4.9 in plugins admin page
* Add a menu under "tools" that displays top 200 plugins from wp.org and their compatibility

## Changelog
* 2019/09/20 v0.1.0
	* added `wp plugin latestgit` to install from GitHub
	* function `cpcompatibility_fixed_plugin()` return an array of slugs of fixed plugins
	* wp core check-update now supports --fields and --format options
* 2019/09/05 v.0.0.9
   * split file/directory structure
   * better CSS
   * i18n 
* 2019/09/02 v.0.0.8
   * bugfix
* 2019/08/29 v.0.0.7
   * wp core check-update response is closer at the original behaviour
* 2019/07/29 v.0.0.6
   * Fixed SEO by Rank Math (v. 1.0.30.2)
   * moved to "Tools" menu

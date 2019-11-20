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

### Screenshots
![plugin page](images/screenshot-1.jpg)
![pupolar page](images/screenshot-2.jpg)
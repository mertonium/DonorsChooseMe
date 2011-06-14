=== Plugin Name ===
Contributors: mertonium
Tags: charity, donorschoose, education, widget, geolocation
Requires at least: 2.8
Tested up to: 3.13
Stable tag: trunk

This plugin displays DonorsChoose.org projects in the vicinity of the user (based on their IP address).

== Description ==

[DonorsChoose.org](http://www.donorschoose.org "DonorsChoose") is an online charity that makes it easy for anyone to help students in need.  
The DonorsChoose Projects Near Me Wordpress plugin does more than allow blog publishers to display DonorsChoose projects on their site: 
it estimates the blog viewer's physical location (via their IP address) and then displays projects that are in close proximity.

This plugin can be used via the `donorschoose` shortcode or via the included widget.

== Installation ==

1. Upload `donorschooseme` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Use the DonorsChoose Me widget or place `[donorschooseme]` in the body or a post or page.
1. You will need to get your own [DonorsChoose.org API key](http://developer.donorschoose.org/help-contact#TOC-Request-a-key) and enter it on the plugins settings page (Settings -> DonorsChoose Me). Note: If you are just testing out the plugin, you can use the API key: DONORSCHOOSE.

== Frequently Asked Questions ==

= Why does the plugin look un-styled =

The plugin has minimal styling so that it can be adapted to your theme.  The plugin automatically includes the *very* basic dcm_style.css style sheet.

= The projects listed are not physically near my users =

When a user visits your site the plugin looks at their IP address and checks it against a [free database service](http://www.geoplugin.com/webservices/php) to see where that IP address is located.
Mistakes can be made and some IP databases are not 100% accurate.

In the worst case scenario of an IP address not being geolocated, the plugin returns projects near Mt. Pleasant, Iowa (my hometown).


== Screenshots ==

1. The plugin in action as a sidebar widget

== Changelog ==

= 0.1 =
First version

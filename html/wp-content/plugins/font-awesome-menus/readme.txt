=== Font Awesome Menus ===
Contributors: New Nine
Author URI: http://www.newnine.com
Tags: menus, font awesome, navigation, responsive, nav menu, wp_nav_menu
Requires at least: 3.0
Tested up to: 3.5.1
Stable tag: 3.2
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

STOP! Before using this plugin, check out our new Font Awesome 4 Menus for WordPress!

== Description ==

STOP! Before using this older version of the plugin, check out [the new Font Awesome 4 Menus for WordPress](http://wordpress.org/plugins/font-awesome-4-menus/)!

__Seriously…__
Seriously - we're not supporting this anymore. If you're starting fresh, use our new plugin (above).  If you're upgrading, make sure you make note of the differences between the old i class codes and the new ones. Any issues that were found in this plugin have been addressed in the new version, and we've added a ton of new functionality too!

[Go to the new Font Awesome 4 Menus for WordPress](http://wordpress.org/plugins/font-awesome-4-menus/)

__Back to the old plugin notes…__

Add Font Awesome icons to your WordPress menus without touching a single line of code! With this plugin, just add icon-(icon name) as a class to your menu and the plugin will put that icon into your menu before the text!

With Font Awesome Menus, you have access to the old Font Awesome 3.2 library throughout your site. With the included shortcode, you can also add icons to pages, posts, custom post types, widgets - anywhere you want them!

__In your menus...__

Want to add a home icon to the "home" link on your menu? Just add:

`icon-home`

as a class in your menu and the icon will be inserted before the text.

__In your posts/pages__

Want that home icon to show up in a post or page or custom post type? Use the shortcode:

`[icon name=icon-home]`

and voila!

__In your theme__

Because Font Awesome is available throughout your site, use it in other parts of your theme too! Want that home icon hardcoded into your footer? Here you go:

`<i class="icon-home"></i>`

__It's all there, baby__

You can also use any and all of the code options and styles on the Font Awesome website. See more examples at <http://fontawesome.io/examples/>

== Installation ==

Use the WordPress installer; or, download and unzip the files and put the folder `font-awesome-menus` into the `/wp-content/plugins/` directory.

Then, activate the plugin through the 'Plugins' menu in WordPress.

To add icons to your menus, simply add the icon name (eg, icon-home) as a CSS Class on your menu item. Use the shortcodes in posts and pages, or use the icon class anywhere on your site!

== Frequently Asked Questions ==

= Will this plugin be updated? =

NO!!!! [Go to the new Font Awesome 4 Menus for WordPress](http://wordpress.org/plugins/font-awesome-4-menus/) for the latest version.

= Why didn't you just update this plugin rather than issue a new one? =

Font Awesome changed its code from using icon-(whatever) to fa-(whatever). If we automatically updated this plugin with the new code, all of the sites using the old one would break. And that's just rude.

= How to I add an icon to my menu? =

Go to Appearance -> Menus, select which menu item to which you want to add the icon, and add the icon class under 'CSS Classes (optional)'. (eg, to add the home icon to your 'Home' link, enter "icon-home" (without quotes) as a class.) Save your menu and voila!

= Why don't I see an option to add classes? =

Under Appearance -> Menus, click 'Screen Options' (top right of screen) and make sure that 'CSS Classes' is checked. If not - check it!

= Can I hide the text and just show the icons for my menu? =

Yes. Font Awesome menus adds a space between the icon and the text, and wraps that portion in a span with a class of "fontawesome-text". To hide the text and just show the icon, you can put `.fontawesome-text {display: none;}` in your stylesheet.

You can see this in action at our responsive site (http://www.newnine.com) where the mobile and smaller tablet versions only show the icons, but the text then appears on larger displays.

= Will this bloat or slow down my WordPress? =

No. The plugin only makes one option entry in your database which means it won't bloat your installation.

On your site, Font Awesome will load two stylesheets - the minified CSS (19kb) and the minified CSS for IE7 (30kb, in a conditional statement) - and the fonts. We use it on mobile-first responsive sites (and our own site) all the time without any noticeable performance drag.

= What happens to my menus if I deactivate/uninstall this? =

Your site will be fine. Where you used Font Awesome menus, those menu items will just have an additional class (icon-whatever) that you can erase or ignore (or style differently).

Only one setting is saved in your database, and that is removed if you uninstall the plugin. No bloat here!
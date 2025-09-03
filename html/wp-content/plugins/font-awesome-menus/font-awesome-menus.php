<?php
/*
Plugin Name: Font Awesome Menus
Description: Allows you to add Font Awesome icons to your WordPress menus. No programming necessary! Simply add "icon-(whatever)" to the menu class and this takes care of the rest!
Version: 3.2
Author: New Nine Media & Advertising
Author URI: http://www.newnine.com
License: GPLv2
Copyright: 2013 New Nine Media LP

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

class N9MFontawesomeMenus{
    private $plugin;
    private $version;
    function activation () {
        $options = get_option( 'n9m-fontawesome' );
        if ( !$options ) {
            $options = array(
                        'css' => 1,
                        'cssie7' => 1
            );
            update_option( 'n9m-fontawesome', $options );
        }
    }
    function admin_options_menu() {
        add_options_page( __( 'Font Awesome Menus' ), __( 'Font Awesome Menus' ), 'manage_options', 'fontawesome-menus', array( $this, 'admin_options_page' ) );
    }
    function admin_options_page() {
        if( $_GET['tab'] ) {
            $current = preg_replace( '/[^a-z]/', '', $_GET['tab'] );
        } else {
            $current = 'settings';
        }
        $tabs = array(
            'settings' => 'Font Awesome Settings',
            'use' => 'How to Use',
            'style' => 'How to Style',
            'updates' => 'Email Updates'
        );
        print '<div class="wrap">' . get_screen_icon();
        print '<h2 class="nav-tab-wrapper">';
        foreach( $tabs as $tab => $title ){
            print '<a class="nav-tab' . ($current==$tab ? ' nav-tab-active' : '' ) . '" href="?page=fontawesome-menus&tab=' .$tab .'">'. $title .'</a>';
        }
        print '</h2>';
        switch($current){
            case 'settings':
                if( $_POST && wp_verify_nonce( $_POST['n9mfaoptions'], 'n9mfaoptionsaction' ) ){
                    $_POST['css'] == 1 ? $options['css'] = 1 : '';
                    $_POST['cssie7'] == 1 ? $options['cssie7'] = 1 : '';
                    update_option( 'n9m-fontawesome', $options );
                    print '<div id="message" class="updated"><p>Your settings have been saved</p></div>';
                }
                $options = get_option( 'n9m-fontawesome' );
                print ' <p>General settings for Font Awesome Menus by <a href="http://www.newnine.com" title="New Nine Media &amp; Advertising" target="_blank">New Nine Media &amp; Advertising</a>:</p>
                        <form action="" method="post">
                            <table class="form-table">
                                <tbody>
                                    <tr valign="top">
                                        <th scope="row">Load Font Awesome stylesheet?</th>
                                        <td><label for="n9mcss"><input type="checkbox" name="css" id="n9mcss" value="1" ' . ( $options['css'] ? 'checked="checked"' : '' ) . ' /> If you already load Font Awesome elsewhere on your site, you can uncheck this. Or, leave this checked and delete the other references to Font Awesome on your site. No need to load the stylesheets twice and slow down your site!</label></td>
                                    </tr>
                                    <tr valign="top">
                                        <th scope="row">Include IE7 support?</th>
                                        <td><label for="n9mcssie7"><input type="checkbox" name="cssie7" id="n9mcssie7" value="1" ' . ( $options['cssie7'] ? 'checked="checked"' : '' ) . ' /> Load the Font Awesome Internet Explorer 7 stylesheet? (Uses a conditional statement specifically for IE7.)</label></td>
                                    </tr>
                                    <tr valign="top">
                                        <th scope="row"></th>
                                        <td>' . wp_nonce_field( 'n9mfaoptionsaction' , 'n9mfaoptions' ) . '<input type="submit" class="button-primary" name="n9moptionssubmit" value="Save Settings" /></td>
                                    </tr>
                                </tbody>
                            </table>
                        </form>';
                break;
            case 'use':
                print ' <h3>Just Getting Started?</h3>
                        <p>If you&#8217;re new to Font Awesome Menus and haven&#8217;t already implemented it on your site, dont&#8217;t start with this older version. Instead, <a href="http://wordpress.org/plugins/font-awesome-4-menus">get the new version free from WordPress - Font Awesome 4 Menus</a>.</p>
                        <h3>Step 1: WordPress Menus</h3>
                        <p>Create a standard WordPress menu (or use an existing menu) as you normally would under <a href="' . admin_url( '/nav-menus.php' ) . '" title="Appearance -> Menus">Appearance -> Menus</a>.</p>
                        <h3>Step 2: Find Your Desired Icon</h3>
                        <p>Locate the icon you want to use for a menu item at <a href="http://fortawesome.github.io/Font-Awesome/icons/" target="_blank" title="the Font Awesome website">the Font Awesome website</a> and make note of its name (<em>eg</em>, <code>icon-home</code> for the house icon).</p>
                        <h3>Step 3: Add the Icon Class to Your Menu Class</h3>
                        <p>Add the name of the icon as a CSS Class on your menu. In this case, add <code>icon-home</code> to make the house icon appear.</p>
                        <p>Don&#8217;t see an option to add a class on your menu? Twirl down <em>Screen Options</em> under <a href="' . admin_url( '/nav-menus.php' ) . '" title="Appearance -> Menus">Appearance -> Menus</a> (at the top right of the screen) and check CSS Classes under Show advanced menu properties. Then, twirl open one of your menu items and you&#8217;ll see it.</p>
                        <h3>Step 4: Enjoy!</h3>
                        <p>That&#8217;s it! You can now add an icon to any menu item at any level of your menu.</p>
                        <hr />
                        <h3>Optional Uses</h3>
                        <p>With Font Awesome Menus, you can use Font Awesome icons anywhere on your site. Unlike the menus which are limited to a single icon declaration, the shortcode and i class allow you to mix an match options like <em>pull-left</em> and <em>icon-spin</em>. Using the shortcode or i class, you can mix and match options just like in the <a href="http://fortawesome.github.io/Font-Awesome/examples/" target="_blank">Font Awesome Examples page</a>.</p>
                        <h4>The Shortcode</h4>
                        <p>In the WordPress text editor, you can use the shortcode <code>[icon name=&#34;icon-home&#34;]</code> to show the home icon, or put whatever icon name you want. Want the home icon to spin? Silly, but okay: <code>[icon name=&#34;icon-home icon-spin&#34;]</code></p>
                        <h4>The i Class</h4>
                        <p>In other parts of your site, use the Font Awesome code <code>&lt;i class=&#34;icon-home&#34;&gt;&lt;/i&gt;</code> (or whatever icon name) to make your icon appear. And who doesn&#8217;t love a spinning home icon? <code>&lt;i class=&#34;icon-home icon-spin&#34;&gt;&lt;/i&gt;</code></p>';
                break;
            case 'style':
                print ' <h3>How to Style Font Awesome Menus</h3>
                        <p>Font Awesome Menus works by inserting the icon before the text in your menu and then wrapping that text in a <code>span</code> with a class of <code>fontawesome-text</code>.</p>
                        <p>Without a Font Awesome icon, your menu code generally looks like this:</p>
                        <p><code>&lt;li id=&#34;menu-item-4&#34; class=&#34;menu-item menu-item-type-custom menu-item-object-custom current-menu-item current_page_item menu-item-home menu-item-4&#34;&gt;&lt;a href=&#34;' . site_url() . '&#34;&gt;Home&lt;/a&gt;&lt;/li&gt;</code></p>
                        <p>When you add an icon class to your link (in this case, the <code>icon-home</code> to the home link), the new menu item looks like this (changes in <strong style="color: #04c;">bold</strong>):</p>
                        <p><code>&lt;li id=&#34;menu-item-4&#34; class=&#34;menu-item menu-item-type-custom menu-item-object-custom current-menu-item current_page_item menu-item-home menu-item-4&#34;&gt;&lt;a href=&#34;' . site_url() . '&#34;&gt;<strong style="color: #04c;">&lt;i class=&#34;icon-home&#34;&gt;&lt;/i&gt;&lt;span class=&#34;fontawesome-text&#34;&gt; Home&lt;/span&gt;</strong>&lt;/a&gt;&lt;/li&gt;</code></p>
                        <p>You can now style your menus like you normally do, but you now have two extra options: 1) style the icon and 2) style the text.</p>
                        <h3>Font Awesome Menus in Responsive Design</h3>
                        <p>A common use of Font Awesome Menus in responsive design might be to hide the text portion of the menu on smaller screens. If you are designing mobile-first, your css might look something like this:</p>
                        <pre>.fontawesome-text {display: none;}
@media (min-width: 30em){
    .fontawesome-text {display: inline;}
}</pre>
                        <p>In this case, screens less than 30em wide (480px in general) would only show the icon. Above that width, both the icon and text would appear in your menu.</p>
                        <p>The possibilities are endless.</p>';
                break;
            case 'updates':
            default:
                global $current_user;
                get_currentuserinfo();
                print ' <h3>Register for Email Updates</h3>
                        <p>Want to be notified via email when we make more Font Awesome icons available for Font Awesome Menus? Register below and we&#8217;ll notify you when it&#8217;s time to update your plugin!</p>
                        <p>We&#8217;ll also infrequently notify you of other great plugins we&#8217;re working on. Don&#8217;t worry - you can unsubscribe at any time!</p>
                        <form action="http://newnine.us2.list-manage.com/subscribe/post?u=067bab5a6984981f003cf003d&amp;id=1b25a2aee6" method="post" name="mc-embedded-subscribe-form" target="_blank">
                            <table class="form-table">
                                <tbody>
                                    <tr valign="top">
                                        <th scope="row"><label for="em">Email Address</label></th>
                                        <td><input type="email" name="EMAIL" id="em" required value="' . $current_user->user_email . '" /></td>
                                    </tr>
                                    <tr valign="top">
                                        <th scope="row"><label for="fn">First Name</label></th>
                                        <td><input type="text" name="FNAME" id="fn" required value="' . $current_user->user_firstname . '" /></td>
                                    </tr>
                                    <tr valign="top">
                                        <th scope="row"><label for="ln">Last Name</label></th>
                                        <td><input type="text" name="LNAME" id="ln" required value="' . $current_user->user_lastname . '" /></td>
                                    </tr>
                                    <tr valign="top">
                                        <th scope="row"></th>
                                        <td><input type="hidden" name="group[14489][1]" value="1" /><input type="submit" name="subscribe" value="Join!" class="button-primary" /></td>
                                    </tr>
                                </tbody>
                            </table>
                        </form>
                        <p>Don&#8217;t forget to check out <a href="http://www.newnine.com/learn" title="our tutorials blog" target="_blank">our tutorials blog</a> to learn more about how you can make your WordPress sing!</p>';
                break;
        }
        print '</div>'; //Close div.wrap
    }
    function fontawesome_menu( $nav ) {
        $menu = preg_replace_callback(
                            '/<li((?:[^>]+)(icon-[^ ]+ )(?:[^>]+))><a[^>]+>(.*)<\/a>/',
                            array( $this, 'fontawesome_replace' ),
                            $nav
                        );
        print $menu;
    }
    function fontawesome_replace( $a ) {
        $listitem = $a[0];
        $icon = $a[2];
        $link_text = $a[3];
        $str_noicon = str_replace( $icon, '', $listitem );
        $str = str_replace( $link_text, '<i class="' . trim( $icon ) . '"></i><span class="fontawesome-text"> ' . $link_text . '</span>', $str_noicon );
        return $str;
    }
    function fontawesome_shortcode( $atts ){
        extract( shortcode_atts( array(
            'name' => '',
        ), $atts ) );
        if( $name ){
            $clean_name=preg_replace( '/[^a-z-\s]/', '', strtolower( $name ) );
            return '<i class="' . $clean_name . '"></i>';
        }
    }
    function settings_links( $links ) {
        $settings_link = '<a href="' . admin_url('options-general.php?page=fontawesome-menus') . '">Settings</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
    function styles() {
        $options = get_option( 'n9m-fontawesome' );
        if( $options['css'] ){
            wp_enqueue_style( 'n9m-fontawesome', plugins_url( '/css/font-awesome.min.css', __FILE__ ) );
        }
        if( $options['cssie7'] ){
            global $wp_styles;
            wp_enqueue_style( 'n9m-fontawesome-ie7', plugins_url( '/css/font-awesome-ie7.min.css', __FILE__ ) );
            $wp_styles->add_data( 'n9m-fontawesome-ie7', 'conditional', 'IE 7' );
        }
    }
    function uninstall() {
        delete_option( 'n9m-fontawesome' );
    }
    function __construct() {
        $this->plugin = basename( dirname( __FILE__ ) );
        $this->version = '3.2';
        add_action( 'admin_menu', array( $this, 'admin_options_menu' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'styles' ) );
        add_filter( 'plugin_action_links_'.plugin_basename( __FILE__ ), array($this, 'settings_links' ) );
        add_filter( 'wp_nav_menu' , array( $this, 'fontawesome_menu' ), 10, 2);
        add_shortcode( 'icon', array( $this, 'fontawesome_shortcode' ) );
        register_activation_hook( __FILE__, array( $this, 'activation' ) );
        register_uninstall_hook( __FILE__, array( $this, 'uninstall' ) );
    }
}
new N9MFontawesomeMenus();
?>
=== Easy Post Gallery ===
Plugin Name: Easy Post Gallery
Author: Megha Shah
Contributors: rathimegha
Tags: Gallery, Wordpress Gallery, Images
Requires at least: 4.6
Tested up to: 6.7.1
Stable tag: 1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html


Easy Post Gallery

== Description ==
"With the Easy Post Gallery plugin, you can effortlessly create and manage image galleries using a simple drag-and-drop interface. Upload multiple images and build any gallery you desire, all without any programming skills."

By integrating these features, Easy Post Gallery offers a user-friendly solution for managing and displaying image galleries across various post types in WordPress.

== Changelog ==

= 1.0 =
* Initial Release

== Upgrade Notice ==

= 1.0 =
* Initial Release

== Screenshots ==

1. Easy Post Gallery - Settings
2. Easy Post Gallery - Custom post type page

== Installation ==

1. Upload the entire `easy-post-gallery` folder to the /wp-content/plugins/ directory.
2. Activate the plugin through the 'Plugins' menu in WordPress


In case you find difficulties in setting up your plugin, feel free to write an email to megharathi715@gmail.com


== Frequently Asked Questions ==

= Can I include the Easy Post type in my Custom Post Type (CPT)? =

Absolutely! You can integrate the Easy Post Gallery with your Custom Post Types. Simply navigate to the WordPress Settings tab, find the Easy Post Gallery menu, and select the desired post type from the list. Click on the post type you wish to associate with the post gallery.

= Is there a limit on the number of images I can add? =

No, there's no limit. You're free to add as many images as you like to your post galleries.

= Is there a shortcode available for displaying images? =

No, currently there isn't a shortcode specifically provided for displaying images.

= How do I retrieve gallery images programmatically? =

You can retrieve gallery images using the following code snippet:

global $post;
$gallery_data = get_post_meta( $post->ID, 'gallery_data', true );

This code fetches the gallery data associated with the current post. Adjust it according to your requirements for displaying the gallery images.

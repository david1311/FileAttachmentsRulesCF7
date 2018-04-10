<?php
/*
Plugin Name: fileAttachmentsRulesCF7
Plugin URI: No yet
Description: Allow send attachments by rules in contact form 7
Version: 1
Author: David
Author URI:
License: GPLv2
*/
use FileAttachments\fileAttachmentsRulesCF7;

if ( file_exists( $composer_autoload = __DIR__ . '/vendor/autoload.php' ) /* check in self */
     || file_exists( $composer_autoload = WP_CONTENT_DIR.'/vendor/autoload.php') /* check in wp-content */
     || file_exists( $composer_autoload = plugin_dir_path( __FILE__ ).'vendor/autoload.php') /* check in plugin directory */
     || file_exists( $composer_autoload = get_stylesheet_directory().'/vendor/autoload.php') /* check in child theme */
     || file_exists( $composer_autoload = get_template_directory().'/vendor/autoload.php') /* check in parent theme */
) {
    require_once $composer_autoload;
}

    new fileAttachmentsRulesCF7();



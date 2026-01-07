<?php
/**
 * Plugin Name:     Dennis Plugin
 * Plugin URI:      PLUGIN SITE HERE
 * Description:     PLUGIN DESCRIPTION HERE
 * Author:          YOUR NAME HERE
 * Author URI:      YOUR SITE HERE
 * Text Domain:     dennis-plugin
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Dennis_Plugin
 */

// Your code starts here.

/**
 * Register the 'dennis_project' custom post type.
 *
 * @return void
 */
function dennis_plugin_register_post_type() {
	$labels = array(
		'name'               => __( 'Projects', 'dennis-plugin' ),
		'singular_name'      => __( 'Project', 'dennis-plugin' ),
		'menu_name'          => __( 'Projects', 'dennis-plugin' ),
		'add_new'            => __( 'Add New', 'dennis-plugin' ),
		'add_new_item'       => __( 'Add New Project', 'dennis-plugin' ),
		'edit_item'          => __( 'Edit Project', 'dennis-plugin' ),
		'new_item'           => __( 'New Project', 'dennis-plugin' ),
		'view_item'          => __( 'View Project', 'dennis-plugin' ),
		'search_items'       => __( 'Search Projects', 'dennis-plugin' ),
		'not_found'          => __( 'No projects found', 'dennis-plugin' ),
		'not_found_in_trash' => __( 'No projects found in Trash', 'dennis-plugin' ),
	);

	$args = array(
		'labels'       => $labels,
		'public'       => true,
		'has_archive'  => true,
		'rewrite'      => array( 'slug' => 'projects' ),
		'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
		'show_in_rest' => true,
		'menu_icon'    => 'dashicons-portfolio',
	);

	register_post_type( 'dennis_project', $args );
}
add_action( 'init', 'dennis_plugin_register_post_type' );

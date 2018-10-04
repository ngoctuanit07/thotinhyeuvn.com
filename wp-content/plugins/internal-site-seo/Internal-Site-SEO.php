<?php

/*
Plugin Name: Internal-Site SEO
Plugin URI: 
Description: This plugin helps you create a silo site where each posts will return back to your homepage and category from where your
			 posts are from and allows interlinking of posts
Version: 1.0
Author: Joshua Chang
Author URL: http://totalseopackage.com
License: GPLv2
*/

/* Copyright 2013 Joshua Chang (email : coralbue@gmail.com)
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/


//Sets version value
define("VERSION", "1.0");

//Calls the method to creates the default option for the plugin
register_activation_hook(__FILE__, 'iss_jj_set_default_options');

//Calls the method to add the changes into the body 
add_filter('the_content', 'iss_jj_link_adder');

// settings API to create the values of the admin page
add_action('admin_init', 'iss_jj_admin_init');

//Calls the method that creates an an admin page under settings
add_action( 'admin_menu', 'iss_jj_settings_menu' );


// Sets the defualt options for the plugin
function iss_jj_set_default_options() {
	if(get_option('iss_jj_options') === false) {
		$new_options['num_of_interlink'] = 3;
		$new_options['link_to_category'] = "true";
		$new_options['link_to_home'] = "true";
		$new_options['interlink_pages'] = "true";
		$new_options['version'] = VERSION;
		add_option('iss_jj_options', $new_options);
	}
}

// Receives the content of the post and adds a link back to the parent category of the post and the homepage
function iss_jj_link_adder($content) {
	$content = iss_jj_interlink_with_tags($content);
	$content = iss_jj_return_to_category_home($content);
	return $content;
}

function iss_jj_interlink_with_tags($content) {
	$options = get_option( 'iss_jj_options' );
	$link_count = 0;
	$max_link_count = $options['num_of_interlink'];
	if($options['interlink_pages']) {
		$tags = get_tags();
		foreach ($tags as $tag) {
			$url = '';
			$pos = stripos($content, $tag->name); // finds the numeric position of the first appearance of the tag
			$tag_name = $tag->name;
			if($pos ==! false && $link_count < $max_link_count) { // if tag exists in content
				$link_count++;
				$query = new WP_Query('tag='.$tag_name);
				//Goes through the posts that contain the keyword
				while($query->have_posts()) : $query -> the_post();
					$url = get_permalink();
				endwhile;
				//resets data of $query
				wp_reset_postdata();
				$content = substr_replace($content, '<a href="' . $url . '">' .$tag->name . '</a>', $pos, strlen($tag->name));
			}
		}	
	}
	return $content;
}

/*
 * Adds the link back to homepage and category
 * Returns the content
*/
function iss_jj_return_to_category_home($content) {
	$options = get_option( 'iss_jj_options' );
	$categories = get_the_category();
	if($options['link_to_category']) { 
		if($categories[0]) {
			$content .= '<p> Return back to <a href="' . get_category_link($categories[0]) . '">' . $categories[0]->name . '</a>';
			if($categories[1]) {
				foreach ($categories as $category) {
					if(!($category->name == $categories[0]->name)) {
						$content .= ', <a href="' . get_category_link($category) . '">' . $category->name . '</a>';
					}
				}
			}
			$content .= '</p>';
		}
	}
	if($options['link_to_home']) { 
		$content .= '<p> Return back to <a href=" ' . site_url() . '">Home' . '</a></p>';
	}
	return $content;
}
//Allows users to set new options for the plugin on the admin page
function iss_jj_admin_init() {
	register_setting('iss_jj_settings', 
		'iss_jj_options', 
		'iss_jj_validate_options');
	
	add_settings_section('iss_jj_main_section', 
		'Main Settings', 
		'iss_jj_main_setting_section_callback', 
		'iss_jj_settings_section');
	
	add_settings_field('num_of_interlink', 
		'Number of links per post', 
		'iss_jj_display_text_field', 
		'iss_jj_settings_section', 
		'iss_jj_main_section', 
		array('name' => 'num_of_interlink'));
		
	add_settings_field( 'link_to_category',
		'Link to category?',
		'iss_jj_display_link_category',
		'iss_jj_settings_section',
		'iss_jj_main_section',
		array( 'name' => 'link_to_category'));
		
	add_settings_field( 'link_to_home',
		'Link to home?',
		'iss_jj_display_link_homepage',
		'iss_jj_settings_section',
		'iss_jj_main_section',
		array( 'name' => 'link_to_home'));
		
	add_settings_field( 'interlink_pages',
		'Interlink posts?',
		'iss_jj_display_interlink_pages',
		'iss_jj_settings_section',
		'iss_jj_main_section',
		array( 'name' => 'interlink_pages'));

}

//Validates that the user is using the correct version
function iss_jj_validate_options($input) {
	$input['version'] = VERSION;
	return $input;
}

//Displays the front of the admin page
function iss_jj_main_setting_section_callback() { 
	?>
	<p>Main Configurations</p>
	<?php 
}

//Displays the text field for the admin page
function iss_jj_display_text_field( $data = array() ) {
	extract( $data );
	$options = get_option( 'iss_jj_options' );
	?>
	<input type="text" name="iss_jj_options[<?php echo $name;
	?>]" value="<?php echo esc_html( $options[$name] );
	?>"/><br />
	<?php 
}

//Displays the check box field for the admin page
function iss_jj_display_link_category( $data = array() ) {
	extract ( $data );
	$options = get_option( 'iss_jj_options' );
	?>
	<input type="checkbox"
	name="iss_jj_options[<?php echo $name; ?>]"
	<?php if ( $options[$name] ) echo ' checked="checked"';
	?>/>
	<?php 
}

function iss_jj_display_link_homepage( $data = array() ) {
	extract ( $data );
	$options = get_option( 'iss_jj_options' );
	?>
	<input type="checkbox"
	name="iss_jj_options[<?php echo $name; ?>]"
	<?php if ( $options[$name] ) echo ' checked="checked"';
	?>/>
	<?php 
}

function iss_jj_display_interlink_pages( $data = array() ) {
	extract ( $data );
	$options = get_option( 'iss_jj_options' );
	?>
	<input type="checkbox"
	name="iss_jj_options[<?php echo $name; ?>]"
	<?php if ( $options[$name] ) echo ' checked="checked"';
	?>/>
	<?php 
}



//Adds the admin page to the setting
function iss_jj_settings_menu() {
	add_options_page( 'Internal Site SEO Settings',
	'Internal Site SEO Settings', 'manage_options',
	'iss_jj-my-internal_site_configs',
	'iss_jj_config_page' );
}

//Creates the admin page for the user to see.
function iss_jj_config_page() { ?>
	<div id="iss_jj-general" class="wrap">
	<h2>Internal Site SEO - Settings</h2>
	<form name="iss_jj_options_form_settings_api" method="post"
	action="options.php">
	<?php settings_fields( 'iss_jj_settings' ); ?>
	<?php do_settings_sections( 'iss_jj_settings_section' ); ?>
	<input type="submit" value="Submit" class="button-primary" />
	</form>
	</div>
<?php 
}

?>
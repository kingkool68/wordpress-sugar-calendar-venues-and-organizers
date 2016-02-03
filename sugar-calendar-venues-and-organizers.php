<?php
/*
Plugin Name: Sugar Calendar - Venues & Organizers
Description: Adds Events & Organizer functionality to Sugar Calendar.
Author: Russell Heimlich
Version: 0.1
Author URI: http://www.russellheimlich.com
*/

function sc_event_custom_taxonomy() {

	$labels = array(
		'name'                       => 'Event Categories',
		'singular_name'              => 'Event Category',
		'menu_name'                  => 'Event Categories',
		'all_items'                  => 'All Event Categories',
		'parent_item'                => 'Parent Event Category',
		'parent_item_colon'          => 'Parent Event Category:',
		'new_item_name'              => 'New Event Category Name',
		'add_new_item'               => 'Add New Event Category',
		'edit_item'                  => 'Edit Event Category',
		'update_item'                => 'Update Event Category',
		'view_item'                  => 'View Event Category',
		'separate_items_with_commas' => 'Separate event categories with commas',
		'add_or_remove_items'        => 'Add or remove event categories',
		'choose_from_most_used'      => 'Choose from the most used',
		'popular_items'              => 'Popular event categories',
		'search_items'               => 'Search Event Categories',
		'not_found'                  => 'Not Found',
		'no_terms'                   => 'No event categories',
		'items_list'                 => 'Event categories list',
		'items_list_navigation'      => 'Event categories list navigation',
	);
	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => true,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => false,
	);
	register_taxonomy( 'event-category', array( 'sc_event' ), $args );

}
add_action( 'init', 'sc_event_custom_taxonomy', 0 );

class SC_Event_Venues_Organizers {
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
        add_action( 'admin_print_scripts-post-new.php', array( $this, 'print_scripts' ) );
        add_action( 'admin_print_scripts-post.php', array( $this, 'print_scripts' ) );

		include 'sc-event-venues.php';
		include 'sc-event-organizers.php';
	}

	public function admin_enqueue_scripts() {
        wp_register_script( 'sc-event-venues-and-organizers', plugins_url( '/js/sc-event-venues-and-organizers.js', __FILE__), array('jquery'), NULL, true );
    }

    public function print_scripts() {
        global $post_type;
        if( $post_type != 'sc_event' ) {
            return;
        }

        wp_enqueue_script( 'sc-event-venues-and-organizers' );

    }

	public function prettify_phone_number( $phone_number = '' ) {
        if( !$phone_number ) {
            return '';
        }
        // via http://stackoverflow.com/a/10741461
        return preg_replace( '~.*(\d{3})[^\d]{0,7}(\d{3})[^\d]{0,7}(\d{4}).*~', '($1) $2-$3', $phone_number );
    }
}
$sc_event_venues_organizers = new SC_Event_Venues_Organizers();

/* Helper Functions */

function sc_prettify_phone_number( $phone_number = '' ) {
	global $sc_event_venues_organizers;
	return $sc_event_venues_organizers->prettify_phone_number( $phone_number );
}

/* TODO:
- Integrate Google's Rich Snippet --> https://developers.google.com/structured-data/rich-snippets/events
- Static maps? -> https://developers.google.com/maps/documentation/static-maps/intro?hl=en#Markers
*/

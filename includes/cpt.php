<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function le_events_register_cpt() {
    register_post_type( 'le_event', array(
        'labels' => array(
            'name'               => 'Events',
            'singular_name'      => 'Event',
            'add_new'            => 'Add New Event',
            'add_new_item'       => 'Add New Event',
            'edit_item'          => 'Edit Event',
            'new_item'           => 'New Event',
            'view_item'          => 'View Event',
            'search_items'       => 'Search Events',
            'not_found'          => 'No events found',
            'not_found_in_trash' => 'No events found in trash',
        ),
        'public'       => true,
        'show_in_menu' => true,
        'menu_icon'    => 'dashicons-calendar-alt',
        'supports'     => array( 'title', 'editor' ),
        'has_archive'  => false,
        'rewrite'      => array( 'slug' => 'le-event' ),
    ) );

    register_taxonomy( 'le_event_category', 'le_event', array(
        'labels' => array(
            'name'          => 'Event Categories',
            'singular_name' => 'Event Category',
        ),
        'hierarchical'      => true,
        'show_in_menu'      => true,
        'show_admin_column' => true,
        'rewrite'           => array( 'slug' => 'le-event-category' ),
    ) );
}
add_action( 'init', 'le_events_register_cpt' );
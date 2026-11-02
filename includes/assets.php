<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_style(
        'le-montserrat',
        'https://fonts.googleapis.com/css2?family=Montserrat:wght@700&display=swap',
        array(),
        null
    );
    wp_enqueue_style(
        'linked-events',
        LE_EVENTS_URL . 'assets/linked-events.css',
        array( 'le-montserrat' ),
        '1.1.0'
    );
} );

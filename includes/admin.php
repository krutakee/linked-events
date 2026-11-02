<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// Add meta boxes
add_action( 'add_meta_boxes', function() {
    add_meta_box(
        'le_event_details',
        'Event Details',
        'le_event_details_meta_box',
        'le_event',
        'side',
        'high'
    );
} );

function le_event_details_meta_box( $post ) {
    wp_nonce_field( 'le_event_save', 'le_event_nonce' );

    $event_date = get_post_meta( $post->ID, '_le_event_date', true );
    $link_url   = get_post_meta( $post->ID, '_le_event_link_url', true );
    ?>
    <p>
        <label for="le_event_date"><strong>Event Date</strong></label><br>
        <input
            type="date"
            id="le_event_date"
            name="le_event_date"
            value="<?php echo esc_attr( $event_date ); ?>"
            style="width:100%;"
        >
    </p>
    <p>
        <label for="le_event_link_url"><strong>Link URL</strong></label><br>
        <input
            type="url"
            id="le_event_link_url"
            name="le_event_link_url"
            value="<?php echo esc_attr( $link_url ); ?>"
            placeholder="https://"
            style="width:100%;"
        >
        <span style="font-size:11px;color:#666;">If left empty, no link will appear.</span>
    </p>
    <?php
}

// Save meta
add_action( 'save_post_le_event', function( $post_id ) {
    if ( ! isset( $_POST['le_event_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['le_event_nonce'], 'le_event_save' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    if ( isset( $_POST['le_event_date'] ) ) {
        update_post_meta( $post_id, '_le_event_date', sanitize_text_field( $_POST['le_event_date'] ) );
    }

    if ( isset( $_POST['le_event_link_url'] ) ) {
        update_post_meta( $post_id, '_le_event_link_url', esc_url_raw( $_POST['le_event_link_url'] ) );
    }
} );

// Add date column to admin list
add_filter( 'manage_le_event_posts_columns', function( $columns ) {
    $new = array();
    foreach ( $columns as $key => $value ) {
        $new[ $key ] = $value;
        if ( $key === 'title' ) {
            $new['le_event_date']     = 'Event Date';
            $new['le_event_category'] = 'Category';
        }
    }
    return $new;
} );

add_action( 'manage_le_event_posts_custom_column', function( $column, $post_id ) {
    if ( $column === 'le_event_date' ) {
        $date = get_post_meta( $post_id, '_le_event_date', true );
        echo $date ? esc_html( date( 'M d, Y', strtotime( $date ) ) ) : '—';
    }
    if ( $column === 'le_event_category' ) {
        $terms = get_the_terms( $post_id, 'le_event_category' );
        echo $terms ? esc_html( implode( ', ', wp_list_pluck( $terms, 'name' ) ) ) : '—';
    }
}, 10, 2 );

// Make date column sortable
add_filter( 'manage_edit-le_event_sortable_columns', function( $columns ) {
    $columns['le_event_date'] = 'le_event_date';
    return $columns;
} );

add_action( 'pre_get_posts', function( $query ) {
    if ( ! is_admin() || ! $query->is_main_query() ) return;
    if ( $query->get( 'post_type' ) !== 'le_event' ) return;
    if ( $query->get( 'orderby' ) === 'le_event_date' ) {
        $query->set( 'meta_key', '_le_event_date' );
        $query->set( 'orderby', 'meta_value' );
    }
} );

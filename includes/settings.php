<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// Register settings
add_action( 'admin_init', function() {
    register_setting( 'le_events_settings', 'le_events_options', array(
        'sanitize_callback' => 'le_events_sanitize_options',
    ) );
} );

function le_events_sanitize_options( $input ) {
    $output = array();
    $output['past_categories'] = isset( $input['past_categories'] ) && is_array( $input['past_categories'] )
        ? array_map( 'intval', $input['past_categories'] )
        : array();
    return $output;
}

add_action( 'admin_menu', function() {
    add_options_page(
        'LinkedEvents Settings',
        'LinkedEvents',
        'manage_options',
        'linked-events-settings',
        'le_events_settings_page'
    );
} );

function le_events_settings_page() {
    $options   = get_option( 'le_events_options', array() );
    $sel_cats  = isset( $options['past_categories'] ) ? $options['past_categories'] : array();

    $all_cats = get_terms( array(
        'taxonomy'   => 'le_event_category',
        'hide_empty' => false,
    ) );
    ?>
    <div class="wrap">
        <h1>LinkedEvents Settings</h1>
        <form method="post" action="options.php">
            <?php settings_fields( 'le_events_settings' ); ?>
            <table class="form-table">
                <tr>
                    <th scope="row">Visible Past Event Categories</th>
                    <td>
                        <?php if ( ! empty( $all_cats ) ) : ?>
                            <?php foreach ( $all_cats as $cat ) : ?>
                            <label style="display:block;margin-bottom:6px;">
                                <input
                                    type="checkbox"
                                    name="le_events_options[past_categories][]"
                                    value="<?php echo esc_attr( $cat->term_id ); ?>"
                                    <?php checked( empty( $sel_cats ) || in_array( $cat->term_id, $sel_cats ) ); ?>
                                >
                                <?php echo esc_html( $cat->name ); ?>
                            </label>
                            <?php endforeach; ?>
                            <p class="description">Only checked categories will appear when "Past Events" is selected. The "Upcoming Events / Past Events" toggle will only show if there are past events in the checked categories. If none are checked, all categories are used.</p>
                        <?php else : ?>
                            <p class="description">No categories found. <a href="<?php echo admin_url( 'edit-tags.php?taxonomy=le_event_category&post_type=le_event' ); ?>">Add categories</a>.</p>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

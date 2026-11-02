<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Shortcode: [le_events_list per_page="8" show_past="false" order="ASC"]
 */
add_shortcode( 'le_events_list', function( $atts ) {

    $options      = get_option( 'le_events_options', array() );
    $past_cats    = isset( $options['past_categories'] ) && ! empty( $options['past_categories'] )
                    ? $options['past_categories'] : array();

    $atts = shortcode_atts( array(
        'per_page'  => 8,
        'show_past' => 'false',
        'order'     => 'ASC',
    ), $atts, 'le_events_list' );

    $per_page   = max( 1, intval( $atts['per_page'] ) );
    $order      = strtoupper( $atts['order'] ) === 'DESC' ? 'DESC' : 'ASC';
    $paged      = max( 1, isset( $_GET['events_page'] ) ? intval( $_GET['events_page'] ) : 1 );
    $filter_cat = isset( $_GET['le_cat'] ) ? sanitize_text_field( $_GET['le_cat'] ) : '';
    $today      = date( 'Y-m-d' );

    // Check if past events exist for the configured past categories
    $past_tax_query = array();
    if ( ! empty( $past_cats ) ) {
        $past_tax_query = array( array(
            'taxonomy' => 'le_event_category',
            'field'    => 'term_id',
            'terms'    => $past_cats,
        ) );
    }
    $past_exists = new WP_Query( array(
        'post_type'      => 'le_event',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'meta_query'     => array( array(
            'key'     => '_le_event_date',
            'value'   => $today,
            'compare' => '<',
            'type'    => 'DATE',
        ) ),
        'tax_query' => $past_tax_query,
    ) );
    $show_radio = $past_exists->found_posts > 0;

    // Determine time filter
    $time_filter = 'upcoming';
    if ( $show_radio && isset( $_GET['le_time'] ) && $_GET['le_time'] === 'past' ) {
        $time_filter = 'past';
    } elseif ( filter_var( $atts['show_past'], FILTER_VALIDATE_BOOLEAN ) && ! isset( $_GET['le_time'] ) ) {
        $time_filter = 'all';
    }

    // Build meta query
    $meta_query = array( 'relation' => 'AND',
        array( 'key' => '_le_event_date', 'compare' => 'EXISTS' ),
    );
    if ( $time_filter === 'upcoming' ) {
        $meta_query[] = array( 'key' => '_le_event_date', 'value' => $today, 'compare' => '>=', 'type' => 'DATE' );
    } elseif ( $time_filter === 'past' ) {
        $meta_query[] = array( 'key' => '_le_event_date', 'value' => $today, 'compare' => '<', 'type' => 'DATE' );
    }

    // Build tax query — past events use past_cats restriction, upcoming shows all
    $tax_query = array();
    if ( $filter_cat ) {
        $tax_query = array( array( 'taxonomy' => 'le_event_category', 'field' => 'slug', 'terms' => $filter_cat ) );
    } elseif ( $time_filter === 'past' && ! empty( $past_cats ) ) {
        $tax_query = array( array( 'taxonomy' => 'le_event_category', 'field' => 'term_id', 'terms' => $past_cats ) );
    }

    $query = new WP_Query( array(
        'post_type'      => 'le_event',
        'posts_per_page' => $per_page,
        'paged'          => $paged,
        'meta_key'       => '_le_event_date',
        'orderby'        => 'meta_value',
        'order'          => $order,
        'meta_query'     => $meta_query,
        'tax_query'      => $tax_query,
    ) );

    // Categories for filter dropdown — all cats for upcoming, past_cats for past
    $cat_args = array( 'taxonomy' => 'le_event_category', 'hide_empty' => false );
    if ( $time_filter === 'past' && ! empty( $past_cats ) ) {
        $cat_args['include'] = $past_cats;
    }
    $categories = get_terms( $cat_args );

    ob_start();
    ?>
    <div class="linked-events-wrap">

        <div class="le-controls-row">
            <?php if ( $show_radio ) : ?>
            <div class="le-time-toggle">
                <form method="get" action="" class="le-time-form">
                    <?php foreach ( $_GET as $key => $val ) :
                        if ( $key !== 'le_time' && $key !== 'events_page' ) : ?>
                            <input type="hidden" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $val ); ?>">
                    <?php endif; endforeach; ?>
                    <label class="le-radio-label <?php echo $time_filter === 'upcoming' ? 'le-radio-active' : ''; ?>">
                        <input type="radio" name="le_time" value="upcoming" <?php checked( $time_filter, 'upcoming' ); ?> onchange="this.form.submit()">
                        Upcoming Events
                    </label>
                    <label class="le-radio-label <?php echo $time_filter === 'past' ? 'le-radio-active' : ''; ?>">
                        <input type="radio" name="le_time" value="past" <?php checked( $time_filter, 'past' ); ?> onchange="this.form.submit()">
                        Past Events
                    </label>
                </form>
            </div>
            <?php endif; ?>

            <?php if ( ! empty( $categories ) ) : ?>
            <div class="linked-events-filter">
                <form method="get" action="">
                    <?php foreach ( $_GET as $key => $val ) :
                        if ( $key !== 'le_cat' && $key !== 'events_page' ) : ?>
                            <input type="hidden" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $val ); ?>">
                    <?php endif; endforeach; ?>
                    <select name="le_cat" class="linked-events-filter-select" onchange="this.form.submit()">
                        <option value="">Filter by</option>
                        <?php foreach ( $categories as $cat ) : ?>
                            <option value="<?php echo esc_attr( $cat->slug ); ?>" <?php selected( $filter_cat, $cat->slug ); ?>>
                                <?php echo esc_html( $cat->name ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
            <?php endif; ?>
        </div>

        <?php if ( $query->have_posts() ) : ?>
        <div class="linked-events-list">
            <?php while ( $query->have_posts() ) : $query->the_post();
                $post_id      = get_the_ID();
                $date         = get_post_meta( $post_id, '_le_event_date', true );
                $link_url     = get_post_meta( $post_id, '_le_event_link_url', true );
                $terms        = get_the_terms( $post_id, 'le_event_category' );
                $cat_name     = $terms ? esc_html( $terms[0]->name ) : '';
                $body         = wp_strip_all_tags( wp_trim_words( get_the_content(), 20, '...' ) );
                $is_valid_url = $link_url && filter_var( $link_url, FILTER_VALIDATE_URL );
            ?>
            <div class="le-event-row">
                <div class="le-event-date">
                    <?php if ( $date ) : $ts = strtotime( $date ); ?>
                        <span class="le-date-month"><?php echo date( 'M', $ts ); ?></span>
                        <span class="le-date-day"><?php echo date( 'd', $ts ); ?></span>
                    <?php endif; ?>
                </div>
                <div class="le-event-info">
                    <span class="le-event-title"><?php the_title(); ?></span>
                    <span class="le-event-meta">
                        <?php if ( $cat_name ) : ?><span class="le-event-cat"><?php echo $cat_name; ?></span><?php endif; ?>
                        <?php if ( $cat_name && $body ) : ?><span class="le-event-sep"> / </span><?php endif; ?>
                        <?php if ( $body ) : ?><span class="le-event-body"><?php echo esc_html( $body ); ?></span><?php endif; ?>
                    </span>
                </div>
                <div class="le-event-link">
                    <?php if ( $link_url ) : ?>
                        <?php if ( $is_valid_url ) : ?>
                            <a href="<?php echo esc_url( $link_url ); ?>" target="_blank" rel="noopener noreferrer" class="le-view-details">View Event Details &rsaquo;</a>
                        <?php else : ?>
                            <span class="le-view-details le-view-details--nolink">View Event Details &rsaquo;</span>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>

        <?php
        $total_pages = $query->max_num_pages;
        if ( $total_pages > 1 ) :
            $base_url = strtok( $_SERVER['REQUEST_URI'], '?' );
            $params   = $_GET;
            unset( $params['events_page'] );
            ?>
            <div class="linked-events-pagination">
                <?php for ( $i = 1; $i <= $total_pages; $i++ ) :
                    $params['events_page'] = $i;
                    $url        = $base_url . '?' . http_build_query( $params );
                    $is_current = ( $i === $paged ); ?>
                    <?php if ( $is_current ) : ?>
                        <span class="le-page-btn le-page-current"><?php echo $i; ?></span>
                    <?php else : ?>
                        <a href="<?php echo esc_url( $url ); ?>" class="le-page-btn"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                <?php if ( $paged < $total_pages ) :
                    $params['events_page'] = $paged + 1;
                    $next_url = $base_url . '?' . http_build_query( $params ); ?>
                    <a href="<?php echo esc_url( $next_url ); ?>" class="le-page-btn le-page-next">Next &rsaquo;</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php else : ?>
        <p class="linked-events-empty">No events found.</p>
        <?php endif; ?>

    </div>
    <?php
    return ob_get_clean();
} );

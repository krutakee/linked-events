<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Shortcode: [le_upcoming_events count="3" view_all_url="/events" order="ASC"]
 */
add_shortcode( 'le_upcoming_events', function( $atts ) {

    $options   = get_option( 'le_events_options', array() );
    $past_cats = isset( $options['past_categories'] ) && ! empty( $options['past_categories'] )
                 ? $options['past_categories'] : array();

    $atts = shortcode_atts( array(
        'count'        => 3,
        'view_all_url' => '/events',
        'order'        => 'ASC',
    ), $atts, 'le_upcoming_events' );

    $count        = max( 1, intval( $atts['count'] ) );
    $view_all_url = esc_url( $atts['view_all_url'] );
    $order        = strtoupper( $atts['order'] ) === 'DESC' ? 'DESC' : 'ASC';
    $today        = date( 'Y-m-d' );

    // Upcoming events only — no category restriction
    $query = new WP_Query( array(
        'post_type'      => 'le_event',
        'posts_per_page' => $count,
        'meta_key'       => '_le_event_date',
        'orderby'        => 'meta_value',
        'order'          => $order,
        'meta_query'     => array( array(
            'key'     => '_le_event_date',
            'value'   => $today,
            'compare' => '>=',
            'type'    => 'DATE',
        ) ),
    ) );

    ob_start();
    ?>
    <div class="linked-events-wrap le-upcoming-wrap">

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

        <?php if ( $view_all_url ) : ?>
        <div class="le-view-all-wrap">
            <a href="<?php echo $view_all_url; ?>" class="le-view-all-btn">View All Events</a>
        </div>
        <?php endif; ?>

        <?php else : ?>
        <p class="linked-events-empty">No upcoming events at this time. Please check back soon.</p>
        <?php endif; ?>

    </div>
    <?php
    return ob_get_clean();
} );

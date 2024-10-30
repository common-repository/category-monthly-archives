<?php
/**
 * Category Monthly Archives Widget class file
 */

/**
 * Category Monthly Archives Widget
 * This Widget displays category monthly archives links on category page.
 * Only displays links on category page.
 */
class CategoryMonthlyArchives extends WP_Widget
{
	/**
	 * Setting widget
	 */
	function __construct() {
		$widget_ops = array(
			'description' => __(
				'Displays monthly archives link on category page.',
				'cmarchives'
			),
		);
		parent::__construct(
			'CategoryMonthlyArchives',
			__( 'Monthly Category Archives', 'mcarchives' ),
			$widget_ops
		);
	}

	/**
	 * Display setting form on admin page.
	 * @param object $instance received from WordPress.
	 */
	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'count' => 0 ) );
		$title    = sanitize_text_field( $instance['title'] );
		$count    = $instance['count'] ? 'checked="checked"' : '';
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
				<?php esc_html_e( 'Title:', 'cmarchives' ); ?>
			</label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
				   name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
				   type="text" value="<?php echo esc_attr( $title ); ?>"/>
		</p>
		<p>
			<input class="checkbox" type="checkbox" <?php echo esc_attr( $count ); ?>
				   id="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>"
				   name="<?php echo esc_attr( $this->get_field_name( 'count' ) ); ?>"/>
			<label for="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>">
				<?php esc_html_e( 'Show post counts', 'cmarchives' ); ?>
			</label>
		</p>
		<?php
	}

	/**
	 * Save widget form setting.
	 * @param object $new_instance received from WordPress.
	 * @param object $old_instance received from WordPress.
	 */
	function update( $new_instance, $old_instance ) {
		$instance          = $old_instance;
		$new_instance      = wp_parse_args( (array) $new_instance, array( 'title' => '', 'count' => 0 ) );
		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		$instance['count'] = $new_instance['count'] ? 1 : 0;
		return $instance;
	}

	/**
	 * Display Category Monthly Archives links.
	 *
	 * @param array $args received from WordPress.
	 * @param object $instance received from WordPress.
	 */
	function widget( $args, $instance ) {
		global $wp_query;

		if ( ! is_category() ) {
			return;
		}
		$count = ! empty( $instance['count'] ) ? '1' : '0';
		$title = apply_filters(
			'widget_title',
			empty( $instance['title'] ) ? __( 'Archives', 'cmarchives' ) : sanitize_text_field( $instance['title'] ),
			$instance,
			$this->id_base
		);
		// Get category id.
		$cat_id = $wp_query->query_vars['cat'];

		// Display category monthly archives link.
		echo $args['before_widget'];
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		?>
		<ul>
			<?php
			$this->get_archives(
				apply_filters(
					'widget_archives_args',
					array(
						'type'            => 'monthly',
						'show_post_count' => $count,
						'cat_id'          => $cat_id,
					)
				)
			);
			?>
		</ul>
		<?php
		echo $args['after_widget'];
	}

	/**
	 * Get archives markup.
	 *
	 * Customize wp-includes/general-template.php -> wp_get_archives
	 *
	 * @param string|array $args Optional. Override defaults.
	 * @return string|null String when retrieving, null when displaying.
	 */
	private function get_archives( $args = '' ) {
		global $wpdb;

		// Get category id
		$cat_id = (int) $args['cat_id'];

		$defaults = array(
			'type'            => 'monthly',
			'limit'           => '',
			'format'          => 'html',
			'before'          => '',
			'after'           => '',
			'show_post_count' => false,
			'echo'            => 1,
			'order'           => 'DESC',
		);

		$r = wp_parse_args( $args, $defaults );

		$type = ( '' === $r['type'] ) ? 'monthly' : $r['type'];

		$limit = '';
		if ( '' !== $r['limit'] ) {
			$limit = absint( $r['limit'] );
			$limit = ' LIMIT ' . $limit;
		}

		$order = strtoupper( $r['order'] );
		if ( 'ASC' !== $order ) {
			$order = 'DESC';
		}

		$where      = apply_filters(
			'getarchives_where',
			"WHERE post_type = 'post' AND post_status = 'publish' AND t.term_taxonomy_id = " . $cat_id,
			$r
		);
		$custom_sql = "LEFT JOIN $wpdb->term_relationships AS r ON $wpdb->posts.ID = r.object_ID LEFT JOIN $wpdb->term_taxonomy AS t ON r.term_taxonomy_id = t.term_taxonomy_id LEFT JOIN $wpdb->terms as terms ON t.term_id = terms.term_id";
		$join       = apply_filters( 'getarchives_join', $custom_sql, $r );

		$output = '';

		$last_changed = wp_cache_get( 'last_changed', 'posts' );
		if ( ! $last_changed ) {
			$last_changed = microtime();
			wp_cache_set( 'last_changed', $last_changed, 'posts' );
		}

		if ( 'monthly' === $type ) {
			$query   = "SELECT YEAR(post_date) AS `year`, MONTH(post_date) AS `month`, count(ID) AS posts FROM $wpdb->posts $join $where GROUP BY YEAR(post_date), MONTH(post_date) ORDER BY post_date $order $limit";
			$key   = md5( $query );
			$key   = "wp_get_archives:$key:$last_changed";
			if ( ! $results = wp_cache_get( $key, 'posts' ) ) {
				$results = $wpdb->get_results( $query );
				wp_cache_set( $key, $results, 'posts' );
			}
			if ( ! empty( $results ) ) {
				$afterafter = $r['after'];
				foreach ( (array) $results as $result ) {
					$url   = $this->get_link( $result->year, $result->month, $cat_id );
					$text  = sprintf( __( '%2$d/%1$02d', 'cmarchives' ), $result->month, $result->year );
					$after = '';
					if ( $r['show_post_count'] ) {
						$after = '&nbsp;( ' . $result->posts . ')' . $afterafter;
					}
					$output .= get_archives_link( $url, $text, $r['format'], $r['before'], $after );
				}
			}
		}
		if ( ! empty( $r['echo'] ) ) {
			echo $output;
		} else {
			return $output;
		}
	}

	/**
	 * Get link.
	 *
	 * Refer wp-includes/link-template.php->get_month_link
	 *
	 * @param bool|int $year False for current year. Integer of year.
	 * @param bool|int $month False for current month. Integer of month.
	 * @param int $cat_id Category ID.
	 * @return string
	 * @link http://wpdocs.sourceforge.jp/%E9%96%A2%E6%95%B0%E3%83%AA%E3%83%95%E3%82%A1%E3%83%AC%E3%83%B3%E3%82%B9/WP_Rewrite
	 * @version 1.0.0
	 * @since 1.0.0
	 */
	private function get_link( $year, $month, $cat_id ) {
		global $wp_rewrite;

		if ( empty( $year ) ) {
			$year = gmdate( 'Y', current_time( 'timestamp' ) );
		}
		if ( empty( $month ) ) {
			$month = gmdate( 'm', current_time( 'timestamp' ) );
		}
		// Get monthly link.
		$month_link = $wp_rewrite->get_month_permastruct();
		if ( ! empty( $month_link ) ) {
			$month_link = str_replace( '%year%', $year, $month_link );
			$month_link = str_replace( '%monthnum%', zeroise( intval( $month ), 2 ), $month_link );
			// Add category id to query parameter.
			return apply_filters(
				'month_link',
				home_url( user_trailingslashit( $month_link, 'month' ) ) . '?cat=' . (int) $cat_id,
				$year,
				$month
			);
		} else {
			return apply_filters(
				'month_link',
				home_url( '?m=' . $year . zeroise( $month, 2 ) . '&cat=' . (int) $cat_id ),
				$year,
				$month
			);
		}
	}
}

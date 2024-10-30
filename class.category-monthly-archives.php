<?php
/**
 * Category Monthly Archives Widget
 * This Widget displays category monthly archives links on category page.
 * Only displays links on category page.
 */
class CategoryMonthlyArchives extends WP_Widget {
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
	 * Include template view.
	 *
	 * @param array  $data     associative array that is used by view.
	 * @param string $template name of view file.
	 */
	private function render( $data, $template = 'category-monthly-archives-form-view.php' ) {
		extract( $data );
		include( dirname( __FILE__ ) . '/' . $template );
	}

	/**
	 * Display setting form on admin page.
	 *
	 * @param object $instance received from WordPress.
	 */
	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'count' => 0 ) );
		$data     = array(
				'title' => sanitize_text_field( $instance['title'] ),
				'count' => $instance['count'] ? 'checked="checked"' : '',
		);

		$this->render( $data, 'category-monthly-archives-form-view.php' );
	}

	/**
	 * Save widget form setting.
	 *
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
	 * @param array  $args     received from WordPress.
	 * @param object $instance received from WordPress.
	 */
	function widget( $args, $instance ) {
		require_once( dirname( __FILE__ ) . '/class.category-monthly-archives-model.php' );
		global $wp_query;

		if ( ! is_category() ) {
			return;
		}
		$count = ! empty( $instance['count'] ) ? '1' : '0';
		$title = apply_filters(
				'widget_title',
				empty( $instance['title'] ) ? __( 'Archives',
						'cmarchives' ) : sanitize_text_field( $instance['title'] ),
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
			$model = new CategoryMonthlyArchivesModel();
			$model->get_archives(
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

}

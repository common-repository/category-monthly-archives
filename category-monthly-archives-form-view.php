<?php
/**
 * Template for admin form view.
 */
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

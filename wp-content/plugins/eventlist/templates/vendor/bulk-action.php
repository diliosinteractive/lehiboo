<?php 
if ( !defined( 'ABSPATH' ) ) exit();
$listing_type = isset( $_GET['listing_type'] ) ? $_GET['listing_type'] : '';

if ($listing_type == 'any') { ?>
	<div class="bulk_action">
		<select name="bulk_action">
			<option value=""><?php esc_html_e( 'Empty', 'eventlist' ); ?></option>
			<option value="trash"><?php esc_html_e( 'Trash', 'eventlist' ); ?></option>
			<option value="publish"><?php esc_html_e( 'Publish', 'eventlist' ); ?></option>
			<option value="pending"><?php esc_html_e( 'Pending', 'eventlist' ); ?></option>
		</select>
		<input type="hidden" value="" class="post_id" />
		<input type="submit" value="<?php esc_html_e('Apply','eventlist'); ?>" class="submit_bulk_action" />
		<?php wp_nonce_field( 'el_bulk_action_nonce', 'el_bulk_action_nonce' ); ?>
	</div>
<?php } elseif ($listing_type == 'publish') { ?>
	<div class="bulk_action">
		<select name="bulk_action">
			<option value=""><?php esc_html_e( 'Empty', 'eventlist' ); ?></option>
			<option value="trash"><?php esc_html_e( 'Trash', 'eventlist' ); ?></option>
			<option value="pending"><?php esc_html_e( 'Pending', 'eventlist' ); ?></option>
		</select>
		<input type="submit" value="<?php esc_html_e('Apply','eventlist'); ?>" class="submit_bulk_action" />
		<?php wp_nonce_field( 'el_bulk_action_nonce', 'el_bulk_action_nonce' ); ?>
	</div>
<?php } elseif ($listing_type == 'pending') { ?>
	<div class="bulk_action">
		<select name="bulk_action">
			<option value=""><?php esc_html_e( 'Empty', 'eventlist' ); ?></option>
			<option value="trash"><?php esc_html_e( 'Trash', 'eventlist' ); ?></option>
			<option value="publish"><?php esc_html_e( 'Publish', 'eventlist' ); ?></option>
		</select>
		<input type="submit" value="<?php esc_html_e('Apply','eventlist'); ?>" class="submit_bulk_action" />
		<?php wp_nonce_field( 'el_bulk_action_nonce', 'el_bulk_action_nonce' ); ?>
	</div>
<?php } elseif ($listing_type == 'trash') { ?>
	<div class="bulk_action">
		<select name="bulk_action">
			<option value=""><?php esc_html_e( 'Empty', 'eventlist' ); ?></option>
			<option value="restore"><?php esc_html_e( 'Restore', 'eventlist' ); ?></option>
			<option value="delete" ><?php esc_html_e( 'Delete Permanently', 'eventlist' ); ?></option>
		</select>
		<input type="submit" value="<?php esc_html_e('Apply','eventlist'); ?>" class="submit_bulk_action" />
		<?php wp_nonce_field( 'el_bulk_action_nonce', 'el_bulk_action_nonce' ); ?>
	</div>
<?php } else { ?>
	<div class="bulk_action">
		<select name="bulk_action">
			<option value=""><?php esc_html_e( 'Empty', 'eventlist' ); ?></option>
			<option value="trash"><?php esc_html_e( 'Trash', 'eventlist' ); ?></option>
			<option value="publish"><?php esc_html_e( 'Publish', 'eventlist' ); ?></option>
			<option value="pending"><?php esc_html_e( 'Pending', 'eventlist' ); ?></option>
		</select>
		<input type="hidden" value="" class="post_id" />
		<input type="submit" value="<?php esc_html_e('Apply','eventlist'); ?>" class="submit_bulk_action" />
		<?php wp_nonce_field( 'el_bulk_action_nonce', 'el_bulk_action_nonce' ); ?>
	</div>
<?php } ?>
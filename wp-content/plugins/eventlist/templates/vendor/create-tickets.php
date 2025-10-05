<?php if ( !defined( 'ABSPATH' ) ) exit(); ?>

<div class="modal fade" id="el_create_tickets_modal" tabindex="-1" role="dialog" aria-labelledby="el_creates_ticket_title" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="el_creates_ticket_title"><?php esc_html_e( 'Create tickets', 'eventlist' ); ?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">

			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">
					<?php esc_html_e( 'Close', 'eventlist' ); ?>
				</button>
				<button type="button" class="el_save_tickets btn btn-primary">
					<?php esc_html_e( 'Create tickets', 'eventlist' ); ?>
				</button>
			</div>
		</div>
	</div>
</div>
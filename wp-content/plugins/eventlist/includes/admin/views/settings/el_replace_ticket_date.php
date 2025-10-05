<?php defined( 'ABSPATH' ) || exit();
	$time 		= el_calendar_time_format();
	$format 	= el_date_time_format_js();
	$first_day 	= el_first_day_of_week();

	$placeholder_dateformat = el_placeholder_dateformat();
	$placeholder_timeformat = el_placeholder_timeformat();

	$events = el_all_events();
?>

<div class="wrap-ticket-replace-date">
	<div class="el-edit-ticket-dates">
		<div class="search">
        	<div class="el-ticket-filter">
        		<h3 class="title"><?php esc_html_e( 'Filter', 'eventlist' ); ?></h3>
        		<form action="<?php echo esc_url( admin_url('/edit.php?post_type=el_tickets&page=el_replace_ticket_date') ); ?>" method="POST" class="form-filter">
	        		<div class="el-ticket-field el-events">
	        			<label for="el-events"><?php esc_html_e( 'Event', 'eventlist' ); ?></label>
	        			<select name="events" id="el-events" data-placeholder="<?php esc_attr_e( 'Select event', 'eventlist' ); ?>" required>
	                		<option value=""></option>
	                	<?php if ( ! empty( $events ) && is_array( $events ) ): ?>
	                		<?php foreach ( $events as $event ): ?>
	                		<option value="<?php echo esc_attr( $event->ID ); ?>">
	                			<?php echo esc_html( $event->post_title ); ?>
	                		</option>
	                	<?php endforeach; endif; ?>
	                	</select>
	        		</div>
	        		<div class="el-ticket-field">
	        			<label for="ticket_start_date"><?php esc_html_e( 'Start Date', 'eventlist' ); ?></label>
	        			<input 
							type="text"
							id="ticket_start_date"
							class="el-ticket-date"
							name="ticket_start_date"
							placeholder="<?php echo esc_attr( $placeholder_dateformat ); ?>"
							autocomplete="off"
							autocorrect="off"
							autocapitalize="none"
							data-date-format="<?php echo esc_attr( $format ); ?>"
							data-firstday="<?php echo esc_attr( $first_day ); ?>"
						/>
	        		</div>
	        		<div class="el-ticket-field">
	        			<label for="ticket_start_time"><?php esc_html_e( 'Start Time', 'eventlist' ); ?></label>
	        			<input 
							type="text"
							id="ticket_start_time"
							class="el-ticket-time"
							name="ticket_start_time"
							placeholder="<?php echo esc_attr( $placeholder_timeformat ); ?>"
							autocomplete="off"
							autocorrect="off"
							autocapitalize="none"
							data-time="<?php echo esc_attr( $time ); ?>"
						/>
	        		</div>
	        		<div class="el-ticket-field">
	        			<label for="ticket_end_date"><?php esc_html_e( 'End Date', 'eventlist' ); ?></label>
	        			<input 
							type="text"
							id="ticket_end_date"
							class="el-ticket-date"
							name="ticket_end_date"
							placeholder="<?php echo esc_attr( $placeholder_dateformat ); ?>"
							autocomplete="off"
							autocorrect="off"
							autocapitalize="none"
							data-date-format="<?php echo esc_attr( $format ); ?>"
							data-firstday="<?php echo esc_attr( $first_day ); ?>"
						/>
	        		</div>
	        		<div class="el-ticket-field">
	        			<label for="ticket_end_time"><?php esc_html_e( 'End Time', 'eventlist' ); ?></label>
	        			<input 
							type="text"
							id="ticket_end_time"
							class="el-ticket-time"
							name="ticket_end_time"
							placeholder="<?php echo esc_attr( $placeholder_timeformat ); ?>"
							autocomplete="off"
							autocorrect="off"
							autocapitalize="none"
							data-time="<?php echo esc_attr( $time ); ?>"
						/>
	        		</div>
	        		<div class="el-ticket-field">
	        			<label for="ticket_replaced_date"><?php esc_html_e( 'Replaced Date', 'eventlist' ); ?></label>
	        			<input 
							type="checkbox"
							id="ticket_replaced_date"
							class="ticket_replaced_date"
							name="ticket_replaced_date"
						/>
	        		</div>
	        		<div class="el-ticket-btn">
	        			<button class="btn btn-search"><?php esc_html_e( 'Search', 'eventlist' ); ?></button>
	        		</div>
        		</form>
        	</div>
        </div>
        <div class="ticket-action">
        	<div class="left-action">
        		<select name="action">
	        		<option value=""><?php esc_html_e( 'Bulk actions', 'eventlist' ); ?></option>
	        		<option value="replace"><?php esc_html_e( 'Replace Date', 'eventlist' ); ?></option>
	        	</select>
	        	<button class="btn btn-apply"><?php esc_html_e( 'Apply', 'eventlist' ); ?></button>
	        	<span class="displaying-num"><?php esc_html_e( '0 items', 'eventlist' ); ?></span>
        	</div>
        	<div class="right-action">
        		<button class="btn btn-send-mail">
        			<?php esc_html_e( 'Send Mail All Customers', 'eventlist' ); ?>
        		</button>
        		<button class="btn btn-export-email">
        			<?php esc_html_e( 'Export All Customer Emails', 'eventlist' ); ?>
        		</button>
        	</div>
        </div>
        <div class="popup-action">
	        <div class="el-ticket-replace">
	        	<span class="replace-close">x</span>
	    		<h3 class="title"><?php esc_html_e( 'Replace', 'eventlist' ); ?></h3>
	    		<form action="<?php echo esc_url( admin_url('/edit.php?post_type=el_tickets&page=el_replace_ticket_date') ); ?>" method="POST" class="form-update">
	        		<div class="el-ticket-field">
	        			<label for="ticket_start_date_replace">
	        				<?php esc_html_e( 'Start Date', 'eventlist' ); ?>
	        			</label>
	        			<input 
							type="text"
							id="ticket_start_date_replace"
							class="el-ticket-date"
							name="ticket_start_date_replace"
							placeholder="<?php echo esc_attr( $placeholder_dateformat ); ?>"
							autocomplete="off"
							autocorrect="off"
							autocapitalize="none"
							data-date-format="<?php echo esc_attr( $format ); ?>"
							data-firstday="<?php echo esc_attr( $first_day ); ?>"
							required
						/>
	        		</div>
	        		<div class="el-ticket-field">
	        			<label for="ticket_start_time_replace">
	        				<?php esc_html_e( 'Start Time', 'eventlist' ); ?>
	        			</label>
	        			<input 
							type="text"
							id="ticket_start_time_replace"
							class="el-ticket-time"
							name="ticket_start_time_replace"
							placeholder="<?php echo esc_attr( $placeholder_timeformat ); ?>"
							autocomplete="off"
							autocorrect="off"
							autocapitalize="none"
							data-time="<?php echo esc_attr( $time ); ?>"
							required
						/>
	        		</div>
	        		<span class="el-br"></span>
	        		<div class="el-ticket-field">
	        			<label for="ticket_end_date_replace">
	        				<?php esc_html_e( 'End Date', 'eventlist' ); ?>
	        			</label>
	        			<input 
							type="text"
							id="ticket_end_date_replace"
							class="el-ticket-date"
							name="ticket_end_date_replace"
							placeholder="<?php echo esc_attr( $placeholder_dateformat ); ?>"
							autocomplete="off"
							autocorrect="off"
							autocapitalize="none"
							data-date-format="<?php echo esc_attr( $format ); ?>"
							data-firstday="<?php echo esc_attr( $first_day ); ?>"
							required
						/>
	        		</div>
	        		<div class="el-ticket-field">
	        			<label for="ticket_end_time_replace">
	        				<?php esc_html_e( 'End Time', 'eventlist' ); ?>
	        			</label>
	        			<input 
							type="text"
							id="ticket_end_time_replace"
							class="el-ticket-time"
							name="ticket_end_time_replace"
							placeholder="<?php echo esc_attr( $placeholder_timeformat ); ?>"
							autocomplete="off"
							autocorrect="off"
							autocapitalize="none"
							data-time="<?php echo esc_attr( $time ); ?>"
							required
						/>
					</div>
					<div class="el-ticket-btn">
						<button class="btn btn-update"><?php esc_html_e( 'Update', 'eventlist' ); ?></button>
						<div class="mb-loading">
				            <i class="dashicons-before dashicons-update-alt"></i>
				        </div>
					</div>
					<div class="el_result">
						<p class="el_error"></p>
						<p class="el_success"></p>
					</div>
				</form>
	    	</div>
    	</div>
    	<div class="popup-send-mail">
    		<div class="el-ticket-send-mail">
	        	<span class="replace-close">x</span>
	    		<h3 class="title"><?php esc_html_e( 'Send Mail', 'eventlist' ); ?></h3>
	    		<form action="<?php echo esc_url( admin_url('/edit.php?post_type=el_tickets&page=el_replace_ticket_date') ); ?>" method="POST" class="form-send-mail">
	        		<div class="el-ticket-field">
	        			<label for="ticket_email_subject">
	        				<?php esc_html_e( 'Subject', 'eventlist' ); ?>
	        			</label>
	        			<input 
							type="text"
							id="ticket_email_subject"
							class="email-subject"
							name="ticket_email_subject"
							placeholder="<?php esc_attr_e( 'Ticket Replace Date', 'eventlist' ); ?>"
							autocomplete="off"
							autocorrect="off"
							autocapitalize="none"
							required
						/>
	        		</div>
	        		<div class="el-ticket-field">
	        			<label for="ticket_email_from_name">
	        				<?php esc_html_e( 'From name', 'eventlist' ); ?>
	        			</label>
	        			<input 
							type="text"
							id="ticket_email_from_name"
							class="form-name"
							name="ticket_email_from_name"
							placeholder="<?php esc_attr_e( 'Ticket Replace Date', 'eventlist' ); ?>"
							autocomplete="off"
							autocorrect="off"
							autocapitalize="none"
							required
						/>
	        		</div>
	        		<div class="el-ticket-field">
	        			<label for="ticket_email_send_from">
	        				<?php esc_html_e( 'Send from email', 'eventlist' ); ?>
	        			</label>
	        			<input 
							type="email"
							id="ticket_email_send_from"
							class="send-from-email"
							name="ticket_email_send_from"
							placeholder="<?php echo esc_attr( get_option('admin_email') ); ?>"
							autocomplete="off"
							autocorrect="off"
							autocapitalize="none"
							required
						/>
	        		</div>
	        		<div class="el-ticket-field">
	        			<label for="ticket_email_content">
	        				<?php esc_html_e( 'Email Content', 'eventlist' ); ?>
	        			</label>
	        			<?php wp_editor('', 'ticket_email_content'); ?>
	        			<textarea id="ticket_email_content"></textarea>
	        		</div>
					<div class="el-ticket-btn">
						<button class="btn btn-send-now"><?php esc_html_e( 'Send', 'eventlist' ); ?></button>
						<div class="mb-loading">
				            <i class="dashicons-before dashicons-update-alt"></i>
				        </div>
					</div>
					<div class="el_result">
						<p class="el_error"></p>
						<p class="el_success"></p>
					</div>
				</form>
	    	</div>
    	</div>
		<table cellspacing="0" cellpadding="10px">
            <thead>
                <tr class="title-column">
                	<th scope="row" class="check-column">
                		<input type="checkbox" class="el-ticket-select-all" />
                	</th>
                	<th class="ticket_number">
                		<?php esc_html_e( 'Ticket Number', 'eventlist' ); ?>
                	</th>
                	<th class="ticket_type">
                		<?php esc_html_e( 'Ticket Type', 'eventlist' ); ?>
                	</th>
                	<th class="ticket_status">
                		<?php esc_html_e( 'Status', 'eventlist' ); ?>
                	</th>
                	<th class="start_date">
                		<?php esc_html_e( 'Start Date', 'eventlist' ); ?>
                	</th>
                	<th class="end_date">
                		<?php esc_html_e( 'End Date', 'eventlist' ); ?>
                	</th>
                	<th class="ticket_qr_code">
                		<?php esc_html_e( 'Qr code', 'eventlist' ); ?>
                	</th>
                	<th class="customer_name">
                		<?php esc_html_e( 'Customer Name', 'eventlist' ); ?>
                	</th>
                	<th class="customer_address">
                		<?php esc_html_e( 'Venue & Address', 'eventlist' ); ?>
                	</th>
                	<th class="event">
                		<?php esc_html_e( 'Event', 'eventlist' ); ?>
                	</th>
                	<th class="booking_id">
                		<?php esc_html_e( 'Booking ID', 'eventlist' ); ?>
                	</th>
                </tr>
            </thead>
            <tbody class="ticket-list">
            	<tr class="no-items">
            		<td class="colspanchange" colspan="11"><?php esc_html_e( 'No items found.', 'eventlist' ); ?></td>
            	</tr>
            </tbody>
            <tfoot>
            	<tr class="title-column">
                	<th scope="row" class="check-column">
                		<input type="checkbox" class="el-ticket-select-all" />
                	</th>
                	<th class="ticket_number">
                		<?php esc_html_e( 'Ticket Number', 'eventlist' ); ?>
                	</th>
                	<th class="ticket_type">
                		<?php esc_html_e( 'Ticket Type', 'eventlist' ); ?>
                	</th>
                	<th class="ticket_status">
                		<?php esc_html_e( 'Status', 'eventlist' ); ?>
                	</th>
                	<th class="start_date">
                		<?php esc_html_e( 'Start Date', 'eventlist' ); ?>
                	</th>
                	<th class="end_date">
                		<?php esc_html_e( 'End Date', 'eventlist' ); ?>
                	</th>
                	<th class="ticket_qr_code">
                		<?php esc_html_e( 'Qr code', 'eventlist' ); ?>
                	</th>
                	<th class="customer_name">
                		<?php esc_html_e( 'Customer Name', 'eventlist' ); ?>
                	</th>
                	<th class="customer_address">
                		<?php esc_html_e( 'Venue & Address', 'eventlist' ); ?>
                	</th>
                	<th class="event">
                		<?php esc_html_e( 'Event', 'eventlist' ); ?>
                	</th>
                	<th class="booking_id">
                		<?php esc_html_e( 'Booking ID', 'eventlist' ); ?>
                	</th>
                </tr>
            </tfoot>
        </table>
        <div class="ticket-pagination"></div>
	</div>
	<div class="el_loading">
		<div class="el-spinner">
	        <div></div><div></div><div></div><div></div>
	        <div></div><div></div><div></div><div></div>
	        <div></div><div></div><div></div><div></div>
	    </div>
	</div>
</div>
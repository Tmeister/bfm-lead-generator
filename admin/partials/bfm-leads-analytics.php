<div class="wrap bfm-analytics">

	<div id="icon-options-general" class="icon32"></div>

	<h2 style="margin-bottom: 20px;"><?php _e( 'Analytics: Impression and Conversion Stats', 'bfm' ); ?></h2>

	<div class="bfm-stats-controls postbox">
		<form method="get" action="<?php echo admin_url( 'admin.php' ); ?>">
			<select id="bfm-stats-form-select" name="form">
				<option value="all"><?php _e( 'All forms', 'bfm' ); ?></option>
				<option value="1" <?php if( isset( $_GET['form'] ) && '1' == $_GET['form'] ): ?>selected='selected'<?php endif; ?>><?php _e( '4 steps form', 'bfm' ); ?></option>
				<option value="2" <?php if( isset( $_GET['form'] ) && '2' == $_GET['form'] ): ?>selected='selected'<?php endif; ?>><?php _e( '3 steps form', 'bfm' ); ?></option>
			</select>
			<label for="bfm-stats-form-select"><?php _e( 'View form', 'bfm' ); ?></label>

			<select id="bfm-stats-date-select" name="period">
				<option value="today" <?php if( isset( $_GET['period'] ) && 'today' == $_GET['period'] ): ?>selected='selected'<?php endif; ?>><?php _e( 'Today', 'bfm' ); ?></option>
				<option value="this_week" <?php if( isset( $_GET['period'] ) && 'this_week' == $_GET['period'] ): ?>selected='selected'<?php endif; ?>><?php _e( 'This Week', 'bfm' ); ?></option>
				<option value="last_week" <?php if( isset( $_GET['period'] ) && 'last_week' == $_GET['period'] ): ?>selected='selected'<?php endif; ?>><?php _e( 'Last Week', 'bfm' ); ?></option>
				<option value="this_month" <?php if( isset( $_GET['period'] ) && 'this_month' == $_GET['period'] ): ?>selected='selected'<?php endif; ?>><?php _e( 'This Month', 'bfm' ); ?></option>
				<option value="last_month" <?php if( isset( $_GET['period'] ) && 'last_month' == $_GET['period'] ): ?>selected='selected'<?php endif; ?>><?php _e( 'Last Month', 'bfm' ); ?></option>
				<option value="this_quarter" <?php if( isset( $_GET['period'] ) && 'this_quarter' == $_GET['period'] ): ?>selected='selected'<?php endif; ?>><?php _e( 'This Quarter', 'bfm' ); ?></option>
				<option value="last_quarter" <?php if( isset( $_GET['period'] ) && 'last_quarter' == $_GET['period'] ): ?>selected='selected'<?php endif; ?>><?php _e( 'Last Quarter', 'bfm' ); ?></option>
				<option value="this_year" <?php if( isset( $_GET['period'] ) && 'this_year' == $_GET['period'] ): ?>selected='selected'<?php endif; ?>><?php _e( 'This Year', 'bfm' ); ?></option>
				<option value="last_year" <?php if( isset( $_GET['period'] ) && 'last_year' == $_GET['period'] ): ?>selected='selected'<?php endif; ?>><?php _e( 'Last Year', 'bfm' ); ?></option>
			</select>
			<label for="bfm-stats-date-select"><?php _e( 'Filter by Date', 'bfm' ); ?></label>
			<input type="hidden" name="page" value="bfm-analytics">
		</form>
	</div>

	<div id="poststuff" class="bfm-custom">

		<div id="post-body" class="metabox-holder columns-2">

			<!-- main content -->
			<div id="post-body-content">

				<div class="meta-box-sortables ui-sortable">

					<?php require_once( $stats_path ); ?>

				</div> <!-- .meta-box-sortables .ui-sortable -->

				<div class="meta-box-sortables ui-sortable">

					<div class="postbox">

						<h3><span><?php _e( 'Total Graph Statistics', 'bfm' ); ?></span></h3>
						<div class="inside">
							<div id="bfm-stats-graph" class="bfm-loading" style="height: 300px; margin: 20px 0;"></div>
						</div> <!-- .inside -->

					</div> <!-- .postbox -->

				</div> <!-- .meta-box-sortables .ui-sortable -->

			</div> <!-- post-body-content -->

			<!-- sidebar -->
			<div id="postbox-container-1" class="postbox-container">

				<div class="meta-box-sortables">

					<div class="postbox">

						<h3><span><?php printf( __( '%s\'s Stats Breakdown', 'bfm' ), ucwords( str_replace( '_', ' ', $period ) ) ); ?></span></h3>
						<?php
						$total_rate = ( 0 === $total_conversions || 0 === $total_impressions ) ? 0 : ( 100 * $total_conversions ) / $total_impressions;
						$total_rate = number_format( $total_rate, 2 );
						?>
						<div class="inside">
							<ul>
								<li><?php printf( __( 'Total impressions for %s: %s', 'bfm' ), str_replace( '_', ' ' , $period ), '<code>' . number_format( $total_impressions, 0 ) . '</code>' ); ?></li>
								<li><?php printf( __( 'Total conversions for %s: %s', 'bfm' ), str_replace( '_', ' ' , $period ), '<code>' . number_format( $total_conversions, 0 ) . '</code>' ); ?></li>
								<li><?php printf( __( '%s of conversions for %s: %s', 'bfm' ), '%',str_replace( '_', ' ' , $period ), "<code>$total_rate%</code>" ); ?></li>
							</ul>
							<p id="bfm-stats-today" data-dimension="254" data-text="<?php echo $total_rate; ?>%" data-info="<?php printf( __( '%s\'s conversion', 'bfm' ), ucwords( str_replace( '_', ' ' , $period ) ) ); ?>" data-width="30" data-fontsize="38" data-percent="<?php echo $total_rate; ?>" data-fgcolor="#61a9dc" data-bgcolor="#f1f1f1"></p>
						</div> <!-- .inside -->

					</div> <!-- .postbox -->

				</div> <!-- .meta-box-sortables -->

			</div> <!-- #postbox-container-1 .postbox-container -->

		</div> <!-- #post-body .metabox-holder .columns-2 -->

		<br class="clear">
	</div> <!-- #poststuff -->

</div> <!-- .wrap -->
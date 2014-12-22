<div class="wrap bfm-profile">

	<div id="icon-options-general" class="icon32"></div>

	<h2><?php _e( 'Lead Profile', 'bfm' ); ?></h2>

	<div id="poststuff" class="bfm-custom">

		<div id="post-body" class="metabox-holder columns-2">

			<div id="post-body-content">
				<div class="postbox bfm-postbox">
					<div class="row">
						<h3><?php _e( 'Name:', 'bfm' ); ?></h3>
						<p>
							<?php echo $lead->first_name ?>  <?php echo $lead->last_name ?>
						</p>
					</div>
					<div class="row">
						<h3><?php _e( 'Email:', 'bfm' ); ?></h3>
						<p>
							<a href="mailto:<?php echo $lead->email ?>" target="_blank" style="text-decoration: none;"><?php echo $lead->email ?></a>
						</p>

					</div>
					<div class="row">
						<h3><?php _e( 'Phone:', 'bfm' ); ?></h3>
						<p>
							<?php echo $lead->phone ?>
						</p>

					</div>
					<div class="row">
						<h3><?php _e( 'Best time to call:', 'bfm' ); ?></h3>
						<p>
							<?php echo $lead->best_time_to_call ?>
						</p>

					</div>
					<div class="row">
						<h3><?php _e( 'Current status of property?', 'bfm' ); ?></h3>
						<p>
							<?php echo $lead->property_status ?>
						</p>

					</div>
					<div class="row">
						<h3><?php _e( 'What is the rent price?', 'bfm' ); ?></h3>
						<p>
							$<?php echo number_format( $lead->rent_price, 2); ?>
						</p>

					</div>
					<div class="row">
						<h3><?php _e( 'Currently using a property manager?', 'bfm' ); ?></h3>
						<p>
							<?php echo $lead->property_manager ?>
						</p>

					</div>
					<div class="row">
						<h3><?php _e( 'Additional comments/questions?', 'bfm' ); ?></h3>
						<p>
							<?php echo nl2br( $lead->comments ) ?>
						</p>

					</div>

				</div>
			</div>

			<div id="postbox-container-1" class="postbox-container">
				<div class="postbox bfm-postbox">
					<strong><?php _e('Lead Conversion Info', 'bfm') ?><strong>
					<p><strong>Lead Status:</strong> <code><?php echo $lead->complete ?></code></p>
					<p><strong>Entry Time:</strong> <code><?php echo $lead->input_time ?></code></p>
					<p><strong>Source:</strong> <code><?php echo $lead->form_id ?></code></p>
				</div>
			</div>

		</div>

	</div>

</div>
<?php

	$id = sprintf("unique-step-form-%s", rand(1, 9999) );
?>

<script>
	jQuery(document).ready(function($) {
		jQuery('#<?php echo $id ?>').bfmForm({
			'type': '<?php echo $layout ?>',
			'steps': '<?php echo $steps ?>'
		});
	});
</script>

<style>
	#<?php echo $id ?> label{
		color: <?php echo $labels_color ?>;
	}
</style>

<div class="step-optin-form <?php echo $layout ?>" id="<?php echo $id ?>">

	<div class="error">
		Please make sure that you have filled in the fields correctly!
	</div>

	<div class="bfm-holder" style="border-color: <?php echo $border_color ?>";>
		<form>
			<div class="bfm-left-inner" style="background-color: <?php echo $left_inner ?>">
				<div class="bfm-left-inner-holder">
					<div class="mask">
						<fieldset class="screen current">
							<div class="centered">

								<div class="bfm-field">
									<label for="bfm-name">Your Name</label>
									<input type="text" name="bfm-name" class="bfm-name" data-parsley-group="block1" required />
								</div>

								<div class="bfm-field">
									<label for="bfm-email">Your Email</label>
									<input type="email" name="bfm-email" class="bfm-email" data-parsley-group="block1" required/>
								</div>

							</div>
						</fieldset>

						<fieldset class="screen">
							<div class="centered">

								<div class="bfm-field">
									<label for="bfm-phone">Your Phone: </label>
									<input type="text" name="bfm-phone" class="bfm-phone" data-parsley-group="block2" required/>
								</div>

								<div class="bfm-field">
									<label for="bfm-best-time">Best Time To Call</label>
									<!-- TODO GET FROM OPTIONS -->
									<select id="bfm-best-time" data-placeholder="TESTT">
										<option value="1">8am to 10am</option>
										<option value="2">10am to 12pm</option>
										<option value="3">12pm to 2pm</option>
										<option value="4">2pm to 4pm</option>
										<option value="5">4pm to 6pm</option>
									</select>
								</div>

							</div>

						</fieldset>

						<fieldset class="screen">

							<div class="centered">

								<div class="bfm-field">
									<label for="bfm-property-status">Current status of property</label>
									<!-- TODO GET FROM OPTIONS -->
									<select id="bfm-property-status">
										<option value="1">Vacant</option>
										<option value="2">Owner-occupied</option>
										<option value="3">Tenant-occupied</option>
									</select>
								</div>

								<div class="bfm-field rent-price">
									<label for="bfm-rent-price">What is the rent price?</label>
									<input type="text" id="bfm-rent-price" data-parsley-group="block3" required  value="0"/>
								</div>

								<div class="bfm-field">
									<label for="property-manager">Are you currently using a property manager?</label>
									<input type="radio" class="radio" name="property-manager" value="yes" data-parsley-group="block3" required/> Yes<br>
									<input type="radio" class="radio" name="property-manager" value="no" data-parsley-group="block3"/> No<br>
								</div>

								<div class="bfm-field">
									<label for="bfm-comments">Additional comments/questions?</label>
									<textarea id="bfm-comments"></textarea>
								</div>
							</div>
						</fieldset>
					</div>
				</div>
			</div>
			<div class="bfm-right-inner" style="background-color: <?php echo $right_inner ?>">
				<div class="bfm-right-inner-holder">
					<div class="bfm-right-inner-cell">
						<div class="loading"></div>
						<div>
							<input type="button" name="next" class="next action-button" value="Next" id="next-button" style="background-color:<?php echo $button_bg ?>; color: <?php echo $button_color ?>;" />
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
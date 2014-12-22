/* global bfm:true  */

(function($) {

	'use strict';

	$.fn.bfmForm = function(options) {

		if (!this.length) { return this; }

		var opts = $.extend(true, {}, $.fn.bfmForm.defaults, options);

		this.each(function() {

			var $this = $(this);

			var $form = $this.find('form');

			var $next = $this.find('.next');

			var $fieldsets = $this.find('fieldset');

			var $window = $(window);

			var $error = $this.find('.error');

			var $mask = $this.find('.mask');

			var $rent = $this.find('.rent-price');

			var $propertyStatus = $this.find('#bfm-property-status');

			var current_fs = 0;

			var user_id;

			var animating = false;

			$form.parsley();

			$rent.hide();

			$this.find('select').select2({

				minimumResultsForSearch: -1,

				width: '98%'

			});

			$this.keydown(function(event) {

				var keyCode = event.keyCode || event.which;

				if( keyCode === 9 ){

					event.preventDefault();

				}

			});

			$this.find('.bfm-phone').mask('(999) 999-9999');


			$propertyStatus.on('change', function(e){

				if( '3' === $('option:selected', $propertyStatus).val() ){

					$rent.fadeIn('slow', function(){

						setMaskHeight( current_fs );

					});

				}else{

					$rent.fadeOut('slow', function(){

						setMaskHeight( current_fs );

					});

				}

			});


			$next.live('click', function(event) {

				if(animating){ return false; }

				if( !validateForm() ){ return; }

				parseForm($this, $fieldsets);

			});

			function validateForm(){

				return $form.parsley().validate( 'block' + ( current_fs + 1 ) );

			}

			function parseForm(){

				var status = null;

				$error.hide();

				switch(current_fs){

					case 0:

						status = ( opts.steps === '4' ) ? saveMail() : saveEmailAndName();

						break;

					case 1:

						status = ( opts.steps === '4' ) ? saveNamePhoneAndBest() : savePhoneAndBest();

						break;

					case 2:

						status = ( opts.steps === '4' ) ? saveStatusRentAndManager() : saveAditionalFields();

						break;

					case 3:

						status = ( opts.steps === '4' ) ? saveComments() : 'NULL';
				}

			}

			/**
			 * 4 Steps Functions
			 */
			function saveMail(){

				var email = $this.find('.bfm-email').val();

				$.ajax({

					url: bfm.ajax_url,

					type: 'POST',

					dataType: 'json',

					data: {

						action: 'add_email',

						nonce: bfm.nonce,

						email: email

					}

				})

				.done(function(data) {

					if( data.status === 'success' ){

						user_id = data.user_id;

						gotonextfs();

					}

					if( data.status === 'fail'){

						$error.html( data.message ).show();

					}

				})

				.fail(function() {

					console.log("error");

				});

			}

			function saveNamePhoneAndBest(){

				var name = $this.find('.bfm-name').val();

				var phone = $this.find('.bfm-phone').val();

				var best_time = $this.find('#bfm-best-time').val();

				$.ajax({

					url: bfm.ajax_url,

					type: 'POST',

					dataType: 'json',

					data: {

						action: 'add_name_phone_best',

						nonce: bfm.nonce,

						name : name,

						phone : phone,

						best_time : best_time,

						user_id : user_id

					}

				})

				.done(function( data ) {

					if( data.status === 'success' ){

						gotonextfs();

					}

					if( data.status === 'fail'){

						$error.html( data.message ).show();

					}

				})

				.fail(function() {

					console.log("error");

				});

			}

			function saveStatusRentAndManager(){

				var property_status = $this.find('#bfm-property-status').val();

				var rent = $this.find('#bfm-rent-price').val();

				var manager = $('input[name=property-manager]:checked', $this).val();

				$.ajax({

					url: bfm.ajax_url,

					type: 'POST',

					dataType: 'json',

					data: {

						action: 'add_status_rent_manager',

						nonce: bfm.nonce,

						status : property_status,

						rent : rent,

						manager : manager,

						user_id : user_id

					}

				})

				.done(function(data) {

					if( data.status === 'success' ){

						gotonextfs();

					}

					if( data.status === 'fail'){

						$error.html( data.message ).show();

					}

				})

				.fail(function() {

					console.log("error");

				});

			}

			function saveComments(){

				var comments = $this.find('#bfm-comments').val();

				$.ajax({

					url: bfm.ajax_url,

					type: 'POST',

					dataType: 'json',

					data: {

						action: 'add_comments',

						nonce: bfm.nonce,

						comments: comments,

						user_id : user_id

					}

				})

				.done(function(data) {

					if( data.status === 'success' ){

						$next.hide();

						if( data.thanks_url ){

							window.location.href = data.thanks_url;

						}

						//gotonextfs();

					}

					if( data.status === 'fail'){

						$error.html( data.message ).show();

					}

				})

				.fail(function() {

					console.log("error");

				});

			}

			/**
			 * 3 Steps Functions
			 */

			function saveEmailAndName(){

				var email = $this.find('.bfm-email').val();

				var name = $this.find('.bfm-name').val();

				$.ajax({

					url: bfm.ajax_url,

					type: 'POST',

					dataType: 'json',

					data: {

						action: 'add_email_and_name',

						nonce: bfm.nonce,

						email: email,

						name : name

					}

				})

				.done(function(data) {

					if( data.status === 'success' ){

						user_id = data.user_id;

						gotonextfs();

					}

					if( data.status === 'fail'){

						$error.html( data.message ).show();

					}

				})

				.fail(function() {

					console.log("error");

				});

			}

			function savePhoneAndBest(){

				var phone = $this.find('.bfm-phone').val();

				var best_time = $this.find('#bfm-best-time').val();

				$.ajax({

					url: bfm.ajax_url,

					type: 'POST',

					dataType: 'json',

					data: {

						action: 'add_phone_best',

						nonce: bfm.nonce,

						phone : phone,

						best_time : best_time,

						user_id : user_id

					}

				})

				.done(function( data ) {

					if( data.status === 'success' ){

						gotonextfs();

					}

					if( data.status === 'fail'){

						$error.html( data.message ).show();

					}

				})

				.fail(function() {

					console.log("error");

				});

			}

			function saveAditionalFields(){

				var property_status = $this.find('#bfm-property-status').val();

				var rent = $this.find('#bfm-rent-price').val();

				var manager = $('input[name=property-manager]:checked', $this).val();

				var comments = $this.find('#bfm-comments').val();

				$.ajax({

					url: bfm.ajax_url,

					type: 'POST',

					dataType: 'json',

					data: {

						action: 'add_aditional_fields',

						nonce: bfm.nonce,

						status : property_status,

						rent : rent,

						manager : manager,

						comments : comments,

						user_id : user_id

					}

				})

				.done(function(data) {

					if( data.status === 'success' ){

						$next.hide();

						if( data.thanks_url ){

							window.location.href = data.thanks_url;

						}

						//gotonextfs();

					}

					if( data.status === 'fail'){

						$error.html( data.message ).show();

					}

				})

				.fail(function() {

					console.log("error");

				});

			}

			function gotonextfs(){

				animating = true;

				var $current = $($fieldsets[current_fs]);

				var $next = $($fieldsets[current_fs + 1]);

				setMaskHeight(current_fs + 1);

				$current.animate({'left': '-100%'}, 500);

				$next.animate({'left': '0%'}, 500, function(){animating = false;});

				current_fs = current_fs + 1;

			}

			function setMaskHeight(screen){

				var $screen = $( $fieldsets[screen] );

				var $fields = $screen.find( '.bfm-field' );

				var height = 0;

				for (var i = $fields.length - 1; i >= 0; i--) {

					var $field = $( $fields[i] );

					if( $field.is(":visible") ){

						height += ( $field.height() + 40);

					}

				}

				$mask.animate({'height' :  height + 'px'}, 500);

			}

			/**
			 * Analytics
			 */

			 function saveHit(){

			 	var form_id = ( opts.steps === '4' ) ? 1 : 2;

			 	$.ajax({

			 		url: bfm.ajax_url,

					type: 'POST',

					dataType: 'json',

			 		data: {

			 			action: 'add_form_hit',

			 			form_id: form_id,

			 			nonce: bfm.nonce

			 		}
			 	})

			 	.done(function() {

			 		console.log("success");


			 	})
			 	.fail(function() {

			 		console.log("error");

			 	});

			 }


			setMaskHeight(0);

			saveHit();

		});

		return this;
	};

	// default options
	$.fn.bfmForm.defaults = {

		layout : 'horizontal',

		steps : '4'

	};

})(jQuery);

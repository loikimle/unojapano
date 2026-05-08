(function ($) {
	var UR_Local_Currency_Admin = {

		init: function () {
			$( document ).on( 'click', '.ur-local-currency-add-pricing-zone', UR_Local_Currency_Admin.handleAddingPricingZone );
			$( document ).on( 'change', 'input[name="ur_local_currencies_conversion_type"]', UR_Local_Currency_Admin.handleConversionType );
			$( document ).on( 'click', '.ur-local-currency-delete-pricing-zone', UR_Local_Currency_Admin.deleteZone );

			$(document).on('click', '.ur-country-label', function (e) {
				e.stopPropagation();

				const $dropdown = $(this).closest('.ur-country-dropdown');

				$('.ur-country-dropdown').not($dropdown).removeClass('is-open');

				var rect = this.getBoundingClientRect();

				$dropdown.find( '.ur-country-menu' ).css({
					position: "fixed",
					top: rect.bottom + 6,
					left: rect.left,
					zIndex: 100000,
					width: "0px"
				});

				$dropdown.toggleClass('is-open');
			});

			$(document).on('click', function () {
				$('.ur-country-dropdown').removeClass('is-open');
			});
			$( document ).on( 'change', '#user_registration_local_currency_by_geolocation', UR_Local_Currency_Admin.toggleGeolocationSettings );
			$( document ).on( 'change', '#user_registration_local_currency_by_geolocation_test_mode', UR_Local_Currency_Admin.toggleTestModeSettings );
		},
		handleAddingPricingZone: function (e) {
			e.preventDefault();
			e.stopPropagation();

			var $el     = $(this);
			var action  = $el.data('action');
			var postId  = action === 'edit' ? $el.data('id') : 0;

			if (action === 'edit') {
				$el.append( '<span class="ur-spinner"></span>' );
				$.ajax({
					url: urm_local_currency_admin_script_data.ajax_url,
					type: 'POST',
					data: {
						action: 'user_registration_local_currency_edit_pricing_zone',
						security: urm_local_currency_admin_script_data.user_registration_pricing_zone,
						post_id: postId
					},
					success: function (response) {
						if (!response.success) {
							Swal.fire('Error', 'Failed to load pricing zone', 'error');
							return;
						}
						$el.find( '.ur-spinner' ).remove();
						UR_Local_Currency_Admin.openPricingZoneModal(response.data.template, action, postId);
					}
				});

			} else {
				UR_Local_Currency_Admin.openPricingZoneModal(
					urm_local_currency_admin_script_data.create_form_template,
					action,
					0
				);
			}
		},
		openPricingZoneModal: function (content, action, postId) {

			Swal.fire({
				title: urm_local_currency_admin_script_data.i18n_title,
				html: content,
				showCancelButton: true,
				showLoaderOnConfirm: true,
				confirmButtonText:
					action === 'edit'
						? urm_local_currency_admin_script_data.i18n_update_btn_text
						: urm_local_currency_admin_script_data.i18n_add_btn_text,
				cancelButtonText: urm_local_currency_admin_script_data.i18n_cancel,
				customClass: {
					container: 'user-registration-swal2-pricing-zone-container'
				},

				didOpen: function () {
					var $popup = $(Swal.getPopup());

					$popup.find('.ur-local-currencies-countries').select2({
						placeholder: 'Select countries',
						width: '100%',
						closeOnSelect: false,
						allowClear: true
					});

					$popup.find('.ur-local-currencies-form__select').select2({
						placeholder: 'Select Currency',
						width: '100%',
						allowClear: true
					});
				},

				preConfirm: function () {
					var $form = $(Swal.getPopup()).find('#ur-local-currencies-add-form');

					if (!$form.find('[name="ur_local_currencies_zone_name"]').val()) {
						Swal.showValidationMessage('Zone name is required');
						return false;
					}

					if (
						!$form.find('.ur-local-currency-exchange-rate').is(':hidden') &&
						!$form.find('[name="ur_local_currencies_exchange_rate"]').val()
					) {
						Swal.showValidationMessage('Exchange rate is required');
						return false;
					}

					var formData = new FormData($form[0]);
					formData.append('action', 'user_registration_local_currency_create_pricing_zone');
					formData.append('security', urm_local_currency_admin_script_data.user_registration_pricing_zone);
					formData.append('pricing_action', action);

					if (action === 'edit') {
						formData.append('post_id', postId);
					}

					return $.ajax({
						url: urm_local_currency_admin_script_data.ajax_url,
						type: 'POST',
						data: formData,
						processData: false,
						contentType: false
					})
					.then(function (response) {

						if (!response.success) {
							Swal.showValidationMessage(
								response.data?.message || 'Failed to save pricing zone'
							);
							return false;
						}

						return response;
					})
					.catch(function () {
						Swal.showValidationMessage('Something went wrong');
					});
				}
			}).then(function (result) {
				if (result.isConfirmed) {
					Swal.fire({
						icon: 'success',
						title: action === 'edit'
							? 'Pricing zone updated'
							: 'Pricing zone added',
						timer: 1500,
						showConfirmButton: false
					});

					location.reload();
				}
			});
		},
		handleConversionType: function( e ){
			e.stopPropagation();
			e.preventDefault();

			var $el = $( this );

			if ( 'manual' != $el.val() ) {
				$( document ).find( '.ur-local-currency-exchange-rate' ).hide();
			}else{
				$( document ).find( '.ur-local-currency-exchange-rate' ).show();
			}
		},
		deleteZone: function ( e ) {
			e.preventDefault();
			e.stopPropagation();

			var postId = $( this ).data('id');

			if ( ! postId ) {
				return;
			}

			Swal.fire({
				title: urm_local_currency_admin_script_data.i18n_delete_btn_title,
				icon: 'warning',
				showCancelButton: true,
				confirmButtonColor: '#d33',
				confirmButtonText: urm_local_currency_admin_script_data.i18n_delete_confirm,
				cancelButtonText: urm_local_currency_admin_script_data.i18n_cancel,
				showLoaderOnConfirm: true,
				allowOutsideClick: () => !Swal.isLoading(),
				allowEscapeKey: () => !Swal.isLoading(),

				preConfirm: function () {
					return $.ajax({
						url: urm_local_currency_admin_script_data.ajax_url,
						type: 'POST',
						data: {
							action: 'user_registration_local_currency_delete_pricing_zone',
							security: urm_local_currency_admin_script_data.user_registration_pricing_zone,
							post_id: postId
						}
					})
					.then(function ( response ) {

						if ( ! response.success ) {
							Swal.showValidationMessage(
								response.data?.message || 'Failed to delete'
							);
							return false;
						}

						return response;
					})
					.catch(function () {
						Swal.showValidationMessage('Something went wrong');
					});
				}
			}).then(function ( result ) {
				if ( result.isConfirmed ) {
					Swal.fire({
						icon: 'success',
						title: 'Deleted',
						timer: 1200,
						showConfirmButton: false
					});

					location.reload();
				}
			});
		},
		toggleGeolocationSettings: function ( e ) {
			if ( e ) {
				e.preventDefault();
			}

			var $geoEnable     = $('#user_registration_local_currency_by_geolocation');
			var $testModeWrap  = $('#user_registration_local_currency_by_geolocation_test_mode')
				.closest('.user-registration-global-settings');
			var $testCountryWrap = $('#user_registration_local_currency_test_country')
				.closest('.user-registration-global-settings');

			var $maxMindAccount = $('#user_registration_max_mind_account_id')
				.closest('.user-registration-global-settings');
			var $maxMindKey = $('#user_registration_max_mind_key')
				.closest('.user-registration-global-settings');

			if ( $geoEnable.is(':checked') ) {
				$maxMindAccount.show();
				$maxMindKey.show();
				$testModeWrap.show();

				// delegate test-country visibility
				UR_Local_Currency_Admin.toggleTestModeSettings();
			} else {
				$maxMindAccount.hide();
				$maxMindKey.hide();
				$testModeWrap.hide();
				$testCountryWrap.hide();
			}
		},

		toggleTestModeSettings: function ( e ) {
			if ( e ) {
				e.preventDefault();
			}

			var geoEnabled = $('#user_registration_local_currency_by_geolocation').is(':checked');
			var testMode   = $('#user_registration_local_currency_by_geolocation_test_mode').is(':checked');

			var $testCountryWrap = $('#user_registration_local_currency_test_country')
				.closest('.user-registration-global-settings');

			if ( geoEnabled && testMode ) {
				$testCountryWrap.show();
			} else {
				$testCountryWrap.hide();
			}
		},

	}

	$(document).ready(function () {
		UR_Local_Currency_Admin.init();
		UR_Local_Currency_Admin.toggleGeolocationSettings();
		UR_Local_Currency_Admin.toggleTestModeSettings();
	});
})(jQuery);

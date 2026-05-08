(function ($) {
	var UR_Tax_Admin = {

		init: function () {
			sessionStorage.removeItem('tax_regions_temp');
			$( document ).on( 'click', '.urm-manage-tax-region-btn, .urm-manage-tax-add-region-btn', function( e ){
				e.stopPropagation();
				e.preventDefault();
				var $el = $( this );
				var spinner = '<span class="ur-spinner is-active"></span>';

				$el.append(spinner);
				UR_Tax_Admin.addNewTaxRegion( $el );
				$( document ).find('.ur-tax-country-checkbox').each(function () {
				  var $el = $(this);

				  var $outerWrapper = $el.closest('.ur-tax-regions-popup-country-outer-wrapper')
									  .siblings('.ur-tax-regions-popup-state-outer-wrapper');

				  UR_Tax_Admin.hideShowStateSettings( $el, $outerWrapper );
			  });

			  $( document ).find('.ur-tax-country-and-state-outer-wrapper').each(function () {
					UR_Tax_Admin.toggleEntireCountryState( $( this ) );
				});
			});

			$( document ).on( 'click', '.ur-tax-region-delete', function( e ){
				e.preventDefault();

				var $el        = $( this );
				var $rateField = $el.closest('tr').find('.ur-tax-region-delete');
				var country    = $rateField.data('country');
				var state      = $rateField.data('state');

				Swal.fire({
					title: urm_tax_admin_script_data.i18n_delete_title,
					showConfirmButton: true,
					showCancelButton: true,
					confirmButtonText: urm_tax_admin_script_data.i18n_confirm_btn_text,
					cancelButtonText: urm_tax_admin_script_data.i18n_cancel,
					customClass: { container: "user-registration-swal2-tax-regions-container" },
				}).then( function( result ) {

					if ( !result.isConfirmed ) return;

					var tax_regions_temp = sessionStorage.getItem( 'tax_regions_temp' );

					if ( tax_regions_temp ) {
						tax_regions_temp = JSON.parse( tax_regions_temp );
					} else {
						tax_regions_temp = JSON.parse( JSON.stringify( urm_tax_admin_script_data.tax_regions_list ) );
					}

					if ( country && state ) {

						if ( tax_regions_temp[country] &&
							tax_regions_temp[country].states &&
							tax_regions_temp[country].states[state] ) {

							delete tax_regions_temp[country].states[state];
						}

						// if ( Object.keys( tax_regions_temp[country].states ).length === 0 ) {
						// 	delete tax_regions_temp[country].states;
						// 	tax_regions_temp[country].states = {};
						// }

					} else if ( ! state ) {
						if ('' != tax_regions_temp[country].states ) {
							return;
						}else{
							delete tax_regions_temp[ country ];
						}
					}

					$.ajax({
						url: urm_tax_admin_script_data.ajax_url,
						type: "POST",
						data: {
							action: "user_registration_pro_add_tax_regions_in_table",
							security: urm_tax_admin_script_data.user_registration_tax_regions,
							regions: JSON.stringify(tax_regions_temp)
						},
						success: function (response) {
							if ( response.success ) {
								$( '.user-registration-list-tax-region-table-container' ).empty().append( response.data.html );
							}
						}
					});

					sessionStorage.setItem(
						'tax_regions_temp',
						JSON.stringify( tax_regions_temp )
					);

					// $el.closest('tr').remove();
				});
			});

			$( document ).on( 'click', '.ur-tax-region-edit', function( e ){
				e.preventDefault();
				var $rateField = $( this ).closest( 'tr' ).find( '.ur-tax-rate-edit-field' );
				$( this ).closest( 'tr' ).find( '.ur-tax-rate-span' ).hide();
				$( this ).hide();
				$( this ).closest( 'tr' ).find( '.ur-tax-region-save' ).show();
				$( this ).closest( 'tr' ).find( '.ur-tax-rate-percentage' ).show();

				$rateField.attr( 'type', 'number');
			});
			$( document ).on( 'click', '.ur-tax-region-save', function( e ){
				e.preventDefault();
				var tax_regions_temp = sessionStorage.getItem( 'tax_regions_temp' );

				var $rateField = $( this ).closest( 'tr' ).find( '.ur-tax-rate-edit-field' );
				var country    = $rateField.data( 'country' );
				var state    = $rateField.data( 'state' );

				if ( tax_regions_temp ) {
					tax_regions_temp = JSON.parse(tax_regions_temp);
				} else {
					tax_regions_temp = JSON.parse(JSON.stringify(urm_tax_admin_script_data.tax_regions_list));
				}

				if ( ! tax_regions_temp[country] ) {
					tax_regions_temp[country] = {
						rate: '',
						states: {}
					};
				}

				if ( country && state ) {
					tax_regions_temp[ country ].states[ state ] = $rateField.val();
				}else if( country ){
					tax_regions_temp[ country ].rate = $rateField.val();
				}

				sessionStorage.setItem( 'tax_regions_temp', JSON.stringify( tax_regions_temp ) );

				$( this ).closest( 'tr' ).find( '.ur-tax-rate-span' ).show();
				$( this ).hide();
				$( this ).closest( 'tr' ).find( '.ur-tax-region-edit' ).show();
				$( this ).closest( 'tr' ).find( '.ur-tax-rate-percentage' ).hide();

				$rateField.attr( 'type', 'hidden');
			});

			$( document ).on( 'input change keyup', '.ur-tax-rate-edit-field', function( e ){
				e.stopPropagation();
				e.preventDefault();

				var value = $( this ).val();

				$( this ).siblings('.ur-tax-rate-span').text(value + '%');
			})

			$( document ).on( 'change', '.ur-tax-country-checkbox', function( e ){
				e.stopPropagation();
				e.preventDefault();
				var $el = $( this );
				var $outerWrapper = $el.closest( '.ur-tax-regions-popup-country-outer-wrapper' ).siblings('.ur-tax-regions-popup-state-outer-wrapper' );
				UR_Tax_Admin.hideShowStateSettings( $el, $outerWrapper );
			});

			$( document ).on( 'change', '.ur-tax-entire-country-checkbox', function( e ){
				e.stopPropagation();
				e.preventDefault();

				var $wrapper = $(this).closest('.ur-tax-country-and-state-outer-wrapper');
				var $rateInput = $wrapper.find('.ur-tax-regions-rate-input[data-has_state="yes"]');

				$wrapper.find( '.ur-tax-regions-default-rate' ).val( 0 );
				$wrapper.find( '.ur-tax-state-rate-input' ).val( 0 );
				$rateInput.val( 0 );
				UR_Tax_Admin.toggleEntireCountryState( $wrapper );
			});
		},
		addNewTaxRegion: function ($el) {
			var regions 		= sessionStorage.getItem('tax_regions_temp');
			var templateData 	= {
				action: "user_registration_pro_get_tax_region_template",
				regions : regions,
				security: urm_tax_admin_script_data.user_registration_tax_regions,
			}
			$.ajax({
				url: urm_tax_admin_script_data.ajax_url,
				type: "POST",
				data: templateData,
				success: function (response) {
						if (!response.success) return;
						var content = '';

						$el.find( '.ur-spinner' ).remove();
						if ( regions ) {
							content = response.data.html;
						}else{
							content = urm_tax_admin_script_data.add_tax_regions_template;
						}

						Swal.fire({
							title: urm_tax_admin_script_data.i18n_title,
							html: content,
							showConfirmButton: true,
							showCancelButton: true,
							confirmButtonText: urm_tax_admin_script_data.i18n_add_btn_text,
							cancelButtonText: urm_tax_admin_script_data.i18n_cancel,
							customClass: {
								container: "user-registration-swal2-tax-regions-container",
							},
							didOpen: function() {
								UR_Tax_Admin.searchRegions();
							}
						}).then(function ( result ) {
							if ( result.isConfirmed ) {
								var regionsData = {};

								$('.ur-tax-country-and-state-outer-wrapper').each(function () {
									var countryKey = $(this).data('country');
									var countryCheckbox = $(this).find('.ur-tax-country-checkbox');

									// Save only selected countries
									if (countryCheckbox.is(':checked')) {
										var $inputs = $(this).find(`input[name="regions[${countryKey}][rate]"]`);

										var $visibleInput = $inputs.not('.urm-hide-wrapper').first();

										var rate = $visibleInput.length
											? $visibleInput.val()
											: $inputs.first().val();

										regionsData[countryKey] = {
											rate: rate,
											states: {}
										};
										$(this).find('.ur-tax-state-list').each(function () {
											var stateCheckbox = $(this).find('input[type="checkbox"]');
											var stateRateInput = $(this).find('input[type="number"]');

											if (stateCheckbox.is(':checked')) {
												var name = stateRateInput.attr('name');
												var match = name.match(/\[states]\[(.*?)\]/);

												if (match) {
													var stateKey = match[1];
													var value = stateRateInput.val();

													regionsData[countryKey].states[stateKey] = value;
												}
											}
										});
									}
								});

								// Save to sessionStorage
								sessionStorage.setItem('tax_regions_temp', JSON.stringify(regionsData));

								$.ajax({
									url: urm_tax_admin_script_data.ajax_url,
									type: "POST",
									data: {
										action: "user_registration_pro_add_tax_regions_in_table",
										security: urm_tax_admin_script_data.user_registration_tax_regions,
										regions: JSON.stringify(regionsData)
									},
									success: function (response) {
										if ( response.success ) {
											$( '.user-registration-list-tax-region-table-container' ).empty().append( response.data.html );
										}
									}
								});
							}
						});
				}
			});
		},
		searchRegions: function(){
			var $search = $( document ).find( '.ur-tax-regions-search' );
			var $rows = $( document ).find(".ur-tax-country-and-state-outer-wrapper");

			$search.on("keyup", function () {
				var keyword = $(this).val().toLowerCase();

				$rows.each(function () {
					var text = $(this).text().toLowerCase();

					if (text.indexOf(keyword) !== -1) {
						$(this).css("display", "flex");
					} else {
						$(this).css("display", "none");
					}
				});
			});
		},
		hideShowStateSettings: function( $el, $outerWrapper ){
			var $countryAndStateWrapper = $el.closest( '.ur-tax-country-and-state-outer-wrapper' );
			if (  $el.is(':checked') ) {
				$outerWrapper.show();
				$outerWrapper.find( '.ur-tax-entire-country-checkbox' ).prop( 'checked', true );
			}else{
				$outerWrapper.hide();
				$outerWrapper.find( '.ur-tax-entire-country-checkbox' ).prop( 'checked', false );
			}

			UR_Tax_Admin.toggleEntireCountryState( $countryAndStateWrapper );
		},
		toggleEntireCountryState: function( $wrapper ) {
			var $entireCountryCheckbox = $wrapper.find('.ur-tax-entire-country-checkbox');
			var $stateWrapper = $wrapper.find('.ur-tax-state-outer-wrapper');
			var $rateInput = $wrapper.find('.ur-tax-regions-rate-input[data-has_state="yes"]');
			var $stateCheckboxes = $wrapper.find('.ur-tax-state-lists-container input[type="checkbox"]');

			if ($entireCountryCheckbox.is(':checked')) {
				$stateWrapper.hide();
				$rateInput.removeClass( 'urm-hide-wrapper' );
			} else {
				$stateWrapper.show();
				$rateInput.addClass( 'urm-hide-wrapper' );
				$stateCheckboxes.prop('checked', false );
			}
		}
	}

	$(document).ready(function () {
		UR_Tax_Admin.init();
	});
})(jQuery);

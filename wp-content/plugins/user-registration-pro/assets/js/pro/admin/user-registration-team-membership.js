/**UR_Snackbar**/
(function ($, ur_team_data) {
	if (UR_Snackbar) {
		var snackbar = new UR_Snackbar();
	}

	//extra utils for membership team
	var ur_team_utils = {
		append_spinner: function ($element) {
			if ($element && $element.append) {
				var spinner = '<span class="ur-spinner is-active"></span>';

				$element.append(spinner);
				return true;
			}
			return false;
		},
		prepend_spinner: function ($element) {
			if ($element && $element.prepend) {
				var spinner = '<span class="ur-spinner is-active"></span>';

				$element.prepend(spinner);
				return true;
			}
			return false;
		},
		/**
		 * Remove spinner elements from a element.
		 *
		 * @param {jQuery} $element
		 */
		remove_spinner: function ($element) {
			if ($element && $element.remove) {
				$element.find('.ur-spinner').remove();
				return true;
			}
			return false;
		},

		if_empty: function (value, _default) {
			if (null === value || undefined === value || '' === value) {
				return _default;
			}
			return value;
		},
		/**
		 * Enable/Disable save buttons i.e. 'Save' button and 'Save as Draft' button.
		 *
		 * @param {Boolean} disable Whether to disable or enable.
		 */
		toggleSaveButtons: function (disable) {
			disable = ur_team_utils.if_empty(disable, true);
			$('.ur-team-update-btn').prop('disabled', !!disable);
		},

		/**
		 * Show success message using snackbar.
		 *
		 * @param {String} message Message to show.
		 */
		show_success_message: function (message) {
			if (snackbar) {
				snackbar.add({
					type: 'success',
					message: message,
					duration: 5,
				});
				return true;
			}
			return false;
		},

		/**
		 * Show failure message using snackbar.
		 *
		 * @param {String} message Message to show.
		 */
		show_failure_message: function (message) {
			if (snackbar) {
				snackbar.add({
					type: 'failure',
					message: message,
					duration: 6,
				});
				return true;
			}
			return false;
		},

		/**
		 * Directly Show failure message using snackbar.
		 *
		 * @param {String} message Message to show.
		 */
		show_direct_failure_message: function (message) {
			snackbar.show({
				type: 'failure',
				message: message,
				duration: 6,
				dismissible: true,
			});
		},

		//regular required validation
		regular_validation: function (inputs, no_errors) {
			inputs.every(function (item) {
				var $this = $(item),
					value = $this.val(),
					is_required = $this.attr('required'),
					name = $this.data('key-name');
				if (is_required && value === '') {
					no_errors = false;
					ur_team_utils.show_failure_message(
						ur_team_data.labels.i18n_error +
							'! ' +
							name +
							' ' +
							ur_team_data.labels.i18n_field_is_required,
					);
					return false;
				}
				return true;
			});
			return no_errors;
		},
	};

	//utils related with ajax requests
	var ur_team_request_utils = {
		/**
		 * prepare team data before ajax requests
		 * @returns {{team_data: {id: *, name: *, team_leader: *, members: *}}}
		 */
		prepare_team_data: function () {
			var team_data = {},
				form = $('#ur-membership-team-form');

			var selectedMembers =
				form.find('#ur-membership-team-members').val() || [];
			var membersId = [];
			var $select = form.find('#ur-membership-team-members');

			selectedMembers.forEach(function (email) {
				var userId = null;

				var $option = $select.find('option[value="' + email + '"]');
				if ($option.length > 0) {
					userId = $option.data('user-id');
				}

				if (!userId) {
					$select.find('option').each(function () {
						if ($(this).val() === email) {
							userId = $(this).data('user-id');
							return false; // break loop
						}
					});
				}

				if (userId) {
					membersId.push(parseInt(userId, 10));
				}
			});

			team_data = {
				id: ur_team_data.team_id,
				name: form.find('#ur-input-type-team-name').val(),
				team_leader: form.find('#ur-membership-team-leader').val(),
				members: selectedMembers,
				members_id: membersId,
				team_seats:
					form.find('#ur-input-type-team-seats').val() ||
					form.find('#ur-membership-max-team-seats').val(),
			};
			return team_data;
		},
		/**
		 * validate team form before submit
		 * @returns {boolean}
		 */
		validate_team_form: function () {
			var team_fields = $('#ur-membership-team-fields').find('input'),
				team_members = $('#ur-membership-team-members').val(),
				max_team_seats = $('#ur-membership-max-team-seats').val(),
				team_seats =
					parseInt($('#ur-input-type-team-seats').val(), 10) ||
					parseInt(max_team_seats, 10),
				used_seats = team_members.length,
				group_leader = $('#ur-membership-team-leader').val(),
				no_errors = true,
				//team fields validation
				team_fields = Object.values(team_fields).reverse().slice(2);
			var result = ur_team_utils.regular_validation(team_fields, true);

			if (!result) {
				return false;
			}

			if (team_seats < used_seats) {
				no_errors = false;
				ur_team_utils.show_failure_message(
					ur_team_data.labels.i18n_error +
						'! ' +
						'Team seats cannot be reduced below ' +
						used_seats +
						' (currently occupied seats).',
				);
			}

			//group max seats validation
			if (team_members.length > team_seats) {
				no_errors = false;
				ur_team_utils.show_failure_message(
					ur_team_data.labels.i18n_error +
						'! ' +
						ur_team_data.labels.i18n_max_seats_exceeded,
				);
			}

			// group leader removal from members list validation
			if (group_leader && !team_members.includes(group_leader)) {
				no_errors = false;
				ur_team_utils.show_failure_message(
					ur_team_data.labels.i18n_error +
						'! ' +
						ur_team_data.labels.i18n_group_leader_removal,
				);
			}

			return no_errors;
		},

		/**
		 * called to update an existing team
		 * @param $this
		 */
		update_team: function ($this) {
			ur_team_utils.toggleSaveButtons(true);
			ur_team_utils.append_spinner($this);
			if (this.validate_team_form()) {
				var prepare_team_data = this.prepare_team_data();
				this.send_data(
					{
						action: 'user_registration_team_membership_update_team',
						team_data: JSON.stringify(prepare_team_data),
						team_id: ur_team_data.team_id,
					},
					{
						success: function (response) {
							if (response.success) {
								ur_team_utils.show_success_message(response.data.message);
								$(location).attr('href', ur_team_data.team_url);
							} else {
								ur_team_utils.show_failure_message(response.data.message);
							}
						},
						failure: function (xhr, statusText) {
							ur_team_utils.show_failure_message(
								ur_team_data.labels.network_error + '(' + statusText + ')',
							);
						},
						complete: function () {
							ur_team_utils.remove_spinner($this);
							ur_team_utils.toggleSaveButtons(false);
						},
					},
				);
			} else {
				ur_team_utils.remove_spinner($this);
				ur_team_utils.toggleSaveButtons(false);
			}
		},

		/**
		 * Send data to the backend API.
		 *
		 * @param {JSON} data Data to send.
		 * @param {JSON} callbacks Callbacks list.
		 */
		send_data: function (data, callbacks) {
			var success_callback =
					'function' === typeof callbacks.success
						? callbacks.success
						: function () {},
				failure_callback =
					'function' === typeof callbacks.failure
						? callbacks.failure
						: function () {},
				beforeSend_callback =
					'function' === typeof callbacks.beforeSend
						? callbacks.beforeSend
						: function () {},
				complete_callback =
					'function' === typeof callbacks.complete
						? callbacks.complete
						: function () {};

			// Inject default data.
			if (!data._wpnonce && ur_team_data) {
				data._wpnonce = ur_team_data._nonce;
			}
			$.ajax({
				type: 'post',
				dataType: 'json',
				url: ur_team_data.ajax_url,
				data: data,
				beforeSend: beforeSend_callback,
				success: success_callback,
				error: failure_callback,
				complete: complete_callback,
			});
		},

		/**
		 *
		 * @param selected_teams
		 * @param is_multiple
		 */
		remove_deleted_teams: function (selected_teams, is_multiple) {
			if (is_multiple) {
				selected_teams.each(function () {
					$(this).parents('tr').remove();
				});
			} else {
				$(selected_teams).parents('tr').remove();
			}
		},
	};

	$('#ur-membership-team-members').select2({
		placeholder: 'Select Members',
		minimumResultsForSearch: -1,
		multiple: true,
		tags: true,

		createTag: function (params) {
			// Only add tag if term is not empty
			var term = $.trim(params.term);
			if (term === '') {
				return null;
			}
			var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
			var isValidEmail = re.test(term);
			if (!isValidEmail) {
				return null;
			}

			return {
				id: term,
				text: term,
				newOption: true,
			};
		},
		templateResult: function (data) {
			if (data.newOption) {
				return $(
					'<span>Add new email: <strong>' + data.text + '</strong></span>',
				);
			}
			return data.text;
		},
	});

	$('#ur-membership-team-members').on('select2:select', function (e) {
		var selectedEmail = e.params.data.id;
		var $select = $(this);

		var $existingOption = $select.find('option[value="' + selectedEmail + '"]');
		if ($existingOption.length === 0) {
			$select.find('option').each(function () {
				var optionEmail = $(this).val();
				if (optionEmail === selectedEmail) {
					var userId = $(this).data('user-id');
					if (userId) {
						var $newOption = $('<option>', {
							value: selectedEmail,
							'data-user-id': userId,
							text: $(this).text(),
						});
						$select.append($newOption);
					}
				}
			});
		}
	});

	var teamMembers = $('#ur-membership-team-members');
	var snackbarShown = false;

	// Prevent adding new members once max is reached, but allow removing existing selections.
	teamMembers.on('select2:selecting', function (e) {
		var selected = $(this).val() || [];
		var maxSeats =
			parseInt(
				$('#ur-input-type-team-seats').val() ||
					$('#ur-membership-max-team-seats').val(),
				10,
			) || 0;
		if (maxSeats > 0 && selected.length >= maxSeats) {
			e.preventDefault();
			if (snackbar && !snackbarShown) {
				snackbarShown = true;
				ur_team_utils.show_direct_failure_message(
					ur_team_data.labels.i18n_error +
						'! ' +
						ur_team_data.labels.i18n_max_seats_exceeded,
				);
				setTimeout(function () {
					snackbarShown = false;
				}, 3000);
			}
		}
	});

	// Prevent removing group leader from members list
	teamMembers.on('select2:unselecting', function (e) {
		var removedUserId = e.params.args.data.id;
		var leaderId = $('#ur-membership-team-leader').val();

		if (leaderId && removedUserId === leaderId) {
			e.preventDefault();

			if (snackbar) {
				ur_team_utils.show_direct_failure_message(
					ur_team_data.labels.i18n_error +
						'! ' +
						ur_team_data.labels.i18n_group_leader_removal,
				);
			}
		}
	});

	$('#ur-input-type-team-seats').on('input change blur', function () {
		var $input = $(this);
		var teamSeats = parseInt($input.val(), 10) || 0;
		var usedSeats = $('#ur-membership-team-members').val()
			? $('#ur-membership-team-members').val().length
			: 0;
		var minSeats = parseInt($input.attr('min'), 10) || 0;

		if (usedSeats > minSeats) {
			$input.attr('min', usedSeats);
			minSeats = usedSeats;
		}

		if (teamSeats < minSeats) {
			$input.val(minSeats);
			if (snackbar) {
				ur_team_utils.show_direct_failure_message(
					ur_team_data.labels.i18n_error +
						'! ' +
						'Team seats cannot be less than ' +
						minSeats +
						' (currently occupied seats).',
				);
			}
		}

		$('#ur-membership-max-team-seats').val(teamSeats || minSeats);
		var $membersLabel = $('label[for="ur-membership-team-members"]');
		if ($membersLabel.length) {
			$membersLabel.html(
				'Members (Max: <span id="team-seats-display">' +
					(teamSeats || minSeats) +
					'</span>)' +
					'<span style="color:red">*</span> :',
			);
		}
	});

	$('#ur-membership-team-members').on('change', function () {
		var usedSeats = $(this).val() ? $(this).val().length : 0;
		var $seatsInput = $('#ur-input-type-team-seats');
		var currentSeats = parseInt($seatsInput.val(), 10) || 0;

		$seatsInput.attr('min', usedSeats);

		if (currentSeats < usedSeats) {
			$seatsInput.val(usedSeats).trigger('change');
		}
	});

	$('.ur-team-update-btn').on('click', function (e) {
		e.preventDefault();
		e.stopPropagation();
		var $this = $(this);
		ur_team_request_utils.update_team($this);
	});

	//delete team
	$('.delete-team').on('click', function (e) {
		e.preventDefault();
		e.stopPropagation();

		var $this = $(this),
			$team_id = $this.data('team-id'),
			parent = $this.closest('.delete');
		if (parent.find('span').hasClass('is-active')) {
			return;
		}
		ur_team_utils.append_spinner(parent);

		Swal.fire({
			title:
				'<img src="' +
				ur_team_data.delete_icon +
				'" id="delete-user-icon">' +
				ur_team_data.labels.i18n_prompt_title,
			html:
				'<p id="html_1">' +
				ur_team_data.labels.i18n_prompt_single_subtitle +
				'</p>',
			showCancelButton: true,
			confirmButtonText: ur_team_data.labels.i18n_prompt_delete,
			cancelButtonText: ur_team_data.labels.i18n_prompt_cancel,
			allowOutsideClick: false,
		}).then(function (result) {
			if (result.isConfirmed) {
				ur_team_request_utils.send_data(
					{
						action: 'user_registration_team_membership_delete_team',
						team_id: $team_id,
					},
					{
						success: function (response) {
							if (response.success) {
								ur_team_utils.show_success_message(response.data.message);
								ur_team_request_utils.remove_deleted_teams($this, false);
							} else {
								Swal.fire({
									title:
										'<img src="' +
										ur_team_data.delete_icon +
										'" id="delete-user-icon">' +
										ur_team_data.labels.i18n_prompt_title,
									html: response.data.message,
									confirmButtonText: ur_team_data.labels.i18n_prompt_ok,
									allowOutsideClick: false,
								});
							}
						},
						failure: function (xhr, statusText) {
							ur_team_utils.show_failure_message(
								ur_team_data.labels.network_error + '(' + statusText + ')',
							);
						},
						complete: function () {
							ur_team_utils.remove_spinner($this.closest('.delete'));
						},
					},
				);
			} else {
				ur_team_utils.remove_spinner($this.closest('.delete'));
			}
		});
	});

})(jQuery, window.ur_team_localized_data);

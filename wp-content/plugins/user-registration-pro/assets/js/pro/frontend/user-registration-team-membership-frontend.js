if (typeof jQuery !== 'undefined') {
	jQuery(function ($) {
		var form = $('.user-registration-EditTeam');
		if (!form.length) return;

		var emailInput = form.find('input.team_member_email[type="email"]');
		var addBtn = form.find('.ur-add-member-btn-container');
		var invitedWrapper = form.find('.ur-invited-email-wrapper');
		var invitedMembers = form.find('input[name="invited_member_emails"]');
		var membersIdInput = form.find('input[name="members_id"]');
		var maxSeats = parseInt(form.find('input[name="max_seats"]').val(), 10) || 0;
		var existingMembers = $('input[name="existing_member_emails"]');
		if (!emailInput.length || !addBtn.length) return;

		function isStrictEmail(email) {
			return /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(email);
		}

		function getInvitedCount() {
			var val = (invitedMembers.val() || '').trim();
			if (!val) return invitedWrapper.find('.ur-invited-email-item').length || 0;
			return val.split(',').filter(function (v) {
				return v && v.trim() !== '';
			}).length;
		}

		function isEmailAlreadyMember(email) {
			email = email.toLowerCase();

			var existingVal = (existingMembers.val() || '').trim();
			if (!existingVal) return false;

			return existingVal
				.split(',')
				.map(function (e) { return e.trim().toLowerCase(); })
				.indexOf(email) !== -1;
		}

		function updateAddMemberInput() {
			var invitedCount = getInvitedCount();
			var membersCount = form.find('.ur-account-table__body tr').length || 0;
			var total = membersCount + invitedCount;
			var full = maxSeats > 0 && total >= maxSeats;
			var email = $.trim(emailInput.val() || '');
			var validEmail = isStrictEmail(email);
			var alreadyMember = validEmail && isEmailAlreadyMember(email);

			addBtn.prop(
				'disabled',
				full || !validEmail || alreadyMember
			);
			emailInput.prop('disabled', full);
			if (full) {
				addBtn.attr('aria-disabled', 'true');
				$('.ur-max-seats-reached-error').text(ur_team.max_seats_reached);
			} else {
				addBtn.removeAttr('aria-disabled');
			}
		}

		emailInput.on('input change', updateAddMemberInput);

		addBtn.on('click', function (e) {
			e.preventDefault();
			var email = $.trim(emailInput.val() || '');
			if (!isStrictEmail(email)) return;

			// append invited item with data-email for reliable removal
			var item = $('<div class="ur-invited-email-item" data-email="' + $('<div>').text(email).html() + '"></div>');
			item.append($('<span>').text(email));
			item.append('<span class="dashicons dashicons-no-alt"></span>');
			invitedWrapper.append(item);

			// update hidden input
			var emails = invitedMembers.val() ? invitedMembers.val().split(',') : [];
			emails.push(email);
			invitedMembers.val(emails.join(','));

			// Try to get user ID for this email and update members_id
			$.ajax({
				url: ur_team.ajax_url,
				type: 'POST',
				data: {
					action: 'user_registration_get_user_id_by_email',
					email: email,
					nonce: ur_team.nonce
				},
				success: function (response) {
					if (response.success && response.data.user_id) {
						var memberIds = membersIdInput.val() ? membersIdInput.val().split(',') : [];
						var userIdStr = response.data.user_id.toString();
						// Only add if not already in the array
						if (memberIds.indexOf(userIdStr) === -1) {
							memberIds.push(userIdStr);
							membersIdInput.val(memberIds.join(','));
						}
					}
				},
				error: function() {
					// If AJAX fails or user doesn't exist yet, that's okay - backend will handle it
				}
			});

			invitedWrapper.show();
			emailInput.val('');
			updateAddMemberInput();
		});

		// removal handler for invited member emails
		$(document).on('click', '.ur-invited-email-item .dashicons-no-alt', function () {
			var item = $(this).closest('.ur-invited-email-item');
			var emailToRemove = item.data('email') || $.trim(item.text());
			item.remove();

			var emails = invitedMembers.val() ? invitedMembers.val().split(',') : [];
			emails = emails.filter(function (e) {
				return e !== emailToRemove;
			});
			invitedMembers.val(emails.join(','));

			// Try to remove user ID from members_id if user exists
			$.ajax({
				url: ur_team.ajax_url,
				type: 'POST',
				data: {
					action: 'user_registration_get_user_id_by_email',
					email: emailToRemove,
					nonce: ur_team.nonce
				},
				success: function (response) {
					if (response.success && response.data.user_id) {
						var memberIds = membersIdInput.val() ? membersIdInput.val().split(',') : [];
						memberIds = memberIds.filter(function (id) {
							return id !== response.data.user_id.toString();
						});
						membersIdInput.val(memberIds.join(','));
					}
				}
			});

			if ($('.ur-invited-email-item').length === 0) {
				invitedWrapper.hide();
			}
			updateAddMemberInput();
		});

		$('.remove-team-members-button').on('click', function(e){
			e.preventDefault();
			e.stopPropagation();
			var emailToRemove = $(this).data('email');
			var userIdToRemove = $(this).data('user-id');
			var emails = existingMembers.val() ? existingMembers.val().split(',') : [];
			emails = emails.filter(function (e) {
				return e !== emailToRemove;
			});
			existingMembers.val(emails.join(','));

			// Update members_id by removing the user ID
			if (userIdToRemove) {
				var memberIds = membersIdInput.val() ? membersIdInput.val().split(',') : [];
				memberIds = memberIds.filter(function (id) {
					return id !== userIdToRemove.toString();
				});
				membersIdInput.val(memberIds.join(','));
			}

			var tr = $(this).closest('tr');
			tr.remove();
			$('.ur-max-seats-reached-error').text('');
			updateAddMemberInput();
		})

		// initialize state
		updateAddMemberInput();
	});
}

/**
 * Custom Email JavaScript
 */
(function ($) {
	'use strict';

	$(document).ready(function () {
		$('input.ur-modern-radio-group')
			.closest('ul')
			.addClass('ur-modern-radio-list');

		function toggleDurationField() {
			$('input[name^="ur_custom_email_delivery_timing_"]').each(function () {
				var $radio = $(this);
				var fieldId = $radio.attr('name');
				var emailId = fieldId.replace('ur_custom_email_delivery_timing_', '');
				var selectedValue = $('input[name="' + fieldId + '"]:checked').val();

				var $durationWrapper = $(
					'#ur_custom_email_duration_' + emailId,
				).closest('.user-registration-global-settings');

				if ($durationWrapper.length === 0) {
					$durationWrapper = $(
						'input[id="ur_custom_email_duration_value_' + emailId + '"]',
					).closest('.ur-duration-input-wrapper');
				}

				if ($durationWrapper.length === 0) {
					$durationWrapper = $(
						'select[id="ur_custom_email_duration_unit_' + emailId + '"]',
					).closest('.ur-duration-input-wrapper');
				}

				if ($durationWrapper.length > 0) {
					if (selectedValue === 'scheduled') {
						$durationWrapper.addClass('ur-duration-visible').show();
					} else {
						$durationWrapper.removeClass('ur-duration-visible').hide();
					}
				}
			});
		}

		function toggleOverrideDefaultField() {
			var emailIds = [];
			$('input[name^="ur_custom_email_delivery_timing_"]').each(function () {
				var fieldId = $(this).attr('name');
				var emailId = fieldId.replace('ur_custom_email_delivery_timing_', '');
				if (emailIds.indexOf(emailId) === -1) {
					emailIds.push(emailId);
				}
			});

			emailIds.forEach(function (emailId) {
				var triggerSelect = $('#ur_custom_email_trigger_event_' + emailId);
				if (triggerSelect.length > 0) {
					var triggerValue = triggerSelect.val();
					toggleOverrideDefaultCheckbox(emailId, triggerValue);
				}
			});
		}

		setTimeout(function () {
			toggleDurationField();
			$('select[id^="ur_custom_email_trigger_event_"]').each(function () {
				var $select = $(this);
				var selectedValue = $select.val();
				var triggerEventId = $select.attr('id');
				var emailId = triggerEventId.replace(
					'ur_custom_email_trigger_event_',
					'',
				);
				toggleOverrideDefaultCheckbox(emailId, selectedValue);
			});
		}, 100);

		$(document).on(
			'change',
			'input[name^="ur_custom_email_delivery_timing_"]',
			function () {
				var fieldId = $(this).attr('name');
				var emailId = fieldId.replace('ur_custom_email_delivery_timing_', '');
				var selectedValue = $(this).val();

				toggleDurationField();
				toggleOverrideDefaultField();

				var triggerSelect = $('#ur_custom_email_trigger_event_' + emailId);
				if (triggerSelect.length > 0) {
					var triggerValue = triggerSelect.val();
					toggleOverrideDefaultCheckbox(emailId, triggerValue);
				}
			},
		);

		function updateTriggerEventDisplay() {
			$('select[id^="ur_custom_email_trigger_event_"]').each(function () {
				var $select = $(this);
				var selectedValue = $select.val();
				var selectedText = $select.find('option:selected').text();
				var triggerEventId = $select.attr('id');
				var emailId = triggerEventId.replace(
					'ur_custom_email_trigger_event_',
					'',
				);

				var $durationWrapper = $select
					.closest('.user-registration-global-settings')
					.siblings('.ur-duration-input-wrapper');

				if ($durationWrapper.length === 0) {
					var $parentSection = $select.closest(
						'.user-registration-card__body, .user-registration-options-container',
					);
					$durationWrapper = $parentSection.find('.ur-duration-input-wrapper');
				}

				var $badge = $durationWrapper.find('.ur-trigger-event-badge');

				if ($badge.length > 0 && selectedText) {
					$badge.find('.ur-trigger-event-text').text(selectedText);
					$badge.closest('.ur-duration-row-3').show();
				} else if ($badge.length > 0 && !selectedText) {
					$badge.closest('.ur-duration-row-3').hide();
				}

				var $beforeAfterSelect = $durationWrapper.find(
					'select[id="ur_custom_email_duration_before_after_' + emailId + '"]',
				);
				var $noticeRow = $durationWrapper.find('.ur-trigger-event-notice-row');

				if ($beforeAfterSelect.length > 0) {
					var afterOnlyTriggers = [
						'member_signs_up',
						'membership_cancellation',
						'membership_upgrade',
						'membership_downgrade',
						'membership_renewal_success',
						'membership_renewal_failed',
					];

					if (afterOnlyTriggers.indexOf(selectedValue) !== -1) {
						$beforeAfterSelect
							.find('option[value="before"]')
							.prop('disabled', true)
							.hide();
						$beforeAfterSelect
							.find('option[value="after"]')
							.prop('disabled', false)
							.show()
							.prop('selected', true);
						$beforeAfterSelect.prop('disabled', true);

						if ($noticeRow.length > 0) {
							if ($noticeRow.is(':empty')) {
								var noticeText =
									typeof urCustomEmail !== 'undefined' &&
									urCustomEmail.i18n &&
									urCustomEmail.i18n.triggerAfterOnly
										? urCustomEmail.i18n.triggerAfterOnly
										: 'This trigger can only send emails <strong>after</strong> the event occurs';
								var noticeHtml = noticeText + '</span>' + '</div>';
								$noticeRow.html(noticeHtml);
							}
							$noticeRow.show();
						}
					} else {
						$beforeAfterSelect.find('option').prop('disabled', false).show();
						$beforeAfterSelect.prop('disabled', false);

						if ($noticeRow.length > 0) {
							$noticeRow.hide();
						}

						var currentValue = $beforeAfterSelect.val();
						if (!currentValue || currentValue === '') {
							$beforeAfterSelect.val('after');
						}
					}
				}

				toggleOverrideDefaultCheckbox(emailId, selectedValue);
			});
		}

		function toggleOverrideDefaultCheckbox(emailId, triggerValue) {
			var $overrideWrapper = $(
				'#ur_custom_email_override_default_' + emailId,
			).closest('.user-registration-global-settings');

			if ($overrideWrapper.length === 0) {
				var $parentSection = $(
					'#ur_custom_email_override_default_' + emailId,
				).closest(
					'.user-registration-card__body, .user-registration-options-container',
				);
				$overrideWrapper = $parentSection
					.find('#ur_custom_email_override_default_' + emailId)
					.closest('.user-registration-global-settings');
			}

			if ($overrideWrapper.length > 0) {
				var $overrideCheckbox = $overrideWrapper.find(
					'#ur_custom_email_override_default_' + emailId,
				);

				var fieldId = 'ur_custom_email_delivery_timing_' + emailId;
				var deliveryTiming = $('input[name="' + fieldId + '"]:checked').val();
				if (!deliveryTiming) {
				}

				var showOverrideTriggers = [
					'member_signs_up',
					'membership_expired',
					'membership_cancellation',
				];

				if (
					showOverrideTriggers.indexOf(triggerValue) !== -1 &&
					deliveryTiming === 'instant'
				) {
					$overrideWrapper.show();
					if ($overrideCheckbox.length > 0) {
						$overrideCheckbox.prop('disabled', false);
					}
				} else {
					$overrideWrapper.hide();
					if ($overrideCheckbox.length > 0) {
						$overrideCheckbox.prop('checked', false).prop('disabled', true);
					}
				}
			}
		}

		updateTriggerEventDisplay();

		setTimeout(function () {
			$('select[id^="ur_custom_email_trigger_event_"]').each(function () {
				var $select = $(this);
				var selectedValue = $select.val();
				var triggerEventId = $select.attr('id');
				var emailId = triggerEventId.replace(
					'ur_custom_email_trigger_event_',
					'',
				);
				toggleOverrideDefaultCheckbox(emailId, selectedValue);
			});
		}, 150);

		$(document).on(
			'change',
			'select[id^="ur_custom_email_trigger_event_"]',
			function () {
				updateTriggerEventDisplay();
			},
		);

		function toggleMembershipField() {
			$('select[id^="ur_custom_email_send_to_"]').each(function () {
				var $select = $(this);
				var selectedValue = $select.val();
				var sentToId = $select.attr('id');
				var emailId = sentToId.replace('ur_custom_email_send_to_', '');

				var $membershipWrapper = $(
					'#ur_custom_email_membership_' + emailId,
				).closest('.user-registration-global-settings');

				if ($membershipWrapper.length === 0) {
					var $parentSection = $select.closest(
						'.user-registration-card__body, .user-registration-options-container',
					);
					$membershipWrapper = $parentSection
						.find('#ur_custom_email_membership_' + emailId)
						.closest('.user-registration-global-settings');
				}

				if ($membershipWrapper.length > 0) {
					if (selectedValue === 'specific_memberships') {
						$membershipWrapper.show();
					} else {
						$membershipWrapper.hide();
					}
				}
			});
		}

		toggleMembershipField();

		$(document).on(
			'change',
			'select[id^="ur_custom_email_send_to_"]',
			function () {
				toggleMembershipField();
			},
		);

		var hideTimeout;

		function showDropdown($wrapper) {
			var $dropdown = $wrapper.find('.ur-email-actions-dropdown');
			if (!$dropdown.length || $dropdown.parent()[0] === document.body) {
				var emailId = $wrapper
					.find('.ur-email-actions-toggle')
					.data('email-id');
				if (emailId) {
					$dropdown = $(
						'.ur-email-actions-dropdown[data-email-id="' + emailId + '"]',
					);
				}
			}

			if ($dropdown.length) {
				clearTimeout(hideTimeout);

				if (!$dropdown.data('original-parent')) {
					$dropdown.data('original-parent', $wrapper);
				}
				$wrapper.data('dropdown', $dropdown);

				var $button = $wrapper.find('.ur-email-actions-toggle');
				var buttonOffset = $button.offset();
				var buttonWidth = $button.outerWidth();
				var buttonHeight = $button.outerHeight();
				var scrollTop = $(window).scrollTop();
				var scrollLeft = $(window).scrollLeft();

				if ($dropdown.parent()[0] !== document.body) {
					$dropdown.appendTo('body');
				}

				var dropdownLeft =
					buttonOffset.left + buttonWidth - $dropdown.outerWidth() - scrollLeft;
				var dropdownTop = buttonOffset.top + buttonHeight + 2 - scrollTop;

				$dropdown.css({
					position: 'fixed',
					top: dropdownTop + 'px',
					left: dropdownLeft + 'px',
					right: 'auto',
					display: 'block',
					'z-index': 99999,
				});

				$dropdown.show();
			}
		}

		function hideDropdown($dropdown) {
			if ($dropdown && $dropdown.length) {
				$dropdown.hide();
				var $originalParent = $dropdown.data('original-parent');
				if ($originalParent && $originalParent.length) {
					$dropdown.appendTo($originalParent);
					$dropdown.css({
						position: 'absolute',
						top: '100%',
						right: '0',
						left: 'auto',
					});
				}
			}
		}

		$(document).on('mouseenter', '.ur-email-actions-wrapper', function () {
			var $wrapper = $(this);
			clearTimeout(hideTimeout);
			showDropdown($wrapper);
		});

		$(document).on('mouseover', '.ur-email-actions-wrapper', function () {
			var $wrapper = $(this);
			var $dropdown =
				$wrapper.data('dropdown') ||
				$wrapper.find('.ur-email-actions-dropdown');
			if (!$dropdown.length) {
				var emailId = $wrapper
					.find('.ur-email-actions-toggle')
					.data('email-id');
				if (emailId) {
					$dropdown = $(
						'.ur-email-actions-dropdown[data-email-id="' + emailId + '"]',
					);
				}
			}
			if ($dropdown.length && $dropdown.is(':visible')) {
				clearTimeout(hideTimeout);
			}
		});

		$(document).on('mouseleave', '.ur-email-actions-wrapper', function () {
			var $wrapper = $(this);
			var $dropdown =
				$wrapper.data('dropdown') ||
				$wrapper.find('.ur-email-actions-dropdown');
			if (!$dropdown.length) {
				var emailId = $wrapper
					.find('.ur-email-actions-toggle')
					.data('email-id');
				if (emailId) {
					$dropdown = $(
						'.ur-email-actions-dropdown[data-email-id="' + emailId + '"]',
					);
				}
			}

			if ($dropdown.length) {
				hideTimeout = setTimeout(function () {
					if (!$wrapper.is(':hover') && !$dropdown.is(':hover')) {
						hideDropdown($dropdown);
					}
				}, 150);
			}
		});

		$(document).on('mouseenter', '.ur-email-actions-dropdown', function () {
			clearTimeout(hideTimeout);
			$(this).show();
		});

		$(document).on('mouseleave', '.ur-email-actions-dropdown', function () {
			var $dropdown = $(this);
			hideTimeout = setTimeout(function () {
				var $originalParent = $dropdown.data('original-parent');
				if (
					$originalParent &&
					!$originalParent.is(':hover') &&
					!$dropdown.is(':hover')
				) {
					hideDropdown($dropdown);
				}
			}, 150);
		});

		$(document).on('click', '.ur-email-action-delete', function (e) {
			e.preventDefault();
			e.stopPropagation();

			var $button = $(this);
			var emailId = $button.data('email-id') || $button.attr('data-email-id');

			if (!emailId) {
				alert(urCustomEmail.i18n.emailIdNotFound);
				return;
			}

			if (!confirm(urCustomEmail.i18n.confirmDelete)) {
				return;
			}

			var originalText = $button.text();
			$button.prop('disabled', true).text(urCustomEmail.i18n.deleting);

			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'ur_delete_custom_email',
					security: urCustomEmail.nonce,
					email_id: emailId,
				},
				success: function (response) {
					if (response.success) {
						window.location.href = response.data.redirect_url;
					} else {
						alert(response.data.message || urCustomEmail.i18n.errorDeleting);
						$button.prop('disabled', false).text(originalText);
					}
				},
				error: function () {
					alert(urCustomEmail.i18n.errorDeletingRetry);
					$button.prop('disabled', false).text(originalText);
				},
			});
		});

		var $addNewButton = $('a.ur-add-new-custom-email');

		if ($addNewButton.length === 0) {
			var $customEmailCard = $('.user-registration-card').filter(function () {
				var titleText = $(this)
					.find('.user-registration-card__title')
					.text()
					.toUpperCase();
				return titleText.indexOf('CUSTOM EMAIL') !== -1;
			});

			$addNewButton = $customEmailCard.find('a').filter(function () {
				var buttonText = $(this).text().toUpperCase();
				return (
					buttonText.indexOf(urCustomEmail.i18n.addNewEmail.toUpperCase()) !==
					-1
				);
			});
		}

		if ($addNewButton.length === 0) {
			$addNewButton = $('a[data-button-type="ur-add-new-custom-email"]');
		}

		if ($addNewButton.length) {
			$addNewButton
				.addClass('page-title-action ur-add-new-custom-email')
				.removeClass('user_registration_smart_tags_used')
				.removeAttr('target')
				.attr('data-button-type', 'ur-add-new-custom-email');

			$addNewButton.find('.dashicons-external').remove();
		}

		$(document).on('click', '.ur-add-new-custom-email', function (e) {
			e.preventDefault();
			e.stopPropagation();

			var $button = $(this);
			var originalText = $button.find('span').text() || $button.text();

			// $button.prop('disabled', true);
			// if ($button.find('span').length) {
			// 	$button.find('span').text(urCustomEmail.i18n.loading);
			// } else {
			// 	$button.text(urCustomEmail.i18n.loading);
			// }

			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'ur_add_custom_email',
					security: urCustomEmail.nonce,
				},
				success: function (response) {
					if (response.success) {
						window.location.href = response.data.redirect_url;
					} else {
						alert(response.data.message || urCustomEmail.i18n.errorLoadingForm);
						$button.prop('disabled', false);
						if ($button.find('span').length) {
							$button.find('span').text(originalText);
						} else {
							$button.text(originalText);
						}
					}
				},
				error: function () {
					alert(urCustomEmail.i18n.errorLoadingFormRetry);
					$button.prop('disabled', false);
					if ($button.find('span').length) {
						$button.find('span').text(originalText);
					} else {
						$button.text(originalText);
					}
				},
			});
		});

		$(document).on('click', '.ur-delete-custom-email', function (e) {
			e.preventDefault();
			e.stopPropagation();

			var $button = $(this);
			var emailId = $button.data('email-id') || $button.attr('data-email-id');

			if (!emailId) {
				alert(urCustomEmail.i18n.emailIdNotFound);
				return;
			}

			if (!confirm(urCustomEmail.i18n.confirmDelete)) {
				return;
			}

			var originalText = $button.text();
			$button.prop('disabled', true).text(urCustomEmail.i18n.deleting);

			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'ur_delete_custom_email',
					security: urCustomEmail.nonce,
					email_id: emailId,
				},
				success: function (response) {
					if (response.success) {
						window.location.href = response.data.redirect_url;
					} else {
						alert(response.data.message || urCustomEmail.i18n.errorDeleting);
						$button.prop('disabled', false).text(originalText);
					}
				},
				error: function () {
					alert(urCustomEmail.i18n.errorDeletingRetry);
					$button.prop('disabled', false).text(originalText);
				},
			});
		});

		$('.ur-delete-custom-email')
			.css({
				color: '#b32d2e',
				'text-decoration': 'none',
			})
			.hover(
				function () {
					$(this).css('color', '#8a2424');
				},
				function () {
					$(this).css('color', '#b32d2e');
				},
			);

		$(document).on(
			'change',
			'input[name^="ur_custom_email_enabled_"]',
			function () {
				var $checkbox = $(this);
				var emailId = $checkbox
					.attr('name')
					.replace('ur_custom_email_enabled_', '');
				var isChecked = $checkbox.is(':checked');

				var $toggle = $('#email_' + emailId);
				if ($toggle.length) {
					$toggle.prop('checked', isChecked);
				}
			},
		);

		$(document).on('change', 'input[name^="email_status["]', function () {
			var $checkbox = $(this);
			var name = $checkbox.attr('name');
			var emailIdMatch = name.match(/\[(.*?)\]/);
			if (!emailIdMatch) {
				return;
			}
			var emailId = emailIdMatch[1];
			var isChecked = $checkbox.is(':checked');

			var $hiddenInput = $checkbox.siblings(
				'input[type="hidden"][name="' + name + '"]',
			);
			if ($hiddenInput.length && isChecked) {
				$hiddenInput.remove();
			}

			var $enableCheckbox = $(
				'input[name="ur_custom_email_enabled_' + emailId + '"]',
			);
			if ($enableCheckbox.length) {
				$enableCheckbox.prop('checked', isChecked);
			}
		});
	});
})(jQuery);

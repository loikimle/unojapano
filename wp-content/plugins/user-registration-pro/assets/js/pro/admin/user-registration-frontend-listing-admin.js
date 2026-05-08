(function ($) {
	'use strict';
	function urflToast(type, message) {
		if (window.UR_Snackbar) {
			window.urflSnackbar = window.urflSnackbar || new UR_Snackbar();
			window.urflSnackbar.add({
				type: type === 'success' ? 'success' : 'failure',
				message: message,
				duration: type === 'success' ? 5 : 6,
			});
			return;
		}

		var cls = type === 'success' ? 'notice-success' : 'notice-error';
		var $notice = $(
			'<div class="notice ' +
				cls +
				' is-dismissible"><p>' +
				message +
				'</p></div>',
		);

		var $target = $('#wpbody-content .wrap h1').first();
		if ($target.length) $notice.insertAfter($target);
		else $('#wpbody-content').prepend($notice);

		setTimeout(function () {
			$notice.fadeOut(200, function () {
				$(this).remove();
			});
		}, 5000);
	}

	var URFL_Admin = {
		init: function () {
			this.moveShortcodeToTitle();
			this.bindEvents();
			this.initSelect2New();
			this.initSortableNew();
			this.initCardFields();
			this.toggleGridOnlyFields();
			this.toggleProfilePageSettings();
			this.handleShowBasedOnChange();
			this.initOrderedGroupedFields();
			this.toggleProfileButtonText();

			this.legacyInitSelect2();
			this.legacyHandleGeneralOptions();
			this.legacyHandleFilterOptions();
			this.legacyHandlePaginationOptions();
			this.legacyHandleAdvancedFilterOptions();
			this.legacyHandleAdvancedFilters();
			this.legacyHandleUserFields();
		},

		moveShortcodeToTitle: function () {
			var $shortcode = $('#urfl-shortcode-inline');
			var $titleWrap = $('#titlewrap');
			if ($shortcode.length && $titleWrap.length) {
				$shortcode.appendTo($titleWrap);
			}
		},

		bindEvents: function () {
			var self = this;

			$(document).on('change', '.urfl-advanced-filter-select', function () {
				self.handleAdvancedFilterSelectChange($(this));
			});

			$(document).on('click', '.urfl-remove-filter', function (e) {
				e.preventDefault();
				var $item = $(this).closest('.urfl-advanced-filter-item');
				var $wrap = $item.closest('.urfl-field-advanced-filter');
				var $orderBox = $wrap.find('.urfl-advanced-filter-order');
				var key = String($item.data('key'));
				var type = $item.attr('data-type');

				$item.remove();

				if (type !== 'custom' && key && key !== 'other' && key !== '') {
					var $select = $wrap.find('.urfl-advanced-filter-select');
					var current = $select.val() || [];
					current = $.grep(current, function (v) {
						return String(v) !== key;
					});
					$select.val(current).trigger('change.select2');
				}

				self.syncAdvancedFilterHidden($wrap);
				self.toggleAdvancedFilterEmptyMessage($wrap);
			});

			$(document).on('click', '.urfl-tab-link', this.handleTabClick);
			$(document).on('click', '.urfl-section-header', this.handleSectionToggle);

			$(document).on(
				'change',
				'.urfl-layout-option input[type="radio"]',
				this.handleLayoutChange,
			);

			$(document).on('click', '#urfl-save-settings', this.handleSave);
			$(document).on('click', '.urfl-copy-shortcode', this.handleCopyShortcode);

			$(document).on(
				'change',
				'#user_registration_frontend_listings_ur_only',
				this.handleShowBasedOnChange,
			);

			$(document).on('change', '.urfl-card-fields-select', function () {
				self.handleCardFieldsSelectChange($(this));
			});

			$(document).on(
				'change',
				'#user_registration_frontend_listings_view_profile',
				function () {
					self.toggleProfilePageSettings();
					self.toggleProfileButtonText();
				},
			);

			$(document).on(
				'change',
				'#user_registration_frontend_listings_ur_only, #user_registration_frontend_listings_ur_forms, #user_registration_member_directory_ur_membership_type',
				function () {
					self.refreshCardFieldOptions();
				},
			);

			$(document).on(
				'click',
				'#member-directory-name-edit-button',
				function (e) {
					e.preventDefault();
					self.enableEditing();
				},
			);

			$(document).on('blur', '#member-directory-name', function () {
				if ($(this).data('editing') === 'true') {
					self.saveTitle();
				}
			});

			$(document).on('keypress', '#member-directory-name', function (e) {
				if (e.which === 13) {
					e.preventDefault();
					$(this).blur();
				}
			});

			$(document).on('keydown', '#member-directory-name', function (e) {
				if (e.which === 27) {
					e.preventDefault();
					self.cancelEditing();
				}
			});

			if (
				$('#user_registration_frontend_listings_allow_guest').is(':checked')
			) {
				$('#user_registration_frontend_listings_access_denied_text')
					.closest('.urfl-field')
					.hide();
			} else {
				$('#user_registration_frontend_listings_access_denied_text')
					.closest('.urfl-field')
					.show();
			}

			$(document).on(
				'change',
				'#user_registration_frontend_listings_allow_guest',
				function (event) {
					event.preventDefault();

					if ($(this).is(':checked')) {
						$('#user_registration_frontend_listings_access_denied_text')
							.closest('.urfl-field')
							.hide();
					} else {
						$('#user_registration_frontend_listings_access_denied_text')
							.closest('.urfl-field')
							.show();
					}
				},
			);

			if ($('#user_registration_frontend_listings_sort_by').is(':checked')) {
				$('#user_registration_frontend_listings_default_sorter')
					.closest('.urfl-field')
					.show();
			} else {
				$('#user_registration_frontend_listings_default_sorter')
					.closest('.urfl-field')
					.hide();
			}

			$(document).on(
				'change',
				'#user_registration_frontend_listings_sort_by',
				function (event) {
					event.preventDefault();

					if ($(this).is(':checked')) {
						$('#user_registration_frontend_listings_default_sorter')
							.closest('.urfl-field')
							.show();
					} else {
						$('#user_registration_frontend_listings_default_sorter')
							.closest('.urfl-field')
							.hide();
					}
				},
			);

			if (
				$('#user_registration_frontend_listings_search_form').is(':checked')
			) {
				$('#user_registration_frontend_listings_search_fields')
					.closest('.urfl-field')
					.show();
			} else {
				$('#user_registration_frontend_listings_search_fields')
					.closest('.urfl-field')
					.hide();
			}

			$(document).on(
				'change',
				'#user_registration_frontend_listings_search_form',
				function (event) {
					event.preventDefault();

					if ($(this).is(':checked')) {
						$('#user_registration_frontend_listings_search_fields')
							.closest('.urfl-field')
							.show();
					} else {
						$('#user_registration_frontend_listings_search_fields')
							.closest('.urfl-field')
							.hide();
					}
				},
			);

			if (
				$('#user_registration_frontend_listings_amount_filter').is(':checked')
			) {
				$(
					'#user_registration_frontend_listings_default_page_filter,#user_registration_frontend_listings_filtered_user_message',
				)
					.closest('.urfl-field')
					.show();
			} else {
				$(
					'#user_registration_frontend_listings_default_page_filter,#user_registration_frontend_listings_filtered_user_message',
				)
					.closest('.urfl-field')
					.hide();
			}

			$(document).on(
				'change',
				'#user_registration_frontend_listings_amount_filter',
				function (event) {
					event.preventDefault();
					if ($(this).is(':checked')) {
						$(
							'#user_registration_frontend_listings_default_page_filter,#user_registration_frontend_listings_filtered_user_message',
						)
							.closest('.urfl-field')
							.show();
					} else {
						$(
							'#user_registration_frontend_listings_default_page_filter,#user_registration_frontend_listings_filtered_user_message',
						)
							.closest('.urfl-field')
							.hide();
					}
				},
			);

			if (
				$('#user_registration_frontend_listings_advanced_filter').is(':checked')
			) {
				$('#user_registration_frontend_listings_advanced_filter_fields')
					.closest('.urfl-field')
					.show();
			} else {
				$('#user_registration_frontend_listings_advanced_filter_fields')
					.closest('.urfl-field')
					.hide();
			}

			$(document).on(
				'change',
				'#user_registration_frontend_listings_advanced_filter',
				function (event) {
					event.preventDefault();

					if ($(this).is(':checked')) {
						$('#user_registration_frontend_listings_advanced_filter_fields')
							.closest('.urfl-field')
							.show();
					} else {
						$('#user_registration_frontend_listings_advanced_filter_fields')
							.closest('.urfl-field')
							.hide();
					}
				},
			);

			$(document).on(
				'input change',
				'.urfl-custom-label, .urfl-custom-key',
				function () {
					var $wrap = $(this).closest('.urfl-field-advanced-filter');
					if ($wrap.length) {
						URFL_Admin.syncAdvancedFilterHidden($wrap);
					}
				},
			);
		},

		enableEditing: function () {
			var $input = $('#member-directory-name');
			var $icon = $('#member-directory-name-edit-button');

			if (!$input.length || !$icon.length) return;

			$input.data('original-value', $input.val());
			$input
				.prop('readonly', false)
				.data('editing', 'true')
				.addClass('is-editing')
				.focus()
				.select();

			$icon.removeClass('dashicons-edit').attr('title', 'Save title');
		},

		cancelEditing: function () {
			var $input = $('#member-directory-name');
			var $icon = $('#member-directory-name-edit-button');

			if (!$input.length || !$icon.length) return;

			var originalValue = $input.data('original-value');
			if (originalValue !== undefined) $input.val(originalValue);

			$input
				.prop('readonly', true)
				.data('editing', 'false')
				.removeClass('is-editing');

			$icon.addClass('dashicons-edit').attr('title', 'Edit title');
		},

		saveTitle: function () {
			var self = this;
			var $input = $('#member-directory-name');
			var $icon = $('#member-directory-name-edit-button');

			if (!$input.length || !$icon.length) return;

			var newTitle = $input.val().trim();
			if (!newTitle) {
				this.cancelEditing();
				return;
			}

			var postId =
				window.urfl_current_post_id ||
				$('#post_ID').val() ||
				$('input[name="post_id"]').val() ||
				new URLSearchParams(window.location.search).get('post');

			if (!postId || typeof urfl_settings === 'undefined') {
				this.cancelEditing();
				return;
			}

			$.ajax({
				url: urfl_settings.ajax_url,
				type: 'POST',
				data: {
					action: 'urfl_save_title',
					nonce: urfl_settings.nonce,
					post_id: postId,
					post_title: newTitle,
				},
				success: function (response) {
					if (response && response.success) {
						setTimeout(function () {
							$('#title').val(newTitle);
							$icon.addClass('dashicons-edit');
							$('#member-directory-name').removeClass('is-editing');
							$('#member-directory-name').css('background', 'none');
						}, 1000);
					} else {
						self.cancelEditing();
					}
				},
				error: function () {
					self.cancelEditing();
				},
			});
		},

		toggleProfileButtonText: function () {
			var $toggle = $('#user_registration_frontend_listings_view_profile');
			if (!$toggle.length) return;

			var isEnabled = $toggle.is(':checked');
			var $field = $(
				'#user_registration_frontend_listings_view_profile_button_text',
			).closest('.urfl-field');

			if (!$field.length) return;

			isEnabled ? $field.show() : $field.hide();
		},

		refreshCardFieldOptions: function () {
			var self = this;
			var $wrap = $('.urfl-field-card-fields');
			if (!$wrap.length || typeof urfl_settings === 'undefined') return;

			var postId = $('#post_ID').val() || this.getUrlParam('post');
			var $multi = $wrap.find('.urfl-card-fields-select');
			var $orderBox = $wrap.find('.urfl-card-fields-order');

			var mode = $('#user_registration_frontend_listings_ur_only').val() || '0';

			var forms = [];
			var $formsSelect = $('#user_registration_frontend_listings_ur_forms');
			if ($formsSelect.length) forms = $formsSelect.val() || [];

			var memberships = [];
			var $membershipsSelect = $(
				'#user_registration_member_directory_ur_membership_type',
			);
			if ($membershipsSelect.length)
				memberships = $membershipsSelect.val() || [];

			$wrap.addClass('urfl-loading');

			$.ajax({
				url: urfl_settings.ajax_url,
				type: 'POST',
				data: {
					action: 'urfl_get_card_field_options',
					nonce: urfl_settings.nonce,
					post_id: postId,
					mode: mode,
					forms: forms,
					memberships: memberships,
				},
				success: function (res) {
					if (!res || !res.success || !res.data || !res.data.options) {
						$wrap.removeClass('urfl-loading');
						return;
					}

					var options = res.data.options;
					var groups = res.data.groups || [];

					if ($multi.hasClass('select2-hidden-accessible')) {
						$multi.selectWoo('destroy');
					}

					$multi.empty();

					if (groups.length) {
						$.each(groups, function (i, group) {
							if (!group || !group.label || !group.options) return;

							var $optgroup = $('<optgroup/>', {
								label: group.label,
							});

							$.each(group.options, function (key, label) {
								$optgroup.append(
									$('<option/>', {
										value: key,
										text: label,
									}),
								);
							});

							$multi.append($optgroup);
						});
					} else {
						$.each(options, function (key, label) {
							$multi.append(
								$('<option/>', {
									value: key,
									text: label,
								}),
							);
						});
					}

					$multi.data('options', options);
					$orderBox.data('options', options);

					$multi.selectWoo({
						width: '100%',
						placeholder: $multi.data('placeholder') || 'Select fields...',
						allowClear: true,
					});

					var orderRaw =
						$wrap.find('.urfl-card-fields-order-value').val() || '[]';
					var order = [];

					try {
						order = JSON.parse(orderRaw) || [];
					} catch (e) {
						order = [];
					}

					order = $.grep(order, function (v) {
						return Object.prototype.hasOwnProperty.call(options, String(v));
					});

					$multi.val(order).trigger('change.select2');

					self.rebuildOrderRows($wrap, order, options);
					self.syncCardOrderHidden($wrap);

					$wrap.removeClass('urfl-loading');
				},

				error: function () {
					$wrap.removeClass('urfl-loading');
				},
			});
		},

		initSelect2New: function () {
			$('.urfl-multiselect').each(function () {
				$(this).selectWoo({
					width: '100%',
					placeholder: $(this).data('placeholder') || 'Select options...',
					allowClear: true,
				});
			});

			$('.urfl-select').each(function () {
				$(this).selectWoo({
					width: '100%',
					minimumResultsForSearch: 10,
				});
			});
		},

		initSortableNew: function () {
			var self = this;

			if (!$.fn.sortable) return;

			$('.urfl-advanced-filter-order').sortable({
				handle: '.urfl-drag-handle',
				placeholder: 'urfl-sortable-placeholder',
				tolerance: 'pointer',
				update: function () {
					var $wrap = $(this).closest('.urfl-field-advanced-filter');
					self.syncAdvancedFilterHidden($wrap);
					self.syncAdvancedFilterSelectFromOrder($wrap);
				},
			});

			$('.urfl-card-fields-order').sortable({
				handle: '.urfl-drag-handle',
				placeholder: 'urfl-sortable-placeholder',
				tolerance: 'pointer',
				update: function () {
					var $wrap = $(this).closest('.urfl-field-card-fields');
					self.syncCardOrderHidden($wrap);
					self.syncMultiSelectFromOrder($wrap);
				},
			});

			$('.urfl-ordered-fields-order').sortable({
				handle: '.urfl-drag-handle',
				placeholder: 'urfl-sortable-placeholder',
				tolerance: 'pointer',
				update: function () {
					var $wrap = $(this).closest(
						'.urfl-field-ordered-grouped-multiselect',
					);
					self.syncOrderedGroupedHidden($wrap);
					self.syncOrderedGroupedSelectFromOrder($wrap);
				},
			});
		},

		handleAdvancedFilterSelectChange: function ($select) {
			var self = this;
			var $wrap = $select.closest('.urfl-field-advanced-filter');
			var $orderBox = $wrap.find('.urfl-advanced-filter-order');
			var options = $orderBox.data('options') || {};

			var rawSelected = $select.val() || [];
			var otherIsSelected = $.inArray('other', rawSelected) !== -1;

			var selected = $.grep(rawSelected, function (v) {
				return String(v) !== 'other';
			});
			selected = self.urflUnique(selected);

			var currentOrder = [];
			$orderBox.find('.urfl-advanced-filter-item[data-type="option"]').each(function () {
				currentOrder.push(String($(this).data('key')));
			});

			$.each(currentOrder, function (_, key) {
				if ($.inArray(String(key), selected) === -1) {
					$orderBox
						.find('.urfl-advanced-filter-item[data-key="' + key + '"]')
						.remove();
				}
			});

			$.each(selected, function (_, key) {
				key = String(key);
				if (!options[key]) return;
				if (
					$orderBox.find(
						'.urfl-advanced-filter-item[data-type="option"][data-key="' +
							key +
							'"]',
					).length
				)
					return;
				$orderBox.append(self.createAdvancedFilterItemHtml(key, options[key]));
			});

			if (otherIsSelected) {
				$orderBox.append(self.createAdvancedFilterCustomItemHtml());

				var valsWithoutOther = $.grep($select.val() || [], function (v) {
					return String(v) !== 'other';
				});
				$select.val(valsWithoutOther).trigger('change.select2');
			}

			self.syncAdvancedFilterHidden($wrap);
			self.toggleAdvancedFilterEmptyMessage($wrap);
		},

		createAdvancedFilterItemHtml: function (key, label) {
			return (
				'<div class="urfl-advanced-filter-item" data-type="option" data-key="' + key + '">' +
				'<span class="urfl-drag-handle">' +
				'<svg width="12" height="20" viewBox="0 0 12 20" fill="none">' +
				'<rect width="4" height="4" fill="#9ca3af"></rect>' +
				'<rect x="8" width="4" height="4" fill="#9ca3af"></rect>' +
				'<rect y="8" width="4" height="4" fill="#9ca3af"></rect>' +
				'<rect x="8" y="8" width="4" height="4" fill="#9ca3af"></rect>' +
				'<rect y="16" width="4" height="4" fill="#9ca3af"></rect>' +
				'<rect x="8" y="16" width="4" height="4" fill="#9ca3af"></rect>' +
				'</svg>' +
				'</span>' +
				'<span class="urfl-filter-label">' + label + '</span>' +
				'<button type="button" class="urfl-remove-filter" aria-label="Remove">' +
				'<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
				'<line x1="18" y1="6" x2="6" y2="18"></line>' +
				'<line x1="6" y1="6" x2="18" y2="18"></line>' +
				'</svg>' +
				'</button>' +
				'</div>'
			);
		},

		createAdvancedFilterCustomItemHtml: function (savedLabel, savedKey) {
			savedLabel = savedLabel || '';
			savedKey   = savedKey   || '';
			return (
				'<div class="urfl-advanced-filter-item" data-type="custom" data-key="">' +
				'<span class="urfl-drag-handle">' +
				'<svg width="12" height="20" viewBox="0 0 12 20" fill="none">' +
				'<rect width="4" height="4" fill="#9ca3af"></rect>' +
				'<rect x="8" width="4" height="4" fill="#9ca3af"></rect>' +
				'<rect y="8" width="4" height="4" fill="#9ca3af"></rect>' +
				'<rect x="8" y="8" width="4" height="4" fill="#9ca3af"></rect>' +
				'<rect y="16" width="4" height="4" fill="#9ca3af"></rect>' +
				'<rect x="8" y="16" width="4" height="4" fill="#9ca3af"></rect>' +
				'</svg>' +
				'</span>' +
				'<div class="urfl-custom-field">' +
				'<input type="text" class="urfl-custom-label" placeholder="Field Label" value="' + savedLabel + '" />' +
				'<input type="text" class="urfl-custom-key"   placeholder="Meta Key ( eg. meta_key_123, meta_key_456 )" value="' + savedKey + '" />' +
				'</div>' +
				'<button type="button" class="urfl-remove-filter" aria-label="Remove">' +
				'<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
				'<line x1="18" y1="6" x2="6" y2="18"></line>' +
				'<line x1="6" y1="6" x2="18" y2="18"></line>' +
				'</svg>' +
				'</button>' +
				'</div>'
			);
		},

		syncAdvancedFilterHidden: function ($wrap) {
			var value = {};
			var $orderBox = $wrap.find('.urfl-advanced-filter-order');
			var options = $orderBox.data('options') || {};

			$orderBox.find('.urfl-advanced-filter-item').each(function () {
				var $item = $(this);
				var type = $item.attr('data-type') || 'option';

				if (type === 'custom') {
					var metaKey   = ($item.find('.urfl-custom-key').val()   || '').trim();
					var metaLabel = ($item.find('.urfl-custom-label').val() || '').trim();
					if (!metaKey || metaKey === 'other') return;
					value[metaKey] = metaLabel || metaKey;
					return;
				}

				var key = String($item.data('key'));
				if (key && options[key]) {
					value[key] = options[key];
				}
			});

			$wrap.find('.urfl-advanced-filter-value').val(JSON.stringify(value));
		},

		syncAdvancedFilterSelectFromOrder: function ($wrap) {
			var order = [];
			$wrap.find('.urfl-advanced-filter-item[data-type="option"]').each(function () {
				var key = $(this).data('key');
				if (key) order.push(String(key));
			});

			var $select = $wrap.find('.urfl-advanced-filter-select');
			$select.val(order);

			if ($select.hasClass('select2-hidden-accessible')) {
				$select.trigger('change.select2');
			}
		},

		toggleAdvancedFilterEmptyMessage: function ($wrap) {
			var $msg = $wrap.find('.urfl-order-empty-message');
			if (!$msg.length) return;
			var count = $wrap.find('.urfl-advanced-filter-item').length;
			count > 0 ? $msg.hide() : $msg.show();
		},

		handleTabClick: function (e) {
			e.preventDefault();

			var $btn = $(this);
			var tabId = $btn.data('tab');

			var $allTabs = $('.urfl-tab-link');

			$allTabs
				.removeClass('active ur-page-title__wrapper--steps-btn-active')
				.attr('aria-selected', 'false');

			$btn
				.addClass('active ur-page-title__wrapper--steps-btn-active')
				.attr('aria-selected', 'true');

			$('.urfl-tab-content').removeClass('active').hide();

			$('#' + tabId)
				.addClass('active')
				.show();
		},

		handleSectionToggle: function () {
			$(this).closest('.urfl-section').toggleClass('collapsed');
		},

		handleLayoutChange: function () {
			var $option = $(this).closest('.urfl-layout-option');
			$('.urfl-layout-option').removeClass('active');
			$option.addClass('active');
			URFL_Admin.toggleGridOnlyFields();
		},

		toggleGridOnlyFields: function () {
			var layout =
				$('.urfl-layout-option input[type="radio"]:checked').val() || '';
			var isGrid = layout === '0';

			isGrid
				? $('.urfl-grid-only').slideDown(200)
				: $('.urfl-grid-only').slideUp(200);
		},

		handleShowBasedOnChange: function () {
			var $mode = $('#user_registration_frontend_listings_ur_only');
			if (!$mode.length) return;

			var value = $mode.val();

			var $formsField = $(
				'#user_registration_frontend_listings_ur_forms',
			).closest('.urfl-field');
			var $membershipField = $(
				'#user_registration_member_directory_ur_membership_type',
			).closest('.urfl-field');

			if (!$formsField.length && !$membershipField.length) return;

			$formsField.hide();
			$membershipField.hide();

			if (value === '0') $membershipField.slideDown(200);
			else if (value === '1') $formsField.slideDown(200);
		},

		toggleProfilePageSettings: function () {
			var $toggle = $('#user_registration_frontend_listings_view_profile');
			if (!$toggle.length) return;

			var isEnabled = $toggle.is(':checked');

			var $profileTab = $('.urfl-tab-link[data-tab="profile-page-settings"]');
			var $profileConnector = $profileTab.prev('.urfl-tab-connector');
			var $profileContent = $('#profile-page-settings');

			if (!$profileTab.length || !$profileContent.length) return;

			if (isEnabled) {
				$profileTab.show();
				$profileConnector.show();
				$profileTab.prev('.ur-page-title__wrapper--steps-separator').show();
				$profileContent.removeClass('urfl-profile-hidden');
			} else {
				if ($profileTab.hasClass('active')) {
					$('.urfl-tab-link[data-tab="general-settings"]').trigger('click');
				}

				$profileTab.hide();
				$profileConnector.hide();
				$profileTab.prev('.ur-page-title__wrapper--steps-separator').hide();
				$profileContent.removeClass('active').addClass('urfl-profile-hidden');
			}
		},

		handleSave: function (e) {
			e.preventDefault();

			if (typeof urfl_settings === 'undefined') return;

			var $btn = $(this);

			$btn.append('<span class="ur-spinner"></span>');

			$('.urfl-save-status').removeClass('success error').text('');

			var settings = URFL_Admin.collectSettings();
			var postId =
				window.urfl_current_post_id ||
				$('#post_ID').val() ||
				$('input[name="post_id"]').val() ||
				new URLSearchParams(window.location.search).get('post');

			$.ajax({
				url: urfl_settings.ajax_url,
				type: 'POST',
				data: {
					action: 'urfl_save_settings',
					nonce: urfl_settings.nonce,
					post_id: postId,
					post_title: $('#title').val() || '',
					settings: settings,
				},
				success: function (response) {
					if (response && response.success) {
						urflToast(
							'success',
							(response.data && response.data.message) ||
								'Settings saved successfully.',
						);

						if (response.data && response.data.post_id) {
							$('#post_ID').val(response.data.post_id);
							$('input[name="post_id"]').val(response.data.post_id);
							window.urfl_current_post_id = response.data.post_id;
						}

						if (
							response.data &&
							response.data.redirect &&
							window.location.href.indexOf('post.php') === -1
						) {
							window.history.replaceState(
								{ post_id: response.data.post_id },
								'',
								response.data.redirect,
							);
						}
					} else {
						urflToast(
							'error',
							(response && response.data && response.data.message) ||
								urfl_settings.i18n.save_error,
						);
						$('.urfl-save-status').text(
							(response.data && response.data.message) ||
								urfl_settings.i18n.save_error,
						);
					}
				},
				error: function () {
					urflToast(
						'error',
						urfl_settings.i18n.save_error || 'Network error.',
					);
				},
				complete: function () {
					$btn.find('.ur-spinner').remove();
				},
			});
		},

		collectSettings: function () {
			var settings = {};

			$('.urfl-settings-wrapper input[type="text"]').each(function () {
				var name = $(this).attr('name');
				if (name) settings[name] = $(this).val();
			});

			$('.urfl-settings-wrapper input[type="hidden"]').each(function () {
				var name = $(this).attr('name');
				if (name && name !== 'urfl_nonce') settings[name] = $(this).val();
			});

			$('.urfl-settings-wrapper input[type="checkbox"]').each(function () {
				var name = $(this).attr('name');
				if (name) settings[name] = $(this).is(':checked') ? '1' : '0';
			});

			$('.urfl-settings-wrapper input[type="radio"]:checked').each(function () {
				var name = $(this).attr('name');
				if (name) settings[name] = $(this).val();
			});

			$('.urfl-settings-wrapper select').each(function () {
				var name = $(this).attr('name');
				if (name && !$(this).hasClass('urfl-advanced-filter-selector')) {
					if (name.indexOf('[]') > -1) {
						var cleanName = name.replace('[]', '');
						settings[cleanName] = $(this).val() || [];
					} else {
						settings[name] = $(this).val();
					}
				}
			});

			return settings;
		},

		handleCopyShortcode: function (e) {
			e.preventDefault();

			var $btn = $(this);
			var shortcode =
				$btn.data('shortcode') || $btn.siblings('.urfl-shortcode-code').text();

			var $temp = $('<textarea>');
			$('body').append($temp);
			$temp.val(shortcode).select();
			document.execCommand('copy');
			$temp.remove();

			$btn.addClass('copied');

			setTimeout(function () {
				$btn.removeClass('copied');
			}, 2000);
		},

		handleAddFilter: function () {
			var $select = $(this);
			var value = $select.val();
			var $list = $select.siblings('.urfl-advanced-filter-list');
			var options = $list.data('options') || {};

			if (!value || !$list.length) return;

			function resetSelect() {
				$select.val('').trigger('change.select2');
				$select.val('').trigger('change');
			}

			if (value === 'other') {
				var itemHtml = `
	<div class="urfl-advanced-filter-item" data-type="custom" data-key="">
		<span class="urfl-drag-handle">
			<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
				<path d="M7 12c0-1.227.985-2.222 2.2-2.222 1.215 0 2.2.995 2.2 2.222a2.211 2.211 0 0 1-2.2 2.222C7.985 14.222 7 13.227 7 12Zm0-7.778C7 2.995 7.985 2 9.2 2c1.215 0 2.2.995 2.2 2.222a2.211 2.211 0 0 1-2.2 2.222A2.21 2.21 0 0 1 7 4.222Zm0 15.556c0-1.227.985-2.222 2.2-2.222 1.215 0 2.2.994 2.2 2.222A2.211 2.211 0 0 1 9.2 22C7.985 22 7 21.005 7 19.778ZM13.6 12c0-1.227.985-2.222 2.2-2.222 1.215 0 2.2.995 2.2 2.222a2.211 2.211 0 0 1-2.2 2.222c-1.215 0-2.2-.995-2.2-2.222Zm0-7.778C13.6 2.995 14.585 2 15.8 2c1.215 0 2.2.995 2.2 2.222a2.211 2.211 0 0 1-2.2 2.222 2.21 2.21 0 0 1-2.2-2.222Zm0 15.556c0-1.227.985-2.222 2.2-2.222 1.215 0 2.2.994 2.2 2.222A2.211 2.211 0 0 1 15.8 22c-1.215 0-2.2-.995-2.2-2.222Z"></path>
			</svg>
		</span>
		<div class="urfl-custom-field">
			<input type="text" class="urfl-custom-label" placeholder="Field Label" />
			<input type="text" class="urfl-custom-key" placeholder="Meta Key ( eg. meta_key_123, meta_key_456 )" />
		</div>
		<button type="button" class="urfl-remove-filter">×</button>
	</div>
`;
				$list.append(itemHtml);
				resetSelect();
				URFL_Admin.updateAdvancedFilterValue($list);
				return;
			}

			if (
				$list.find(
					'.urfl-advanced-filter-item[data-type="option"][data-key="' +
						value +
						'"]',
				).length
			) {
				resetSelect();
				return;
			}

			var label = options[value] || value;

			var itemHtml = `
	<div class="urfl-advanced-filter-item" data-type="option" data-key="${value}">
		<span class="urfl-drag-handle">
			<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
				<path d="M7 12c0-1.227.985-2.222 2.2-2.222 1.215 0 2.2.995 2.2 2.222a2.211 2.211 0 0 1-2.2 2.222C7.985 14.222 7 13.227 7 12Zm0-7.778C7 2.995 7.985 2 9.2 2c1.215 0 2.2.995 2.2 2.222a2.211 2.211 0 0 1-2.2 2.222A2.21 2.21 0 0 1 7 4.222Zm0 15.556c0-1.227.985-2.222 2.2-2.222 1.215 0 2.2.994 2.2 2.222A2.211 2.211 0 0 1 9.2 22C7.985 22 7 21.005 7 19.778ZM13.6 12c0-1.227.985-2.222 2.2-2.222 1.215 0 2.2.995 2.2 2.222a2.211 2.211 0 0 1-2.2 2.222c-1.215 0-2.2-.995-2.2-2.222Zm0-7.778C13.6 2.995 14.585 2 15.8 2c1.215 0 2.2.995 2.2 2.222a2.211 2.211 0 0 1-2.2 2.222 2.21 2.21 0 0 1-2.2-2.222Zm0 15.556c0-1.227.985-2.222 2.2-2.222 1.215 0 2.2.994 2.2 2.222A2.211 2.211 0 0 1 15.8 22c-1.215 0-2.2-.995-2.2-2.222Z"></path>
			</svg>
		</span>
		<span class="urfl-filter-label">${label}</span>
		<button type="button" class="urfl-remove-filter">×</button>
	</div>
`;

			$list.append(itemHtml);
			resetSelect();
			URFL_Admin.updateAdvancedFilterValue($list);
		},

		handleRemoveFilter: function (e) {
			e.preventDefault();

			var $item = $(this).closest('.urfl-advanced-filter-item');
			var $list = $item.closest('.urfl-advanced-filter-list');

			$item.fadeOut(200, function () {
				$(this).remove();
				URFL_Admin.updateAdvancedFilterValue($list);
			});
		},

		updateAdvancedFilterValue: function ($list) {
			var value = {};
			var options = $list.data('options') || {};

			$list.find('.urfl-advanced-filter-item').each(function () {
				var $item = $(this);
				var type = $item.attr('data-type') || 'option';

				if (type === 'custom') {
					var key = ($item.find('.urfl-custom-key').val() || '').trim();
					var label = ($item.find('.urfl-custom-label').val() || '').trim();

					if (!key || key === 'other') return;

					value[key] = label || key;
					return;
				}

				var key = String($item.data('key') || '').trim();

				if (!key || key === 'other') return;

				var label =
					options[key] || $item.find('.urfl-filter-label').text() || key;
				value[key] = label;
			});

			$list.siblings('.urfl-advanced-filter-value').val(JSON.stringify(value));
		},

		initCardFields: function () {
			var self = this;

			$('.urfl-field-card-fields').each(function () {
				var $wrap = $(this);
				var $orderBox = $wrap.find('.urfl-card-fields-order');
				var options = $orderBox.data('options') || {};

				var orderRaw =
					$wrap.find('.urfl-card-fields-order-value').val() || '[]';
				var order = [];

				try {
					order = JSON.parse(orderRaw) || [];
				} catch (e) {
					order = [];
				}

				order = $.grep(order, function (v) {
					return Object.prototype.hasOwnProperty.call(options, String(v));
				});

				var $multi = $wrap.find('.urfl-card-fields-select');
				$multi.val(order);

				if ($multi.hasClass('select2-hidden-accessible')) {
					$multi.trigger('change.select2');
				}

				self.toggleEmptyMessage($wrap, order.length);
			});
		},

		handleCardFieldsSelectChange: function ($multi) {
			var $wrap = $multi.closest('.urfl-field-card-fields');
			var $orderBox = $wrap.find('.urfl-card-fields-order');
			var options = $orderBox.data('options') || {};

			var selected = $multi.val() || [];
			selected = this.urflUnique(selected);

			var currentOrder = [];
			$orderBox.find('.urfl-card-field-row').each(function () {
				currentOrder.push(String($(this).data('key')));
			});

			var toAdd = $.grep(selected, function (v) {
				return $.inArray(String(v), currentOrder) === -1;
			});

			var toRemove = $.grep(currentOrder, function (v) {
				return $.inArray(String(v), selected) === -1;
			});

			$.each(toRemove, function (_, key) {
				$orderBox.find('.urfl-card-field-row[data-key="' + key + '"]').remove();
			});

			$.each(toAdd, function (_, key) {
				if (!options[key]) return;
				$orderBox.append(URFL_Admin.createOrderRowHtml(key, options[key]));
			});

			this.syncCardOrderHidden($wrap);
			this.toggleEmptyMessage($wrap, selected.length);
		},

		createOrderRowHtml: function (key, label) {
			return `
	<div class="urfl-card-field-row" data-key="${key}">
		<span class="urfl-drag-handle">
			<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
				<path d="M7 12c0-1.227.985-2.222 2.2-2.222 1.215 0 2.2.995 2.2 2.222a2.211 2.211 0 0 1-2.2 2.222C7.985 14.222 7 13.227 7 12Zm0-7.778C7 2.995 7.985 2 9.2 2c1.215 0 2.2.995 2.2 2.222a2.211 2.211 0 0 1-2.2 2.222A2.21 2.21 0 0 1 7 4.222Zm0 15.556c0-1.227.985-2.222 2.2-2.222 1.215 0 2.2.994 2.2 2.222A2.211 2.211 0 0 1 9.2 22C7.985 22 7 21.005 7 19.778ZM13.6 12c0-1.227.985-2.222 2.2-2.222 1.215 0 2.2.995 2.2 2.222a2.211 2.211 0 0 1-2.2 2.222c-1.215 0-2.2-.995-2.2-2.222Zm0-7.778C13.6 2.995 14.585 2 15.8 2c1.215 0 2.2.995 2.2 2.222a2.211 2.211 0 0 1-2.2 2.222 2.21 2.21 0 0 1-2.2-2.222Zm0 15.556c0-1.227.985-2.222 2.2-2.222 1.215 0 2.2.994 2.2 2.222A2.211 2.211 0 0 1 15.8 22c-1.215 0-2.2-.995-2.2-2.222Z"></path>
			</svg>
		</span>
		<span class="urfl-card-field-label">${label}</span>
	</div>
`;
		},

		rebuildOrderRows: function ($wrap, order, options) {
			var $orderBox = $wrap.find('.urfl-card-fields-order');
			$orderBox.empty();

			$.each(order, function (_, key) {
				if (!options[key]) return;
				$orderBox.append(URFL_Admin.createOrderRowHtml(key, options[key]));
			});

			this.toggleEmptyMessage($wrap, order.length);

			if ($.fn.sortable && $orderBox.data('ui-sortable')) {
				$orderBox.sortable('refresh');
			}
		},

		toggleEmptyMessage: function ($wrap, count) {
			var $emptyMsg = $wrap.find('.urfl-order-empty-message');
			if (!$emptyMsg.length) return;
			count > 0 ? $emptyMsg.hide() : $emptyMsg.show();
		},

		syncCardOrderHidden: function ($wrap) {
			var order = [];
			$wrap.find('.urfl-card-field-row').each(function () {
				var key = $(this).data('key');
				if (key) order.push(String(key));
			});
			order = this.urflUnique(order);
			$wrap.find('.urfl-card-fields-order-value').val(JSON.stringify(order));
		},

		syncMultiSelectFromOrder: function ($wrap) {
			var order = [];
			$wrap.find('.urfl-card-field-row').each(function () {
				var key = $(this).data('key');
				if (key) order.push(String(key));
			});
			order = this.urflUnique(order);

			var $multi = $wrap.find('.urfl-card-fields-select');
			$multi.val(order);

			if ($multi.hasClass('select2-hidden-accessible')) {
				$multi.trigger('change.select2');
			}
		},

		initOrderedGroupedFields: function () {
			var self = this;

			$('.urfl-field-ordered-grouped-multiselect').each(function () {
				var $wrap = $(this);
				var $select = $wrap.find('.urfl-ordered-fields-select');
				var $orderBox = $wrap.find('.urfl-ordered-fields-order');
				var options = $orderBox.data('options') || {};

				var orderRaw =
					$wrap.find('.urfl-ordered-fields-order-value').val() || '[]';
				var order = [];

				try {
					order = JSON.parse(orderRaw) || [];
				} catch (e) {
					order = [];
				}

				var selected = $select.val() || [];
				selected = self.urflUnique(selected);

				if (!order.length && selected.length) {
					order = selected.slice();
					$wrap
						.find('.urfl-ordered-fields-order-value')
						.val(JSON.stringify(order));
				}

				order = $.grep(order, function (v) {
					v = String(v);
					return (
						Object.prototype.hasOwnProperty.call(options, v) &&
						$.inArray(v, selected) !== -1
					);
				});

				$.each(selected, function (_, v) {
					v = String(v);
					if (
						$.inArray(v, order) === -1 &&
						Object.prototype.hasOwnProperty.call(options, v)
					) {
						order.push(v);
					}
				});

				self.rebuildOrderedGroupedRows($wrap, order, options);
				self.syncOrderedGroupedHidden($wrap);
				self.syncOrderedGroupedSelectFromOrder($wrap);

				$select.on('change', function () {
					self.handleOrderedGroupedSelectChange($(this));
				});

				$wrap.on('click', '.urfl-remove-ordered-field', function (e) {
					e.preventDefault();
					var key = String(
						$(this).closest('.urfl-ordered-field-row').data('key'),
					);
					$(this).closest('.urfl-ordered-field-row').remove();

					var current = $select.val() || [];
					current = $.grep(current, function (v) {
						return String(v) !== key;
					});
					$select.val(current).trigger('change.select2');

					self.syncOrderedGroupedHidden($wrap);
					self.toggleOrderedGroupedEmptyMessage($wrap);
				});
			});
		},

		handleOrderedGroupedSelectChange: function ($select) {
			var self = this;
			var $wrap = $select.closest('.urfl-field-ordered-grouped-multiselect');
			var $orderBox = $wrap.find('.urfl-ordered-fields-order');
			var options = $orderBox.data('options') || {};

			var selected = $select.val() || [];
			selected = self.urflUnique(selected);

			var currentOrder = [];
			$orderBox.find('.urfl-ordered-field-row').each(function () {
				currentOrder.push(String($(this).data('key')));
			});

			$.each(currentOrder, function (_, key) {
				if ($.inArray(String(key), selected) === -1) {
					$orderBox
						.find('.urfl-ordered-field-row[data-key="' + key + '"]')
						.remove();
				}
			});

			$.each(selected, function (_, key) {
				key = String(key);
				if (!options[key]) return;

				if (
					$orderBox.find('.urfl-ordered-field-row[data-key="' + key + '"]')
						.length
				)
					return;

				$orderBox.append(self.createOrderedGroupedRowHtml(key, options[key]));
			});

			self.syncOrderedGroupedHidden($wrap);
			self.toggleOrderedGroupedEmptyMessage($wrap);
		},

		createOrderedGroupedRowHtml: function (key, label) {
			return (
				'<div class="urfl-ordered-field-row" data-key="' +
				key +
				'">' +
				'<span class="urfl-drag-handle">' +
				'<svg width="12" height="20" viewBox="0 0 12 20" fill="none" xmlns="http://www.w3.org/2000/svg">' +
				'<rect width="4" height="4" fill="#9ca3af"></rect>' +
				'<rect x="8" width="4" height="4" fill="#9ca3af"></rect>' +
				'<rect y="8" width="4" height="4" fill="#9ca3af"></rect>' +
				'<rect x="8" y="8" width="4" height="4" fill="#9ca3af"></rect>' +
				'<rect y="16" width="4" height="4" fill="#9ca3af"></rect>' +
				'<rect x="8" y="16" width="4" height="4" fill="#9ca3af"></rect>' +
				'</svg>' +
				'</span>' +
				'<span class="urfl-ordered-field-label">' +
				label +
				'</span>' +
				'<button type="button" class="urfl-remove-ordered-field">×</button>' +
				'</div>'
			);
		},

		rebuildOrderedGroupedRows: function ($wrap, order, options) {
			var $orderBox = $wrap.find('.urfl-ordered-fields-order');
			$orderBox.empty();

			$.each(order || [], function (_, key) {
				key = String(key);
				if (!options[key]) return;
				$orderBox.append(
					URFL_Admin.createOrderedGroupedRowHtml(key, options[key]),
				);
			});

			URFL_Admin.toggleOrderedGroupedEmptyMessage($wrap);

			if ($.fn.sortable && $orderBox.data('ui-sortable')) {
				$orderBox.sortable('refresh');
			}
		},

		toggleOrderedGroupedEmptyMessage: function ($wrap) {
			var $msg = $wrap.find('.urfl-order-empty-message');
			if (!$msg.length) return;
			var count = $wrap.find('.urfl-ordered-field-row').length;
			count > 0 ? $msg.hide() : $msg.show();
		},

		syncOrderedGroupedHidden: function ($wrap) {
			var order = [];
			$wrap.find('.urfl-ordered-field-row').each(function () {
				var key = $(this).data('key');
				if (key) order.push(String(key));
			});
			order = this.urflUnique(order);
			$wrap.find('.urfl-ordered-fields-order-value').val(JSON.stringify(order));
		},

		syncOrderedGroupedSelectFromOrder: function ($wrap) {
			var order = [];
			$wrap.find('.urfl-ordered-field-row').each(function () {
				var key = $(this).data('key');
				if (key) order.push(String(key));
			});
			order = this.urflUnique(order);

			var $select = $wrap.find('.urfl-ordered-fields-select');
			$select.val(order);

			if ($select.hasClass('select2-hidden-accessible')) {
				$select.trigger('change.select2');
			}
		},

		urflUnique: function (arr) {
			var out = [];
			$.each(arr || [], function (_, v) {
				v = String(v);
				if ($.inArray(v, out) === -1) out.push(v);
			});
			return out;
		},

		getUrlParam: function (name) {
			var results = new RegExp('[\\?&]' + name + '=([^&#]*)').exec(
				window.location.href,
			);
			if (results == null) return null;
			return decodeURI(results[1]) || 0;
		},

		legacyInitSelect2: function () {
			if (!$('.multiple-select').length || typeof $.fn.select2 === 'undefined')
				return;

			$('.multiple-select').select2();
			$('.multiple-select').select2({
				dropdownAutoWidth: true,
				containerCss: { display: 'block' },
				width: '20%',
			});
		},

		legacySettingsFieldsToggler: function (setting, toggle_field) {
			if (!setting || !setting.length || !toggle_field || !toggle_field.length)
				return;
			setting.is(':checked') ? toggle_field.show() : toggle_field.hide();
		},

		legacyHandleGeneralOptions: function () {
			var view_profile_selector = $(
				'#user_registration_frontend_listings_view_profile',
			);

			var card_fields_selector = $(
				'.user_registration_frontend_listings_card_fields_field',
			);
			var view_profile_label_selector = $(
				'.user_registration_frontend_listings_view_profile_button_text_field ',
			);

			if (!card_fields_selector.length && !view_profile_label_selector.length)
				return;

			this.legacySettingsFieldsToggler(
				view_profile_selector,
				card_fields_selector,
			);
			view_profile_selector.on('change.legacy', function () {
				URFL_Admin.legacySettingsFieldsToggler($(this), card_fields_selector);
			});

			this.legacySettingsFieldsToggler(
				view_profile_selector,
				view_profile_label_selector,
			);
			view_profile_selector.on('change.legacy2', function () {
				URFL_Admin.legacySettingsFieldsToggler(
					$(this),
					view_profile_label_selector,
				);
			});
		},

		legacyHandleFilterOptions: function () {
			var search_form_selector = $(
				'#user_registration_frontend_listings_search_form',
			);
			var search_criteria_selector = $(
				'.user_registration_frontend_listings_search_fields_field',
			);
			var sort_by_selector = $('#user_registration_frontend_listings_sort_by');
			var default_sorter_selector = $(
				'.user_registration_frontend_listings_default_sorter_field',
			);
			var only_ur_selector = $('#user_registration_frontend_listings_ur_only');
			var only_ur_forms_selector = $(
				'.user_registration_frontend_listings_ur_forms_field ',
			);

			if (
				!search_form_selector.length &&
				!sort_by_selector.length &&
				!only_ur_selector.length
			)
				return;

			this.legacySettingsFieldsToggler(
				search_form_selector,
				search_criteria_selector,
			);
			this.legacySettingsFieldsToggler(
				sort_by_selector,
				default_sorter_selector,
			);
			this.legacySettingsFieldsToggler(
				only_ur_selector,
				only_ur_forms_selector,
			);

			search_form_selector.on('change.legacy', function () {
				URFL_Admin.legacySettingsFieldsToggler(
					$(this),
					search_criteria_selector,
				);
			});

			sort_by_selector.on('change.legacy', function () {
				URFL_Admin.legacySettingsFieldsToggler(
					$(this),
					default_sorter_selector,
				);
			});

			only_ur_selector.on('change.legacy', function () {
				URFL_Admin.legacySettingsFieldsToggler($(this), only_ur_forms_selector);
			});
		},

		legacyHandlePaginationOptions: function () {
			var amount_filter_selector = $(
				'#user_registration_frontend_listings_amount_filter',
			);
			var amount_field_selector = $(
				'.user_registration_frontend_listings_default_page_filter_field',
			);
			var quantity_message_selector = $(
				'.user_registration_frontend_listings_filtered_user_message_field',
			);

			if (!amount_filter_selector.length) return;

			this.legacySettingsFieldsToggler(
				amount_filter_selector,
				amount_field_selector,
			);
			amount_filter_selector.on('change.legacy', function () {
				URFL_Admin.legacySettingsFieldsToggler($(this), amount_field_selector);
			});

			this.legacySettingsFieldsToggler(
				amount_filter_selector,
				quantity_message_selector,
			);
			amount_filter_selector.on('change.legacy2', function () {
				URFL_Admin.legacySettingsFieldsToggler(
					$(this),
					quantity_message_selector,
				);
			});
		},

		legacyHandleAdvancedFilterOptions: function () {
			var display_advanced_filter_selector = $(
				'#user_registration_frontend_listings_advanced_filter',
			);
			var advanced_filter_fields_selector = $(
				'.user_registration_frontend_listings_advanced_filter_fields_field',
			);

			if (!display_advanced_filter_selector.length) return;

			this.legacySettingsFieldsToggler(
				display_advanced_filter_selector,
				advanced_filter_fields_selector,
			);
			display_advanced_filter_selector.on('change.legacy', function () {
				URFL_Admin.legacySettingsFieldsToggler(
					$(this),
					advanced_filter_fields_selector,
				);
			});
		},

		legacyHandleAdvancedFilters: function () {
			var flag = false;

			var selector =
				'#user_registration_frontend_listings_advanced_filter_fields_selector';
			var listSel =
				'.user_registration_frontend_listings_advanced_filter_fields_list';
			var hiddenInput =
				'#user_registration_frontend_listings_advanced_filter_fields';

			$(selector).on('focusout', function () {
				flag = false;
			});

			$(selector).on('click', function (event) {
				event.preventDefault();

				var $select = $(this);
				var $detail = $select.closest('.ur-metabox-field-detail');
				var $list = $detail.find(listSel);

				if (flag) {
					var selectedValue = $select.val();

					var existingValues = [];
					$list
						.find(
							'.user_registration_frontend_listings_advanced_filter_fields_map',
						)
						.each(function () {
							var $el = $(this);

							if ($el.is('select')) {
								existingValues.push($el.val());
							} else {
								var metaKey = $el.find('input[id$="_meta_key"]').val();
								if (metaKey) existingValues.push(metaKey);
							}
						});

					var canAdd = false;
					if (selectedValue === 'other') {
						canAdd = true;
					} else if (
						selectedValue &&
						existingValues.indexOf(selectedValue) === -1
					) {
						canAdd = true;
					}

					if (canAdd) {
						var filter_options =
							user_registration_frontend_listing_admin_script_data.ur_frontend_listing_advanced_filter_options;

						var count =
							$list.find(
								'.user_registration_frontend_listings_advanced_filter_fields_container',
							).length + 1;

						var field =
							'<div class="user_registration_frontend_listings_advanced_filter_fields_container selected-options">' +
							'<div class="ur-draggable-option">' +
							'<div class="selected-option-container">' +
							'<svg width="12" height="20" viewBox="0 0 12 20" fill="none" xmlns="http://www.w3.org/2000/svg">' +
							'<rect width="4" height="4" fill="#4C5477"></rect>' +
							'<rect x="8" width="4" height="4" fill="#4C5477"></rect>' +
							'<rect y="16" width="4" height="4" fill="#4C5477"></rect>' +
							'<rect x="8" y="16" width="4" height="4" fill="#4C5477"></rect>' +
							'<rect y="8" width="4" height="4" fill="#4C5477"></rect>' +
							'<rect x="8" y="8" width="4" height="4" fill="#4C5477"></rect>' +
							'</svg>' +
							'<select id="user_registration_frontend_listings_advanced_filter_fields_' +
							count +
							'" ' +
							'class="user_registration_frontend_listings_advanced_filter_fields_map ur-selected ur-input-group">';

						for (var option_key in filter_options) {
							var sel = selectedValue === option_key ? 'selected' : '';
							field +=
								'<option value="' +
								option_key +
								'" ' +
								sel +
								'>' +
								filter_options[option_key] +
								'</option>';
						}

						field +=
							'</select>' +
							'</div>' +
							'<span class="user_registration_frontend_listings_advanced_filter_fields_remove delete-option">' +
							'<svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" class="feather feather-trash-2" viewBox="0 0 24 24">' +
							'<path d="M3 6h18m-2 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m-6 5v6m4-6v6"></path>' +
							'</svg>' +
							'</span>' +
							'</div>' +
							'</div>';

						$list.prepend(field);

						$detail.trigger('change');
						$detail
							.find(
								'#user_registration_frontend_listings_advanced_filter_fields_' +
									count,
							)
							.trigger('change');
					}
				}

				flag = !flag;
			});

			$(document.body).on(
				'click',
				'.user_registration_frontend_listings_advanced_filter_fields_remove',
				function (event) {
					event.preventDefault();
					var $detail = $(this).closest('.ur-metabox-field-detail');

					$(this)
						.closest(
							'.user_registration_frontend_listings_advanced_filter_fields_container',
						)
						.remove();
					$detail.trigger('change');
				},
			);

			$('.ur-metabox-field-detail').on('change', function () {
				var $detail = $(this);

				if ($detail.find(selector).length === 0) return;

				var field_mapper = {};
				$detail
					.find(
						'.user_registration_frontend_listings_advanced_filter_fields_map',
					)
					.each(function () {
						var $el = $(this);

						if ($el.is('select')) {
							field_mapper[$el.val()] = $el.find('option:selected').text();
						} else {
							var key = $el.find('input[id$="_meta_key"]').val();
							var label = $el.find('input[id$="_meta_label"]').val();

							if (key) field_mapper[key] = label || key;
						}
					});

				$detail.find(hiddenInput).val(JSON.stringify(field_mapper));
			});

			$(document.body).on(
				'change',
				'.user_registration_frontend_listings_advanced_filter_fields_map',
				function () {
					var $el = $(this);

					if ($el.is('select') && $el.val() === 'other') {
						var id = $el.attr('id');
						var klass = $el.attr('class');

						var field =
							'<div class="' +
							klass +
							'" id="' +
							id +
							'">' +
							'<input type="text" id="' +
							id +
							'_meta_label" ' +
							'class="user-registration-frontend-listing-advance-filter-meta-mapper ur-select custom-input" ' +
							'placeholder="Field Label">' +
							'<input type="text" id="' +
							id +
							'_meta_key" ' +
							'class="user-registration-frontend-listing-advance-filter-meta-mapper ur-select custom-input" ' +
							'placeholder="Meta Key ( eg. meta_key_123, meta_key_456 )">' +
							'</div>';

						$(field).insertBefore($el);
						$el.remove();
					}
				},
			);

			$(document.body).on(
				'input change',
				'.user-registration-frontend-listing-advance-filter-meta-mapper',
				function () {
					$(this).closest('.ur-metabox-field-detail').trigger('change');
				},
			);

			if (typeof $.fn.sortable !== 'undefined') {
				$(listSel).sortable({
					containment: listSel,
					tolerance: 'pointer',
					revert: 'invalid',
					forceHelperSize: true,
					stop: function () {
						$('.ur-metabox-field-detail').trigger('change');
					},
				});
			}
		},

		legacyHandleUserFields: function () {
			var $selected_forms = $(
				'.user_registration_frontend_listings_ur_forms_field',
			).find('select');
			if (!$selected_forms.length) return;

			if (
				typeof user_registration_frontend_listing_admin_script_data ===
				'undefined'
			)
				return;

			$selected_forms.on('change.legacy', function () {
				var form_ids = $(this).val();

				$.ajax({
					url: user_registration_frontend_listing_admin_script_data.ajax_url,
					type: 'POST',
					data: {
						action: 'user_registration_frontend_listing_display_user_fields',
						form_ids: form_ids,
					},
					success: function (response) {
						if (response && response.success === true) {
							$(
								'.user_registration_frontend_listings_lists_fields_field select',
							).empty();
							$(
								'.user_registration_frontend_listings_card_fields_field select',
							).empty();

							$.each(response.data, function (_, val) {
								var result = '<optgroup label="' + val.form_label + '">';
								$.each(val.field_list, function (key, v) {
									result += '<option value="' + key + '">' + v + '</option>';
								});
								result += '</optgroup>';

								$(
									'.user_registration_frontend_listings_lists_fields_field select',
								).append(result);
								$(
									'.user_registration_frontend_listings_card_fields_field select',
								).append(result);
							});
						}
					},
				});
			});
		},
	};

	$(document).ready(function () {
		URFL_Admin.init();
	});
})(jQuery);

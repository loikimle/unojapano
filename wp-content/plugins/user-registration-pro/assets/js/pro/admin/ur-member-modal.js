(function ($) {
    'use strict';

    /**
     * URFL Modal Handler
     */
    var URFL_Modal = {
        /**
         * Initialize modal
         */
        init: function () {
            this.bindEvents();
            this.interceptAddNew();
        },

        /**
         * Bind event handlers
         */
        bindEvents: function () {
            var self = this;


            $(document).on('click', '.urfl-modal-overlay', function () {
                self.closeModal();
            });


            $(document).on('click', '.urfl-modal-close, .urfl-modal-cancel', function (e) {
                e.preventDefault();
                self.closeModal();
            });


            this.email_template_inline_title_edit();


            $(document).on('click', '.urfl-modal-create', function (e) {
                e.preventDefault();
                self.createListing();
            });


            $(document).on('input', '#urfl-listing-title', function () {
                $('.urfl-modal-error').hide().text('');
                $(this).css('border-color', '');
            });
        },

        /**
         * Inline title editing functionality for listings
         */
        email_template_inline_title_edit: function () {
            var isEditing = false;

            $(document.body).on("click", ".edit-title-link", function (e) {
                e.preventDefault();

                var $this = $(this);
                var titleCell = $(this).closest(".column-title");
                var titleElement = titleCell.find(".ur-edit-title");
                var previousTitle = titleElement.text();


                titleElement.html(
                    '<input class="ur-title-input" value="' + previousTitle + '"/>'
                );
                titleCell.find(".ur-title-input").trigger("focus");

                titleCell
                    .find(".edit-title-link")
                    .text("Editing...");

                var postID = $this.data("post-id");
                var titleInput = titleCell.find(".ur-title-input");

                var data = {
									id: postID,
									action: 'urfl_update_title',
									nonce: urfl_modal.nonce,
								};

                var newTitle = "";

                isEditing = !isEditing;
                if (isEditing) {
                    titleInput.on("focusout", function () {
                        newTitle = titleInput.val();
                        if (newTitle) data["title"] = newTitle;
                        URFL_Modal.edit_title_ajax($this, titleElement, data);
                    });

                    titleInput.on("keypress", function (e) {
                        if ("Enter" === e.key) {
                            newTitle = titleInput.val();
                            data["title"] = newTitle;
                            URFL_Modal.edit_title_ajax($this, titleElement, data);
                        }
                    });
                }
            });
        },

        /**
         * AJAX request to update the post title
         */
        edit_title_ajax: function (selector, titleSelector, data) {
					titleSelector.append('Saving...');

					$.ajax({
						url: urfl_modal.ajax_url,
						data: data,
						type: 'POST',
						success: function (response) {
							titleSelector.html(response.data.message);
							selector.text('Edit Title');
						},
						error: function () {
							alert('Error updating title');
						},
					});
				},

        /**
         * Intercept "Add New" button clicks
         */
        interceptAddNew: function () {
            var self = this;


            $(document).on(
                'click',
                'a[href*="post-new.php?post_type=ur_frontend_listings"]',
                function (e) {
                    e.preventDefault();
                    self.openModal();
                },
            );


            $(document).on('click', '.urfl-add-new-listing', function (e) {
                e.preventDefault();
                self.openModal();
            });
        },

        /**
         * Open modal
         */
        openModal: function () {
            $('#urfl-create-modal').fadeIn(200);
            $('#urfl-listing-title').val('').focus();
            $('.urfl-modal-error').hide().text('');
            $('body').addClass('urfl-modal-open');
        },

        /**
         * Close modal
         */
        closeModal: function () {
            $('#urfl-create-modal').fadeOut(200);
            $('#urfl-listing-title').val('');
            $('.urfl-modal-error').hide().text('');
            $('.urfl-modal-create').removeClass('loading').prop('disabled', false);
            $('body').removeClass('urfl-modal-open');
        },

        /**
         * Validate title
         */
        validateTitle: function (title) {
            if (!title || title.trim().length === 0) {
                this.showError(urfl_modal.i18n.title_required);
                return false;
            }
            return true;
        },

        /**
         * Show error message
         */
        showError: function (message) {
            $('.urfl-modal-error').text(message).show();
            $('#urfl-listing-title').css('border-color', '#ef4444').focus();
        },

        /**
         * Create listing via AJAX
         */
        createListing: function () {
            var self = this;
            var $btn = $('.urfl-modal-create');
            var $input = $('#urfl-listing-title');
            var title = $input.val();


            if (!this.validateTitle(title)) {
                return;
            }


            $btn.addClass('loading').prop('disabled', true);


            $.ajax({
                url: urfl_modal.ajax_url,
                type: 'POST',
                data: {
                    action: 'urfl_create_listing',
                    nonce: urfl_modal.nonce,
                    title: title,
                },
                success: function (response) {
                    if (response.success && response.data.redirect) {

                        window.location.href = response.data.redirect;
                    } else {
                        self.showError(
                            response.data.message || urfl_modal.i18n.create_error,
                        );
                        $btn.removeClass('loading').prop('disabled', false);
                    }
                },
                error: function () {
                    self.showError(urfl_modal.i18n.create_error);
                    $btn.removeClass('loading').prop('disabled', false);
                },
            });
        },
    };


    $(document).ready(function () {
        URFL_Modal.init();
    });
})(jQuery);

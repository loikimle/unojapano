(function ($) {
	"use strict";

	$(document).ready(function () {
		setTimeout(function () {
			initMenuRestriction();
		}, 100);
	});

	function initMenuRestriction() {
		var $modal = $("#URCR-Restriction-Modal");
		var $modalDescription = $("#URCR-Modal-Description");
		var $closeButtons = $(
			".urcr-restriction-modal__close, .urcr-restriction-modal__button--close"
		);

		if (!$modal.length) {
			return;
		}

		function showRestrictionModal(message) {
			if (!$modal.length) {
				alert(message);
				return;
			}

			if ($modalDescription.length) {
				$modalDescription.html(message);
			}

			$modal[0].showModal();
			$("body").css("overflow", "hidden");
		}

		function closeRestrictionModal() {
			if ($modal.length) {
				$modal[0].close();
				$("body").css("overflow", "");
			}
		}

		$(document).on("click", "a[data-restricted]", function (e) {
			var $link = $(this);
			var identifier = $link.data("restricted");

			if (identifier) {
				e.preventDefault();
				e.stopPropagation();

				var $messageElement = $("#urcr-msg-" + identifier);

				if ($messageElement.length) {
					var messageContent = $messageElement.html();
					showRestrictionModal(messageContent);
				} else {
					var fallbackMessage =
						(window.urcrMenuRestriction &&
							window.urcrMenuRestriction.i18n &&
							window.urcrMenuRestriction.i18n
								.restrictedContent) ||
						"This content is restricted.";
					showRestrictionModal(fallbackMessage);
				}
			}
		});

		$closeButtons.on("click", function (e) {
			e.preventDefault();
			closeRestrictionModal();
		});

		$modal.on("click", function (e) {
			if (e.target === $modal[0]) {
				closeRestrictionModal();
			}
		});

		$(document).on("keydown", function (e) {
			if (e.key === "Escape" && $modal[0].open) {
				closeRestrictionModal();
			}
		});
	}
})(jQuery);

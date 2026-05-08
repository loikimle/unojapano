/**
 * Email Template Preview Popover Handler
 *
 * Handles the preview link click to show email template preview in a popover.
 */
(function ($) {
	"use strict";

	$(document).ready(function () {
		// Initialize Thickbox for email template preview links.
		// Thickbox will automatically handle links with class 'thickbox' and TB_iframe parameter.
		$(".ur-email-template-preview").on("click", function (e) {
			// Thickbox handles the rest, but we can add custom styling if needed.
			// The link already has TB_iframe=true in the URL, so Thickbox will open it in an iframe.
		});

		// Function to adjust iframe height based on body content.
		var adjustIframeHeight = function($tbWindow, $iframe, $iframeContent) {
			try {
				var iframeDoc = $iframe[0].contentDocument || $iframe[0].contentWindow.document;
				if (!iframeDoc || !iframeDoc.body) {
					return false;
				}

				var iframeBody = iframeDoc.body;
				var iframeHtml = iframeDoc.documentElement;

				// Get the actual body content height.
				var bodyHeight = Math.max(
					iframeBody.scrollHeight,
					iframeBody.offsetHeight,
					iframeHtml.scrollHeight,
					iframeHtml.offsetHeight
				);

				// Ensure we have a valid height.
				if (bodyHeight <= 0) {
					return false;
				}

				// Set width to 90% of viewport width.
				var viewportWidth = $(window).width();
				var modalWidth = Math.floor(viewportWidth * 0.9);

				// Add title bar height.
				var titleHeight = $tbWindow.find("#TB_title").outerHeight() || 0;
				var totalHeight = bodyHeight + titleHeight;

				// Limit to 90% of viewport height if content is too tall.
				var maxHeight = Math.floor($(window).height() * 0.9);
				var finalHeight = Math.min(totalHeight, maxHeight);

				// Update Thickbox window size.
				$tbWindow.css({
					width: modalWidth + "px",
					height: finalHeight + "px",
					marginLeft: "-" + Math.floor(modalWidth / 2) + "px",
					marginTop: "-" + Math.floor(finalHeight / 2) + "px"
				});

				// Set iframe container and iframe height to match body content exactly.
				$iframeContent.css({
					width: "100%",
					height: bodyHeight + "px",
					border: "none",
					overflow: "hidden",
					display: "block"
				});

				$iframe.css({
					width: "100%",
					height: bodyHeight + "px",
					border: "none",
					display: "block"
				});

				return true;
			} catch (e) {
				console.log('Iframe height adjustment error:', e);
				return false;
			}
		};

		// Listen for postMessage from iframe content.
		$(window).on('message', function(e) {
			var $tbWindow = $("#TB_window");
			if (!$tbWindow.length || !$tbWindow.hasClass("ur-email-template-preview-modal")) {
				return;
			}

			var $iframe = $tbWindow.find("#TB_iframeContent iframe");
			var $iframeContent = $tbWindow.find("#TB_iframeContent");

			if (e.originalEvent.data && e.originalEvent.data.type === 'ur-email-preview-height') {
				var bodyHeight = parseInt(e.originalEvent.data.height, 10);
				if (bodyHeight > 0) {
					var viewportWidth = $(window).width();
					var modalWidth = Math.floor(viewportWidth * 0.9);
					var titleHeight = $tbWindow.find("#TB_title").outerHeight() || 0;
					var totalHeight = bodyHeight + titleHeight;
					var maxHeight = Math.floor($(window).height() * 0.9);
					var finalHeight = Math.min(totalHeight, maxHeight);

					$tbWindow.css({
						width: modalWidth + "px",
						height: finalHeight + "px",
						marginLeft: "-" + Math.floor(modalWidth / 2) + "px",
						marginTop: "-" + Math.floor(finalHeight / 2) + "px"
					});

					$iframeContent.css({
						width: "100%",
						height: bodyHeight + "px",
						border: "none",
						overflow: "hidden"
					});

					$iframe.css({
						width: "100%",
						height: bodyHeight + "px",
						border: "none"
					});
				}
			}
		});

		// Customize Thickbox window size when it opens for email preview.
		$(document).on("thickbox:iframe:loaded", function () {
			var $tbWindow = $("#TB_window");
			if (!$tbWindow.length || !$tbWindow.find("#TB_iframeContent").length) {
				return;
			}

			// Set background color for title bar.
			$tbWindow.find("#TB_title").css({
				"background-color": "#ffffff"
			});

			var $iframe = $tbWindow.find("#TB_iframeContent iframe");
			var $iframeContent = $tbWindow.find("#TB_iframeContent");

			if (!$iframe.length) {
				return;
			}

			// Try multiple times to get the height (content might load asynchronously).
			var attempts = 0;
			var maxAttempts = 10;
			var checkInterval = setInterval(function() {
				attempts++;
				if (adjustIframeHeight($tbWindow, $iframe, $iframeContent) || attempts >= maxAttempts) {
					clearInterval(checkInterval);
				}
			}, 200);

			// Also try on iframe load event.
			$iframe.on('load', function() {
				setTimeout(function() {
					adjustIframeHeight($tbWindow, $iframe, $iframeContent);
				}, 300);
			});
		});

		// Also handle window resize to maintain proper sizing.
		$(window).on("resize", function () {
			var $tbWindow = $("#TB_window");
			if (
				$tbWindow.length &&
				$tbWindow.hasClass("ur-email-template-preview-modal")
			) {
				var $iframe = $tbWindow.find("#TB_iframeContent iframe");
				var $iframeContent = $tbWindow.find("#TB_iframeContent");
				
				if ($iframe.length) {
					try {
						var iframeDoc = $iframe[0].contentDocument || $iframe[0].contentWindow.document;
						var iframeBody = iframeDoc.body;
						var iframeHtml = iframeDoc.documentElement;

						var bodyHeight = Math.max(
							iframeBody.scrollHeight,
							iframeBody.offsetHeight,
							iframeHtml.clientHeight,
							iframeHtml.scrollHeight,
							iframeHtml.offsetHeight
						);

						var viewportWidth = $(window).width();
						var modalWidth = Math.floor(viewportWidth * 0.9);
						var titleHeight = $tbWindow.find("#TB_title").outerHeight() || 0;
						var totalHeight = bodyHeight + titleHeight + 20;
						var maxHeight = Math.floor($(window).height() * 0.9);
						var finalHeight = Math.min(totalHeight, maxHeight);

						$tbWindow.css({
							width: modalWidth + "px",
							height: finalHeight + "px",
							marginLeft: "-" + Math.floor(modalWidth / 2) + "px",
							marginTop: "-" + Math.floor(finalHeight / 2) + "px"
						});

						$iframeContent.css({
							height: bodyHeight + "px"
						});

						$iframe.css({
							height: bodyHeight + "px"
						});
					} catch (e) {
						// Fallback
						var viewportWidth = $(window).width();
						var modalWidth = Math.floor(viewportWidth * 0.9);
						var modalHeight = Math.floor($(window).height() * 0.9);
						$tbWindow.css({
							width: modalWidth + "px",
							height: modalHeight + "px",
							marginLeft: "-" + Math.floor(modalWidth / 2) + "px",
							marginTop: "-" + Math.floor(modalHeight / 2) + "px"
						});
					}
				}
			}
		});

		// Add class to identify our modal when it opens and set title background.
		$(".ur-email-template-preview").on("click", function () {
			setTimeout(function () {
				var $tbWindow = $("#TB_window");
				$tbWindow.addClass("ur-email-template-preview-modal");
				// Set background color for title bar.
				$tbWindow.find("#TB_title").css({
					"background-color": "#ebebeb"
				});
			}, 100);
		});
	});
})(jQuery);

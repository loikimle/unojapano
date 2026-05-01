(function ($) {
	("use strict");
	function HandleRowSticky() {
		if ($(".wp-block-ultimate-post-row.row_sticky_active").length > 0) {
			$(".wp-block-ultimate-post-row.row_sticky_active").each(function () {
				let windowScroll = 0;
				const rowSelector = $(this);
				if (
					rowSelector.hasClass("row_sticky") ||
					rowSelector.hasClass("row_scrollToStickyTop")
				) {
					const elementHeight = rowSelector.height();
					const elementWidth = rowSelector.outerWidth();
					const elementPosition = rowSelector.offset().top;
					const elementPositionLeft = rowSelector.offset().left;
					$(window).on("scroll", function () {
						if (rowSelector.hasClass("row_sticky")) {
							if (
								!rowSelector.hasClass("alignfull") &&
								rowSelector.hasClass("stickyTopActive")
							) {
								rowSelector.css({
									left: elementPositionLeft,
									"max-width": `${elementWidth}px`,
									width: "100%",
								});
							}
							if (
								windowScroll < $(this).scrollTop() &&
								elementPosition < $(this).scrollTop() + elementHeight
							) {
								$(rowSelector).animate(
									{ height: elementHeight - elementHeight }, // Target height
									10,
									"swing",
									function () {
										rowSelector
											.addClass("stickyTopActive")
											.removeClass("stickyTopDeActive");
									}
								);
							} else {
								if (elementPosition + elementHeight > $(this).scrollTop()) {
									rowSelector
										.addClass("stickyTopDeActive")
										.removeClass("stickyTopActive");
								}
								// rowSelector.css({ 'position': 'static !important;'});
							}
						} else {
							if (
								windowScroll < $(this).scrollTop() &&
								elementHeight < $(this).scrollTop()
							) {
								rowSelector
									.addClass("stickyTopActive")
									.removeClass("stickyTopDeActive");
							} else {
								rowSelector
									.addClass("stickyTopDeActive")
									.removeClass("stickyTopActive");
							}
						}
						windowScroll = $(this).scrollTop();
					});
				}
			});
		}
	}
	const isFront = $("body.postx-admin-page").length == 0;
	if (isFront) {
		HandleRowSticky();
	}
})(jQuery);

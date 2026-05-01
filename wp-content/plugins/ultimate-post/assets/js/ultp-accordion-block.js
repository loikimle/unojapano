(function ($) {
	("use strict");
	const accordionActive =
		$("body.postx-admin-page").length == 0 &&
		$(".wp-block-ultimate-post-accordion").length > 0;
	if (accordionActive) {
		handleAccordionBlock();
	}
	function handleAccordionBlock() {
		$(".wp-block-ultimate-post-accordion").each(function () {
			const iniselect = $(this).data("active");
			const autoCollapse = $(this).data("autocollapse");
			const accordionItem = $(this)
				.children()
				.children(".wp-block-ultimate-post-accordion-item");
			accordionItem.each(function (idx) {
				const acItem = $(this);
				// For initial Select
				if (idx == iniselect) {
					$(this).addClass("active active-accordion");
					acItem
						.find(".ultp-accordion-item__content")
						.first()
						.css({ display: "block" });
				} else {
					$(this).removeClass("active active-accordion");
				}
				// on Click
				$(this)
					.children(".ultp-accordion-item")
					.children(".ultp-accordion-item__navigation")
					.on("click", function () {
						const accordioClosestItem = $(this)
							.parent()
							.parent(".wp-block-ultimate-post-accordion-item");
						const content = accordioClosestItem
							.find(".ultp-accordion-item__content")
							.first();
						const currentAccordion = accordioClosestItem
							.parent()
							.parent(".wp-block-ultimate-post-accordion");
						if (content.is(":visible")) {
							content.stop(true, true).slideUp(300, function () {
								accordioClosestItem.removeClass("active active-accordion");
							});
						} else {
							if (autoCollapse) {
								currentAccordion
									.find(".ultp-accordion-item__content:visible")
									.first()
									.stop(true, true)
									.slideUp(300, function () {
										accordioClosestItem
											.siblings()
											.removeClass("active active-accordion");
										accordioClosestItem.addClass("active active-accordion");
									});
							}
							accordioClosestItem.addClass("active active-accordion");
							content.stop(true, true).slideDown(300);
						}
					});
			});
		});
	}
})(jQuery);

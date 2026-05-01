(function ($) {
	("use strict");
	handleTabBlock();
	$(window).on("resize", function () {
		handleTabBlock();
	});
	// Tab Block Function
	function handleTabBlock() {
		const rootTabSelector = (element) =>
			element.closest(".wp-block-ultimate-post-tabs");

		$(".wp-block-ultimate-post-tabs").each(function () {
			let tab = $(this);
			const responsiveType = tab.data("responsive");
			const tabNavMain = tab.find(".ultp-tabs-nav").first();

			const tabContent = tab
				.find(".ultp-tab-content")
				.first()
				.children(".wp-block-ultimate-post-tab-item");

			const isTabVertical =
				tab.children().children(".ultp-nav-right").length > 0 ||
				tab.children().children(".ultp-nav-left").length > 0;

			if (tab.parent(".wp-block-ultimate-post-tab-item").length > 0) {
				tab.closest(".ultp-tab-content").css({ overflow: "hidden" });
			}

			// Responsive
			if (responsiveType == "slider" && tab.width() < 600) {
				tab.find(".ultp-tabs-nav").css({ flexWrap: "nowrap" });
			}

			// Accordion
			if (
				tab.width() < 600 &&
				tab.data("responsive") == "accordion" &&
				!tab.hasClass(".ultp-tab-accordion-active")
			) {
				tab.addClass("ultp-tab-accordion-active");
				tab.find(".ultp-tabs-nav-element").each(function (i) {
					if ($(this).data("order")) {
						$(this).css({ order: (i + 1) * 2 - 1 });
					}
				});
				tabContent.each(function (i) {
					$(this).css({ order: (i + 1) * 2 });
				});
			}

			// Active Accordion
			if (tab.hasClass("ultp-tab-accordion-active")) {
				tabNavMain
					.css("display", "contents")
					.parent()
					.css("display", "contents");
				tabContent
					.addClass("ultp-tab-content")
					.parent()
					.css("display", "contents");
			}

			// initial active
			tabContent.each(function (idx) {
				if (
					rootTabSelector($(this)).data("activetab") == $(this).data("tabindex")
				) {
					$(this).addClass("active");
				}
			});

			const tabNavContainer = tab.find(".ultp-tabs-nav-wrapper");
			const leftArrow = tab.find(".ultp-tab-left-arrow").first();
			const rightArrow = tab.find(".ultp-tab-right-arrow").first();

			const tabevent =
				tab.data("tabevent") == "autoplay" ? "click" : tab.data("tabevent");

			let tabOverflow =
				responsiveType == "slider" ||
				tab.find(".ultp-tab-wrapper").hasClass("ultp-nav-left") ||
				tab.find(".ultp-tab-wrapper").hasClass("ultp-nav-right");

			let navContainerSize = isTabVertical
				? tabNavMain.height()
				: tabNavMain.width();

			let singleElementWidth = navContainerSize / tabNavMain?.children().length;
			let navParenTContainer = isTabVertical
				? tabNavContainer.height()
				: tabNavContainer.width();

			let shitValue = 0;
			let SlideshitValue = singleElementWidth;

			// on Click/Hover Element Show
			$(".ultp-tabs-nav-element")
				.off(tabevent)
				.on(tabevent, function (event) {
					event.stopPropagation;

					const navContent = $(this);

					navContent
						.addClass("ultp-tab-active")
						.siblings()
						.removeClass("ultp-tab-active");

					rootTabSelector($(this))
						.find(".wp-block-ultimate-post-tab-item")
						.each(function () {
							$(this).removeClass("active");
							if ($(this).data("tabindex") == navContent.data("tabindex")) {
								$(this).addClass("active");
							}
						});

					navContainerSize = isTabVertical
						? tabNavMain.height()
						: tabNavMain.width();

					singleElementWidth = navContainerSize / tabNavMain?.children().length;

					navParenTContainer = isTabVertical
						? tabNavContainer.height()
						: tabNavContainer.width();

					if (navContainerSize > navParenTContainer && tabOverflow) {
						handleTabArrowControl();
					}
				});

			if (navContainerSize > navParenTContainer && tabOverflow) {
				handleTabArrowControl();
			}

			// Left Right Arrow Slide
			function handleTabArrowControl() {
				if (shitValue == 0) {
					rightArrow.addClass("ultp-arrow-active");
				}
				rightArrow.off("click").on("click", function (event) {
					event.stopPropagation();
					const parentSize = isTabVertical
						? $(this).closest(".ultp-tabs-nav-wrapper").height()
						: $(this).closest(".ultp-tabs-nav-wrapper").width();

					const size = isTabVertical
						? $(this).siblings().find(".ultp-tabs-nav").height()
						: $(this).siblings().find(".ultp-tabs-nav").width();

					const shiftValueLimit = size - parentSize;

					const singleWidth =
						size / $(this).siblings().find(".ultp-tabs-nav")?.children().length;

					if (
						shiftValueLimit > shitValue &&
						shiftValueLimit - shitValue > singleWidth
					) {
						shitValue = shitValue + singleWidth;
					}

					if (shiftValueLimit - shitValue < singleWidth + 1) {
						shitValue = shitValue + (shiftValueLimit - shitValue);
						$(this).removeClass("ultp-arrow-active");
					}

					if (shitValue > 0) {
						$(this)
							.siblings(".ultp-tab-left-arrow")
							.addClass("ultp-arrow-active");
					}

					let transformStyle = isTabVertical
						? `translate(0px, -${shitValue}px)`
						: `translate(-${shitValue}px, 0px)`;

					$(this)
						.siblings()
						.find(".ultp-tabs-nav")
						.css({ transform: transformStyle });
				});

				leftArrow.off("click").on("click", function (event) {
					const size = isTabVertical
						? $(this).siblings().find(".ultp-tabs-nav").height()
						: $(this).siblings().find(".ultp-tabs-nav").width();

					const singleWidth =
						size / $(this).siblings().find(".ultp-tabs-nav")?.children().length;

					if (shitValue > singleWidth) {
						shitValue = shitValue - singleWidth;
					} else {
						shitValue = 0;
					}

					if (shitValue > 0) {
						$(this)
							.siblings(".ultp-tab-right-arrow")
							.addClass("ultp-arrow-active");
					} else {
						$(this).removeClass("ultp-arrow-active");
					}

					let transformStyle = isTabVertical
						? `translate(0px, -${shitValue}px)`
						: `translate(-${shitValue}px, 0px)`;

					$(this)
						.siblings()
						.find(".ultp-tabs-nav")
						.css({ transform: transformStyle });
				});
			}

			// Auto Play function
			function handleTabSlider(tab, tabContent, tabNavMain) {
				const parentBlock =
					tab.parent(".wp-block-ultimate-post-tab-item").length > 0
						? tab.parent(".wp-block-ultimate-post-tab-item").hasClass("active")
						: true;
				if (parentBlock) {
					navContainerSize = isTabVertical
						? tabNavMain.height()
						: tabNavMain.width();

					singleElementWidth = navContainerSize / tabNavMain?.children().length;

					navParenTContainer = isTabVertical
						? tabNavContainer.height()
						: tabNavContainer.width();
					const navContent = tabNavMain.find(".ultp-tabs-nav-element");
					SlideshitValue = SlideshitValue + singleElementWidth;

					if (slideIndex * singleElementWidth < navParenTContainer) {
						tabNavMain.css({ transform: `translate(0px, 0px)` });
						if (responsiveType == "slider") {
							rightArrow.addClass("ultp-arrow-active");
							leftArrow.removeClass("ultp-arrow-active");
						}
					}

					if (slideIndex * singleElementWidth > navParenTContainer) {
						if (tabOverflow) {
							leftArrow.addClass("ultp-arrow-active");
							rightArrow.addClass("ultp-arrow-active");
						}
						let transformStyle = isTabVertical
							? `translate(0px, ${
									navParenTContainer - slideIndex * singleElementWidth
							  }px)`
							: `translate(${
									navParenTContainer - slideIndex * singleElementWidth
							  }px, 0px)`;
						tabNavMain.css({ transform: transformStyle });
					}

					const totalElementCount = navContent.parent().children().length;

					navContent.each(function (idx) {
						if (progressbarEnable) {
							$(this).removeClass("tab-progressbar-active");
						}
						if (idx + 1 == slideIndex) {
							$(this).addClass("ultp-tab-active");
							if (progressbarEnable) {
								$(this).addClass("tab-progressbar-active");
							}
						} else {
							$(this).removeClass("ultp-tab-active");
						}
					});

					tabContent.each(function (idx) {
						$(this).each(function () {
							if (idx + 1 == slideIndex) {
								$(this).addClass("active");
							} else {
								$(this).removeClass("active");
							}
						});
					});

					if (totalElementCount == slideIndex) {
						rightArrow.removeClass("ultp-arrow-active");
						slideIndex = 0;
					}
					slideIndex++;
				}
			}

			// Auto Play
			const progressbarEnable = tab.data("progressbar");
			const duration = $(this).data("duration") * 1000;

			let slideIndex = rootTabSelector($(this)).data("activetab") - 1 || 1;
			if ($(this).data("tabevent") == "autoplay") {
				let tabSlider = setInterval(
					() => handleTabSlider(tab, tabContent, tabNavMain),
					duration
				);

				tab.on("mouseleave", function () {
					tabSlider = setInterval(
						() => handleTabSlider(tab, tabContent, tabNavMain),
						duration
					);
				});

				tab.on("mouseenter", function () {
					clearInterval(tabSlider);
				});
			}
		});
	}
})(jQuery);

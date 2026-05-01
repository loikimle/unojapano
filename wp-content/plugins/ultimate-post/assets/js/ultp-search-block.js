(function ($) {
	("use strict");
	if ($(".wp-block-ultimate-post-advanced-search")?.length) {
		handleUltpSearchBlock();
	}
	function handleUltpSearchBlock() {
		let postPages = 1;
		// Search Clear Text Button
		$(document).on("click", ".ultp-search-clear", function () {
			postPages = 1;
			const blockId = $(this).data("blockid");
			$(this)
				.parents(".ultp-search-inputwrap")
				.find(".ultp-searchres-input")
				.val("");
			$(this).removeClass("active");
			$(`.ultp-block-${blockId}`).find(".ultp-result-data").html("");
			$(`.ultp-block-${blockId}`)
				.find(
					".ultp-search-noresult, .ultp-viewall-results, .ultp-result-loader"
				)
				.removeClass("active");
		});

		// Popup Window Close
		$(document).on("click", ".ultp-popupclose-icon", function () {
			$(this).parents(".result-data").removeClass("popup-active");
		});

		// Search Button Popup Icon
		$(document).on("click", ".ultp-searchpopup-icon", function () {
			const el = $(this).parents(".ultp-search-frontend");
			const blockId = el.data("blockid");
			handleSetPosition(
				el,
				$(`.result-data.ultp-block-${blockId}`).length ? false : true
			);
			$(`.result-data.ultp-block-${blockId}`).toggleClass("popup-active");
		});
		// In search result page clear button active
		if ($(".ultp-searchres-input").val().length > 2) {
			$(".ultp-searchres-input")
				.closest(".ultp-search-inputwrap")
				.find(".ultp-search-clear")
				.addClass("active");
		}
		// Input On Change Action
		$(document).on("input", ".ultp-searchres-input", function (e) {
			searchResultAPI($(this), e.target.value);
		});

		const searchResultAPI = (
			that,
			searchText,
			blockId = "",
			isAppend = true
		) => {
			blockId = blockId
				? blockId
				: that
						.parents(".ultp-search-inputwrap")
						.find(".ultp-search-clear")
						.data("blockid");
			const el = $(
				`.wp-block-ultimate-post-advanced-search.ultp-block-${blockId}`
			).find(".ultp-search-frontend");
			const selector = $(`.result-data.ultp-block-${blockId}`);
			// Set PopUp Positions
			handleSetPosition(el, selector.length ? false : true);

			if (searchText.length > 2) {
				if (el.data("ajax")) {
					selector.find(".ultp-search-result").addClass("ultp-search-show");
					selector.find(".ultp-result-loader").addClass("active");
					selector.addClass("popup-active");
					wp.apiFetch({
						path: "/ultp/ultp_search_data",
						method: "POST",
						data: {
							searchText: searchText,
							date: parseInt(el.data("date")),
							image: parseInt(el.data("image")),
							author: parseInt(el.data("author")),
							excerpt: parseInt(el.data("excerpt")),
							category: parseInt(el.data("catenable")),
							excerptLimit: parseInt(el.data("excerptlimit")),
							postPerPage: el.data("allresult") ? el.data("postno") : 10,
							exclude:
								typeof el.data("searchposttype") !== "string" &&
								el.data("searchposttype").length > 0 &&
								el.data("searchposttype"),
							paged: postPages,
							wpnonce: ultp_data_frontend.security,
						},
					}).then((res) => {
						if (res.post_data) {
							if (isAppend) {
								selector
									.find(".ultp-search-result")
									.addClass("ultp-search-show");
								selector.find(".ultp-result-data").addClass("ultp-result-show");
								selector.find(".ultp-result-data").html(res.post_data);
							} else {
								selector
									.find(".ultp-search-result")
									.addClass("ultp-search-show");
								selector.find(".ultp-result-data").addClass("ultp-result-show");

								selector
									.find(".ultp-result-data")
									.append(res.post_data)
									.fadeIn(500, function () {
										$(this).animate(
											{ scrollTop: $(this).prop("scrollHeight") },
											400
										);
									});
							}
							selector
								.find(".ultp-search-noresult, .ultp-result-loader")
								.removeClass("active");
							const itemCount = selector.find(
								".ultp-result-data .ultp-search-result__item"
							).length;
							selector
								.find(".ultp-viewall-results")
								.addClass("active")
								.find("span")
								.text(`(${res.post_count - itemCount})`);
						} else {
							selector
								.find(".ultp-result-data")
								.removeClass("ultp-result-show");
							selector.find(".ultp-result-data").html("");
							selector.find(".ultp-search-noresult").addClass("active");
							selector
								.find(".ultp-result-loader, .ultp-viewall-results")
								.removeClass("active");
						}
						if (el.data("allresult")) {
							const itemCount = selector.find(
								".ultp-result-data .ultp-search-result__item"
							).length;
							if (res.post_count && res.post_count > itemCount) {
								selector
									.find(".ultp-viewall-results")
									.addClass("active")
									.find("span")
									.text(`(${res.post_count - itemCount})`);
							} else {
								selector.find(".ultp-viewall-results").removeClass("active");
							}
						}
					});
				}
			} else {
				selector.find(".ultp-search-result").removeClass("ultp-search-show");
				selector.find(".ultp-result-data").removeClass("ultp-result-show");
				selector.find(".ultp-search-noresult").removeClass("active");
			}
			if (searchText.length < 3) {
				postPages = 1;
				selector.find(".ultp-result-data").html("");
				selector.find(".ultp-viewall-results").removeClass("active");
				selector.find(".ultp-search-noresult").removeClass("active");

				// Clear Button hide
				el.find(".ultp-search-clear").removeClass("active");
				$(`.result-data.ultp-block-${blockId}`)
					.find(".ultp-search-clear")
					.removeClass("active");
			} else {
				// Clear Button Show
				el.find(".ultp-search-clear").addClass("active");
				$(`.result-data.ultp-block-${blockId}`)
					.find(".ultp-search-clear")
					.addClass("active");
			}
		};

		// View All Result
		$(document).on("click", ".ultp-viewall-results", function (e) {
			postPages++;
			const blockId = $(this).closest(".result-data").data("blockid");
			searchResultAPI(
				$(this),
				$(`.ultp-block-${blockId} .ultp-searchres-input`).val(),
				blockId,
				false
			);
		});

		// Outside Click Close Popup [Done]
		if ($(".wp-block-ultimate-post-advanced-search").length > 0) {
			$(document).on("click", function (e) {
				if (
					!$(e.target).closest(".ultp-searchpopup-icon").length &&
					!$(e.target).closest(".ultp-searchres-input").length
				) {
					if (!$(e.target).closest(".result-data.popup-active").length) {
						$(".result-data").removeClass("popup-active");
					}
				}
				if (!$(e.target).closest(".ultp-search-frontend").length) {
					if (!$(e.target).closest(".result-data.popup-active").length) {
						$(".result-data").removeClass("popup-active");
					}
				}
			});
		}

		// Enter Key In Search Box
		$(document).on("keyup", ".ultp-searchres-input", function (e) {
			const blockId = $(this)
				.closest(".ultp-search-inputwrap")
				.find(".ultp-search-clear")
				.data("blockid");
			const goSearch = $(
				`.wp-block-ultimate-post-advanced-search.ultp-block-${blockId}`
			)
				.find(".ultp-search-frontend")
				.data("gosearch");
			const newTabData = $(
				`.wp-block-ultimate-post-advanced-search.ultp-block-${blockId}`
			)
				.find(".ultp-search-frontend")
				.data("enablenewtab");
			let tabTarget = "_self";
			if (newTabData) {
				tabTarget = "_blank";
			}
			if (goSearch) {
				if (e.key == "Enter" && $(this).val().length > 2) {
					const el = $(
						`.wp-block-ultimate-post-advanced-search.ultp-block-${blockId}`
					).find(".ultp-search-frontend");
					let exclude =
						typeof el.data("searchposttype") !== "string" &&
						el.data("searchposttype")?.length > 0 &&
						el?.data("searchposttype");
					exclude = exclude.length
						? `&ultp_exclude=${JSON.stringify(
								exclude.map((e) => {
									return e.value;
								})
						  )}`
						: "";
					window.open(
						`${ultp_data_frontend.home_url}/?s=${$(this).val()}${exclude}`,
						tabTarget
					);
				}
			}
		});

		// Search Button Click Event
		$(document).on("click", ".ultp-search-button", function (e) {
			const blockId = $(this)
				.closest(".ultp-searchform-content")
				.find(".ultp-search-clear")
				.data("blockid");
			const goSearch = $(
				`.wp-block-ultimate-post-advanced-search.ultp-block-${blockId}`
			)
				.find(".ultp-search-frontend")
				.data("gosearch");
			const newTabData = $(
				`.wp-block-ultimate-post-advanced-search.ultp-block-${blockId}`
			)
				.find(".ultp-search-frontend")
				.data("enablenewtab");
			let tabTarget = "_self";
			if (newTabData) {
				tabTarget = "_blank";
			}
			if (goSearch) {
				const el = $(
					`.wp-block-ultimate-post-advanced-search.ultp-block-${blockId}`
				).find(".ultp-search-frontend");
				let exclude =
					typeof el.data("searchposttype") !== "string" &&
					el.data("searchposttype")?.length > 0 &&
					el?.data("searchposttype");
				exclude = exclude.length
					? `&ultp_exclude=${JSON.stringify(
							exclude.map((e) => {
								return e.value;
							})
					  )}`
					: "";
				window.open(
					`${ultp_data_frontend.home_url}/?s=${$(this)
						.closest(".ultp-searchform-content")
						.find(".ultp-searchres-input")
						.val()}${exclude}`,
					tabTarget
				);
			} else {
				$(`.result-data.ultp-block-${blockId}`).addClass("popup-active");
			}
		});

		// Search Input Click Popup Show
		$(document).on("click", ".ultp-searchres-input", function (e) {
			const blockId = $(this)
				.closest(".ultp-searchform-content")
				.find(".ultp-search-clear")
				.data("blockid");
			$(".result-data").removeClass("popup-active");
			$(`.result-data.ultp-block-${blockId}`).addClass("popup-active");
		});

		// Resize Window Popup Position Reset
		$(window).on("resize", function () {
			if ($(".ultp-search-result").length > 0) {
				$(".ultp-search-frontend").each(function (el) {
					handleSetPosition($(el));
				});
			}
		});

		// Popup Top/Left Position and Append Result Content
		const handleSetPosition = (el, newBlock = false) => {
			const blockId = el.data("blockid");
			const popupType = el.data("popuptype");
			const popupposition = el.data("popupposition");

			if (newBlock) {
				const viewAllResult = el.data("allresult");
				const searchResultTemplate = `<div class="ultp-search-result" data-image=${
					el.data("image") || false
				} data-author=${el.data("author") || false} data-date=${
					el.data("date") || false
				} data-excerpt=${
					el.data("excerpt") || false
				} data-excerptlimit=${el.data("excerptlimit")} data-allresult=${
					viewAllResult || false
				} data-catenable=${el.data("catenable") || false} data-postno=${
					el.data("postno") || false
				} data-gosearch=${el.data("gosearch") || false} data-popupposition=${
					popupposition || false
				}>
                    <div class="ultp-result-data"></div>
                    <div class="ultp-search-result__item ultp-search-noresult">${el.data(
											"noresultext"
										)}</div>
                    <div class="ultp-search-result__item ultp-result-loader"></div>
                    ${
											viewAllResult
												? `<div class="ultp-viewall-results ultp-search-result__item">${el.data(
														"viewmoretext"
												  )}<span></span></div><div class="ultp-search-result__item ultp-viewmore-loader"></div>`
												: ""
										}
                    </div>`;

				if (popupType) {
					const canvas = $(`.ultp-block-${blockId}`)
						.find(".ultp-search-canvas")
						.detach();
					$("body").append(
						`<div class="result-data ultp-block-${blockId} ultp-search-animation-${popupType}" data-blockid=${blockId}><div class="ultp-search-canvas">${
							canvas.html() + (el.data("ajax") ? searchResultTemplate : "")
						}</div></div>`
					);
				} else {
					$("body").append(
						`<div class="result-data ultp-block-${blockId}" data-blockid=${blockId}>${searchResultTemplate}</div>`
					);
				}
			}

			let posSelector = "";
			if (!popupType) {
				posSelector = el.find(".ultp-searchform-content");
				const elementPosition = posSelector.offset();
				return $(`body > .ultp-block-${blockId}`).css({
					width: `${posSelector.width()}px`,
					top: `${elementPosition?.top + posSelector.height()}px`,
					left: `${elementPosition?.left}px`,
				});
			}
			if (popupType == "popup") {
				posSelector = el.find(".ultp-searchpopup-icon");
				const elementPosition = posSelector.offset();
				const contentPosition =
					popupposition == "right"
						? elementPosition?.left > $(`body > .ultp-block-${blockId}`).width()
						: $(document).width() - elementPosition?.left >
						  $(`body > .ultp-block-${blockId}`).width();
				let right = "";
				let left = "";
				if (popupposition == "right") {
					right = contentPosition
						? $(document).width() -
						  elementPosition?.left -
						  posSelector.outerWidth() +
						  "px"
						: "unset";
					left = contentPosition
						? "auto"
						: elementPosition?.left +
						  (popupposition == "right" ? 10 : 0) +
						  "px";
				} else {
					right = contentPosition
						? "unset"
						: $(document).width() -
						  elementPosition?.left -
						  posSelector.outerWidth() +
						  "px";
					left = contentPosition
						? elementPosition?.left + (popupposition == "right" ? 10 : 0) + "px"
						: "auto";
				}
				return $(`body > .ultp-block-${blockId}`).css({
					top: `${elementPosition?.top + posSelector.outerHeight()}px`,
					right: right,
					left: left,
				});
			}
		};
	}
})(jQuery);

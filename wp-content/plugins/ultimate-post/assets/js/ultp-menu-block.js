(function ($) {
	("use strict");
	/*************************************
         Menu Block
      *************************************/
	setTimeout(() => {
		handleMegaMenuWidth("setTimeout");
	}, 10);
	handleMegaMenuWidth("normal");

	function handleMegaMenuWidth(t) {
		if ($(".editor-styles-wrapper")?.length) {
			return;
		}
		const hasMegaMenu = $(
			".wp-block-ultimate-post-menu-item.hasMegaMenuChild > .ultp-menu-item-wrapper > .ultp-menu-item-content"
		);
		if (hasMegaMenu.length > 0) {
			hasMegaMenu.each(function () {
				if ($(this).hasClass("ultpMegaWindowWidth")) {
					const bodyWrapperWidth = $("body")?.width() || 1200;
					const bodyWrapperLeft = $("body")?.offset()?.left || 0;
					const contentLeft = $(this)?.offset()?.left || 0;
					$(this)
						.find(
							" > .wp-block-ultimate-post-mega-menu > .ultp-mega-menu-wrapper"
						)
						.css({
							maxWidth: `${bodyWrapperWidth}px`,
							boxSizing: "border-box",
						});
					// $(this).css({ "left" : `${bodyWrapperLeft - contentLeft}px`});

					const siblingLeft =
						$(this).siblings(".ultp-menu-item-label-container")?.offset()
							?.left || 0;
					$(this).css({ left: `${bodyWrapperLeft - siblingLeft}px` });

					//do not remove $('.wp-block-ultimate-post-menu-item.hasMegaMenuChild > .ultp-menu-item-wrapper > .ultp-menu-item-content.ultpMegaWindowWidth > .block-editor-inner-blocks > .block-editor-block-list__layout > .wp-block > .wp-block-ultimate-post-mega-menu > .ultp-mega-menu-wrapper').css({ "maxWidth" : `${editorMainWrapperWidth}px`, "boxSizing": "border-box"});
					//do not remove $('.wp-block-ultimate-post-menu-item.hasMegaMenuChild > .ultp-menu-item-wrapper > .ultp-menu-item-content.ultpMegaWindowWidth > .block-editor-inner-blocks').css({ "left" : `-${contentLeft - editorMainWrapperLeft}px`});
				} else if ($(this).hasClass("ultpMegaMenuWidth")) {
					const closetMenuWidth =
						$(this).closest(".wp-block-ultimate-post-menu")?.width() || 800;
					const closetMenuLeft =
						$(this).closest(".wp-block-ultimate-post-menu")?.offset()?.left ||
						0;
					const contentLeft = $(this)?.offset()?.left || 0;
					$(this)
						.find(
							" > .wp-block-ultimate-post-mega-menu > .ultp-mega-menu-wrapper"
						)
						.css({ maxWidth: `${closetMenuWidth}px`, boxSizing: "border-box" });
					$(this).css({ left: `${closetMenuLeft - contentLeft}px` });
					const siblingLeft =
						$(this).siblings(".ultp-menu-item-label-container")?.offset()
							?.left || 0;
					$(this).css({ left: `${closetMenuLeft - siblingLeft}px` });
				} else {
					$(this)
						.find(
							" > .wp-block-ultimate-post-mega-menu > .ultp-mega-menu-wrapper"
						)
						.css({ maxWidth: ``, boxSizing: "" });
					$(this).css({ left: `` });
				}
			});
		}
	}

	/*
          Menu responsive
      */
	const isFront = $("body.postx-admin-page").length == 0;
	if (isFront) {
		$(window).on("resize", function () {
			handleMegaMenuWidth("resize");
		});
	}
	let currentStack = "";
	let prevStack = [];
	let toAppend;
	let toAppendBack;
	let rawMenu;
	let mv_rcsstype;
	let mv_rstr;
	let mv_naviIcon;
	let mv_naviExpandIcon;
	let mv_animationDuration;
	let mv_HeadText;
	let mv_dropIconArray = [];
	if (isFront) {
		$(".wp-block-ultimate-post-menu").each(function () {
			const that = $(this);
			if (that.data("hasrootmenu") != "hasRootMenu") {
				that
					.find(
						`.ultp-menu-item-wrapper[data-parentbid=".ultp-block-${that?.data(
							"bid"
						)}"] > .ultp-menu-item-label-container a`
					)
					.each(function () {
						const theA = $(this);
						const currentUrl = window.location.href;
						let isActive = false;
						const targetUrl = theA[0].href;
						if (currentUrl.endsWith("/") && !targetUrl.endsWith("/")) {
							const normalizedTargetUrl = targetUrl + "/";
							if (
								currentUrl.replace("https:", "http:") ==
								normalizedTargetUrl.replace("https:", "http:")
							) {
								isActive = true;
							}
						}
						if (
							currentUrl.replace("https:", "http:") ==
								targetUrl.replace("https:", "http:") ||
							isActive
						) {
							theA
								.closest(
									`.ultp-menu-item-wrapper[data-parentbid=".ultp-block-${that?.data(
										"bid"
									)}"]`
								)
								.addClass("ultp-current-link");
						}
					});
			}
		});
		$(document).on(
			"click",
			'.wp-block-ultimate-post-menu[data-mv="enable"] > .ultp-mv-ham-icon.ultp-active',
			function (e) {
				enableDisableMolibeView(e, "ham");
			}
		);
		$(document).on(
			"click",
			".ultp-mobile-view-container .ultp-mv-back, .ultp-mobile-view-container .ultp-mv-back-label-con",
			function (e) {
				if (mv_rstr == "mv_dissolve" || mv_rstr == "mv_slide") {
					handleResponsiveMenuHtml(e, "back");
				} else {
					handleMenuDropDownStyle(e, "back");
				}
			}
		);
		$(document).on(
			"click",
			".ultp-mobile-view-container .ultp-mv-close",
			function (e) {
				enableDisableMolibeView(e, "close");
			}
		);
		$(document).on("click", ".ultp-mobile-view-container", function (e) {
			if ($(e.target).hasClass("ultp-mobile-view-container")) {
				enableDisableMolibeView(e, "close");
			}
		});
		$(document).on(
			"click",
			".ultp-mobile-view-container .ultp-menu-item-label-container",
			function (e) {
				if (
					$(e.target).is(".ultp-menu-item-label") ||
					$(e.target).parent().is(".ultp-menu-item-label") ||
					($(e.target).is(".ultp-menu-item-label-container") &&
						$(e.target).siblings(".ultp-menu-item-content").find("> *")
							.length == 0)
				) {
					return;
				}
				e.preventDefault();
				if (mv_rstr == "mv_dissolve" || mv_rstr == "mv_slide") {
					handleResponsiveMenuHtml(e, "next");
				} else {
					handleMenuDropDownStyle(e, "next");
				}
			}
		);
	}

	function enableDisableMolibeView(e, type = "") {
		// ham close
		if (type == "close") {
			$(rawMenu)
				.find("> .ultp-mobile-view-container > .ultp-mobile-view-wrapper")
				.css({
					transform: "translateX(-100%)",
					visibility: "hidden",
					opacity: "0",
				});
			setTimeout(() => {
				if ($(rawMenu).hasClass("ultpMenu__Css")) {
					$(rawMenu).addClass("ultpMenuCss");
					$(rawMenu).removeClass("ultpMenu__Css");
				}
				$(rawMenu).removeClass("ultp-mobile-menu");
				$(rawMenu)
					.find("> .ultp-mobile-view-container")
					.removeClass("ultp-mv-active");
				$(rawMenu)
					.find("> .ultp-mobile-view-container > .ultp-mobile-view-wrapper")
					.css({
						transform: "",
						visibility: "",
						opacity: "",
						"transition-property": "",
						"transition-timing-function": "",
						"transition-duration": "",
					});
				toAppend?.html("");
				currentStack = "";
				prevStack = [];
				toAppend = "";
				toAppendBack = "";
				rawMenu = "";
				mv_rcsstype = "";
				mv_rstr = "";
				mv_animationDuration = 0;
				mv_HeadText = "";
				mv_dropIconArray = [];
			}, mv_animationDuration);
		} else {
			// ham
			const that = $(e.target);
			mv_animationDuration = $(that).hasClass("ultp-mv-ham-icon")
				? $(that).data("animationduration")
				: that.closest(".ultp-mv-ham-icon").data("animationduration");
			mv_animationDuration = mv_animationDuration || 100;
			mv_HeadText = $(that).hasClass("ultp-mv-ham-icon")
				? $(that).data("headtext")
				: that.closest(".ultp-mv-ham-icon").data("headtext");
			mv_naviIcon = that
				.closest(".wp-block-ultimate-post-menu")
				.find(
					"> .ultp-mobile-view-container > .ultp-mv-icons > .ultp-mv-label-icon svg"
				)
				.prop("outerHTML");
			mv_naviExpandIcon = that
				.closest(".wp-block-ultimate-post-menu")
				.find(
					"> .ultp-mobile-view-container > .ultp-mv-icons > .ultp-mv-label-icon-expand svg"
				)
				.prop("outerHTML");

			rawMenu = that.closest(".wp-block-ultimate-post-menu");
			const menuObj = $(rawMenu);
			if ($(rawMenu).hasClass("ultpMenuCss")) {
				$(rawMenu).removeClass("ultpMenuCss");
				$(rawMenu).addClass("ultpMenu__Css");
			}
			currentStack = "ultp-block-" + menuObj.data("bid");
			menuObj.addClass("ultp-mobile-menu");
			menuObj.find("> .ultp-mobile-view-container").addClass("ultp-mv-active");
			handleResponsiveMenuHtml("", "hamIcon");

			menuObj
				.find("> .ultp-mobile-view-container > .ultp-mobile-view-wrapper")
				.css({
					"transition-property": "opacity, visibility, transform",
					"transition-timing-function": "ease-in",
					"transition-duration": mv_animationDuration
						? mv_animationDuration / 1000 + "s"
						: ".25s",
				});
		}
	}
	function handleDropIcon(data) {
		const _replace = data?._replace || mv_naviIcon;
		let _string = data?._string;
		if (_string && mv_dropIconArray.length) {
			mv_dropIconArray.forEach((el) => {
				if (el && _replace) {
					_string = _string.replace(el, _replace);
				}
			});
		}
		return _string;
	}
	function handleResponsiveMenuHtml(e, src) {
		// hamIcon next back
		if (src == "hamIcon") {
			mv_rcsstype = rawMenu.data("rcsstype");
			mv_rstr = rawMenu.data("rstr");
			toAppend = $(rawMenu).find(
				"> .ultp-mobile-view-container .ultp-mobile-view-body"
			);
			toAppendBack = $(rawMenu).find(
				"> .ultp-mobile-view-container .ultp-mv-back-label"
			);
			let theHtml = $(rawMenu)
				.find("> .ultp-menu-wrapper > .ultp-menu-content")
				.html();

			$(rawMenu)
				.find(".ultp-menu-item-dropdown")
				.toArray()
				.forEach((element) => {
					if ($(element).html()) {
						mv_dropIconArray.push($(element).html());
					}
				});
			if (theHtml) {
				let tempHtml = $("<div>").html(theHtml);
				tempHtml
					.find(".wp-block-ultimate-post-menu")
					.addClass("ultp-mobile-menu");
				theHtml = tempHtml.html();

				theHtml = handleDropIcon({ type: "hamicon", _string: theHtml });
				toAppend.html(
					mv_rcsstype == "custom"
						? theHtml.replaceAll("ultpMenuCss", "ultpMenu__Css")
						: theHtml
				);
				toAppendBack.html(mv_HeadText);
			}
		} else if (src == "next") {
			const that = $(e.target);
			const parentItem = that.closest(".wp-block-ultimate-post-menu-item");
			const bid = parentItem.data("bid");
			if (!prevStack.includes("ultp-block-" + bid)) {
				let theHtml = "";
				let d_none = "";
				if (parentItem.hasClass("hasListMenuChild")) {
					d_none = parentItem
						.find(
							"> .ultp-menu-item-wrapper > .ultp-menu-item-content > .wp-block-ultimate-post-list-menu"
						)
						.css("display");
					theHtml = parentItem
						.find(
							"> .ultp-menu-item-wrapper > .ultp-menu-item-content > .wp-block-ultimate-post-list-menu > .ultp-list-menu-wrapper > .ultp-list-menu-content"
						)
						.html();
				} else {
					d_none = parentItem
						.find(
							"> .ultp-menu-item-wrapper > .ultp-menu-item-content > .wp-block-ultimate-post-mega-menu"
						)
						.css("display");
					theHtml = parentItem
						.find("> .ultp-menu-item-wrapper > .ultp-menu-item-content")
						.html();
				}
				if (d_none == "none") {
					return;
				}
				if (theHtml) {
					$(rawMenu)
						.find(".ultp-mv-back-label-con")
						.removeClass("ultpmenu-dnone");
					let tempHtml = $("<div>").html(theHtml);
					tempHtml
						.find(".wp-block-ultimate-post-menu")
						.addClass("ultp-mobile-menu");
					theHtml = tempHtml.html();

					prevStack.push(currentStack);
					currentStack = "ultp-block-" + bid;
					toAppendBack.html(
						parentItem
							.find(
								"> .ultp-menu-item-wrapper > .ultp-menu-item-label-container .ultp-menu-item-label-text"
							)
							.html()
					);

					if (mv_rstr == "mv_dissolve") {
						toAppend.find("> *").animate(
							{
								opacity: 0.2,
							},
							mv_animationDuration,
							function () {
								toAppend.html(
									mv_rcsstype == "custom"
										? theHtml.replaceAll("ultpMenuCss", "ultpMenu__Css")
										: theHtml
								);

								toAppend.find("> *").css("opacity", ".1");
								toAppend.find("> *").animate(
									{
										opacity: 1,
									},
									mv_animationDuration
								);
							}
						);
					} else {
						// Slide View
						toAppend.html(
							mv_rcsstype == "custom"
								? theHtml.replaceAll("ultpMenuCss", "ultpMenu__Css")
								: theHtml
						);
						toAppend.find("> *").css({
							opacity: ".1",
							transform: "translateX(100%)",
							transition: `transform ${mv_animationDuration / 1000 + "s"} ease`,
						});
						toAppend.find("> *").animate(
							{
								opacity: 0.3,
							},
							10,
							function () {
								toAppend
									.find("> *")
									.css({ opacity: "1", transform: "translateX(0px)" });
							}
						);
					}
				}
			}
		} else if (src == "back") {
			if (prevStack.length == 0) {
				return;
			}
			currentStack = prevStack.pop() || "";
			let backHtml = "";
			if (prevStack.length == 0) {
				backHtml = $(rawMenu)
					.find("> .ultp-menu-wrapper > .ultp-menu-content")
					.html();
				toAppendBack.html(mv_HeadText);
				$(rawMenu).find(".ultp-mv-back-label-con").addClass("ultpmenu-dnone");
			} else {
				if (
					$("." + currentStack).hasClass("wp-block-ultimate-post-menu-item")
				) {
					if ($("." + currentStack).hasClass("hasListMenuChild")) {
						backHtml = $(rawMenu)
							.find("." + currentStack)
							.find(
								"> .ultp-menu-item-wrapper > .ultp-menu-item-content > .wp-block-ultimate-post-list-menu > .ultp-list-menu-wrapper > .ultp-list-menu-content"
							)
							.html();
					} else {
						backHtml = $(rawMenu)
							.find("." + currentStack)
							.find("> .ultp-menu-item-wrapper > .ultp-menu-item-content")
							.html();
					}
				}
			}
			if (backHtml) {
				let tempHtml = $("<div>").html(backHtml);
				tempHtml
					.find(".wp-block-ultimate-post-menu")
					.addClass("ultp-mobile-menu");
				backHtml = tempHtml.html();
				backHtml = handleDropIcon({ type: "back", _string: backHtml });
				toAppendBack.html(
					$(rawMenu)
						.find("." + currentStack)
						.find(
							"> .ultp-menu-item-wrapper > .ultp-menu-item-label-container .ultp-menu-item-label-text"
						)
						.html()
				);

				if (mv_rstr == "mv_dissolve") {
					toAppend.find("> *").animate(
						{
							opacity: 0.2,
						},
						mv_animationDuration,
						function () {
							toAppend.html(
								mv_rcsstype == "custom"
									? backHtml.replaceAll("ultpMenuCss", "ultpMenu__Css")
									: backHtml
							);
						}
					);
					toAppend.find("> *").animate(
						{
							opacity: 1,
						},
						mv_animationDuration
					);
				} else {
					// Slide View
					toAppend.html(
						mv_rcsstype == "custom"
							? backHtml.replaceAll("ultpMenuCss", "ultpMenu__Css")
							: backHtml
					);
					toAppend.find("> *").css({
						opacity: ".1",
						transform: "translateX(-100%)",
						transition: `transform ${mv_animationDuration / 1000 + "s"} ease`,
					});
					toAppend.find("> *").animate(
						{
							opacity: 0.3,
						},
						10,
						function () {
							toAppend
								.find("> *")
								.css({ opacity: "1", transform: "translateX(0px)" });
						}
					);
				}
			}
		}
		// console.log(prevStack, currentStack);
	}

	function handleMenuDropDownStyle(e, type) {
		const that = $(e.target);
		const parentItem = that.closest(".wp-block-ultimate-post-menu-item");
		let c_type = "next";
		if (parentItem.hasClass("ultp-menu-res-css")) {
			c_type = "back";
		} else {
			parentItem.addClass("ultp-menu-res-css");
		}
		let target;
		let theHeight;
		if (c_type == "next") {
			parentItem.addClass("ultp-hammenu-accordian-active");
			if (mv_naviExpandIcon) {
				parentItem
					.find(
						"> .ultp-menu-item-wrapper > .ultp-menu-item-label-container .ultp-menu-item-dropdown"
					)
					.html(mv_naviExpandIcon);
			}

			if (parentItem.hasClass("hasListMenuChild")) {
				target = parentItem.find(
					"> .ultp-menu-item-wrapper > .ultp-menu-item-content > .wp-block-ultimate-post-list-menu > .ultp-list-menu-wrapper > .ultp-list-menu-content"
				);
			} else {
				target = parentItem.find(
					"> .ultp-menu-item-wrapper > .ultp-menu-item-content"
				);
			}
			if (!target.length) {
				target = parentItem.find(
					"> .ultp-menu-item-wrapper > .ultp-menu-item-content"
				);
			}
			theHeight = target.outerHeight();
			const paddingObj = parentItem.find(
				"> .ultp-menu-item-wrapper > .ultp-menu-item-content"
			);
			const paddingTop = paddingObj.css("padding-top");
			const paddingBottom = paddingObj.css("padding-bottom");

			parentItem
				.find("> .ultp-menu-item-wrapper > .ultp-menu-item-content")
				.html(target.html());
			parentItem
				.find("> .ultp-menu-item-wrapper > .ultp-menu-item-content")
				.css({ height: "0px", "padding-top": "0", "padding-bottom": "0" });
			parentItem
				.find("> .ultp-menu-item-wrapper > .ultp-menu-item-content")
				.animate(
					{
						height: theHeight + "px",
						"padding-top": paddingTop,
						"padding-bottom": paddingBottom,
					},
					mv_animationDuration,
					function () {
						$(this).css({
							height: "",
							"padding-top": "",
							"padding-bottom": "",
						});
					}
				);
		} else if (c_type == "back") {
			parentItem.removeClass("ultp-hammenu-accordian-active");
			parentItem
				.find(".wp-block-ultimate-post-menu-item")
				.removeClass("ultp-hammenu-accordian-active");
			parentItem
				.find("> .ultp-menu-item-wrapper > .ultp-menu-item-content")
				.animate(
					{
						height: "0",
						paddingTop: "0",
						paddingBottom: "0",
					},
					mv_animationDuration,
					function () {
						$(this).css({
							height: "",
							paddingTop: "",
							paddingBottom: "",
						});
						if (mv_naviIcon) {
							parentItem
								.find(
									".ultp-menu-item-wrapper .ultp-menu-item-label-container .ultp-menu-item-dropdown"
								)
								.each(function () {
									if ($(this).html()) {
										$(this).html(mv_naviIcon);
									}
								});
						}
						parentItem.removeClass("ultp-menu-res-css");
						parentItem
							.find(".wp-block-ultimate-post-menu-item")
							.removeClass("ultp-menu-res-css");
					}
				);
		}
	}
})(jQuery);

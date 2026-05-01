(function ($) {
	"use strict";
	let galleryAvailable = $(".wp-block-ultimate-post-gallery").length != 0;
	$(".wp-block-ultimate-post-gallery").each(function () {
		const gl = $(this);
		handleUltpGalleryBlock(gl);
		handleGalleryLayout(gl);
	});

	// Run on load and resize
	const isFront = $("body.postx-admin-page").length == 0;
	if (isFront && galleryAvailable) {
		$(window).on("load resize", function () {
			$(".wp-block-ultimate-post-gallery").each(function () {
				const gl = $(this);
				handleGalleryLayout(gl);
			});
		});
	}

	function handleUltpGalleryBlock(element) {
		const enableLightbox = element.data("lightbox");
		const lightboxCaption = element.data("caption");
		const gallery = element.find(".ultp-gallery-item");
		const lightboxIndicator = element.data("indicators");
		const lightBox = element.find(".ultp-gallery-lightbox");
		const loadMoreButton = element.find(".ultp-gallery-loadMore");
		const zoomIn = element.find(".ultp-gallery-lightbox__zoom-in");
		const zoomOut = element.find(".ultp-gallery-lightbox__zoom-out");
		const galleryClose = element.find(".ultp-gallery-lightbox__close");
		const galleryControl = element.find(".ultp-gallery-lightbox__control");
		const fullScreen = element.find(".ultp-gallery-lightbox__full-screen");
		const galleryIndicator = element.find(
			".ultp-gallery-lightbox__indicator-control",
		);

		let galleryIndex = null;
		let lightboxVisible = true;
		/*
            Lightbox Query Start
        */
		/* Lightbox Enable Disable */
		$(document).on("click", function (event) {
			const $target = $(event.target);
			const $lightbox = $(".ultp-lightbox");

			const isInsideLightboxControl =
				$target.closest(
					".ultp-gallery-lightbox__control, .ultp-lightbox__left-icon, .ultp-lightbox__right-icon, .ultp-lightbox__img-container, .ultp-lightbox-indicator__item-img",
				).length > 0;

			if (!isInsideLightboxControl && !lightboxVisible) {
				$lightbox.hide();
				galleryControl.hide();
				lightboxVisible = true;
			}

			if ($lightbox.is(":visible")) {
				lightboxVisible = false;
			}
		});

		/*  lightbox close */
		galleryClose.on("click", function () {
			const lightbox = $(this)
				.parent()
				.parent(".ultp-gallery-wrapper")
				.find(".ultp-lightbox");
			lightbox.hide();
			galleryControl.hide();
			// If Enable Full screen
			if (document.exitFullscreen) {
				document.exitFullscreen().catch((err) => {
					console.error("Failed to exit fullscreen:", err);
				});
			} else if (document.webkitExitFullscreen) {
				document.webkitExitFullscreen();
			} else if (document.msExitFullscreen) {
				document.msExitFullscreen();
			}
			galleryIndex = null;
		});

		/*  lightbox zoom in */
		let scale = 1;
		zoomIn.on("click", function () {
			scale += 0.5;
			const mainImg = $(this)
				.closest(".ultp-gallery-wrapper")
				.find(".ultp-lightbox__inside img");

			mainImg.css({
				transform: `scale(${scale})`,
			});
			if (scale > 1) {
				$(".ultp-lightbox__caption").slideUp(300);
				$(".ultp-lightbox-indicator").slideUp(300);
			} else if (!$(".ultp-lightbox__caption").is(":visible")) {
				$(".ultp-lightbox__caption").fadeIn(300);
			}
		});

		/*  lightbox zoom Out */
		zoomOut.on("click", function () {
			scale -= 0.5;
			const mainImg = $(this)
				.closest(".ultp-gallery-wrapper")
				.find(".ultp-lightbox__inside img");

			mainImg.css({
				transform: `scale(${scale})`,
			});

			if (!$(".ultp-lightbox__caption").is(":visible") && scale == 1) {
				$(".ultp-lightbox__caption").fadeIn(300);
			}
			if (!$(".ultp-lightbox-indicator").is(":visible") && scale == 1) {
				$(".ultp-lightbox-indicator").fadeIn(300);
			}
		});
		/*  lightbox Indicator */
		galleryIndicator.off("click").on("click", function (e) {
			e.stopPropagation();
			const indicator = $(this)
				.closest(".ultp-gallery-wrapper")
				.find(".ultp-lightbox-indicator");

			indicator.slideToggle(300);
		});
		/*  Full screen enable */
		fullScreen.on("click", function () {
			const galleryTab = document.documentElement; // fullscreen whole page
			const isFullscreen =
				document.fullscreenElement ||
				document.webkitFullscreenElement ||
				document.msFullscreenElement;
			if (!isFullscreen) {
				// Enter fullscreen
				if (galleryTab.webkitRequestFullscreen) {
					// Safari - no Promise, so just call it
					galleryTab.webkitRequestFullscreen();
				} else if (galleryTab.msRequestFullscreen) {
					// IE11 - no Promise
					galleryTab.msRequestFullscreen();
				}
			} else {
				// Exit fullscreen
				if (document.exitFullscreen) {
					document.exitFullscreen().catch((err) => {
						console.error("Failed to exit fullscreen:", err);
					});
				} else if (document.webkitExitFullscreen) {
					document.webkitExitFullscreen();
				} else if (document.msExitFullscreen) {
					document.msExitFullscreen();
				}
			}
		});

		enableLightbox &&
			lightBox.each(function () {
				$(this)
					.off()
					.on("click", function (e) {
						e.stopPropagation();
						const lightboxSelector = $(this);
						lightboxVisible = false;
						const imgId = lightboxSelector.data("id");
						const imgIndex = lightboxSelector.data("index");
						const imgSrc = lightboxSelector.data("img");
						const imgCaption = lightboxSelector
							.closest(".ultp-gallery-item")
							.data("caption");
						const glSelector = lightboxSelector.parent().parent();
						handleLightBox(
							lightboxSelector,
							imgId,
							imgIndex,
							imgSrc,
							imgCaption,
							glSelector,
							lightboxCaption,
							lightboxIndicator,
						);
					});
			});

		$(document).on(
			"click",
			".ultp-lightbox__right-icon, .ultp-lightbox__left-icon",
			function () {
				const $this = $(this);
				const isRight = $this.hasClass("ultp-lightbox__right-icon");
				const $lightbox = $this.closest(".ultp-lightbox");
				const $galleryContainer = $this.closest(".ultp-gallery-container");
				const $indicator = $lightbox.find(".ultp-lightbox-indicator");
				const $mainImg = $lightbox.find(".ultp-lightbox__img");
				const imgCaption = $lightbox.find(".ultp-lightbox__caption");
				const $galleryItems = $galleryContainer.children();
				const activeIndex = $lightbox.data("index");

				if (galleryIndex == null) {
					galleryIndex = activeIndex;
				}

				// Calculate new index
				galleryIndex = isRight
					? (galleryIndex + 1) % $galleryItems.length
					: (galleryIndex - 1 + $galleryItems.length) % $galleryItems.length;

				const $targetItem = $galleryItems.eq(galleryIndex);
				const newImgSrc = $targetItem
					.find(".ultp-gallery-media img")
					.attr("src");
				const itemId = $targetItem.data("id");
				const newCaption = $targetItem.data("caption");

				// Animate image fade out -> change src -> fade in
				$mainImg.fadeOut(200, function () {
					$mainImg.attr("src", newImgSrc).fadeIn(200);
				});

				// Optional: animate caption change as well
				imgCaption.fadeOut(200, function () {
					imgCaption.text(newCaption).fadeIn(200);
				});

				// Update indicator
				const $activeIndicator = $indicator.children().eq(galleryIndex);
				$activeIndicator
					.addClass("lightbox-active")
					.siblings()
					.removeClass("lightbox-active");

				if ($activeIndicator.data("id") !== itemId) {
					$activeIndicator.removeClass("lightbox-active");
				}
			},
		);

		$(document).on("click", ".ultp-lightbox-indicator__item", function () {
			const indicator = $(this);
			const getImgSrc = indicator.find("img").attr("src");
			const lightbox = indicator.closest(".ultp-lightbox");
			const mainImg = lightbox.find(".ultp-lightbox__img");
			galleryIndex = indicator.data("index");

			mainImg.fadeOut(200, function () {
				mainImg.attr("src", getImgSrc).fadeIn(200);
			});
			indicator
				.addClass("lightbox-active")
				.siblings()
				.removeClass("lightbox-active");
		});

		enableLightbox &&
			!(lightBox.length > 0) &&
			gallery.off().on("click", function (event) {
				const downloadButton = $(event.target)
					.closest("svg")
					.parent()
					.is(".ultp-gallery-action a");
				if (!gallery.find(".ultp-lightbox").is(":visible") && !downloadButton) {
					const glSelector = $(this);
					galleryIndex = glSelector.data("index");
					const imgId = glSelector.data("id");
					const imgCaption = glSelector.data("caption");
					const imgIndex = glSelector.data("index");
					const imgSrc = glSelector.find(".ultp-gallery-media img").attr("src");
					const appendSelector = glSelector.find(
						".ultp-gallery-action-container",
					);
					handleLightBox(
						glSelector,
						imgId,
						imgIndex,
						imgSrc,
						imgCaption,
						appendSelector,
						lightboxCaption,
						lightboxIndicator,
					);
				}
			});
		/*  
                    Loadmore 
                */
		const loadMoreCount = loadMoreButton[0]
			? Number(
					getComputedStyle(loadMoreButton[0])
						.getPropertyValue("--ultp-gallery-count")
						.trim(),
				)
			: "";
		let rootImgCount = loadMoreCount;

		if (loadMoreButton?.length > 0) {
			gallery.each(function (index) {
				if (loadMoreCount < index + 1) {
					$(this)
						.find("img")
						.attr({
							width: $(this).find("img").width(),
							height: $(this).find("img").height(),
						});
					$(this).css({ display: "none" });
				}
			});
		}

		if (gallery.length <= rootImgCount) {
			loadMoreButton.css({ display: "none" });
		}
		// lightboxSelector,
		// 	imgId,
		// 	imgIndex,
		// 	imgSrc,
		// 	imgCaption,
		// 	glSelector,
		// 	lightboxCaption,
		// 	lightboxIndicator;
		function handleLightBox(
			selector,
			imgId,
			imgIndex,
			imgSrc,
			imgCaption,
			appendSelector,
			lightboxCaption,
			lightboxIndicator,
		) {
			galleryControl.css({ display: "flex" });
			$(".ultp-lightbox").remove();
			const indicatorHtml = gallery
				.map(function (index) {
					const imgSrc = $(this).find("img").attr("src");
					const caption = $(this).data("caption");
					if (imgId == $(this).data("id")) {
						galleryIndex = index;
					}
					console.log(imgSrc, "imgSrc");
					return `<div  data-index=${index} class="${imgId == $(this).data("id") ? "ultp-lightbox-indicator__item lightbox-active" : "ultp-lightbox-indicator__item"}" data-id=${$(this).data("id")} data-caption=${caption}><img class="ultp-lightbox-indicator__item-img" src="${imgSrc}" /></div>`;
				})
				.get()
				.join("");

			const galleryData = `<div class="ultp-lightbox" data-id=${imgId} data-index=${imgIndex}>
                        <div class="ultp-lightbox__container">
                            <div class="ultp-lightbox__inside">
                                <div class="ultp-lightbox__left-icon"><svg  fill="currentColor"xmlns="http://www.w3.org/2000/svg" viewBox="0 0 49.16 37.25"><path  stroke-miterlimit="10" d="M18.157 36.154 2.183 20.179l-.053-.053-1.423-1.423 17.45-17.449a2.05 2.05 0 0 1 2.9 2.9l-12.503 12.5h38.053a2.05 2.05 0 1 1 0 4.1H8.555l12.5 12.5a2.05 2.05 0 1 1-2.9 2.9Z"></path></svg></div>
                                <div class="ultp-lightbox__img-container">
                                    <img class="ultp-lightbox__img" src="${imgSrc}" />
                                    ${
																			lightboxCaption
																				? `<span class="ultp-lightbox__caption">${imgCaption}</span>`
																				: ""
																		}
                                </div>
                                <div class="ultp-lightbox__right-icon"><svg fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 49.16 37.25"><path  stroke-miterlimit="10" d="M28.1 36.154a2.048 2.048 0 0 1 0-2.9l12.5-12.5H2.55a2.05 2.05 0 1 1 0-4.1H40.6l-12.5-12.5a2.05 2.05 0 1 1 2.9-2.9l17.45 17.448L31 36.154a2.047 2.047 0 0 1-2.9 0Z"></path></svg></div>
                            </div>
                            ${
															lightboxIndicator
																? `<div class="ultp-lightbox-indicator">${indicatorHtml}</div>`
																: ""
														}
                        </div>
                    </div>`;
			appendSelector.append(galleryData);
		}
	}

	function handleGalleryLayout(element) {
		const $gallery = element;
		let loader = $gallery.find(".ultp-gallery-loader");
		let $noItems = $gallery.find(".ultp-no-gallery-message");
		const container = $gallery.find(".ultp-gallery-container");
		const filterItem = $gallery.find(".ultp-gallery-filter__item");
		const loadMoreButton = $gallery.find(".ultp-gallery-loadMore");
		const tiled = $gallery.find(".ultp-gallery-container.ultp-gallery-tiled");

		const masonry = $gallery.find(
			".ultp-gallery-container.ultp-gallery-masonry",
		);

		const columns = container[0]
			? Number(
					getComputedStyle(container[0])
						.getPropertyValue("--ultp-gallery-columns")
						.trim(),
				)
			: 3;
		const gap = container[0]
			? Number(
					getComputedStyle(container[0])
						.getPropertyValue("--ultp-gallery-gap")
						.trim(),
				)
			: 10;
		const galleryItems = container.find(".ultp-gallery-item");
		const loadMoreCount = loadMoreButton[0]
			? Number(
					getComputedStyle(loadMoreButton[0])
						.getPropertyValue("--ultp-gallery-count")
						.trim(),
				)
			: 0;

		let rootImgCount = loadMoreCount;

		// Add Loader if not present
		if (loader.length === 0 && (masonry?.length || tiled?.length)) {
			container.css({ height: "250px", overflow: "hidden" });
			loader =
				$(`<div class="ultp-gallery-loader" style="display:none;position:absolute;top:0;left:0;right:0;bottom:0;background:rgb(255 255 255 / 92%);z-index:9; display: flex; align-items:center;justify-content:center;">
                    <div class="spinner" style="border: 4px solid #f3f3f3;border-top: 4px solid #037FFF;border-radius: 50%;width: 30px;height: 30px;animation: spin 0.8s linear infinite;"></div>
                            </div>`);
			$gallery.css("position", "relative");
			container.append(loader);
		}

		// Inject keyframes for spinner if not already added
		if (!document.getElementById("ultp-spinner-style")) {
			const spinnerStyle = `<style id="ultp-spinner-style">
                    @keyframes spin {
                        0% { transform: rotate(0deg); }
                        100% { transform: rotate(360deg); }
                    }
                </style>`;
			$("head").append(spinnerStyle);
		}

		// Show loader initially
		loader.fadeIn(150);

		// Add No Gallery Message
		if ($noItems.length === 0) {
			$noItems = $(
				'<div class="ultp-no-gallery-message">No gallery item found</div>',
			);
			container.after($noItems);
		}

		let loadGallery = function () {};

		function reLayoutTiledGallery() {
			if (tiled.length === 0) return;
			const visibleItems = tiled.find(".ultp-gallery-item:visible");
			tiled.css("visibility", "hidden");
			visibleItems.each(function () {
				$(this).css({
					top: "",
					left: "",
					width: "",
					height: "",
					position: "",
				});
			});
			requestAnimationFrame(() => {
				loadGallery(visibleItems);
				tiled.css("visibility", "visible");
			});
		}

		function reLayoutMasonryGallery() {
			if (masonry.length === 0) return;
			const containerWidth = masonry.width();
			const itemWidth = (containerWidth - (columns - 1) * gap) / columns;
			const visibleItems = masonry.find(".ultp-gallery-item:visible");
			let columnHeights = new Array(columns).fill(0);
			visibleItems.each(function () {
				const $el = $(this);
				$el.css({ width: itemWidth + "px", position: "absolute" });
				const minCol = columnHeights.indexOf(Math.min(...columnHeights));
				const top = columnHeights[minCol];
				const left = minCol * (itemWidth + gap);
				$el.stop().animate({ top: `${top}px`, left: `${left}px` }, 300);
				columnHeights[minCol] += $el.outerHeight(true) + gap;
			});
			masonry.css("height", Math.max(...columnHeights) + "px");
		}

		function updateVisibleItemsByFilter(selectedTag, limit) {
			let imgCount = 0;
			let totalMatch = 0;
			galleryItems.each(function () {
				const $this = $(this);
				const tag = $this.data("tag") || "";
				if (selectedTag === "All" || tag.includes(selectedTag)) {
					totalMatch++;
					if (imgCount < limit) {
						$this.css({ display: "block" });
						imgCount++;
					} else {
						$this.css({ display: "none" });
					}
				} else {
					$this.css({ display: "none" });
				}
			});
			$noItems.toggle(totalMatch === 0);
			return { visibleCount: imgCount, totalMatch };
		}

		function showLoader(callback) {
			loader.fadeIn(150);
			setTimeout(() => {
				callback();
				loader.fadeOut(150);
			}, 400);
		}

		filterItem.on("click", function () {
			const item = $(this);
			const selectedTag = item.text();
			item.siblings().removeClass("active-gallery-filter");
			item.addClass("active-gallery-filter");

			rootImgCount = loadMoreCount;

			showLoader(() => {
				const { visibleCount, totalMatch } = updateVisibleItemsByFilter(
					selectedTag,
					rootImgCount,
				);
				loadMoreButton.css({
					display: visibleCount < totalMatch ? "block" : "none",
				});
				reLayoutTiledGallery();
				reLayoutMasonryGallery();
			});
		});

		loadMoreButton.on("click", function (e) {
			e.preventDefault();
			const activeTag = $gallery.find(".active-gallery-filter").text() || "All";

			let visibleCount = 0;
			let totalMatch = 0;

			galleryItems.each(function () {
				const $this = $(this);
				const tag = $this.data("tag") || "";
				if (activeTag === "All" || tag.includes(activeTag)) {
					totalMatch++;
					if ($this.is(":visible")) visibleCount++;
				}
			});

			const remainder = visibleCount % columns;
			let itemsToAdd = remainder !== 0 ? columns - remainder : columns;
			rootImgCount = Math.min(visibleCount + itemsToAdd, totalMatch);

			showLoader(() => {
				let currentVisible = 0;
				galleryItems.each(function () {
					const $this = $(this);
					const tag = $this.data("tag") || "";
					if (activeTag === "All" || tag.includes(activeTag)) {
						$this.css({
							display: currentVisible < rootImgCount ? "block" : "none",
						});
						currentVisible++;
					} else {
						$this.css({ display: "none" });
					}
				});

				if (rootImgCount >= totalMatch) {
					loadMoreButton.css({ display: "none" });
				}

				$noItems.toggle(totalMatch === 0);
				reLayoutTiledGallery();
				reLayoutMasonryGallery();
			});
		});

		if (tiled.length) {
			const customHeight = Number(
				getComputedStyle(container[0])
					.getPropertyValue("--ultp-gallery-height")
					.trim(),
			);

			const rowHeight = customHeight || 300; // FIX ME → the target rendered height

			/* Utility: robust aspect ratio (works before <img> finishes loading) */
			function getAspect($img) {
				const el = $img[0];
				const w = el.naturalWidth || el.width || 1;
				const h = el.naturalHeight || el.height || 1;
				return w / h;
			}

			/* Core layout function  */
			loadGallery = function (visibleItems) {
				const $container = tiled;
				const containerW =
					$container.width() || $container.closest(".ultp-tab-content").width();
				let cursorTop = 0;

				/* Prepare rows---- */
				const rows = [];
				let currentRow = [];
				let currentRatioSum = 0;

				visibleItems.each(function () {
					const $item = $(this);
					const $img = $item.find("img");
					const ratio = getAspect($img);

					currentRow.push({ $item, ratio });
					currentRatioSum += ratio;

					if (currentRow.length === columns) {
						rows.push({ cells: currentRow, ratioSum: currentRatioSum });
						currentRow = [];
						currentRatioSum = 0;
					}
				});

				/* last partial row (if any) */
				if (currentRow.length) {
					rows.push({ cells: currentRow, ratioSum: currentRatioSum });
				}

				/* Layout every row */
				rows.forEach((row) => {
					const gapsTotal = gap * (row.cells.length - 1);
					const availW = containerW - gapsTotal;

					let cursorLeft = 0;
					row.cells.forEach(({ $item, ratio }, idx) => {
						const cellW = availW * (ratio / row.ratioSum);

						$item.css({
							position: "absolute",
							top: cursorTop,
							left: cursorLeft,
							width: cellW,
							height: rowHeight, // keeps every image exactly this tall
						});

						cursorLeft += cellW + gap;
					});

					cursorTop += rowHeight + gap;
				});
				/* set container height once, after all items are positioned */
				$container.height(cursorTop - gap);
			};
		}

		setTimeout(() => {
			const initialFilter =
				$gallery
					.find(".ultp-gallery-filter__item.active-gallery-filter")
					.text() || "All";
			const { visibleCount, totalMatch } = updateVisibleItemsByFilter(
				initialFilter,
				rootImgCount,
			);
			loadMoreButton.css({
				display: visibleCount < totalMatch ? "block" : "none",
			});
			$noItems.toggle(totalMatch === 0);
			reLayoutTiledGallery();
			reLayoutMasonryGallery();
			loader.fadeOut(50);
		}, 500);
	}
})(jQuery);

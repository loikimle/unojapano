(function ($) {
	("use strict");

	$(document).on("click", ".ultp-video-icon", function () {
		const vid = $(this);
		let isAutoPlay =
			vid.attr("enableautoplay") === "true" ||
			vid.attr("enableautoplay") === "1";

		const parent = vid.parents(".ultp-block-item");
		const blockImage = vid.closest(".ultp-block-image");

		// Get settings from video icon attributes
		let enablePopup =
			vid.attr("enableVideoPopup") === "true" ||
			vid.attr("enableVideoPopup") === "1";

		// Fix: Get video content based on popup mode
		let videoContent;
		if (enablePopup) {
			// For popup, look for modal video content
			videoContent = parent.find(".ultp-video-modal .ultp-video-wrapper");
			if (videoContent.length === 0) {
				// Fallback to blockImage if modal not found
				videoContent = blockImage.find(
					"div.ultp-block-video-content .ultp-video-wrapper"
				);
			}
		} else {
			// For inline, use blockImage video content
			videoContent = blockImage.find(
				"div.ultp-block-video-content .ultp-video-wrapper"
			);
			blockImage.find("div.ultp-block-video-content").show();
		}

		// Check if video content exists
		if (videoContent.length === 0) {
			console.error("Video content wrapper not found");
			return;
		}

		const videoData = {
			url: videoContent.data("video-url"),
			id: videoContent.data("video-id"),
			type: videoContent.data("video-type"),
			autoplay: videoContent.data("autoplay"),
			loop: videoContent.data("loop"),
			mute: videoContent.data("mute"),
			controls: videoContent.data("controls"),
			preload: videoContent.data("preload"),
			poster: videoContent.data("poster"),
			playsinline: videoContent.data("playsinline"),
			width: videoContent.data("width"),
			height: videoContent.data("height"),
		};

		// Extract video ID if not available
		let videoId = videoData.id;
		if (!videoId && videoData.url) {
			switch (videoData.type) {
				case "youtube":
					const youtubeRegex =
						/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})/;
					const youtubeMatch = videoData.url.match(youtubeRegex);
					videoId = youtubeMatch ? youtubeMatch[1] : null;
					break;
				case "vimeo":
					const vimeoRegex =
						/vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|)(\d+)(?:$|\/|\?)/;
					const vimeoMatch = videoData.url.match(vimeoRegex);
					videoId = vimeoMatch ? vimeoMatch[vimeoMatch.length - 1] : null;
					break;
				case "local":
					videoId = "local";
					break;
			}
		}

		// Return early if no video ID found and it's not a local video
		if (!videoId && videoData.type !== "local") {
			console.error(`${videoData.type} video ID not found`);
			return;
		}

		// Fix: Clear existing video content before adding new
		videoContent.empty();

		// Generate iframe based on video type
		let embedHtml = "";

		switch (videoData.type) {
			case "youtube":
				const youtubeParams = new URLSearchParams({
					autoplay: isAutoPlay ? "1" : "0",
					loop: videoData.loop ? "1" : "0",
					mute: videoData.mute || isAutoPlay ? "1" : "0",
					controls: videoData.controls ? "1" : "0",
					playsinline: videoData.playsinline ? "1" : "0",
					modestbranding: "1",
					rel: "0",
				});

				if (videoData.loop && videoId) {
					youtubeParams.append("playlist", videoId);
				}

				embedHtml = `<iframe 
                    src="https://www.youtube.com/embed/${videoId}?${youtubeParams.toString()}" 
                    width="${videoData.width || "100%"}" 
                    height="${videoData.height || "315"}" 
                    frameborder="0" 
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                    allowfullscreen
                    loading="lazy">
                </iframe>`;
				break;

			case "vimeo":
				const vimeoParams = new URLSearchParams({
					autoplay: isAutoPlay ? "1" : "0",
					loop: videoData.loop ? "1" : "0",
					muted: videoData.mute || isAutoPlay ? "1" : "0",
					controls: videoData.controls ? "1" : "0",
					playsinline: videoData.playsinline ? "1" : "0",
					dnt: "1",
				});

				embedHtml = `<iframe 
                    src="https://player.vimeo.com/video/${videoId}?${vimeoParams.toString()}" 
                    width="${videoData.width || "100%"}" 
                    height="${videoData.height || "315"}" 
                    frameborder="0" 
                    allow="autoplay; fullscreen; picture-in-picture" 
                    allowfullscreen
                    loading="lazy">
                </iframe>`;
				break;

			case "local":
				const videoFormat = videoData.url
					.split(".")
					.pop()
					.toLowerCase()
					.split("?")[0];
				const formats = {
					mp4: "mp4",
					webm: "webm",
					ogg: "ogg",
					avi: "mp4",
					mov: "mp4",
				};
				const mimeType = formats[videoFormat] || "mp4";

				const attributes = [];
				if (isAutoPlay) {
					attributes.push("autoplay");
					attributes.push("muted");
				}
				if (videoData.loop) attributes.push("loop");
				if (videoData.controls) attributes.push("controls");
				if (videoData.playsinline) attributes.push("playsinline");
				if (videoData.poster) attributes.push(`poster="${videoData.poster}"`);
				if (videoData.preload)
					attributes.push(`preload="${videoData.preload}"`);

				embedHtml = `<video ${attributes.join(" ")} 
                    width="${videoData.width || "100%"}" 
                    height="${videoData.height || "auto"}" 
                    class="ultp-video-html">
                    <source src="${videoData.url}" type="video/${mimeType}">
                    <p>Your browser does not support the video tag. 
                        <a href="${
													videoData.url
												}" target="_blank">Download the video</a>
                    </p>
                </video>`;
				break;

			default:
				console.error(`Unsupported video type: ${videoData.type}`);
				return;
		}

		// Return early if no embed HTML generated
		if (!embedHtml) {
			console.error("Failed to generate video embed");
			return;
		}

		// Append the generated embed HTML
		videoContent.html(embedHtml);

		// Handle popup vs inline display
		if (enablePopup) {
			// Fix: Show video in modal popup
			const modal = parent.find(".ultp-video-modal");
			if (modal.length > 0) {
				modal.addClass("modal_active");

				// Fix: Show modal content and hide loader
				modal.find(".ultp-video-modal__content").show();
				$(".ultp-loader-container").hide();

				// Fix: Focus management for accessibility
				modal.attr("tabindex", "-1").focus();
			} else {
				console.error("Video modal not found");
				return;
			}
		} else {
			// Inline video display without popup
			blockImage.find("> a img").hide();
			videoContent.parent().css({ display: "block" });
			vid.hide();
		}

		// Auto-play handling for local videos
		const videoElement = videoContent.find("video");
		if (isAutoPlay && videoElement.length > 0) {
			setTimeout(() => {
				videoElement[0]
					.play()
					.catch((e) => console.log("Autoplay prevented:", e));
			}, 500);
		}

		// Hide loader when video loads
		const allVideoElements = videoContent.find("iframe, video");
		if (allVideoElements.length) {
			allVideoElements.on("load loadeddata canplay", function () {
				$(".ultp-loader-container").hide();
			});
		}
	});

	// Fix: Close modal on click (improved)
	$(document).on("click", ".ultp-video-close, .ultp-video-modal", function (e) {
		// Only close if clicking the close button or modal backdrop
		if ($(this).hasClass("ultp-video-close") || e.target === this) {
			closeVideoModal();
		}
	});

	// Prevent modal close when clicking modal content
	$(document).on("click", ".ultp-video-modal__content", function (e) {
		e.stopPropagation();
	});

	// Close modal on escape key
	$(document).on("keyup", function (e) {
		if (e.key === "Escape" || e.keyCode === 27) {
			closeVideoModal();
		}
	});

	// Fix: Improved close modal function
	function closeVideoModal() {
		const activeModal = $(".ultp-video-modal.modal_active");
		if (activeModal.length > 0) {
			const videoIframe = activeModal.find("iframe");
			const videoElement = activeModal.find("video");

			// Stop iframe videos
			if (videoIframe.length) {
				videoIframe.each(function () {
					const iframe = $(this);
					const videoSrc = iframe.attr("src");
					if (videoSrc) {
						// Force stop by removing and re-adding src
						iframe.attr("src", "");
						setTimeout(() => {
							iframe.remove();
						}, 100);
					}
				});
			}

			// Stop HTML5 videos
			if (videoElement.length) {
				videoElement.each(function () {
					this.pause();
					this.currentTime = 0;
				});
			}

			// Hide modal and clear content
			activeModal.removeClass("modal_active");
			activeModal.find(".ultp-video-wrapper").empty();

			// Return focus to the video icon
			activeModal.closest(".ultp-block-item").find(".ultp-video-icon").focus();
		}
	}

	// ...existing code...
})(jQuery);

/**
 * Image gallery based on jQuery.Justified-Gallery
 * @link https://github.com/miromannino/Justified-Gallery/
 */
marknotes.arrPluginsFct.push("fnPluginHTMLImageGallery");

function fnPluginHTMLImageGallery() {

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('      Plugin Page html - image_gallery');
	}
	/*<!-- endbuild -->*/

	if ($.isFunction($.fn.justifiedGallery)) {
		try {
			if ($("#image_gallery").length > 0) {
				$("#image_gallery").justifiedGallery();

				// Capture clicks on images (i.e. anchor)
				$('#image_gallery a').click(function (e) {
					e.preventDefault();

					// Does the modal already loaded ?
					if (!$('#Modal_ImgGallery').length) {
						// No, load it
						loadModal(this);
					} else {
						// Yes, immediatly show the image in the modal
						showImageFromThumbnail(this);
					}
				});
			}
		} catch (e) {
			/*<!-- build:debug -->*/
			if (marknotes.settings.debug) {
				console.warn(err.message);
			}
			/*<!-- endbuild -->*/
		}

	}
}

/**
 * Call index.php?task=task.image_gallery.getmodal to get the HTML
 * code for the modal window. When loaded, the function will call
 * the showImageFromThumbnail() function to display the image in
 * the modal
 */
function loadModal(obj) {

	// Retrieve the HTML for the modal form
	if (!$('#Modal_ImgGallery').length) {
		$.ajax({
			type: "POST",
			url: marknotes.webroot + "index.php",
			data: "task=task.image_gallery.getmodal",
			dataType: "json",
			success: function (data) {
				if (data.hasOwnProperty("form")) {
					// The getmodal task return a JSON string : the modal HTML.
					$("#CONTENT").append(data['form']);
					$('#btnImgGalleryClose').click(function (e) {
						$('#Modal_ImgGallery').toggleClass('show').toggleClass('fade');
					});

					// And show the image
					showImageFromThumbnail(obj);

				} else {
					/*<!-- build:debug -->*/
					if (marknotes.settings.debug) {
						console.error('      Invalid JSON returned by the task.image_gallery.getmodal');
					}
					/*<!-- endbuild -->*/
				}
			}
		});
	}

	return true;
}

/**
 * Display the image in the modal.
 */
function showImageFromThumbnail(obj) {

	imgSrc = $(obj).attr("href");

	// The link has a data-modal attribute.
	// 1 = open in a modal  -  0 = open in a new tab
	modal = $(obj).data('modal');

	if (modal == '1') {
		imgTitle = $(obj).attr("title");
		$('#imgGallery').attr('src', imgSrc);
		$('#imgTitle').text(imgTitle);
		$('#Modal_ImgGallery').toggleClass('show').toggleClass('fade');
	} else {
		window.open(imgSrc);
	}

	return true;

}

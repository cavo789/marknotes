$("document")
	.ready(function () {

		// Be sure that images are not bigger than the max size (class fullimg)
		// And add special class : hidden-xs and hidden-sm so images are hidden on smartphones

		$("img").addClass("hidden-xs hidden-sm");

		// Check if, thanks to att(), a width or a height has been specified (forced)
		// If no, and this is the default, give the fullimg class to the image
		$('img').each(function () {

			var $height = $(this).attr("height");
			var $width = $(this).attr("width");

			if (($height == undefined) && ($width == undefined)) {
				$(this).addClass("fullimg");
				console.log('Add class fullimg');
			}
		});

	});

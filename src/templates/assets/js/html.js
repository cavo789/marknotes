$("document")
	.ready(function () {

		// Be sure that images are not bigger than the max size (class fullimg)
		// And add special class : hidden-xs and hidden-sm so images are hidden on smartphones

		$("img")
			.addClass("fullimg hidden-xs hidden-sm");

	});

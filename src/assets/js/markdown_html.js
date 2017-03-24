$("document")
	.ready(function () {

		// Be sure that images are not bigger than the max size (class fullimg)
		// And add special class : hidden-xs and hidden-sm so images are hidden on smartphones

		$("img")
			.addClass("fullimg hidden-xs hidden-sm");

		//addTOC();

	});

/**
 * Add a table of content with a link to every headings 2
 */
function addTOC() {

	/*@url : https://css-tricks.com/automatic-table-of-contents/ */

	if ($("article h2")
		.length > 0) {

		var ToC =
			"<nav role='navigation' class='table-of-contents hidden-xs hidden-sm'>" +
			"<h2>Sur cette page:</h2>" +
			"<ul>";

		var newLine, el, title, link;
		$("article h2")
			.each(function () {

				el = $(this);
				title = el.text();
				link = "#" + el.attr("id");

				newLine = "<li><a href='" + link + "'>" + title + "</a></li>";

				ToC += newLine;

			});

		ToC += "</ul></nav>";

		$("article")
			.prepend(ToC);
	}

} // function addTOC()

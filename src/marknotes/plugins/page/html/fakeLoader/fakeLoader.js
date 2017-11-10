// @link https://github.com/joaopereirawd/fakeLoader.js

function showAfterFakeLoading() {
	$(".wrapper").removeClass("hidden");
	//		$("header").removeClass("hidden");
};

$(document).ready(function () {

	setTimeout(showAfterFakeLoading, marknotes.fakeLoader.timeToHide);

	$("body").append("<div class='fakeloader'></div>");

	try {
		$(".fakeloader").fakeLoader({
			timeToHide: marknotes.fakeLoader.timeToHide,
			bgColor: marknotes.fakeLoader.bgColor,
			spinner: marknotes.fakeLoader.spinner
		});

	} catch (e) {} finally {}
});

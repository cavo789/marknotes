var $oldBackground = '';
var $menuWidth = '';

// More info https://github.com/hakimel/reveal.js#configuration

var URL_Lib = marknotes.root + 'marknotes/plugins/page/html/reveal/libs/reveal.js/';

var $arrThemes = [];

// Get the list of available themes.
// marknotes.slideshow.themes is initialized by the plugin
// page/html/reveal.php with the list of available themes in reveal.
if (typeof marknotes.slideshow.themes !== 'undefined') {
	var arr = marknotes.slideshow.themes.split(",");

	arr.forEach(function($theme) {
	  $arrThemes.push({
		  name: $theme,
		  theme: marknotes.root + marknotes.slideshow.themes_folder + $theme +'.css'
	  });
	});
}

// ----------------------------------
// Initialize the list of dependencies
var $arrDependencies = [];

$arrDependencies.push({
	src: URL_Lib + 'lib/js/title-footer.js',
	condition: function () {
		return document.querySelector('title-footer');
	}
});

$arrDependencies.push({
	src: URL_Lib + 'lib/js/classList.js',
	condition: function () {
		return !document.body.classList;
	}
});

$arrDependencies.push({
	src: URL_Lib + 'plugin/markdown/marked.js',
	condition: function () {
		return !!document.querySelector('[data-markdown]');
	}
});

$arrDependencies.push({
	src: URL_Lib + 'plugin/markdown/markdown.js',
	condition: function () {
		return !!document.querySelector('[data-markdown]');
	}
});

// Get the list of dependencies that should be loaded.
// marknotes.slideshow.dependencies is initialized by the plugin
// page/html/reveal.php
if (typeof marknotes.slideshow.dependencies !== 'undefined') {
	var arr = marknotes.slideshow.dependencies.split(",");
	arr.forEach(function($src) {
		$arrDependencies.push({
			src: URL_Lib + $src,
			async: true
		//condition: true,
		});
	});
}
//
// ----------------------------------

var $sActions = "";

// Check if pandoc is installed. marknotes.slideshow.pandoc
// is initialized by the plugin page/html/reveal.php
if (typeof marknotes.slideshow.pandoc !== 'undefined') {

	// If pandoc is installed, add the PDF, DOCX and TXT formats
	if (marknotes.slideshow.pandoc === 1) {

		$sActions =
			'<li class="slide-menu-item">' +
			'<a href="'+marknotes.note.url_noext+'.reveal.pdf">'+
			'Export a PDF version (dangerously slow!!!)</a>' +
			'</li>' +
			'<li class="slide-menu-item">' +
			'<a href="'+marknotes.note.url_noext+'.txt">'+
			'Get the TXT version</a>' +
			'</li>' +
			'<li class="slide-menu-item">' +
			'<a href="'+marknotes.note.url_noext+'.docx">'+
			'Get the DOCX version</a>' +
			'</li>';
	}
}

Reveal.initialize({
	history: true,
	controls: true,
	progress: true,
	// display the current slide number / total slides number
	slideNumber: 'c/t',
	mouseWheel: true,
	transition: 'convex',
	backgroundTransition: 'concave',

	// The presentation, by default, is foreseen for 45 minutes.
	// This can be changed in the settings.json file (should
	// be allocattedTime but there is a spelling faut in the
	// plugin .js file)
	allottedTime: ((typeof marknotes.slideshow === 'undefined') ? 45 : marknotes.slideshow.durationMinutes) * 60 * 1000,

	// Height of the ElapsedTimeBar (3 pixels). This can be changed
	// in the settings.json file
	progressBarHeight: ((typeof marknotes.slideshow === 'undefined') ? 3 : marknotes.slideshow.durationBarHeight),

	// See https://github.com/denehyg/reveal.js-menu for options
	menu: {
		// Don't show slides in the table-of-contents without
		// titles i.e. without H1->H6
		hideMissingTitles: true,
		// Add markers to the slide titles to indicate the
		// progress through the presentation
		markers: true,
		loadIcons: false,
		themes : $arrThemes,
		custom: [
			{
				title: 'About',
				icon: '<i class="fa fa-info">',
				content: '<p>This slidedeck is created with '+
					'marknotes and is based on reveal</p>' +
					'<h4>External Links</h4>' +
					'<ul class="slide-menu-items">' +
					'<li class="slide-menu-item">' +
					'<a href="https://github.com/cavo789/marknotes" target="_blank" rel="noopener">marknotes</a>' +
					'</li>' +
					'<li class="slide-menu-item">' +
					'<a href="https://github.com/hakimel/reveal.js" target="_blank" rel="noopener">Reveal.js</a>' +
					'</li>' +
					'</ul>'
			},
			{
				title: 'Actions',
				icon: '<i class="fa fa-cog">',
				content:
					'<ul id="mn_actions" class="slide-menu-items">' +
					'<li class="slide-menu-item">' +
					'<a href="'+marknotes.note.url_noext+'.html">Get the HTML version</a>' +
					'</li>' +
					$sActions +
					'</ul>'
			}
		]
	},
	// More info https://github.com/hakimel/reveal.js#dependencies
	dependencies: $arrDependencies

});

Reveal.addEventListener('ready', function (event) {
	// initialize the variable so we can restore the background
	// when the user will "rewind" the presentation
	$oldBackground = $('body').css('background-image');
});

/*
 * Add a listener that will capture the current slide number
 * (given by evt.indexh).
 * Except on the very first slide, background, logos, ...
 * will be removed so the attendance won't be disturb by
 * unneeded visual content and will only see valuable
 * informations : the slide content.
 */
Reveal.addEventListener('slidechanged', function (evt) {

	var $hide = (typeof marknotes.slideshow === 'undefined') ? 0 : marknotes.slideshow.hideunnecessarythings;

	/**
	 * Depending on the setting, hide every "unneeded things" on
	 * the slides as from slide #2
	 * Unneeded things are for instance logos, background image, ...
	 * So give the maximum space to the content and reduce the
	 * "screen" pollution
	 */

	if ($hide === 1) {

		// slide-menu is a reveal plugin, not yet initialized
		// during the 'ready' event of Reveal since the loading
		// is asynchrone.
		if ($menuWidth === '') {
			$menuWidth = $('.slide-menu-button').css('font-size');
		}

		if (evt.indexh > 0) {

			// Remove the background image during the presentation
			// but not on the very first slide
			$('body').css('background-image', 'none');

			if ($('#title-footer').length > 0) {
				$('#title-footer').hide();
			}

			// Logos should be visible only on the first slide
			if ($('.logo').length > 0) {
				$('.logo').hide();
			}

			// Reduce the size of the controls (left, right, top, bottom)
			// and be sure to make them visible (not under the number of
			// the current slide)
			if ($('.controls').length > 0) {
				$('.controls').css('font-size', '5px').css('margin-bottom', '15px');
			}

			// If the menu-reveal.js plugin has been loaded,
			// decrease his size.
			// No need to have a big button on each slides
			if ($('.reveal .slide-menu-button').length > 0) {
				$('.reveal .slide-menu-button').css('font-size', '10px');
			}

			// Hide the google translate plugin if needed
			if ($('#google_translate_element').length > 0) {
				$('#google_translate_element').hide();
			}

			// Hide the Google translate top menu bar
			if ($('.skiptranslate').length > 0) {
				$('.skiptranslate').hide();
			}

		} else {

			// The very first slide is displayed background
			// Restore the background image and the height of the menu
			// $('body').css('background-image', $oldBackground);

			if ($('.logo').length > 0) {
				$('.logo').show();
			}

			if ($('.controls').length > 0) {
				$('.controls').show();
			}

			if ($('.reveal .slide-menu-button').length > 0) {
				$('.reveal .slide-menu-button').css('font-size', $menuWidth);
			}

			// Restore the google translate plugin
			if ($('#google_translate_element').length > 0) {
				$('#google_translate_element').show();
			}

			// Restore the Google translate top menu bar
			if ($('.skiptranslate').length > 0) {
				$('.skiptranslate').show();
			}

			if ($('#title-footer').length > 0) {
				$('#title-footer').show();
			}
		}
	} // if ($hide===1)

});

/* Title-footer plugin : initialize the footer title */
Reveal.addEventListener('ready', function(evt) {
	if (evt.indexh === 0 && evt.indexv === 0) {
		pushHelpNotification();
	} else {
		pushFootnoteNotification();
	}
});

/* Title-footer plugin : adjust the footer text when showing a new slide */
Reveal.addEventListener('slidechanged', function(evt) {
	if (evt.indexh === 0 && evt.indexv === 0) {
		// First slide, show help like how to navigate
		pushHelpNotification();
	} else {
		// As from the second slide
		pushFootnoteNotification();
	}
});

/* Title-footer plugin */
function pushNotification(msg, fade) {

	var footer = document.getElementById('title-footer')
	var notification = document.getElementById('notification')

	if(window.innerWidth < 700) {
		// Not large enough, hide the footer
		footer.className = "footer-hidden";
	} else {
		footer.className = "footer-visible";
		if(fade) {
			notification.className = "footer-fade";
			notification.innerHTML = msg;
		} else {
			notification.className = "footer-hard";
			notification.innerHTML = msg;
		}
	}
}

/* Title-footer plugin */
function pushHelpNotification() {
	if(window.innerWidth < 700)
		pushNotification("<a href='#' onclick='RevealMenu.toggle()'>Menu</a> | <a href='#' onclick='enterFullscreen()'>Fullscreen</a> | <a href='#' onclick='enterOverview()'>Overview</a> | <a href='#' onclick='enterBlackout()'>Blackout</a> | <a href='#' onclick='RevealNotes.open()'>Speaker</a> | <a href='#' onclick='enterHelp()'>Help</a>");
	else
		pushNotification("Navigate : Space / Arrow Keys | <a href='#' onclick='RevealMenu.toggle()'>M</a> - Menu | <a href='#' onclick='enterFullscreen()'>F</a> - Fullscreen | <a href='#' onclick='enterOverview()'>O</a> - Overview | <a href='#' onclick='enterBlackout()'>B</a> - Blackout | <a href='#' onclick='RevealNotes.open()'>S</a> - Speaker | <a href='#' onclick='enterHelp()'>?</a> - Help");
}

/* Title-footer plugin */
function pushFootnoteNotification() {
	/* As from the second slide, retrieve the page title and display it in the footer */
	var $title = $(document).find("title").text();
	pushNotification($title, true);
}

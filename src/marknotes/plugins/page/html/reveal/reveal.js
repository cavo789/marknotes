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
		  theme: marknotes.root + 'templates/assets/css/' + $theme +'.css'
	  });
	});
}

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
					'<a href="https://github.com/cavo789/marknotes" target="_blank">marknotes</a>' +
					'</li>' +
					'<li class="slide-menu-item">' +
					'<a href="https: //github.com/hakimel/reveal.js" target="_blank">Reveal.js</a>' +
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

	dependencies: [
		{
			src: URL_Lib + 'lib/js/classList.js',
			condition: function () {
				return !document.body.classList;
			}
		},
		{
			src: URL_Lib + 'plugin/markdown/marked.js',
			condition: function () {
				return !!document.querySelector('[data-markdown]');
			}
		},
		{
			src: URL_Lib + 'plugin/markdown/markdown.js',
			condition: function () {
				return !!document.querySelector('[data-markdown]');
			}
		},
		{
			src: URL_Lib + 'plugin/zoom-js/zoom.js',
			async: true
		},
		{
			src: URL_Lib + 'plugin/notes/notes.js',
			async: true
		},
		/*{	Add a search box on the top left of any slides
			src: marknotes.root + 'libs/reveal.js/plugin/search/search.js',
			async: true
		},*/
		{ // Add a bottom left menu icon to let the user to access to a table-of-contents
			src: URL_Lib + 'plugin/reveal.js-menu/menu.js'
		},
		/*{ // Add a footer on any slide and display the presentation H1 title
			src: marknotes.root + 'libs/reveal.js/plugin/title-footer/title-footer.js',
			async: true,
			callback: function () {
				title_footer.initialize();
			}
		},*/
		{ // Add an ElapsedTimeBar on the bottom (by default a red line)
			src: URL_Lib + 'plugin/elapsed-time-bar/elapsed-time-bar.js'
		}
	]
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
			// $('body').css('background-image', 'none');

			// Logos should be visible only on the first slide
			if ($('.logo').length > 0) {
				$('.logo').hide();
			}

			// No need to display the navigation controls button
			// during the slideshow
			if ($('.controls').length > 0) {
				$('.controls').hide();
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
		}
	} // if ($hide===1)

});

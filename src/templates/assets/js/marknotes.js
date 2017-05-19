var $oldBackground = '';
var $menuWidth = '';

$(document).ready(function () {

	var $currentURL = location.protocol + '//' + location.host;

	$('a[href^="http:"], a[href^="https:"]')
		.not('[href^="' + $currentURL + '/"]')
		.attr('target', '_blank');

	// Last part : the array marknotes.arrPluginsFct is a global array and will be initialized by
	// the differents plugins (like Bootstrap, DataTable, ...) and will contains functions name.
	//
	// For instance : the file /marknotes/plugins/content/html/bootstrap/bootstrap.js contains this line :
	// marknotes.arrPluginsFct.push("PluginBootstrap");
	//
	// This to tell to this code that the PluginBootstrap function should be fired once the note
	// is displayd.  So, let's do it

	try {
		for (var i = 0, len = marknotes.arrPluginsFct.length; i < len; i++) {
			// As explained here : https://www.sitepoint.com/call-javascript-function-string-without-using-eval/
			fn = window[marknotes.arrPluginsFct[i]];

			if (typeof fn === "function") fn();

		}
	} catch (err) {
		console.warn(err.message);
	}

});

// More info https://github.com/hakimel/reveal.js#configuration
Reveal.initialize({
	history: true,
	controls: true,
	progress: true,
	slideNumber: 'c/t', // display the current slide number / total slides number
	mouseWheel: true,
	transition: 'convex',
	backgroundTransition: 'concave',

	// The presentation, by default, is foreseen for 45 minutes.
	// This can be changed in the settings.json file (should be allocattedTime but there
	// is a spelling faut in the plugin .js file)
	allottedTime: ((typeof marknotes.slideshow === 'undefined') ? 45 : marknotes.slideshow.durationMinutes) * 60 * 1000,

	// Height of the ElapsedTimeBar (3 pixels). This can be changed in the settings.json file
	progressBarHeight: ((typeof marknotes.slideshow === 'undefined') ? 3 : marknotes.slideshow.durationBarHeight),

	// See https://github.com/denehyg/reveal.js-menu for options
	menu: {
		// Don't show slides in the table-of-contents without titles i.e. without H1->H6
		hideMissingTitles: true,
		// Add markers to the slide titles to indicate the progress through the presentation
		markers: true,
		custom: [
			{
				title: 'About',
				icon: '<i class="fa fa-info">',
				content: '<p>This slidedeck is created with marknotes and is based on reveal</p>' +
					'<h4>External Links</h4>' +
					'<ul class="slide-menu-items">' +
					'<li class="slide-menu-item">' +
					'<a href="https://github.com/cavo789/marknotes" target="_blank">marknotes</a>' +
					'</li>' +
					'<li class="slide-menu-item">' +
					'<a href="https: //github.com/hakimel/reveal.js" target="_blank">Reveal.js</a>' +
					'</li>' +
					'</ul>'
			}
		],
		themes: [
			/*{
				name: 'JD17FR_Contrast',
				theme: marknotes.root + 'templates/assets/jd17fr/jd17fr_contrast.css'
			},*/
			{
				name: 'Black',
				theme: marknotes.root + 'libs/reveal/css/theme/black.css'
			},
			{
				name: 'White',
				theme: marknotes.root + 'libs/reveal/css/theme/white.css'
			},
			{
				name: 'League',
				theme: marknotes.root + 'libs/reveal/css/theme/league.css'
			},
			{
				name: 'Sky',
				theme: marknotes.root + 'libs/reveal/css/theme/sky.css'
			},
			{
				name: 'Beige',
				theme: marknotes.root + 'libs/reveal/css/theme/beige.css'
			},
			{
				name: 'Simple',
				theme: marknotes.root + 'libs/reveal/css/theme/simple.css'
			},
			{
				name: 'Serif',
				theme: marknotes.root + 'libs/reveal/css/theme/serif.css'
			},
			{
				name: 'Blood',
				theme: marknotes.root + 'libs/reveal/css/theme/blood.css'
			},
			{
				name: 'Night',
				theme: marknotes.root + 'libs/reveal/css/theme/night.css'
			},
			{
				name: 'Moon',
				theme: marknotes.root + 'libs/reveal/css/theme/moon.css'
			},
			{
				name: 'Solarized',
				theme: marknotes.root + 'libs/reveal/css/theme/solarized.css'
			}
		],
	},
	// More info https://github.com/hakimel/reveal.js#dependencies

	dependencies: [{
			src: marknotes.root + 'libs/reveal/lib/js/classList.js',
			condition: function () {
				return !document.body.classList;
			}
		},
		{
			src: marknotes.root + 'libs/reveal/plugin/markdown/marked.js',
			condition: function () {
				return !!document.querySelector('[data-markdown]');
			}
		},
		{
			src: marknotes.root + 'libs/reveal/plugin/markdown/markdown.js',
			condition: function () {
				return !!document.querySelector('[data-markdown]');
			}
		},
		{
			src: marknotes.root + 'libs/reveal/plugin/highlight/highlight.js',
			async: true,
			callback: function () {
				hljs.initHighlightingOnLoad();
			}
		},
		{
			src: marknotes.root + 'libs/reveal/plugin/zoom-js/zoom.js',
			async: true
		},
		{
			src: marknotes.root + 'libs/reveal/plugin/notes/notes.js',
			async: true
		},
		/*{   Add a search box on the top left of any slides
			src: marknotes.root + 'libs/reveal/plugin/search/search.js',
			async: true
		},*/
		{ // Add a bottom left menu icon to let the user to access to a table-of-contents
			src: marknotes.root + 'libs/reveal/plugin/reveal.js-menu/menu.js'
		},
		/*{ // Add a footer on any slide and display the presentation H1 title
			src: marknotes.root + 'libs/reveal/plugin/title-footer/title-footer.js',
			async: true,
			callback: function () {
				title_footer.initialize();
			}
		},*/
		{ // Add an ElapsedTimeBar on the bottom (by default a red line)
			src: marknotes.root + 'libs/reveal/plugin/elapsed-time-bar/elapsed-time-bar.js'
		}
	]

});

Reveal.addEventListener('ready', function (event) {

	// initialize the variable so we can restore the background
	// when the user will "rewind" the presentation
	$oldBackground = $('body').css('background-image');
});

/*
 * Add a listener that will capture the current slide number (given by evt.indexh).
 * Except on the very first slide, background, logos, ... will be removed so the attendance
 * won't be disturb by unneeded visual content and will only see valuable informations : the slide content.
 */

Reveal.addEventListener('slidechanged', function (evt) {

	var $hide = (typeof marknotes.slideshow === 'undefined') ? 0 : marknotes.slideshow.hideunnecessarythings;

	// Depending on the setting, hide every "unneeded things" on the slides as from slide #2
	// Unneeded things are for instance logos, background image, ...
	// So give the maximum space to the content and reduce the "screen" pollution

	if ($hide === 1) {

		// slide-menu is a reveal plugin, not yet initialized during the 'ready' event of Reveal
		// since the loading is asynchrone.

		if ($menuWidth === '') {
			$menuWidth = $('.slide-menu-button').css('font-size');
		}

		if (evt.indexh > 0) {

			// Remove the background image during the presentation but not on the very first slide
			//$('body').css('background-image', 'none');

			// Logos should be visible only on the first slide
			if ($('.logo').length > 0) {
				$('.logo').hide();
			}

			// No need to display the navigation controls button during the slideshow
			if ($('.controls').length > 0) {
				$('.controls').hide();
			}

			// If the menu-reveal.js plugin has been loaded, decrease his size
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
			//$('body').css('background-image', $oldBackground);

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

$(document).ready(function () {

	var $currentURL = location.protocol + '//' + location.host;

	$('a[href^="http:"], a[href^="https:"]')
		.not('[href^="' + $currentURL + '/"]')
		.attr('target', '_blank');

	// Last part : the array marknotes.arrPluginsFct is a global array and will be initialized by
	// the differents plugins (like Bootstrap, DataTable, ...) and will contains functions name.
	//
	// For instance : the file /plugins/content/html/bootstrap/bootstrap.js contains this line :
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
	// This can be changed in the settings.json file
	allottedTime: ((typeof marknotes.slideshow === 'undefined') ? 45 : marknotes.slideshow.durationMinutes) * 60 * 1000,

	// Height of the ElapsedTimeBar (3 pixels). This can be changed in the settings.json file
	progressBarHeight: ((typeof marknotes.slideshow === 'undefined') ? 3 : marknotes.slideshow.durationBarHeight),
	keyboard: {
		// By pressing the Enter key, the ElapsedTimeBar will be paused
		13: () => {
			ElapsedTimeBar.isPaused ? ElapsedTimeBar.resume() : ElapsedTimeBar.pause();
		},
		// By pressing the "r" key, the ElapsedTimeBar will be resetted
		82: () => {
			ElapsedTimeBar.reset();
		}
	},

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
		{ // Add a footer on any slide and display the presentation H1 title
			src: marknotes.root + 'libs/reveal/plugin/title-footer/title-footer.js',
			async: true,
			callback: function () {
				title_footer.initialize();
			}
		},
		{ // Add an ElapsedTimeBar on the bottom (by default a red line)
			src: marknotes.root + 'libs/reveal/plugin/elapsed-time-bar/elapsed-time-bar.js'
		}
	]

});

Reveal.addEventListener('ready', function (event) {
	var isMobileDevice = /(iphone|ipod|ipad|android)/gi.test(navigator.userAgent);

	if (!isMobileDevice) {
		var $objDOM = document.getElementById('logo');
		if ($objDOM !== null) $objDOM.style.display = 'inline';
		$objDOM = document.getElementById('logo2');
		if ($objDOM !== null) $objDOM.style.display = 'inline';
	}

});

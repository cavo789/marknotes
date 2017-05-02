$(document)
	.ready(function () {

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
	slideNumber: true,
	mouseWheel: true,
	transition: 'convex',
	backgroundTransition: 'concave',

	// your allotted time for presentation
	allottedTime: marknotes.slideshow.durationMinutes * 60 * 1000,
	progressBarHeight: marknotes.slideshow.durationBarHeight,
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

	// Parallax background image
	//parallaxBackgroundImage: 'img/background.png',
	//parallaxBackgroundSize: '1917px 1080px',

	// Number of pixels to move the parallax background per slide
	// - Calculated automatically unless specified
	// - Set to 0 to disable movement along an axis
	//parallaxBackgroundHorizontal: 0,
	//parallaxBackgroundVertical: 0,

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
		{
			src: marknotes.root + 'libs/reveal/plugin/reveal.js-menu/menu.js'
		},
		{
			src: marknotes.root + 'libs/reveal/plugin/title-footer/title-footer.js',
			async: true,
			callback: function () {
				title_footer.initialize();
			}
		},
		{
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

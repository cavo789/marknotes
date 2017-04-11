$(document).ready(function () {

	var $currentURL = location.protocol + '//' + location.host;

	$('a[href^="http:"], a[href^="https:"]')
		.not('[href^="' + $currentURL + '/"]')
		.attr('target', '_blank');

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

	// Parallax background image
	//parallaxBackgroundImage: 'img/background.png',
	//parallaxBackgroundSize: '1917px 1080px',

	// Number of pixels to move the parallax background per slide
	// - Calculated automatically unless specified
	// - Set to 0 to disable movement along an axis
	//parallaxBackgroundHorizontal: 0,
	//parallaxBackgroundVertical: 0,

	// More info https://github.com/hakimel/reveal.js#dependencies

	dependencies: [
		{
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

<?php
// @codingStandardsIgnoreFile
?>
<!--
   based on reveal.js : https://github.com/hakimel/reveal.js
-->
<!DOCTYPE HTML>
<html lang="fr-fr" dir="ltr">

    <head>

		<meta charset="utf-8"/>
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta name="robots" content="%ROBOTS%" />
		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=9; IE=8;" />
		<meta name="author" content="Markdown | Notes management" />
		<meta name="designer" content="Markdown | Notes management" />
		<meta name="keywords" content="%TITLE%" />
		<meta name="description" content="%TITLE%" />
		<meta name="apple-mobile-web-app-capable" content="yes" />
		<meta name="apple-mobile-web-app-status-bar-style" content="black" />
		<meta property="og:url" content="%URL_PAGE%" />
		<meta property="og:type" content="article" />
		<meta property="og:image" content="%URL_IMG%" />
		<meta property="og:image:width" content="1200" />
		<meta property="og:image:height" content="522" />
		<meta property="og:title" content="%TITLE%" />
		<meta property="og:site_name" content="%SITE_NAME%" />
		<meta property="og:description" content="%TITLE%" />

        <title>%TITLE%</title>

        <link rel="stylesheet" type="text/css" href="%ROOT%/libs/reveal/css/reveal.css" media="screen" />
		<link rel="stylesheet" type="text/css" href="%ROOT%/libs/reveal/css/theme/beige.css" id="theme" media="screen" />
		<link rel="stylesheet" type="text/css" href="%ROOT%/libs/font-awesome/css/font-awesome.min.css" media="screen" />

        <!-- Theme used for syntax highlighting of code -->
        <link rel="stylesheet" href="%ROOT%/libs/reveal/lib/css/zenburn.css">

        <style>
            .reveal .controls .navigate-left,
            .reveal .controls .navigate-left.enabled {
                border-right-color: #B9312C;
            }

            .reveal .controls .navigate-right,
            .reveal .controls .navigate-right.enabled {
                border-left-color: #B9312C;
            }

            .reveal .controls .navigate-up,
            .reveal .controls .navigate-up.enabled {
                border-bottom-color: #B9312C;
            }

            .reveal .controls .navigate-down,
            .reveal .controls .navigate-down.enabled {
                border-top-color: #B9312C;
            }

            /* The bullet will be added thanks to Fontawesome */
            .reveal ul {
                list-style: none;
            }

            #footer{position:absolute;z-index:10;font-size:0.8em;color:white;right:0;bottom:0;left:0;padding:1em;background-color:rgba(185, 49, 44, 0.52);}
            #footer a {color:white;}

            strong {background-color:green;color:white;}
            .blink {animation: blink 1s steps(5, start) infinite; -webkit-animation: blink 1s steps(5, start) infinite;} @keyframes blink {to {visibility: hidden;}} @-webkit-keyframes blink {to {visibility: hidden;}}
section>.frame{margin:25px;padding:25px;background-color:#5bc0de;border:4px dashed #204d74;color:white;font-size:1.1em;font-weight:bolder;border-radius: 100px;}
        </style>

        <!-- Printing and PDF exports -->
        <script>
            var link = document.createElement('link');
            link.rel = 'stylesheet';
            link.type = 'text/css';
            link.href = window.location.search.match('/print-pdf/gi') ? '%ROOT%/libs/reveal/css/print/pdf.css' : '%ROOT%/libs/reveal/css/print/paper.css';
            document.getElementsByTagName('head')[0].appendChild(link);

        </script>
    </head>

    <body>

        <div class="reveal">
            <div class="slides">
			    %CONTENT%
            </div>
        </div>

        <div id="footer"><a href="%VERSION_PDF%">Version PDF</a> | <a href="%VERSION_HTML%">Version HTML</a> </div>

        <aside class="controls">
            <a class="left" href="#">&#x25C4;</a>
            <a class="right" href="#">&#x25BA;</a>
            <a class="up" href="#">&#x25B2;</a>
            <a class="down" href="#">&#x25BC;</a>
        </aside>

		<script src="%ROOT%/libs/reveal/lib/js/head.min.js"></script>
        <script src="%ROOT%/libs/reveal/js/reveal.js"></script>

        <script>
            // More info https://github.com/hakimel/reveal.js#configuration
            Reveal.initialize({

                // Push each slide change to the browser history
                history: true,

                // Display controls in the bottom right corner
                controls: true,

                // Display a presentation progress bar
                progress: true,

                // Display the page number of the current slide
                slideNumber: true,

                // Enable slide navigation via mouse wheel
                mouseWheel: true,

                // Transition style
                transition: 'convex', // none/fade/slide/convex/concave/zoom

                // Transition style for full page slide backgrounds
                backgroundTransition: 'concave', // none/fade/slide/convex/concave/zoom

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
					{ src: '%ROOT%/libs/reveal/lib/js/classList.js', condition: function() { return !document.body.classList; } },
					{ src: '%ROOT%/libs/reveal/plugin/markdown/marked.js', condition: function() { return !!document.querySelector( '[data-markdown]' ); } },
					{ src: '%ROOT%/libs/reveal/plugin/markdown/markdown.js', condition: function() { return !!document.querySelector( '[data-markdown]' ); } },
					{ src: '%ROOT%/libs/reveal/plugin/highlight/highlight.js', async: true, callback: function() { hljs.initHighlightingOnLoad(); } },
					{ src: '%ROOT%/libs/reveal/plugin/zoom-js/zoom.js', async: true },
					{ src: '%ROOT%/libs/reveal/plugin/notes/notes.js', async: true }
				]


            });

            Reveal.addEventListener('ready', function(event) {
                var isMobileDevice = /(iphone|ipod|ipad|android)/gi.test(navigator.userAgent);

                if (!isMobileDevice) {
                    document.getElementById('logo').style.display = 'inline';
                    document.getElementById('menu').style.display = 'inline';
                }

            });

        </script>

    </body>

</html>

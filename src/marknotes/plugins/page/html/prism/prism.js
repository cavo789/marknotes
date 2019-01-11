marknotes.arrPluginsFct.push("fnPluginHTMLPrism");

function fnPluginHTMLPrism() {

    /*<!-- build:debug -->*/
    if (marknotes.settings.debug) {
        console.log('	  Plugin Page html - Prism - highlightAll');
    }
    /*<!-- endbuild -->*/

    if (typeof Prism === 'object') {
        // @link https://github.com/PrismJS/prism
        Prism.highlightAll();

        // ----------------------------------
        // Load the "Copy to clipboard" button
        //
        // Code from http://prismjs.com/plugins/copy-to-clipboard/#how-to-use

		/*if (!Prism.plugins.toolbar) {
			console.warn('Copy to Clipboard plugin loaded before Toolbar plugin.');
			return;
		}

		var Clipboard = window.Clipboard || undefined;

		if (Clipboard && /(native code)/.test(Clipboard.toString())) {
			Clipboard = undefined;
		}

		if (!Clipboard && typeof require === 'function') {
			Clipboard = require('clipboard');
		}

		var callbacks = [];

		if (!Clipboard) {
			var script = document.createElement('script');
			var head = document.querySelector('head');

			script.onload = function () {
				Clipboard = window.Clipboard;

				if (Clipboard) {
					while (callbacks.length) {
						callbacks.pop()();
					}
				}
			};

			$url = marknotes.webroot + 'marknotes/plugins/page/html/clipboard/';
			$url += 'libs/clipboard-js/clipboard.min.js';

			// clipboard.min.js is already part of Marknotes
			script.src = $url;
			head.appendChild(script);
		}

		Prism.plugins.toolbar.registerButton('copy-to-clipboard', function (env) {
			var linkCopy = document.createElement('a');
			linkCopy.textContent = 'Copy';

			if (!Clipboard) {
				callbacks.push(registerClipboard);
			} else {
				registerClipboard();
			}

			return linkCopy;

			function registerClipboard() {
				var clip = new Clipboard(linkCopy, {
					'text': function () {
						return env.code;
					}
				});

				clip.on('success', function () {
					linkCopy.textContent = 'Copied!';

					resetText();
				});
				clip.on('error', function () {
					linkCopy.textContent = 'Press Ctrl+C to copy';

					resetText();
				});
			}

			function resetText() {
				setTimeout(function () {
					linkCopy.textContent = 'Copy';
				}, 5000);
			}
		});
*/
    } // if (typeof Prism === 'object')
}

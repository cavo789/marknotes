marknotes.arrPluginsFct.push("fnPluginHTMLSmileys");

// https://github.com/markdown-it/markdown-it-emoji/blob/master/lib/data/light.json
// :\ and :-\ not used because of conflict with markdown escaping
var $emoticons = {
	'>:(': '😠',
	'>:-(': '😠',
	':")': '😊',
	':-")': '😊',
	'</3': ' 💔',
	'<\\3': '💔',
	':-/': '😕',
	":'(": "😢",
	":'-(": "😢",
	':,(': '😢',
	':,-(': '😢',
	':(': '😦',
	':-(': '😦',
	'<3': '❤️',
	']:(': '👿',
	']:-(': '👿',
	'o:)': '😇',
	'O:)': '😇',
	'o:-)': '😇',
	'O:-)': '😇',
	'0:)': '😇',
	'0:-)': '😇',
	":')": '😂',
	":'-)": '😂',
	':,)': '😂',
	':,-)': '😂',
	":'D": '😂',
	":'-D": '😂',
	':,D': '😂',
	':,-D': '😂',
	':*': '😗',
	':-*': '😗',
	'x-)': '😆',
	'X-)': '😆',
	':|': '😐',
	':-|': '😐',
	':o': '😮',
	':-o': '😮',
	':O': '😮',
	':-O': '😮',
	':@': '😡',
	':-@': '😡',
	':D': '😄',
	':-D': '😄',
	':)': '😃',
	':-)': '😃',
	']:)': '😈',
	']:-)': '😈',
	":,'(": '😭',
	":,'-(": '😭',
	';(': '😭',
	';-(': '😭',
	':P': '😛',
	':-P': '😛',
	'8-)': '😎',
	'B-)': '😎',
	',:(': '😓',
	',:-(': '😓',
	',:)': '😅',
	',:-)': '😅',
	':s': '😒',
	':-S': '😒',
	':z': '😒',
	':-Z': '😒',
	':$': '😒',
	':-$': '😒',
	';)': '😉',
	';-)': '😉'
};


function escapeRegExp(str) {
	return str.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
}

function fnPluginHTMLSmileys() {

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('Plugin html - Smileys - Convert Smileys code into emoticons');
	}
	/*<!-- endbuild -->*/

	var $html = $('article').html();

	if (typeof $html !== 'undefined') {

		if ($html !== '') {

			var $patterns = [];
			var $metachars = /[[\]{}()*+?.\\|^$\-,&#\s]/g

			// build a regex pattern for each defined property
			for (var i in $emoticons) {
				if ($emoticons.hasOwnProperty(i)) { // escape metacharacters
					$patterns.push('(' + i.replace($metachars, "\\$&") + ')');
				}
			}

			// build the regular expression and replace
			try {
				var tmp = $html.replace(new RegExp($patterns.join('|'), 'g'), function (match) {
					return typeof $emoticons[match] != 'undefined' ?
						$emoticons[match] :
						match;
				});
			} catch (err) {
				console.warn(err.message);
			}

			// Replace ASCII Emojis with images
			$('article').html(tmp);

		} // if ($html!=='')
	} // if (typeof $html !== 'undefined')

}

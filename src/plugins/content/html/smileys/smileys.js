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

	var $html = $('#note_content').html();
	var $patterns = [];
	var $metachars = /[[\]{}()*+?.\\|^$\-,&#\s]/g

	// build a regex pattern for each defined property
	for (var i in $emoticons) {
		if ($emoticons.hasOwnProperty(i)) { // escape metacharacters
			$patterns.push('(' + i.replace($metachars, "\\$&") + ')');
		}
	}

	// build the regular expression and replace
	var tmp = $html.replace(new RegExp($patterns.join('|'), 'g'), function (match) {
		return typeof $emoticons[match] != 'undefined' ?
			$emoticons[match] :
			match;
	});

	// Replace ASCII Emojis with images
	$('#note_content').html(tmp);

}

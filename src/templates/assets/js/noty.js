/**
 *
 * @param {json} $params
 *      message : the message to display
 *      type    : success, error, warning, information, notification
 *
 * @returns {undefined}
 */
function Noty($params) {

	// If present, retrieve the marknotes.isBot variable. Is set to 1 when
	// the site is visited by a crawler bot
	isBot = ((marknotes.isBot === 'undefined') ? 0 : marknotes.isBot);

	// Notification are not usefull for bots ;-)
	if (!marknotes.isBot) {
		if ($.isFunction($.fn.noty)) {
			if ($params.message === '') {
				return false;
			}

			$type = (($params.type === 'undefined') ? 'info' : $params.type);

			// More options, see http://ned.im/noty/options.html
			var n = noty({
				text: $params.message,
				theme: 'relax',
				timeout: 2400,
				layout: 'bottomRight',
				type: $type
			}); // noty()
		}
	}

}

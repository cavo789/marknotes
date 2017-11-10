// If the i18n plugin is loaded, assign fnPluginTaskTimeline to
// the list of functions to call when language's files are loaded
if (typeof marknotes.arri18nFct !== "undefined") {
	marknotes.arri18nFct.push("fnPluginTaskTimeline");
}

$(document).ready(function () {

	// Don't wait to translate the interface
	runPluginsFunctions();

	$.ajax({
		type: 'POST',
		url: 'timeline.json',
		beforeSend: function () {
			/*<!-- build:debug -->*/
			console.time('Search time');
			/*<!-- endbuild -->*/
			var loading = '<div id="ajax_loading" class="lds-css" style="background-color:black;"><div style="width:100%;height:100%" class="lds-ellipsis"><div><div></div></div><div><div></div></div><div><div></div></div><div><div></div></div><div><div></div></div></div>';
			$('#divTimeline').append(loading);
		}, // beforeSend()
		success: function (data) {
			$('#ajax_loading').remove();
			ShowTimeline(data)
		},
		dataType: 'json'
	});
}); // $( document ).ready()

/**
 * This function will be automatically called by the i18n HTML plugin
 * Offers internationalization features
 */
function fnPluginTaskTimeline(params) {
	document.title = $.i18n('app_name');
	$('body').i18n();
}

function ShowTimeline($data) {
	$.fn.albeTimeline.languages = [{
		"en-US": {
			days: ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"],
			months: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
			shortMonths: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
			msgEmptyContent: "No information to display."
		},
		"fr-FR": {
			days: ["Dimanche", "Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi"],
			months: ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"],
			shortMonths: ["Jan", "Fév", "Mar", "Avr", "Mai", "Juin", "Juil", "Août", "Sep", "Oct", "Nov", "Déc"],
			msgEmptyContent: "Aucune information à afficher."
		}
	}];

	$("#divTimeline").albeTimeline($data, {
		'effect': 'zoomIn',
		'showMenu': true,
		'language': marknotes.settings.language
	});
}

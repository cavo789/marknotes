function initializeSettings() {

	// change() event for textboxes and comboboxes
	$("[data-task]").change(function (event) {

		event.stopImmediatePropagation();

		// When clicking on a checkbox, the click() event is
		// first called then the change() event ==> for checkboxes
		// we don't need to run this code
		var $elt = document.getElementById(this.id);
		if($elt!==null) {
			// Get the type (checkbox, text, ...)
			var $type = $elt.type.toLowerCase();
			if ($type==='checkbox') {
				return true;
			}
		}

		// Ok, we can continue
		var $task = $(this).data('task');

		// Just to be sure ...
		if ($task === 'settings') {
			// Ok, we'll update a setting
			$task = 'task.settings.update';

			// Get the key (f.i. plugins.options.task.login.username)
			var $key = $(this).data('key');

			// Just to be sure
			if ($key!=='') {
				// Get the new value (f.i. "new_admin")
				var $value = $(this).val();

				// Update the settings
				updateSettings($task, $key, $value);
			}
		} // if ($task === 'settings')
	}); //.change()

	// click() event for buttons and checkboxes
	$("[data-task]").click(function (event) {

		var $task = $(this).data('task');

		if ($task==='settings') {

			event.stopImmediatePropagation();

			// Update a setting in settings.json
			var $key = $(this).data('key');
			var $task = '';

			if ($key.substring(0, 5) == 'task=') {
				// data-key is a task like f.i. "task=show_form")
				// Retrieve "show_form"
				$task = $key.substring(5, $key.strlen);
				// The full task is task.settings.XXXX
				$task = "task.settings." + $task;

				// No key here
				$key = '';

				$bContinue = true;

			} else {

				$task = 'task.settings.update';

				var $bContinue = false;
				var $value = '';

				// Retrieve the value

				// data-key is for instance "interface.show_tips".
				// This is also the ID of the input element (f.i.
				// the ID of the checkbox)
				var $elt = document.getElementById(this.id);

				// $elt is equal to null when the element has no "id"
				// This is the case for comboboxes since they're not
				// handled by this onclick() code but onchange().
				if($elt!==null) {
					// Get the type (checkbox, text, ...)
					var $type = $elt.type.toLowerCase();

					switch ($type) {
						case 'checkbox':
							// If it's a checkbox, if checked, the value to store
							// is 1. 0 if unchecked
							$value = $(this).is(":checked") ? 1 : 0;

							$bContinue = true;
							break;
						default:
					}
				}
			}

			if ($bContinue) {
				// $bContinue is set to True when we're sure
				// that we've a key and a value
				updateSettings($task, $key, $value);
			}
		} // if ($task==='settings')
	}); // .click()

	return true;
}

/**
 * Run the task.settings.xxx (show_form, update, ...)
 */
function updateSettings($task, $key, $value) {

	var $data = {};
	$data.task = $task;
	if ($key!=='') {
		$data.key = $key;
		$data.value = $value;
	}

	$.ajax({
		async: true,
		type: (marknotes.settings.debug ? 'GET' : 'POST'),
		url: marknotes.url,
		data: $data,
		// If a key is mentionned, result is a JSON info
		datatype: ($key!=='' ? 'json' : 'html'),
		success: function (data) {
			if ($key!=='') {
				// Was an update of a key =>
				// show a notification
				Noty({
					message: data.message,
					type: (data.status == 1 ? 'success' : 'error')
				});
			} else {
				// The return is a HTML (like the
				// settings's form). Put it into the
				// content area
				$('#CONTENT').html(data);
			}

			afterDisplay("");
		}
	}); // $.ajax()

	return true;

}

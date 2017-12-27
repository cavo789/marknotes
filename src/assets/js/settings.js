function initializeSettings() {

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

				if ($(this).attr('data-value')) {
					// We've a data-value, just get it.
					$value = $(this).data('value');
alert('Line 41 - Value '+$value);
				} else {
					// Retrieve the value

					// data-key is for instance "interface.show_tips".
					// This is also the ID of the input element (f.i.
					// the ID of the checkbox)
					var $elt = document.getElementById(this.id);

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
				} // if ($(this).attr('data-value')) {
			}

			if ($bContinue) {
				// $bContinue is set to True when we're sure
				// that we've a key and a value

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
			}
		} // if ($task==='settings')
	});

	return true;
}

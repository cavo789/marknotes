<!DOCTYPE html>
<html lang="%LANGUAGE%">

	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />

		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge" />

		<link media="screen" rel="stylesheet" type="text/css" href="%ROOT%libs/bootstrap/css/bootstrap.min.css" />
		<link media="screen" rel="stylesheet" type="text/css" href="%ROOT%marknotes/plugins/page/html/datatables/libs/datatables/css/jquery.dataTables.min.css" />
		<link media="screen" rel="stylesheet" type="text/css" href="%ROOT%marknotes/plugins/page/html/datatables/libs/datatables/css/dataTables.bootstrap.min.css" />

	</head>

	<body>

		<div class="container-fluid">
			<h1>Debug file name : <strong>%FILENAME%</strong></h1>
			<table id="debugLog" class="table table-striped table-bordered table-hover">
				%CONTENT%
			</table>
		</div>

		<script src="%ROOT%libs/jquery/jquery.min.js"></script>
		<script src="%ROOT%marknotes/plugins/page/html/datatables/libs/datatables/js/jquery.dataTables.min.js"></script>

		<script>
			$(document).ready(function () {
				if ($.isFunction($.fn.DataTable)) {
					$('#debugLog').DataTable({
						"fixedHeader": true,
						// So column's headings will scroll with
						// "scrollX": "100%",
						// vertical height : not greater than 50% of the client height
						"scrollY": "50vh",
						"scrollCollapse": true,
						"info": true,
						"order": [], // No ordering by default
						"lengthMenu": [
							[-1, 5, 10, 25, 50],
							["All", 5, 10, 25, 50]
						],
						initComplete: function () {
							// https://datatables.net/examples/api/multi_filter_select.html
							// Create a list with unique values like the autofilter in Excelµ
							//
							// Don't use this.api().columns().every(
							// but this.api() .columns([0]).every( so the list will be
							// 		made only for the first column; the one with the type
							this.api().columns([0]).every( function () {
								var column = this;
								var select = $('<select><option value=""></option></select>')
									// Append the listbox with unique values
									// in the header of the column
									.appendTo( $(column.header()) )
									.on('change', function () {
										var val = $.fn.dataTable.util.escapeRegex(
											$(this).val()
										);
										column
											.search(val ? '^'+val+'$' : '', true, false)
											.draw();
									} );

								column.data().unique().sort().each(function (d, j) {
									select.append('<option value="'+d+'">'+d+'</option>')
								});
							} );
						}
					});
				}
			});
		</script>
	</body>
</html>

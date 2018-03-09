<div id="backup_form" class="container" style="width:95%">
	<h1>%BACKUP_TITLE%</h1>

	<div class="panel panel-default">
		<div class="panel-heading">%BACKUP_THIS_SETTINGS%</div>
		<div class="panel-body">
			<div class="row">
				<div class="col-md-6">
					<div class="form-group">
						<label>%BACKUP_THIS_FOLDER%</label>
						%BACKUP_CBX_FOLDERS%
						<br/>
					</div>
				</div>
				<div class="col-md-6">
					<div class="form-group">
						<small>%BACKUP_ONLY_CAN_SEE_FOLDERS%</small>
					</div>
				</div>
			</div>

			<div class="col-md-12" style="text-align: center;">
				<button id="backup_start" class="btn btn-primary">%BACKUP_START%</button>
				<input type="hidden" id="btn_start_text" value="%BACKUP_START%"/>
				<br/><br/>
				<small>%BACKUP_FOLDER_LOCATION%</small>
			</div>

		</div>
	</div>

	<div class="panel panel-default">
		<div class="panel-heading">%BACKUP_LOG%</div>
		<div class="panel-body">
			<textarea class="form-control" disabled="disabled" style="resize: none; height: 200px;"></textarea>
		</div>
	</div>
</div>

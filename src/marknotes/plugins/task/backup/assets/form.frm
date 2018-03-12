<style>
	#backup_form { width : 95%; }
	#backup_filenames { display : none; }
	#backup_filenames ul li { display: inline; text-decoration : underline; cursor : pointer;}
	#ignore_extensions { width : 100%; height : 80px; }
	.backupaction { text-align : center; }
	.backupaction_log{ font-size : 0.8em; }
</style>

<div id="backup_form" class="container">
	<h1>%BACKUP_TITLE%</h1>

	<div class="panel panel-default">
		<div class="panel-heading">%BACKUP_THIS_SETTINGS%</div>
		<div class="panel-body">
			<div class="row">
				<div class="col-md-6">
					<div class="form-group">
						<label>%BACKUP_THIS_FOLDER%</label>
						<select id="backup_folder" class="form-control select2 select2-hidden-accessible">%BACKUP_CBX_FOLDERS%</select>
						<label>%BACKUP_LABEL_IGNORE_EXTENSIONS%</label>
						<textarea id="ignore_extensions">%BACKUP_IGNORE_EXTENSIONS%</textarea>
						<br/>
					</div>
				</div>
				<div class="col-md-6">
					<div class="form-group">
						<small>%BACKUP_ONLY_CAN_SEE_FOLDERS%</small>
					</div>
				</div>
			</div>

			<div class="col-md-12 backupaction">
				<button id="backup_start" class="btn btn-primary">%BACKUP_START%</button>
				<input type="hidden" id="btn_start_text" value="%BACKUP_START%"/>
				<br/><br/>
				<div class="backupaction_log">
					%BACKUP_FOLDER_LOCATION%
					<div id="backup_filenames">%BACKUP_FILES_GENERATED% <ul></ul></div>
				</div>
			</div>

		</div>
	</div>

	<div class="panel panel-default">
		<div class="panel-heading">%BACKUP_LOG%</div>
		<div class="panel-body">
			<textarea id="backup_history" class="form-control" disabled="disabled" style="resize: none; height: 200px;"></textarea>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-12">
		<div class="box">

			<div class="box-header">
				<h3 class="box-title">%FULLNAME%</h3>
			</div>

			<!-- Upload form -->
			<div class="box-body pad" style="display:none;" id="divEditUpload">
				<div class="editor-wrapper">'
					<div class="pull-right box-tools" style="margin:5px;">
						<button type="button" class="btn btn-default btn-sm btn-exit-upload-droparea">
							<i class="fa fa-times"></i>
						</button>
					</div>
					<form action="" class="dropzone" id="upload_droparea">
						<div>
							<input type="hidden" name="folder" value="%IMAGEFOLDER%">
						</div>
					</form>
				</div>
			</div> <!-- Upload form -->

			<!-- The editor -->
			<div class="box-body pad" id="divEditEditor">
				<div class="editor-wrapper">
					<div id="sourceMarkDown" style="display:none;">%SOURCE%</div>
					<div id="editorMarkDown" lang="%LANGUAGE%" %SPELLCHECK%></div>
				</div>
			</div>
		</div>
	</div>
</div>

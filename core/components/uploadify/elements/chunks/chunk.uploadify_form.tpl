<div id="Uploadify">
	<div class="fieldset options">
		<span class="title">[[%uf_frontend_form_options]]</span>
		<div class="options">
			<div class="row">
				<span class="span2 center">
					[[%uf_frontend_thumb_size]]<br/>
					<select name="ThumbSize" class="span2">[[+listThumbSize]]</select>
				</span>
				<span class="span2 center">
					[[%uf_frontend_thumb_zc]]<br/>
					<select name="ThumbZC" class="span2">[[+listThumbZC]]</select>
				</span>
				<span class="span2 center">
					[[%uf_frontend_thumb_bg]]<br/>
					<select name="ThumbBG" class="span2">[[+listThumbBG]]</select>
				</span>
			</div>
			<small>
				[[%uf_frontend_file_extensions]]: [[+fileExtensions]]<br/>
				[[%uf_frontend_max_filesize]]: [[+maxFilesize]]
			</small>
		</div>
	</div>

	<div class="fieldset queue">
		<span class="title">[[%uf_frontend_form_queue]]</span>
		<div id="UploadifyQueue"></div>
	</div>

	<form id="UploadifyForm">
		<input name="file_upload" type="file" multiple="true" />
	</form>

	<div class="fieldset response">
		<span class="title">[[%uf_frontend_form_uploaded]]</span>
		<ul id="UploadifyResponse" class="thumbnails"></ul>
	</div>

	<br/><i><small>[[%uf_frontend_options_desc]]</small></i>
</div>

<script type="text/javascript">
	$(function() {
		$('#UploadifyForm').uploadify({
			'width': 150
			,'queueID': 'UploadifyQueue'
			,'formData': {
				'timestamp': '[[+timestamp]]'
				,'token': '[[+hash]]'
				,'action': 'uploadFile'
			}
			,'fileTypeDesc' : '[[%uf_frontend_images]]'
			,'fileTypeExts' : '[[+fileExtensions]]'
			,'buttonText': '[[%uf_frontend_upload]]'
			,'fileSizeLimit' : '[[+maxFilesize]]'
			,'removeCompleted': true
			,'onUploadSuccess' : function(file, data, response) {
				Uploadify.response(data);
			}
			,'uploader': '[[+actionUrl]]'
			,'swf': '[[+assetsUrl]]uploadify.swf'
		});
	});
</script>
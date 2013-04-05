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
			,'fileTypeExts' : '*.jpeg; *.jpg; *.png'
			,'buttonText': '[[%uf_frontend_upload]]'
			,'fileSizeLimit' : '[[+maxFilesize]]'
			,'removeCompleted': true
			,'onUploadSuccess' : function(file, data, response) {
				Uploadify.response(data);
			}
			,'uploader': '[[+assetsUrl]]action.php'
			,'swf': '[[+assetsUrl]]uploadify.swf'
		});
	});
</script>
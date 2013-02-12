<div class="fieldset queue">
	<span class="title">Очередь файлов<br/><br/></span>
	<div id="UploadifyQueue"></div>
</div>

<form id="UploadifyForm">
	<input name="file_upload" type="file" multiple="true" />
</form>

<div class="fieldset response">
	<span class="title">Загруженные файлы</span>
	<ul id="UploadifyResponse" class="thumbnails"></ul>
</div>

<script type="text/javascript">
	$(function() {
		$('#UploadifyForm').uploadifive({
			'width': 150
			,'queueID'  : 'UploadifyQueue'
			,'formData': {
				'timestamp': '[[+timestamp]]'
				,'token': '[[+hash]]'
				,'action': 'uploadFile'
			}
			,'fileTypeDesc' : 'Картинки'
			,'fileTypeExts' : '*.jpeg; *.jpg; *.png'
			,'buttonText': 'Загрузить'
			,'fileSizeLimit' : '1024KB'
			,'removeCompleted': true
			,'uploadScript': '/assets/components/uploadify/action.php'
			,'onUploadComplete' : function(file, data, response) {
				Uploadify.response(data);
			}
		});
	});
</script>
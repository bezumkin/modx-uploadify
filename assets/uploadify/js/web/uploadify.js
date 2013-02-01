

Uploadify = {
	initialize: function() {
		if(!jQuery().uploadify) {
			document.write('<script type="text/javascript" src="'+UploadifyConfig.jsUrl+'lib/jquery.uploadify.min.js"><\/script>');
		}

	}
	,response: function(response) {
		var data = $.parseJSON(response);
		if (data.success) {
			this.success(data.data);
		}
		else {
			this.error(data.message);
		}
	}
	,success: function (data) {
		$('#UploadifyResponse').append(data);
	}
	,error: function(message) {
		alert(message);
	}

};

Uploadify.initialize();
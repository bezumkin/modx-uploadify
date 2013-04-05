
Uploadify = {
	initialize: function() {
		if(typeof jQuery == "undefined") {
			document.write('<script type="text/javascript" src="'+ UploadifyConfig.jsUrl+'lib/jquery-1.9.1.min.js"></script>');
		}
		if (UploadifyConfig.uploadiFive) {
			if(!jQuery().uploadifive) {
				document.write('<script type="text/javascript" src="'+UploadifyConfig.jsUrl+'lib/jquery.uploadifive.min.js"><\/script>');
			}
		}
		else {
			if(!jQuery().uploadify) {
				document.write('<script type="text/javascript" src="'+UploadifyConfig.jsUrl+'lib/jquery.uploadify.min.js"><\/script>');
			}
		}
	}
	,response: function(response) {
		if (response == 'Access denied') {
			this.error(response);
		}
		else {
			var data = $.parseJSON(response);
			if (data.success) {
				this.success(data.data);
			}
			else {
				this.error(data.message);
			}
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

Uploadify = {
	initialize: function() {
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

		$(document).on('change', '#Uploadify .options select', function(e) {
			e.preventDefault();
			var key = $(this).attr('name');
			var value = $(this).val();

			if (key == 'ThumbZC') {
				var bg = $('#Uploadify select[name="ThumbBG"]');
				if (value != 0) {bg.attr('disabled', true);}
				else {bg.attr('disabled', false);}
			}

			$.post(UploadifyConfig.actionUrl, {action: 'form/option', key: key, value: value}, function(response) {
				return Uploadify.response(response);
			})
		})

		$(document).ready(function() {
			var value = $('#Uploadify select[name="ThumbZC"]').val();
			var bg = $('#Uploadify select[name="ThumbBG"]');
			if (value != 0) {bg.attr('disabled', true);}
			else {bg.attr('disabled', false);}
		})
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
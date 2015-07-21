var Main = {
	initSort: function($){

		$("#better-gallery").sortable({
			'items': 'li',
			'update' : function(e, ui) {

				var data = {
					action: 'save-attachment-order',
					nonce: $('#_wpnonce').val(),
					post_id: $('#post_ID').val()
				};

				var array = $(this).sortable('toArray');

				for(var i = 0; i < array.length; ++i){
					data[array[i]] = i;
				}

				$.post(ajaxurl, data);
			}
		});
	},
	openFileUpload: function(){
		var file_frame;

		if (typeof file_frame != 'undefined')
			file_frame.close();

		file_frame = wp.media.frames.file_frame = wp.media({

			title: 'Select an Image',
			library: {
				type: 'image'
			},
			button: {
				text: 'Use Image'
			},
			multiple: false,
		});


		file_frame.on('select', function() {

			var attachment = file_frame.state().get('selection').first().toJSON();
			Main.updateSorting(attachment.id);

		});

		/*
		file_frame.on('insert', function(url) {
			console.log('isnerted image: ' + url);

		});
		*/

		wp.Uploader.prototype.success = function(file_attachment) {

			var attachment = file_attachment.attributes;
			Main.updateSorting(attachment.id);

		};

		file_frame.open();

	},
	updateSorting: function(data){

		var data = {
			'action': 'update_attachment_sorting',
			'data': data,
			'ID': document.getElementById('post_ID').value
		};

		jQuery.post(ajaxurl, data, function(response) {
			document.getElementById('wrapper-ajax-better-gallery').innerHTML = response;
			Main.initSort(jQuery);
		});
	},
	remove: function(data){

		var data = {
			'action': 'delete_attachment',
			'data': data,
			'ID': document.getElementById('post_ID').value
		};

		jQuery.post(ajaxurl, data, function(response) {
			document.getElementById('wrapper-ajax-better-gallery').innerHTML = response;
			Main.initSort(jQuery);
		});
	},
	update: function(){

		var data = {
			'action': 'update_list_attachment',
			'ID': document.getElementById('post_ID').value
		};

		jQuery.post(ajaxurl, data, function(response) {
			document.getElementById('wrapper-ajax-better-gallery').innerHTML = response;
			Main.initSort(jQuery);
		});
	}
};


(function($){

	Main.initSort($);

	$(document).ajaxComplete(function(headers, content, result) {

		var obj = {};

		result.data.replace(/([^=&]+)=([^&]*)/g, function(m, key, value) {
			obj[decodeURIComponent(key)] = decodeURIComponent(value);
		});

		if(obj.action == 'set-post-thumbnail'){
			Main.update();
		}

	});

})(jQuery);

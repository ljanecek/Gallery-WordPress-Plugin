(function($){
	$("#easy-gallery").sortable({
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

})(jQuery);

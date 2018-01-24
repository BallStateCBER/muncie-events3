var ImageManager = {
	user_id: null,
    event_img_base_url: '',

	setupManager: function() {
		$('#selected_images').sortable({
			placeholder: 'ui-state-highlight'
		});

        $('#image_select_toggler').click(function (event) {
            event.preventDefault();
            ImageManager.toggleUploadedImages();
        });

		ImageManager.hidePreselectedImages();
	},

	getSelectionContainer: function(image_id) {
		return $('<li id="selectedimage_'+image_id+'" data-image-id="'+image_id+'"></li>');
	},

	getDragHandle: function() {
		return $('<img src="/img/icons/arrow-move.png" class="handle" alt="Move" title="Move" />');
	},

	getLinkedImage: function(image_id, filename) {
        var fullUrl = this.event_img_base_url + 'full/' + filename;
        var tinyUrl = this.event_img_base_url + 'tiny/' + filename;
		return $('<a href="' + fullUrl + '" rel="popup" id="thumbnail_link_'+image_id+'"><img src="' + tinyUrl + '" class="selected_image" /></a>');
	},

	getCaptionFieldLabel: function(image_id) {
		return $('<label for="selected_img_'+image_id+'_caption">Caption:</label>');
	},

	getCaptionField: function(image_id) {
		return $('<input type="text" class="caption" placeholder="Enter a caption for this image" id="selected_img_'+image_id+'_caption" name="data[Image]['+image_id+']" />');
	},

	getRemoveButton: function(image_id) {
		var remove_handle = $('<a href="#" class="remove"><img src="/img/icons/cross.png" class="remove" alt="Remove" title="Remove" /></a>');
		remove_handle.click(function (event) {
			event.preventDefault();
			var container = $(this).parent('li');
			ImageManager.unselectImage(container);
		});
		return remove_handle;
	},

	addHiddenListedImage: function(image_id, filename) {
		var link = $('<a href="#" id="listed_image_'+image_id+'" data-image-id="'+image_id+'" data-image-filename="'+filename+'"></a>');
        var url = this.event_img_base_url + 'tiny/' + filename;
        link.html('<img src="' + url + '" />');
		link.click(function (event) {
			event.preventDefault();
			var image_id = $(this).data('imageId');
			ImageManager.selectListedImage(image_id);
		});
		link.hide();
		$('#image_select_container').prepend(link);
	},

	populateSelectionContainer: function(selection_container, image_id, filename) {
		selection_container
			.append(ImageManager.getDragHandle())
			.append(ImageManager.getRemoveButton(image_id))
			.append(ImageManager.getLinkedImage(image_id, filename))
			.append(ImageManager.getCaptionFieldLabel(image_id))
			.append(ImageManager.getCaptionField(image_id))
			.appendTo($('#selected_images'));
	},

	afterSelection: function(image_id) {
		$('#no_images_selected').hide();
		$('#thumbnail_link_'+image_id).magnificPopup({
			closeBtnInside: true,
			type: 'image',
			fixedContentPos: false,
			fixedBgPos: true,
			midClick: true,
			removalDelay: 300,
			mainClass: 'my-mfp-zoom-in'
		});

		$('#selected_images').sortable('refresh');
	},

	unselectImage: function(container) {
		var image_id = container.data('imageId');
		var listed_image = $('#listed_image_'+image_id);
		var remove_selection = function() {
			container.slideUp(300, function() {
				container.remove();
				if ($('#selected_images li').length == 0) {
					$('#no_images_selected').show();
				}
			});
		};
		if (listed_image.length == 0) {
			remove_selection();
			return;
		}
		if ($('#image_select_container').is(':visible')) {
			listed_image.fadeIn(300);
			var options = {
				to: '#listed_image_'+image_id,
				className: 'ui-effects-transfer'
			};
			var callback = function() {
				listed_image.fadeOut(300);
			};
			container.effect('transfer', options, 300, remove_selection);
		} else {
			remove_selection();
			listed_image.show();
		}
	},

	selectListedImage: function(image_id) {
		var listed_image = $('#listed_image_'+image_id);
		var filename = listed_image.data('imageFilename');
		if (listed_image.length == 0 || ! filename) {
			return ImageManager.selectUnlistedImage(image_id);
		}
		var selection_container = ImageManager.getSelectionContainer(image_id);
		selection_container.fadeTo(0, 0);
		selection_container.hide();
		ImageManager.populateSelectionContainer(selection_container, image_id, filename);
		selection_container.slideDown(200, function() {
			selection_container.fadeTo(200, 1);
			var options = {
				to: '#selectedimage_'+image_id,
				className: 'ui-effects-transfer'
			};
			var callback = function() {
				listed_image.fadeOut(200);
			};
			listed_image.effect('transfer', options, 400, callback);
			ImageManager.afterSelection(image_id);
		});
	},

	selectUnlistedImage: function(image_id) {
		// Add an empty container with a loading icon
		var selection_container = ImageManager.getSelectionContainer(image_id);
		selection_container
			.hide()
			.addClass('loading')
			.appendTo($('#selected_images'))
			.fadeIn(300);

		$.ajax({
			url: '/images/filename/'+image_id,
			success: function (data) {
				if (data == 0) {
					alert('There was an error selecting an image (image not found).');
					$('#selectedimage_'+image_id).remove();
				} else {
					var filename = data;
					selection_container.removeClass('loading');
					ImageManager.populateSelectionContainer(selection_container, image_id, filename);
					ImageManager.afterSelection(image_id);
					ImageManager.addHiddenListedImage(image_id, filename);
				}
			},
			error: function() {
				alert('There was an error selecting an image.');
			}
		});
	},

	setupUpload: function(params) {
        this.event_img_base_url = params.event_img_base_url;

		$('#image_upload_button').uploadifive({
			'uploadScript': '/images/upload',
			'checkScript': '/images/file_exists',
			'onCheck': false,
			'fileSizeLimit': params.filesize_limit,
			'buttonText': 'Click to select an image',
			'formData': {
				'user_id': params.user_id,
				'event_id': params.event_id
			},
			'onUploadComplete': function(file, data) {
				console.log(file);
				console.log(params);
				console.log(data);

				var intRegex = /^\d+$/;

				// If the image's ID is returned
				if (intRegex.test(data)) {
					var image_id = data;
					ImageManager.selectUnlistedImage(image_id);
				}
			},
			'onError': function(errorType, files) {
				alert('There was an error uploading that file: '+file.xhr.responseText);
			},
			'onQueueComplete': function() {
				this.uploadifive('clearQueue');
			}
		});
	},

	// Hide preselected images in the collection of selectable images
	hidePreselectedImages: function () {
		$('#selected_images li').each(function() {
			var li = $(this);
			var image_id = li.data('imageId');
			var listed_image = $('#listed_image_'+image_id);
			if (listed_image.length != 0) {
				listed_image.hide();
			}
			li.find('a.remove').click(function(event) {
				event.preventDefault();
				var container = $(this).parent('li');
				ImageManager.unselectImage(container);
			});
		});
	},

    toggleUploadedImages: function () {
        if ($('#image_select_toggler').hasClass('loading')) {
            return;
        }

        ImageManager.showUploadedImages();
    },

	showUploadedImages: function () {
		if ($('#image_select_toggler').hasClass('loading')) {
			return;
		}

		var container = $('#image_select_container');

			this.loadUploadedImages();

	},

	loadUploadedImages: function () {
		var container = $('#image_select_container');
		var link = $('#image_select_toggler');
		$.ajax({
			url: '/images/user_images/'+ImageManager.user_id,
			beforeSend: function () {
				link.addClass('loading');
				container.html('<img src="/img/loading.gif" class="loading" alt="Loading..." />');
			},
			complete: function () {
				link.removeClass('loading');
			},
			error: function (jqXHR, textStatus, errorThrown) {
				console.log(jqXHR);
				console.log(textStatus);
				console.log(errorThrown);
				container.find('.loading').slideUp(300, function () {
					$(this).remove();
				});
				var error = $('<div class="alert alert-danger">There was an error loading your uploaded images. Please try again or contact an administrator for assistance.</div>');
				error.hide();
				container.after(error);
			},
			success: function (data) {
				container.find('.loading').slideUp(300, function () {
					$(this).remove();
					container.html(data);
					ImageManager.addHiddenUploadedImages();
					container.find('a').click(function (event) {
						event.preventDefault();
						var image_id = $(this).data('imageId');
						ImageManager.selectListedImage(image_id);
					});
				});
			}
		});
	},

	/**
	 * Look for selected images that aren't in the uploaded images list
	 * (which might happen if the current user is an admin editing someone
	 * else's event) and add them to the uploaded images list. This allows
	 * such a user to unselect and then reselect such images. */
	addHiddenUploadedImages: function () {
		var container = $('#image_select_container');
		$('#selected_images li').each(function () {
			var image_id = $(this).data('imageId');
			var filename = $(this).find('img.selected_image').attr('src').split('/').pop();

			// The Calendar helper does not show image thumbnails if the file is not found
			if (typeof filename == 'undefined') {
				return;
			}

			var linked_image = $('<a href="#" id="listed_image_'+image_id+'" data-image-id="'+image_id+'" data-image-filename="'+filename+'">');
            var url = this.event_img_base_url + 'tiny/' + filename;
            linked_image.html('<img src="' + url + '" />');
			linked_image.hide();
			container.append(linked_image);
		});
	}
};

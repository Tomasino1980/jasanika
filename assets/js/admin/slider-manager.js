(function(){
	// Minimal live preview for Slider Manager.
	document.addEventListener('DOMContentLoaded', function(){
		var preview = document.getElementById('jasanika-slide-preview');
		if (!preview) return;

		function qs(name){return document.querySelector('[name="'+name+'"]');}

		function updatePreview(){
			var imageUrl = qs('slide_image_url') ? qs('slide_image_url').value : '';
			var overlayColor = qs('slide_overlay_color') ? qs('slide_overlay_color').value : '';
			var overlayOpacity = qs('slide_overlay_opacity') ? parseInt(qs('slide_overlay_opacity').value,10) : 50;
			var contentPos = qs('slide_content_position') ? qs('slide_content_position').value : 'center';
			var contentVPos = qs('slide_content_vertical_position') ? qs('slide_content_vertical_position').value : 'center';
			var contentWidth = qs('slide_content_width') ? parseInt(qs('slide_content_width').value,10) : 60;
			var textAlign = qs('slide_text_alignment') ? qs('slide_text_alignment').value : 'left';
			var imageFit = qs('slide_image_fit') ? qs('slide_image_fit').value : 'cover';
			var imagePos = qs('slide_image_position') ? qs('slide_image_position').value : 'center';
			var buttonAlign = qs('slide_button_alignment') ? qs('slide_button_alignment').value : 'left';
			var buttonWidth = qs('slide_button_width') ? parseInt(qs('slide_button_width').value,10) : 200;
			var buttonStyle = qs('slide_button_style') ? qs('slide_button_style').value : 'solid';

			var imgEl = preview.querySelector('.jasanika-slide-preview__image');
			var overlayEl = preview.querySelector('.jasanika-slide-preview__overlay');
			var contentEl = preview.querySelector('.jasanika-slide-preview__content');
			var btnEl = preview.querySelector('.preview-button .button');

			if(imgEl){
				imgEl.style.backgroundImage = imageUrl ? 'url("'+imageUrl+'")' : 'none';
				imgEl.style.backgroundSize = imageFit === 'stretch' ? '100% 100%' : imageFit;
				imgEl.style.backgroundPosition = imagePos;
			}
			if(overlayEl){
				overlayEl.style.background = overlayColor || '#000';
				overlayEl.style.opacity = (isNaN(overlayOpacity)?0.5:overlayOpacity/100);
			}
			if(contentEl){
				contentEl.style.width = (isNaN(contentWidth)?60:contentWidth)+'%';
				contentEl.style.justifyContent = contentPos === 'left' ? 'flex-start' : (contentPos === 'right' ? 'flex-end' : 'center');
				contentEl.style.alignItems = contentVPos === 'top' ? 'flex-start' : (contentVPos === 'bottom' ? 'flex-end' : 'center');
				contentEl.style.textAlign = textAlign;
				contentEl.style.display = 'flex';
			}
			if(btnEl){
				btnEl.style.display = 'inline-block';
				btnEl.style.width = (isNaN(buttonWidth)?200:buttonWidth)+'px';
				btnEl.className = 'button ' + (buttonStyle === 'outline' ? 'outline' : (buttonStyle === 'ghost' ? 'ghost' : 'solid'));
				btnEl.style.margin = '0';
				btnEl.parentNode.style.display = 'flex';
				btnEl.parentNode.style.justifyContent = buttonAlign === 'left' ? 'flex-start' : (buttonAlign === 'right' ? 'flex-end' : 'center');
			}
		}

		// inputs to observe
		var inputs = preview.parentNode.querySelectorAll('input, select, textarea');
		inputs.forEach(function(inp){
			inp.addEventListener('input', updatePreview);
			inp.addEventListener('change', updatePreview);
		});

		// media uploader button
		var mediaBtn = document.getElementById('jasanika-slide-image-select');
		if(mediaBtn){
			mediaBtn.addEventListener('click', function(e){
				e.preventDefault();
				var frame = wp.media({title: 'Select Slide Image', multiple: false});
				frame.on('select', function(){
					var attachment = frame.state().get('selection').first().toJSON();
					var input = document.querySelector('[name="slide_image_url"]');
					if(input) input.value = attachment.url;
					updatePreview();
				});
				frame.open();
			});
		}

		// initial update
		updatePreview();
	});
})();
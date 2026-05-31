/* Theme Presets live preview */
(function () {
	'use strict';

	var previewRoot = document.getElementById('jasanika-preset-live-preview');
	if (!previewRoot) {
		return;
	}

	function applyPreview(config) {
		if (!config || typeof config !== 'object') {
			return;
		}

		previewRoot.style.setProperty('--js-primary', config.primary_color || '#b78acb');
		previewRoot.style.setProperty('--js-secondary', config.secondary_color || '#24212b');
		previewRoot.style.setProperty('--js-accent', config.accent_color || '#f1c95d');
		previewRoot.style.setProperty('--js-bg', config.background_color || '#1b1a1f');
		previewRoot.style.setProperty('--js-text', config.text_color || '#f5f2f7');

		var button = previewRoot.querySelector('.jasanika-presets__preview-button');
		if (!button) {
			return;
		}

		button.classList.remove(
			'jasanika-presets__preview-button--solid',
			'jasanika-presets__preview-button--outline',
			'jasanika-presets__preview-button--ghost'
		);
		button.classList.add('jasanika-presets__preview-button--' + (config.button_style || 'solid'));
	}

	var triggers = document.querySelectorAll('.jasanika-preview-trigger');
	triggers.forEach(function (trigger) {
		trigger.addEventListener('click', function () {
			var json = trigger.getAttribute('data-config');
			if (!json) {
				return;
			}

			try {
				var config = JSON.parse(json);
				applyPreview(config);
			} catch (e) {
				// Ignore invalid inline JSON payload.
			}
		});
	});

	var first = document.querySelector('.jasanika-preview-trigger');
	if (first) {
		first.click();
	}
})();

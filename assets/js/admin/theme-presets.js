/* Theme Presets – Jasanika Admin */
(function ($) {
	'use strict';

	var previewRoot = document.getElementById('jasanika-preset-live-preview');

	function isValidHex(value) {
		return /^#[0-9a-fA-F]{6}$/.test(value);
	}

	function applyPreview(config) {
		if (!config || typeof config !== 'object' || !previewRoot) {
			return;
		}

		previewRoot.style.setProperty('--js-primary', isValidHex(config.primary_color) ? config.primary_color : '#b78acb');
		previewRoot.style.setProperty('--js-secondary', isValidHex(config.secondary_color) ? config.secondary_color : '#24212b');
		previewRoot.style.setProperty('--js-accent', isValidHex(config.accent_color) ? config.accent_color : '#f1c95d');
		previewRoot.style.setProperty('--js-bg', isValidHex(config.background_color) ? config.background_color : '#1b1a1f');
		previewRoot.style.setProperty('--js-text', isValidHex(config.text_color) ? config.text_color : '#f5f2f7');

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

	function readColorsFromPanel(panel) {
		var config = {};
		panel.querySelectorAll('.jasanika-color-picker').forEach(function (input) {
			var field = input.getAttribute('data-field');
			if (field) {
				config[field] = input.value;
			}
		});
		var styleInput = panel.querySelector('[name="config[button_style]"]');
		config.button_style = styleInput ? styleInput.value : 'solid';
		return config;
	}

	// Preset card preview triggers.
	document.querySelectorAll('.jasanika-preview-trigger').forEach(function (trigger) {
		trigger.addEventListener('click', function () {
			var json = trigger.getAttribute('data-config');
			if (!json) {
				return;
			}
			try {
				applyPreview(JSON.parse(json));
			} catch (e) {
				// Ignore invalid JSON.
			}
		});
	});

	// Auto-preview first preset on load.
	var first = document.querySelector('.jasanika-preview-trigger');
	if (first) {
		first.click();
	}

	// Edit panel toggle buttons.
	document.querySelectorAll('.jasanika-presets__edit-toggle').forEach(function (btn) {
		btn.addEventListener('click', function () {
			var panelId = btn.getAttribute('data-panel');
			var panel = document.getElementById(panelId);
			if (!panel) {
				return;
			}
			var isOpen = panel.style.display === 'block';
			panel.style.display = isOpen ? 'none' : 'block';
			btn.textContent = isOpen
				? (btn.getAttribute('data-label-edit') || 'Edit Colors')
				: (btn.getAttribute('data-label-close') || 'Close Editor');
			if (!isOpen) {
				applyPreview(readColorsFromPanel(panel));
			}
		});
	});

	// Initialize wp-color-picker on all color picker inputs.
	if (typeof $ !== 'undefined' && $.fn && $.fn.wpColorPicker) {
		$('.jasanika-color-picker').wpColorPicker({
			change: function (event, ui) {
				var $input = $(this);
				$input.val(ui.color.toString());
				var panel = $input.closest('.jasanika-presets__edit-panel')[0];
				if (panel) {
					applyPreview(readColorsFromPanel(panel));
				}
			},
			clear: function () {
				var panel = $(this).closest('.jasanika-presets__edit-panel')[0];
				if (panel) {
					applyPreview(readColorsFromPanel(panel));
				}
			}
		});
	}

	// Client-side HEX validation before Save Colors submit.
	document.querySelectorAll('.jasanika-presets__edit-form').forEach(function (form) {
		form.addEventListener('submit', function (e) {
			var hexPattern = /^#[0-9a-fA-F]{6}$/;
			var valid = true;
			var errorEl = form.querySelector('.jasanika-presets__validation-error');

			form.querySelectorAll('.jasanika-color-picker').forEach(function (input) {
				if (!hexPattern.test(input.value)) {
					valid = false;
				}
			});

			if (!valid) {
				e.preventDefault();
				if (errorEl) {
					var msg = (window.jasanikaPresetsData && window.jasanikaPresetsData.i18n)
						? window.jasanikaPresetsData.i18n.invalidColor
						: 'Invalid color. Please enter a valid HEX color (#rrggbb).';
					errorEl.textContent = msg;
					errorEl.style.display = 'inline';
				}
			}
		});
	});

}(window.jQuery));

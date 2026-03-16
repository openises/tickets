/**
 * FormValidator — shared required-field validation for all Tickets CAD forms.
 * Uses data attributes on inputs to define validation rules:
 *   data-required="true"          — field is required
 *   data-required-msg="..."       — error message to display
 *   data-validate="notempty"      — validation type (default: notempty)
 *   data-validate="select"        — for SELECT elements (value !== "0" and !== "")
 *   data-validate="group:name"    — at least one field in the named group must have a value
 *   aria-required="true"          — accessibility attribute (add alongside data-required)
 *
 * Usage:
 *   FormValidator.init(document.myForm);              // attach listeners on page load
 *   FormValidator.validateForm(document.myForm);       // validate on submit (returns false if errors)
 *   FormValidator.validateForm(form, {submitOnSuccess: false}); // validate without auto-submit
 *
 * Compatible with PHP 7.2-era browsers (IE11 safe): no const, let, arrow functions, or template literals.
 * Does not conflict with MooTools $() — uses document.querySelector throughout.
 *
 * 3/16/26 — initial release (Backlog #1)
 */

var FormValidator = {

	/**
	 * Initialize validation on a form: mark required fields, attach blur/input listeners.
	 * Call once after the form DOM is ready.
	 */
	init: function(form) {
		if (!form) { return; }
		var fields = form.querySelectorAll('[data-required]');
		for (var i = 0; i < fields.length; i++) {
			var field = fields[i];
			// Add subtle background to indicate required field
			if (!field.classList.contains('field-error')) {
				field.classList.add('field-required');
			}
			// Validate on blur (leaving a field)
			FormValidator._addEvent(field, 'blur', function() {
				FormValidator.validateField(this);
			});
			// Clear error in real-time as user types or changes selection
			var clearEvent = (field.tagName === 'SELECT') ? 'change' : 'input';
			FormValidator._addEvent(field, clearEvent, function() {
				if (FormValidator._hasValue(this)) {
					FormValidator.clearError(this);
					FormValidator.showValid(this);
				}
			});
		}
	},

	/**
	 * Validate all required fields in a form.
	 * Returns true if all valid, false if errors found.
	 * Options:
	 *   submitOnSuccess: true (default) — call form.submit() if valid
	 *   submitOnSuccess: false — just validate, don't submit
	 */
	validateForm: function(form, opts) {
		if (!form) { return false; }
		opts = opts || {};
		var submitOnSuccess = (opts.submitOnSuccess !== undefined) ? opts.submitOnSuccess : true;

		var fields = form.querySelectorAll('[data-required]');
		var hasErrors = false;
		var firstError = null;
		var groupsChecked = {};

		for (var i = 0; i < fields.length; i++) {
			var field = fields[i];
			var validateType = field.getAttribute('data-validate') || 'notempty';

			// Handle group validation (e.g., "group:address")
			if (validateType.indexOf('group:') === 0) {
				var groupName = validateType.split(':')[1];
				if (groupsChecked[groupName]) { continue; } // already checked this group
				groupsChecked[groupName] = true;

				var groupValid = FormValidator._validateGroup(form, groupName);
				if (!groupValid) {
					hasErrors = true;
					// Show error on all fields in the group
					var groupFields = form.querySelectorAll('[data-validate="group:' + groupName + '"]');
					for (var g = 0; g < groupFields.length; g++) {
						FormValidator.showError(groupFields[g], groupFields[g].getAttribute('data-required-msg') || 'At least one field in this group is required');
						if (!firstError) { firstError = groupFields[g]; }
					}
				} else {
					// Clear errors on all group fields
					var groupFields = form.querySelectorAll('[data-validate="group:' + groupName + '"]');
					for (var g = 0; g < groupFields.length; g++) {
						FormValidator.clearError(groupFields[g]);
					}
				}
				continue;
			}

			// Standard field validation
			if (!FormValidator.validateField(field)) {
				hasErrors = true;
				if (!firstError) { firstError = field; }
			}
		}

		if (hasErrors && firstError) {
			FormValidator.scrollToFirstError(firstError);
			return false;
		}

		if (!hasErrors && submitOnSuccess) {
			form.submit();
		}

		return !hasErrors;
	},

	/**
	 * Validate a single field. Shows error or valid state. Returns boolean.
	 */
	validateField: function(input) {
		if (!input) { return true; }
		var validateType = input.getAttribute('data-validate') || 'notempty';

		// Skip group fields — they're handled by validateForm
		if (validateType.indexOf('group:') === 0) { return true; }

		var isValid = false;

		if (validateType === 'select') {
			isValid = (input.value !== '' && input.value !== '0');
		} else {
			// notempty (default)
			isValid = FormValidator._hasValue(input);
		}

		if (!isValid) {
			var msg = input.getAttribute('data-required-msg') || 'This field is required';
			FormValidator.showError(input, msg);
			return false;
		} else {
			FormValidator.clearError(input);
			FormValidator.showValid(input);
			return true;
		}
	},

	/**
	 * Show an error state on a field with an inline message.
	 */
	showError: function(input, msg) {
		if (!input) { return; }
		input.classList.remove('field-required', 'field-valid');
		input.classList.add('field-error');

		// Find or create the error message span
		var errorId = 'err_' + (input.id || input.name);
		var errorSpan = input.parentNode.querySelector('.error-message[data-error-for="' + errorId + '"]');
		if (!errorSpan) {
			errorSpan = document.createElement('span');
			errorSpan.className = 'error-message';
			errorSpan.setAttribute('data-error-for', errorId);
			// Insert after the input (or after the last sibling in the same cell)
			if (input.nextSibling) {
				input.parentNode.insertBefore(errorSpan, input.nextSibling);
			} else {
				input.parentNode.appendChild(errorSpan);
			}
		}
		errorSpan.innerHTML = msg;
		errorSpan.style.display = 'block';
	},

	/**
	 * Clear error state from a field.
	 */
	clearError: function(input) {
		if (!input) { return; }
		input.classList.remove('field-error');

		var errorId = 'err_' + (input.id || input.name);
		var errorSpan = input.parentNode.querySelector('.error-message[data-error-for="' + errorId + '"]');
		if (errorSpan) {
			errorSpan.style.display = 'none';
		}
	},

	/**
	 * Show valid state on a field (green border).
	 */
	showValid: function(input) {
		if (!input) { return; }
		input.classList.remove('field-required', 'field-error');
		input.classList.add('field-valid');
	},

	/**
	 * Scroll to the first error field smoothly.
	 */
	scrollToFirstError: function(element) {
		if (!element) { return; }
		if (element.scrollIntoView) {
			element.scrollIntoView({behavior: 'smooth', block: 'center'});
		}
		// Also focus the field
		if (element.focus) { element.focus(); }
	},

	/**
	 * Display non-field-specific errors (e.g., custom date validation messages)
	 * in an error summary div at the top of the form.
	 */
	showCustomErrors: function(form, errorText) {
		if (!form) { return; }
		var summaryId = 'validation_error_summary';
		var summary = form.querySelector('#' + summaryId);
		if (!summary) {
			summary = document.createElement('div');
			summary.id = summaryId;
			summary.className = 'error-summary';
			// Insert at the beginning of the form
			if (form.firstChild) {
				form.insertBefore(summary, form.firstChild);
			} else {
				form.appendChild(summary);
			}
		}
		// Convert newline-separated error text to HTML list
		var errors = errorText.split('\n');
		var html = '<b>Please correct the following:</b><br>';
		for (var i = 0; i < errors.length; i++) {
			var line = errors[i].replace(/^\s+|\s+$/g, '');
			if (line !== '') {
				html += '&bull; ' + line + '<br>';
			}
		}
		summary.innerHTML = html;
		summary.style.display = 'block';
		if (summary.scrollIntoView) {
			summary.scrollIntoView({behavior: 'smooth', block: 'center'});
		}
	},

	/**
	 * Clear the custom error summary div.
	 */
	clearCustomErrors: function(form) {
		if (!form) { return; }
		var summary = form.querySelector('#validation_error_summary');
		if (summary) {
			summary.style.display = 'none';
		}
	},

	// --- Private helpers ---

	/**
	 * Check if a field has a non-empty value.
	 */
	_hasValue: function(input) {
		if (!input) { return false; }
		var val = input.value;
		if (typeof val === 'string') {
			val = val.replace(/^\s+|\s+$/g, ''); // trim
		}
		return (val !== '' && val !== null && val !== undefined);
	},

	/**
	 * Validate a group — at least one field in the group must have a value.
	 */
	_validateGroup: function(form, groupName) {
		var fields = form.querySelectorAll('[data-validate="group:' + groupName + '"]');
		for (var i = 0; i < fields.length; i++) {
			if (FormValidator._hasValue(fields[i])) {
				return true;
			}
		}
		return false;
	},

	/**
	 * Cross-browser event attachment.
	 */
	_addEvent: function(element, eventName, handler) {
		if (element.addEventListener) {
			element.addEventListener(eventName, handler, false);
		} else if (element.attachEvent) {
			element.attachEvent('on' + eventName, handler);
		}
	}
};

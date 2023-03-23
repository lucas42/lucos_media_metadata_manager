/**
 * Range elements have a separate toggle to indicate a null value
 * This gets sent to the server by disabling the range input and enabling a hidden input of the same name
 */
window.addEventListener('DOMContentLoaded', event => {
	document.querySelectorAll(".form-field input[type=range]").forEach(range => {
		const row = range.parentElement.parentElement;
		const blankInput = row.querySelector(".blank > input");
		const nullInput = row.querySelector(".isnull > input");
		const preview = row.querySelector(".preview");
		const hidden = row.querySelector("input[type=hidden]");
		updatePreview();
		row.addEventListener("click", () => {
			nullInput.checked = false;
			if (blankInput) blankInput.checked = false;
			updatePreview();
			range.focus();
		});
		range.addEventListener("input", updatePreview);
		nullInput.addEventListener("change", updatePreview);
		nullInput.parentElement.addEventListener("click", event => {
			event.stopPropagation();
		});
		if (blankInput) {
			blankInput.parentElement.addEventListener("click", event => {
				event.stopPropagation();
			});
			blankInput.addEventListener("change", updatePreview);
		}
		function updatePreview(event) {
			if (event) {
				if (event.target == nullInput && nullInput.checked && blankInput) {
					blankInput.checked = false;
				}
				if (event.target == blankInput && blankInput.checked) {
					nullInput.checked = false;
				}
			}
			const fieldDisabled = nullInput.checked || blankInput?.checked;
			let val = Number(range.value).toFixed(1);
			if (fieldDisabled) val = " - ";
			preview.innerText = val;
			range.disabled = fieldDisabled;
			hidden.disabled = !fieldDisabled;

			/**
			 * Weird hack to make it easy to re-enable element when disabled
			 * See https://github.com/whatwg/html/issues/5886 for the whole discussion
			 * Basically need to set CSS pointer-events to 'none' when disabled, so click even bubbles to parent
			 * But need to set it to 'auto' when enabled, so clicking actually moves the range
			 * Don't understand why CSS is solution here, but seems to work on Chrome 110
			 **/
			range.style.pointerEvents = fieldDisabled ? 'none' : 'auto';
		}
	});
});

/**
 * Bulk edit fields have a toggle to indicate a value should be deliberately blanked out
 * (The default for blank values to keep the exisiting value as-is)
 */
window.addEventListener('DOMContentLoaded', event => {
	document.querySelectorAll(".form-field").forEach(row => {
		const mainInput = row.querySelector(".form-input > :first-child");
		const blankInput = row.querySelector(".blank > input");
		if (!mainInput || !blankInput) return;

		// If there's also a null input, then that'll have been handled by the range input logic
		if (row.querySelector(".isnull > input")) return;
		updateState();
		row.addEventListener("click", () => {
			blankInput.checked = false;
			updateState();
		});
		mainInput.addEventListener("input", updateState);
		blankInput.addEventListener("change", updateState);
		blankInput.parentElement.addEventListener("click", event => {
			event.stopPropagation();
		});
		function updateState() {
			const isblank = blankInput.checked;
			mainInput.disabled = isblank;

			/**
			 * Weird hack to make it easy to re-enable element when disabled
			 * See https://github.com/whatwg/html/issues/5886 for the whole discussion
			 * Basically need to set CSS pointer-events to 'none' when disabled, so click even bubbles to parent
			 * But need to set it to 'auto' when enabled, so clicking actually moves the range
			 * Don't understand why CSS is solution here, but seems to work on Chrome 110
			 **/
			mainInput.style.pointerEvents = isblank ? 'none' : 'auto';
		}
	});
});


// Following a post-redirect-get flow, indicate the successful save and modify the current URL
window.addEventListener('DOMContentLoaded', event => {
	if (window.location.search.indexOf("saved=true") === -1) return;
	document.getElementById("save").dataset.saved = true;
	document.getElementById("save").offsetHeight; // Force a repaint for the transition effect to take place
	delete document.getElementById("save").dataset.saved;

	// Remove saved=true from the current url
	const newSearch = window.location.search.replace('saved=true','').replace(/([\&\?])\&/, '$1').replace(/[\&\?]$/, '');
	history.replaceState(null, "", window.location.pathname + newSearch);
});

window.addEventListener('DOMContentLoaded', event => {
	const trackform = document.getElementById("trackform");
	const save = document.getElementById("save");
	if (!trackform || !save) return;

	// When a form input changes, set the save button to pending to highlight there's unsave changes
	document.querySelectorAll(".form-field .form-input > input, .form-field .form-input > select, .form-field .form-input > textarea").forEach(input => {
		input.addEventListener('change', () => {
			save.dataset.pending = true;
		});
	});

	// When the form is submitted, disable the save button
	trackform.addEventListener("submit", () => {
		const save = document.getElementById("save");
		save.disabled = true;
		save.classList.add("loading");
	});
});
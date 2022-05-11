/**
 * Range elements have a separate toggle to indicate a null value
 * This gets sent to the server by disabling the range input and enabling a hidden input of the same name
 */
window.addEventListener('DOMContentLoaded', event => {
	document.querySelectorAll(".form-field input[type=range]").forEach(range => {
		const row = range.parentElement.parentElement;
		const nullInput = row.querySelector(".isnull > input");
		const preview = row.querySelector(".preview");
		const hidden = row.querySelector("input[type=hidden]");
		updatePreview();
		row.addEventListener("click", () => {
			nullInput.checked = false;
			updatePreview();
			range.focus();
		});
		range.addEventListener("input", updatePreview);
		nullInput.addEventListener("change", updatePreview);
		nullInput.parentElement.addEventListener("click", event => {
			event.stopPropagation();
		});
		function updatePreview() {
			let val = Number(range.value).toFixed(1);
			const isnull = row.querySelector(".isnull > input").checked;
			if (isnull) val = " - ";
			preview.innerText = val;
			range.disabled = isnull;
			hidden.disabled = !isnull;
		}
	});
});


// Following a post-redirect-get flow, indicate the successful save and modify the current URL
window.addEventListener('DOMContentLoaded', event => {
	if (window.location.search.indexOf("saved=true") !== 1) return;
	document.getElementById("save").dataset.saved = true;
	document.getElementById("save").offsetHeight; // Force a repaint for the transition effect to take place
	delete document.getElementById("save").dataset.saved;
	history.replaceState(null, "", window.location.pathname);
});

window.addEventListener('DOMContentLoaded', event => {
	document.getElementById("trackform").addEventListener("submit", () => {
		document.getElementById("save").disabled = true;
	});
});

window.addEventListener('DOMContentLoaded', () => {
	document.querySelectorAll(".form-field input, .form-field select").forEach(input => {
		input.addEventListener('change', () => {
			document.getElementById("save").dataset.pending = true;
		});
	});
});
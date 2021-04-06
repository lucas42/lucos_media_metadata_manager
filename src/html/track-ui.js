window.addEventListener('DOMContentLoaded', event => {
	document.querySelectorAll(".form-field").forEach(row => {
		enableDisableRow(row);
		row.querySelector(".isnull > input").addEventListener("change", () => {
			enableDisableRow(row);
		});
		row.querySelector(".form-input").addEventListener("click", () => {
			row.querySelector(".isnull > input").checked = false;
			row.querySelector(".isnull > input").dispatchEvent(new Event('change'));
			row.querySelector(".form-input > input, .form-input > select").focus();
		});
	});
});

function enableDisableRow(row) {
	const isnull = row.querySelector(".isnull > input").checked;
	row.querySelector(".form-input > input, .form-input > select, .form-input > textarea").disabled = isnull;
}

window.addEventListener('DOMContentLoaded', event => {
	document.querySelectorAll(".form-field input[type=range]").forEach(range => {
		const row = range.parentElement.parentElement;
		updatePreview(row, range);
		range.addEventListener("input", () => {
			updatePreview(row, range);
		});
		row.querySelector(".isnull > input").addEventListener("change", () => {
			updatePreview(row, range);
		});
	});
});

function updatePreview(row, range) {
	let val = Number(range.value).toFixed(1);
	const isnull = row.querySelector(".isnull > input").checked;
	if (isnull) val = " - ";
	row.querySelector(".preview").innerText = val;
}

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
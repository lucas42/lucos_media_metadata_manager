window.addEventListener('DOMContentLoaded', event => {
	document.querySelectorAll(".form-field").forEach(row => {
		enableDisableRow(row);
		row.querySelector(".isnull > input").addEventListener("change", () => {
			enableDisableRow(row);
		});
		row.querySelector(".form-input").addEventListener("click", () => {
			row.querySelector(".isnull > input").checked = false;
			row.querySelector(".isnull > input").dispatchEvent(new Event('change'));
			row.querySelector(".form-input > input").focus();
		});
	});
});

function enableDisableRow(row) {
	const isnull = row.querySelector(".isnull > input").checked;
	row.querySelector(".form-input > input").disabled = isnull;
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
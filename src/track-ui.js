window.addEventListener('DOMContentLoaded', event => {
	document.querySelectorAll(".form-field").forEach(row => {
		enableDisableRow(row);
		row.querySelector(".isnull > input").addEventListener("change", () => {
			enableDisableRow(row);
		});
		row.querySelector(".form-input").addEventListener("click", () => {
			row.querySelector(".isnull > input").checked = false;
			enableDisableRow(row);
			row.querySelector(".form-input > input").focus();
		});
	});
});

function enableDisableRow(row) {
	const isnull = row.querySelector(".isnull > input").checked;
	row.querySelector(".form-input > input").disabled = isnull;
}


window.addEventListener('DOMContentLoaded', event => {
	const bulkedit = document.getElementById("bulk-edit");
	const content = document.getElementById("content");
	if (!bulkedit || !content) return;

	const toggleButton = document.createElement("button");
	const toggleLabel = document.createTextNode("");
	toggleButton.append(toggleLabel);
	toggleButton.id = "toggle-button";
	toggleButton.addEventListener("click", (event) => {
		if (bulkedit.dataset.hide) {
			delete bulkedit.dataset.hide;
			toggleLabel.nodeValue = "Hide Edit";
			toggleButton.dataset.mode = "hide";

			// Non-standard function, so check if it's available before calling
			if ('scrollIntoViewIfNeeded' in bulkedit) bulkedit.scrollIntoViewIfNeeded();
		} else {
			bulkedit.dataset.hide = true;
			toggleLabel.nodeValue = "Edit Tracks";
			toggleButton.dataset.mode = "show";
		}
	});
	content.append(toggleButton);
	toggleButton.click(); // Default to hidden
});
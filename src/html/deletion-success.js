// Following a post-redirect-get flow, indicate a successful deletion and modify the current URL
window.addEventListener('DOMContentLoaded', event => {
	let message;
	if (window.location.search.indexOf("deleted=collection") > -1) {
		message = "Collection successfully deleted"
	} else {
		return;
	}
	const messageContainer = document.createElement("div");
	const messageText = document.createElement("div");
	messageText.append(document.createTextNode(message));
	messageContainer.append(messageText);
	messageContainer.classList.add("message");
	document.getElementById("content").prepend(messageContainer);
	messageContainer.offsetHeight; // Force a repaint for the transition effect to take place
	messageContainer.dataset.collapse = true;

	// Remove deleted=true from the current url
	const newSearch = window.location.search.replace('deleted=collection','').replace(/([\&\?])\&/, '$1').replace(/[\&\?]$/, '');
	history.replaceState(null, "", window.location.pathname + newSearch);
});
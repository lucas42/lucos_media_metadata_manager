class CollectionControls extends HTMLElement {
	static get observedAttributes() {
		return ['slug'];
	}
	constructor() {
		super();

		const component = this;
		const shadow = component.attachShadow({mode:'closed'});

		const style = document.createElement('style');
		style.textContent = `
			:host {
				display: flex;
			}
			button {
				flex: 1;
				font-size: 10px;
				background-color: #923762;;
				color: white;
				border-radius: 8px;
				padding: 4px 15px;
				border: outset;
				transition: background-color 10s ease-out;
			}
			button[data-disabled] {
				transition: none;
				background-color: #555555;
			}
			button[data-success] {
				transition: none;
				background-color: #009900;
			}
		`;
		shadow.append(style);

		const actionButton = document.createElement("button");
		component.label = document.createTextNode("⏹ Clear Collection");
		actionButton.appendChild(component.label);
		actionButton.addEventListener("click", async event => {
			const reqURL = mediaManager+"v3/current-collection";
			actionButton.dataset.disabled = true;
			await fetch(reqURL, {
				method: "PUT",
				body: component.getAttribute("slug"),
				headers:{
					Authorization: "Key "+mediaManager_apiKey,
				},
			});
			delete actionButton.dataset.disabled;
			actionButton.dataset.success = true;
			actionButton.offsetHeight; // Force a repaint for the transition effect to take place
			delete actionButton.dataset.success;
		});
		shadow.append(actionButton);
	}
	attributeChangedCallback(name, oldValue, newValue) {
		switch (name) {
			case "slug":
				this.label.textContent = (newValue === "all") ? "⏹ Clear Collection" : "▶ Play Collection";
				break;
		}
	}
}

customElements.define('collection-controls', CollectionControls);

class QueueControls extends HTMLElement {
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
				background-color: #5599cc;
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

		const actions = {
			"now": "Play Now",
			"next": "Play Next",
			"end": "Queue Track"
		};
		for (const position in actions) {
			const label = actions[position];
			const actionButton = document.createElement("button");
			actionButton.appendChild(document.createTextNode(label));
			actionButton.addEventListener("click", async event => {
				const params = new URLSearchParams({
					position,
				});
				const reqURL = "https://ceol.l42.eu/v3/queue-track?"+params.toString();
				actionButton.dataset.disabled = true;
				await fetch(reqURL, {method: "POST", body: component.dataset.trackurl});
				delete actionButton.dataset.disabled;
				actionButton.dataset.success = true;
				actionButton.offsetHeight; // Force a repaint for the transition effect to take place
				delete actionButton.dataset.success;
			});
			shadow.append(actionButton);
		}
	}
}


customElements.define('queue-controls', QueueControls);

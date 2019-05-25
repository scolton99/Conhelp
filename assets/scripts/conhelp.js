window.search = function() {

};

window.turnOver = function() {
	let boxes = document.querySelectorAll(".item-box");

	for (let i = 0; i < boxes.length; i++) {
		boxes[i].classList.toggle("flipped");
	}
};

window.getSubcategories = function(e) {
	let box = e.target;

	let id = box.dataset.id;
	let first = parseInt(box.dataset.first);

	let x = new XMLHttpRequest();
	x.open("GET", "api/v1/subcategories/?super=" + id, true);
	x.onload = function() {
		let res = JSON.parse(this.responseText);

		clearWorkspace();

		if (res.length === 0) {
			document.querySelector("#grid").classList.add("workflow");
			document.querySelector("#grid").dataset.workflow = first;

			changeStep(first);
		} else {
			let main = document.querySelector("#grid");

			for (let j = 0; j < res.length; j++) {
				let cat = document.createElement("DIV");
				cat.setAttribute("data-id", res[j].id);
				cat.setAttribute("data-first", res[j].first);
				cat.textContent = res[j].text;

				cat.classList.add("item-box");
				cat.classList.add("white");

				cat.addEventListener("click", turnOver);
				cat.addEventListener("click", getSubcategories);

				main.appendChild(cat);
			}
		}
	};
	x.send();
};

window.decodeHashChange = function() {
	let hash = location.hash.substring(1);

	if (hash === "") {
		loadCategories();
		window.stepHistory = [];
		window.workflow = undefined;
		return;
	}

	let vars = hash.split("&");
	let obj = {};

	for(let i = 0; i < vars.length; i++) {
		let pair = vars[i].split("=");

		if (pair[1].split(",").length === 1)
			obj[pair[0]] = parseInt(pair[1]);
		else {
			let arr = pair[1].split(",");

			let objArr = [];
			for (let j = 0; j < arr.length; j++) {
				if (arr[j] === "")
					continue;
				objArr.push(parseInt(arr[j]));
			}

			obj[pair[0]] = objArr;
		}
	}

    let main = document.querySelector("#grid");

	if (obj["history"][obj["history"].length - 1] !== obj["current"]) {
		loadCategories();
		return;
	}

    window.stepHistory = obj["history"];

	if (main.dataset.workflow !== "" + obj["workflow"] || typeof window.workflow === 'undefined') {
        main.dataset.step = obj["current"];

		main.dataset.workflow = obj["workflow"];

        clearWorkspace();

		setLoading();

        let x = new XMLHttpRequest();
        x.open("GET", "api/v1/workflow/?start=" + obj["workflow"], true);
        x.onload = function () {
            window.workflow = JSON.parse(this.responseText);

            renderWorkflowStep(obj["current"], true);
        };
        x.send();
	} else {
		if (obj["current"] !== parseInt(main.dataset.step)) {
            main.dataset.step = obj["current"];
            renderWorkflowStep(obj["current"], true);
        }
	}
};

window.init = function() {
    window.stepHistory = [];

    if (location.hash !== "" && location.hash !== "#")
        decodeHashChange();
    else
    	loadCategories();

	window.addEventListener("hashchange", decodeHashChange);
};

window.clearWorkspace = function() {
	let toDestroy = document.querySelectorAll("#grid *");

	for (let i = 0; i < toDestroy.length; i++) {
		toDestroy[i].parentNode.removeChild(toDestroy[i]);
	}
};

window.renderWorkflowStep = function(next) {
	clearWorkspace();

	for (let k = 0; k < window.workflow["steps"].length; k++) {
		if (parseInt(window.workflow["steps"][k].id) === next) {
			let type = window.workflow["steps"][k].type;
			let text = window.workflow["steps"][k].text;

			let main = document.querySelector("#grid");

			main.dataset.step = next;

			if (!main.classList.contains("workflow"))
				main.classList.add("workflow");

			let stepBody = document.createElement("DIV");
			stepBody.classList.add("step-body");
			stepBody.innerHTML = text;

			// if (window.stepHistory.length > 1) {
			// 	let backButton = document.createElement("DIV");
			// 	backButton.classList.add("back");
			//
			// 	let backArrow = document.createElement("SPAN");
			// 	backArrow.classList.add("fa");
			// 	backArrow.classList.add("fa-arrow-left");
			//
			// 	backButton.appendChild(backArrow);
			// 	backButton.appendChild(document.createTextNode(" Back"));
			//
			// 	backButton.addEventListener("click", stepBackwards);
			//
			// 	main.appendChild(backButton);
			// }

			switch (type) {
				case "END": {
					main.appendChild(stepBody);
					break;
				}
				case "Y-N":
				case "MC": {
					main.appendChild(stepBody);

					let options = [];
					for (let i = 0; i < window.workflow["options"].length; i++) {
						if (parseInt(window.workflow["options"][i].step) === next)
							options.push(window.workflow["options"][i])
					}

					for (let j = 0; j < options.length; j++) {
						let l = document.createElement("DIV");

						l.classList.add("step-option");
						l.dataset.next = options[j].next;
						l.dataset.type = options[j].type;
						l.dataset.id = options[j].id;

						if (options[j].type === "Y") {
							l.innerHTML = "Yes";
						} else if (options[j].type === "N") {
							l.innerHTML = "No";
						} else {
							l.innerHTML = options[j].text;
						}

						l.addEventListener("click", renderFromOption);

						main.appendChild(l);
					}
					break;
				}
			}

			break;
		}
	}
};

window.stepBackwards = function() {
	changeStep(window.stepHistory[window.stepHistory.length - 2], true);
};

window.renderFromOption = function(e) {
	changeStep(e.target.dataset.next);
};

window.loadCategories = function() {
    let x = new XMLHttpRequest();
    x.open("GET", "api/v1/categories/", true);
    x.onload = function() {
        clearWorkspace();

        let cats = JSON.parse(x.responseText);
        let main = document.querySelector("#grid");

        if (main.classList.contains("workflow"))
        	main.classList.remove("workflow");

        for (let i = 0; i < cats.length; i++) {
            let cat = document.createElement("DIV");
            cat.setAttribute("data-id", cats[i].id);
            cat.setAttribute("data-first", cats[i].first);
            cat.textContent = cats[i].text;

            cat.classList.add("item-box");
            cat.classList.add("white");

            main.appendChild(cat);
        }

        let boxes = document.querySelectorAll(".item-box");

        for (let j = 0; j < boxes.length; j++) {
            boxes[j].addEventListener("click", turnOver);
            boxes[j].addEventListener("click", getSubcategories);
        }
    };
    x.send();
};

window.changeStep = function(newStep, back = false) {
	let historystr = "";

	if (back)
		window.stepHistory.pop();
	else
		window.stepHistory.push(newStep);

	for (let i = 0; i < window.stepHistory.length; i++) {
		historystr += window.stepHistory[i] + ",";
	}

	let workflow = document.querySelector("#grid").dataset.workflow;

	location.hash = "#current=" + newStep + "&history=" + historystr + "&workflow=" + workflow;
};

window.setLoading = function() {
	let main = document.querySelector("#grid");

	let loading = document.createElement("DIV");
	loading.classList.add("loading");
	let spin = document.createElement("SPAN");
	spin.classList.add("fa");
	spin.classList.add("fa-spin");
	spin.classList.add("fa-cog");
	loading.appendChild(spin);

	if (main.classList.contains("workflow"))
		main.classList.remove("workflow");

	main.appendChild(loading);
};

window.showWorkflows = function() {
	clearWorkspace();

	let main = document.querySelector("#grid");

	for (let i = 0; i < window.workflowList.length; i++) {
		let box = document.createElement("DIV");
		box.classList.add("item-box");
		box.classList.add("white");
		box.textContent = window.workflowList[i].name;

		box.dataset.first = window.workflowList[i].first;
		box.dataset.id = window.workflowList[i].id;

		box.addEventListener("click", goToWorkflowEditor);

		main.appendChild(box);
	}
};

window.goToWorkflowEditor = function(e) {
	window.location = "edit/?start=" + e.target.dataset.first;
};

window.initAdmin = function() {
	let x = new XMLHttpRequest();
	x.open("GET", rel + "api/v1/workflow", true);
	x.onload = function() {
		window.workflowList = JSON.parse(x.responseText);

		showWorkflows();
	};
	x.send();
};
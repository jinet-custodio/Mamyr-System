// tourJS

const dropdownButton = document.getElementById("dropdownMenuButton");
const dayTour = document.getElementById("dayTour");
const nightTour = document.getElementById("nightTour");

function tourUpdateButtonText(selectedText) {
  dropdownButton.textContent = selectedText;
}

dayTour.addEventListener("click", function (e) {
  e.preventDefault();
  tourUpdateButtonText("DAY TOUR");
});

nightTour.addEventListener("click", function (e) {
  e.preventDefault();
  tourUpdateButtonText("NIGHT TOUR");
});

// tourJS

// cottageJS
const cottageDropdownButton = document.getElementById(
  "cottageDropdownMenuButton"
);
const cottage1 = document.getElementById("cottage1");
const cottage2 = document.getElementById("cottage2");
const cottage3 = document.getElementById("cottage3");
const cottage4 = document.getElementById("cottage4");
const cottage5 = document.getElementById("cottage5");

function cottageUpdateButtonText(selectedText) {
  cottageDropdownButton.textContent = selectedText;
}

cottage1.addEventListener("click", function (e) {
  e.preventDefault();
  cottageUpdateButtonText("Php 500 - Good for 5 pax");
});

cottage2.addEventListener("click", function (e) {
  e.preventDefault();
  cottageUpdateButtonText("Php 800 - Good for 10 pax");
});

cottage3.addEventListener("click", function (e) {
  e.preventDefault();
  updateButtonText("Php 900 - Good for 12 pax");
});

cottage4.addEventListener("click", function (e) {
  e.preventDefault();
  cottageUottageUpdateButtonText("Php 1,000 - Good for 15 pax");
});

cottage5.addEventListener("click", function (e) {
  e.preventDefault();
  cottageUpdateButtonText("Php 2,000 - Good for 25 pax");
});
// cottageJS

//videokeJS
const videokeDropdownButton = document.getElementById(
  "videokeDropdownMenuButton"
);
const yes = document.getElementById("yes");
const no = document.getElementById("no");

function videokeUpdateButtonText(selectedText) {
  videokeDropdownButton.textContent = selectedText;
}

yes.addEventListener("click", function (e) {
  e.preventDefault();
  videokeUpdateButtonText("YES");
});

no.addEventListener("click", function (e) {
  e.preventDefault();
  videokeUpdateButtonText("NO");
});
//videokeJS

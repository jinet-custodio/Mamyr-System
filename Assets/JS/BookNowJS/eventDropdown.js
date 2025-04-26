//type of event
const eventDropdownButton = document.getElementById("eventDropdownMenuButton");
const bday = document.getElementById("bday");
const wedding = document.getElementById("wedding");
const teamBuilding = document.getElementById("teamBuilding");
const christening = document.getElementById("christening");
const thanksgiving = document.getElementById("thanksgiving");
const xmas = document.getElementById("xmas");
const otherOption = document.getElementById("other-option");
const otherInput = document.getElementById("other-input");

function eventUpdateButtonText(selectedText) {
  eventDropdownButton.textContent = selectedText;
}

bday.addEventListener("click", function (e) {
  e.preventDefault();
  eventUpdateButtonText("BIRTHDAY");
  resetOtherInput();
});

wedding.addEventListener("click", function (e) {
  e.preventDefault();
  eventUpdateButtonText("WEDDING");
  resetOtherInput();
});

teamBuilding.addEventListener("click", function (e) {
  e.preventDefault();
  eventUpdateButtonText("TEAM BUILDING");
  resetOtherInput();
});

christening.addEventListener("click", function (e) {
  e.preventDefault();
  eventUpdateButtonText("CHRISTENING");
  resetOtherInput();
});

thanksgiving.addEventListener("click", function (e) {
  e.preventDefault();
  eventUpdateButtonText("THANKSGIVING PARTY");
  resetOtherInput();
});

xmas.addEventListener("click", function (e) {
  e.preventDefault();
  eventUpdateButtonText("CHRISTMAS PARTY");
  resetOtherInput();
});

otherOption.addEventListener("click", function (e) {
  e.preventDefault();
  eventUpdateButtonText("Other");
  otherInput.disabled = false;
});

function resetOtherInput() {
  otherInput.value = "";
  otherInput.disabled = true;
}

const venueDropdownButton = document.getElementById("venueDropdownMenuButton");
const pavHall = document.getElementById("pavHall");
const miniPavHall = document.getElementById("miniPavHall");

function venueUpdateButtonText(selectedText) {
  venueDropdownButton.textContent = selectedText;
}

pavHall.addEventListener("click", function (e) {
  e.preventDefault();
  venueUpdateButtonText("PAVILION HALL (MAX. 300PAX)");
});

miniPavHall.addEventListener("click", function (e) {
  e.preventDefault();
  venueUpdateButtonText("MINI PAVILION HALL (MAX. 50PAX)");
});
//type of event

//guest
const guestDropdownButton = document.getElementById("guestDropdownMenuButton");
const guestNo1 = document.getElementById("guestNo1");
const guestNo2 = document.getElementById("guestNo2");
const guestNo3 = document.getElementById("guestNo3");
const guestNo4 = document.getElementById("guestNo4");

function guestUpdateButtonText(selectedText) {
  guestDropdownButton.textContent = selectedText;
}

guestNo1.addEventListener("click", function (e) {
  e.preventDefault();
  guestUpdateButtonText("10-50 pax");
});

guestNo2.addEventListener("click", function (e) {
  e.preventDefault();
  guestUpdateButtonText("51-100 pax");
});

guestNo3.addEventListener("click", function (e) {
  e.preventDefault();
  guestUpdateButtonText("101-200 pax");
});

guestNo4.addEventListener("click", function (e) {
  e.preventDefault();
  guestUpdateButtonText("201-350 pax");
});

//guest


//pacakge
const packageDropdownButton = document.getElementById('packageDropdownMenuButton');
const p1 = document.getElementById('p1');
const p2 = document.getElementById('p2');
const p3 = document.getElementById('p3');
const p4 = document.getElementById('p4');

function packageUpdateButtonText(selectedText) {
    packageDropdownButton.textContent = selectedText;
}

p1.addEventListener('click', function(e) {
    e.preventDefault();
    packageUpdateButtonText('PACKAGE 1');
});

p2.addEventListener('click', function(e) {
    e.preventDefault();
    packageUpdateButtonText('PACKAGE 2');
});

p3.addEventListener('click', function(e) {
    e.preventDefault();
    packageUpdateButtonText('PACKAGE 3');
});

p4.addEventListener('click', function(e) {
    e.preventDefault();
    packageUpdateButtonText('PACKAGE 4');
});
//pacakge
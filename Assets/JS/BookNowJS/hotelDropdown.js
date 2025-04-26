const roomDropdownButton = document.getElementById("roomDropdownMenuButton");
const roomNo1 = document.getElementById("roomNo1");
const roomNo2 = document.getElementById("roomNo2");
const roomNo3 = document.getElementById("roomNo3");
const roomNo4 = document.getElementById("roomNo4");
const roomNo5 = document.getElementById("roomNo5");
const roomNo6 = document.getElementById("roomNo6");
const roomNo7 = document.getElementById("roomNo7");
const roomNo8 = document.getElementById("roomNo8");
const roomNo9 = document.getElementById("roomNo9");
const roomNo10 = document.getElementById("roomNo10");
const roomNo11 = document.getElementById("roomNo11");

function roomUpdateButtonText(selectedText) {
  roomDropdownButton.textContent = selectedText;
}

roomNo1.addEventListener("click", function (e) {
  e.preventDefault();
  roomUpdateButtonText("ROOM 1");
});

roomNo2.addEventListener("click", function (e) {
  e.preventDefault();
  roomUpdateButtonText("ROOM 2");
});

roomNo3.addEventListener("click", function (e) {
  e.preventDefault();
  roomUpdateButtonText("ROOM 3");
});

roomNo4.addEventListener("click", function (e) {
  e.preventDefault();
  roomUpdateButtonText("ROOM 4");
});

roomNo5.addEventListener("click", function (e) {
  e.preventDefault();
  roomUpdateButtonText("ROOM 5");
});

roomNo6.addEventListener("click", function (e) {
  e.preventDefault();
  roomUpdateButtonText("ROOM 6");
});

roomNo7.addEventListener("click", function (e) {
  e.preventDefault();
  roomUpdateButtonText("ROOM 7");
});

roomNo8.addEventListener("click", function (e) {
  e.preventDefault();
  roomUpdateButtonText("ROOM 8");
});

roomNo9.addEventListener("click", function (e) {
  e.preventDefault();
  roomUpdateButtonText("ROOM 9");
});

roomNo10.addEventListener("click", function (e) {
  e.preventDefault();
  roomUpdateButtonText("ROOM 10");
});

roomNo11.addEventListener("click", function (e) {
  e.preventDefault();
  roomUpdateButtonText("ROOM 11");
});

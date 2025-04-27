window.addEventListener("scroll", function () {
  const navbar = document.getElementById("navbar");
  if (navbar) {
    // Check if navbar exists
    if (window.scrollY > window.innerHeight) {
      navbar.classList.add("scrolled");
    } else {
      navbar.classList.remove("scrolled");
    }
  }
});

window.addEventListener("scroll", function () {
  const navbarHalf = document.getElementById("navbar-half");
  if (navbarHalf) {
    // Check if navbar-half exists
    if (window.scrollY > window.innerHeight / 2) {
      navbarHalf.classList.add("scrolled");
    } else {
      navbarHalf.classList.remove("scrolled");
    }
  }
});

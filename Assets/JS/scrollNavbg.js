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

window.addEventListener("scroll", function () {
  const navbarHalf2 = document.getElementById("navbar-half2");
  if (navbarHalf2) {
    // Check if navbar-half2 exists
    if (window.scrollY > window.innerHeight * 0.2) {
      navbarHalf2.classList.add("scrolled");
    } else {
      navbarHalf2.classList.remove("scrolled");
    }
  }
});

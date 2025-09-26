//  <!-- Responsive Navbar -->
        document.addEventListener("DOMContentLoaded", function() {
            const icons = document.querySelectorAll('.navbar-icon');
            const navbarUL = document.getElementById('navUL');
            const nav = document.getElementById('navbar')

            function handleResponsiveNavbar() {
                if (window.innerWidth <= 991.98) {
                    navbarUL.classList.remove('w-100');
                    navbarUL.style.position = "fixed";
                    nav.style.margin = "0";
                    nav.style.maxWidth = "100%";
                    navbarUL.style.zIndex = "10000";
                    icons.forEach(icon => {
                        icon.style.display = "none";
                    })
                } else {
                    navbarUL.classList.add('w-100');
                    navbarUL.style.position = "relative";
                    nav.style.margin = "20px auto";
                    nav.style.maxWidth = "80vw";
                    icons.forEach(icon => {
                        icon.style.display = "block";
                    })
                }
            }

            handleResponsiveNavbar();
            window.addEventListener('resize', handleResponsiveNavbar);
        });

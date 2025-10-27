
        //Handle sidebar for responsiveness
        document.addEventListener("DOMContentLoaded", function() {
            const toggleBtn = document.getElementById('toggle-btn');
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main');
            const items = document.querySelectorAll('.nav-item');
            const links = document.querySelectorAll('.nav-link');
            const toggleCont = document.getElementById('sidebar-toggle')
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('collapsed');

                if (sidebar.classList.contains('collapsed')) {
                    items.forEach(item => {
                        item.style.justifyContent = "center";
                    });
                    toggleCont.style.justifyContent = "center"
                } else {
                    items.forEach(item => {
                        item.style.justifyContent = "flex-start";
                    });
                    toggleCont.style.justifyContent = "flex-end"
                }
            });

            function handleResponsiveSidebar() {
                if (window.innerWidth <= 700) {
                    sidebar.classList.add('collapsed');
                    toggleBtn.style.display = "flex";
                    items.forEach(item => {
                        item.style.justifyContent = "center";
                    });
                    console.log(sidebar.classList);
                } else {
                    toggleBtn.style.display = "none";
                    items.forEach(item => {
                        item.style.justifyContent = "flex-start";
                    })
                    sidebar.classList.remove('collapsed');
                    console.log(sidebar.classList);
                }
            }
            console.log("Current size: " + window.innerWidth); 
            // Run on load and when window resizes
            handleResponsiveSidebar();
            window.addEventListener("load", handleResponsiveSidebar);
            window.addEventListener('resize', handleResponsiveSidebar);
        });


        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', function(e) {
                // Ignore clicks that originated from the <a> itself
                if (e.target.tagName.toLowerCase() === 'a') return;
                const link = this.querySelector('a.nav-link');
                if (link) link.click();
            });
        });


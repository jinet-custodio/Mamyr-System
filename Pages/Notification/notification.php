<!-- Notification Modal Form -->
<form action="../../Function/Notification/updateNotification.php" method="POST">
    <!-- Notification Modal -->
    <div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">

                <div class="modal-header d-flex justify-content-between align-items-center">
                    <h5 class="modal-title" id="notificationModalLabel">Notifications</h5>
                    <button id="markAllRead" type="button" class="btn btn-link text-decoration-none mark-read-btn">Mark all as read</button>
                </div>

                <div class="modal-body p-0">
                    <div class="notification-list" id="notificationList">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Sweetalert Link -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Notification Script -->
<script>
    const alert = Swal.mixin({
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.onmouseenter = Swal.stopTimer;
            toast.onmouseleave = Swal.resumeTimer;
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        const receiverInput = document.getElementById('receiver');
        const userIDInput = document.getElementById('userID');

        if (!receiverInput || !userIDInput) {
            console.error(`Missing receiver ${receiverInput.value} or userID ${userIDInput.value}`);
            return;
        }

        const receiver = receiverInput.value;
        const userID = userIDInput.value;

        fetch(`../../Function/Notification/getNotification.php?id=${encodeURIComponent(userID)}&role=${encodeURIComponent(receiver)}`)
            .then(response => {
                if (!response.ok) throw new Error('Network Error');
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    alert("Error: " + data.error);
                    return;
                }

                const notifications = data.notifications || [];
                const count = data.count || 0;
                const notificationList = document.getElementById("notificationList");
                notificationList.innerHTML = "";
                const notificationButton = document.getElementById('notificationButton');
                if (notifications.length === 0) {
                    notificationList.innerHTML = "<p class='text-center p-3 text-muted'>No unread notifications</p>";
                    return;
                }

                notifications.forEach(notif => {
                    const parentDiv = document.createElement('div');
                    parentDiv.classList.add('notification-item');
                    parentDiv.dataset.id = notif.notificationID;

                    const textDiv = document.createElement('div');
                    textDiv.classList.add('notification-text');

                    const message = document.createElement('p');
                    message.classList.add('notification-message');
                    message.innerHTML = notif.message;

                    const dateDiv = document.createElement('div');
                    dateDiv.classList.add('notification-date', 'text-muted', 'small');
                    dateDiv.textContent = notif.createdAt;

                    textDiv.appendChild(message);
                    textDiv.appendChild(dateDiv);
                    parentDiv.appendChild(textDiv);
                    notificationList.appendChild(parentDiv);
                });

                if (count > 0) {
                    const span = document.createElement('span');
                    span.classList.add('position-absolute', 'top-0', 'start-100', 'translate-middle', 'badge', 'rounded-pill', 'bg-danger');
                    span.id = 'notification-count'
                    span.innerHTML = count;

                    notificationButton.appendChild(span);
                }
            })
            .catch(error => {
                console.error('There was a problem with the fetch operation', error);
                alert("Failed to load notifications. Please try again later.");
            });

        const notificationList = document.getElementById("notificationList");
        notificationList.addEventListener('click', function(event) {
            const item = event.target.closest('.notification-item');
            if (!item) return;

            if (item.classList.contains('read')) {
                alert.fire({
                    title: 'Alread mark as read',
                    icon: 'warning'
                });
                return;
            }
            const notificationID = item.dataset.id;
            // console.log('Clicked notification:', notificationID);

            fetch(`../../Function/Notification/readNotification.php?id=${encodeURIComponent(notificationID)}`)
                .then(response => {
                    if (!response.ok) throw new Error("Network Error");
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        alert("Error: " + data.error);
                        return;
                    }
                    item.classList.add('read');

                    const notificationBadge = document.getElementById('notification-count');
                    if (notificationBadge) {
                        let count = parseInt(notificationBadge.textContent, 10) || 0;;
                        if (count > 1) {
                            notificationBadge.textContent = count - 1;
                        } else {
                            notificationBadge.remove();
                        }
                        // console.log(count);
                    }
                })
                .catch(err => console.error('Error updating notification:', err));
        });
    });
    const userIDInput = document.getElementById('userID');
    const userID = userIDInput.value;
    document.getElementById('markAllRead').addEventListener('click', () => {
        fetch(`../../Function/Notification/updateNotification.php?id=${encodeURIComponent(userID)}`).then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log(data.message);
                    document.querySelectorAll('.notification-item').forEach(el => el.classList.add('read'));
                } else {
                    console.error(data.message);
                }
            })
            .catch(err => console.error('Fetch error:', err));
    });
</script>
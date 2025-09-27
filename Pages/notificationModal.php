<div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="notificationModalLabel">Notifications</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-0">
                <?php if (!empty($notificationsArray)): ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($notificationsArray as $index => $message):
                            $bgColor = $color[$index];
                            $notificationID = $notificationIDs[$index];
                        ?>
                            <li class="list-group-item mb-2 notification-item" data-id="<?= htmlspecialchars($notificationID) ?>" style="background-color: <?= htmlspecialchars($bgColor) ?>; border: 1px solid rgba(84, 87, 92, 0.5)">
                                <?= htmlspecialchars($message) ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="p-3 text-muted">No new notifications.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
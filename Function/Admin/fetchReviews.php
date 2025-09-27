<?php
require '../../Config/dbcon.php';
session_start();

$filter = $_POST['filter'] ?? null;
$sql = "SELECT * FROM userreview";
$conditions = [];
$params = [];
$types = '';
$today = new DateTime();

if ($filter) {
    switch ($filter) {
        case '1': // Last 30 days
            $start = (clone $today)->modify('-30 days')->format('Y-m-d');
            $conditions[] = "dateReviewed >= ?";
            $params[] = $start;
            $types .= 's';
            break;

        case '2': // Last 3 months
            $start = (clone $today)->modify('-3 months')->format('Y-m-d');
            $conditions[] = "dateReviewed >= ?";
            $params[] = $start;
            $types .= 's';
            break;

        case '3': // Last 6 months
            $start = (clone $today)->modify('-6 months')->format('Y-m-d');
            $conditions[] = "dateReviewed >= ?";
            $params[] = $start;
            $types .= 's';
            break;

        case '4': // This year
            $start = new DateTime($today->format('Y-01-01'));
            $conditions[] = "dateReviewed >= ?";
            $params[] = $start->format('Y-m-d');
            $types .= 's';
            break;

        case '5': // This quarter
            $month = (int)$today->format('n');
            $quarterStartMonth = floor(($month - 1) / 3) * 3 + 1;
            $start = new DateTime($today->format("Y-$quarterStartMonth-01"));
            $conditions[] = "dateReviewed >= ?";
            $params[] = $start->format('Y-m-d');
            $types .= 's';
            break;

        case '6': // Older than 6 months
            $end = (clone $today)->modify('-6 months')->format('Y-m-d');
            $conditions[] = "dateReviewed < ?";
            $params[] = $end;
            $types .= 's';
            break;
    }
}

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$getReviewInfo = $conn->prepare($sql);
if (!empty($params)) {
    $getReviewInfo->bind_param($types, ...$params);
}
$getReviewInfo->execute();
$result = $getReviewInfo->get_result();

if ($result->num_rows > 0) {
    while ($review = $result->fetch_assoc()) {
        $date = new DateTime($review['dateReviewed']);
        $formattedDate = $date->format('d F Y');
        $rating = floatval($review['reviewRating']);
?>
        <div class="card reviewCard">
            <div class="card-header">
                <h5 class="bookingTypeText"><?= htmlspecialchars($review['bookingType']) . " Booking" ?></h5>
            </div>

            <div class="card-body">
                <section class="dateContainer">
                    <h5 class="reviewdateText"><?= htmlspecialchars($formattedDate) ?></h5>
                </section>

                <section class="starContainer">
                    <?php
                    for ($i = 1; $i <= 5; $i++) {
                        if ($i <= $rating) {
                            echo '<i class="fa-solid fa-star" style="color: #FFD43B;"></i>';
                        } else {
                            echo '<i class="fa-regular fa-star"></i>';
                        }
                    }
                    ?>
                </section>

                <section class="reviewInfo">
                    <h6 class="reviewInfoLabel">Additional Feedback</h6>
                    <textarea class="form-control" readonly><?= htmlspecialchars($review['reviewComment']) ?></textarea>
                </section>
            </div>
        </div>
<?php
    }
} else {
    echo "<p>No reviews found for this time range.</p>";
}
?>
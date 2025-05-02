<?php
require '../../Config/dbcon.php';
session_start();
$partnerID = $_POST['partnerID'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mamyr Resort and Events Place</title>
    <link
        rel="icon"
        type="image/x-icon"
        href="../../Assets/Images/Icon/favicon.png " />

    <!-- Bootstrap Link -->
    <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css" />
</head>

<body>
    <!-- View Indivudual Applicant -->
    <div class="applicant">
        <?php
        $query = "SELECT u.firstName, u.lastName, u.userProfile, u.phoneNumber, p.* 
               FROM partnerships p
               INNER JOIN users u ON p.userID = u.userID 
               WHERE partnershipID = '$partnerID'";
        $result = mysqli_query($conn, $query);
        if (mysqli_num_rows($result) > 0) {
            $data = mysqli_fetch_assoc($result);
            $applicantName = ucfirst($data['firstName']) . " " . ucfirst($data['lastName']);
            $companyName = $data['companyName'];
            $partnerType = $data['partnerType'];
            $businessEmail = $data['businessEmail'];
            // $email = $data['email'];
            $phoneNumber = $data['phoneNumber'];
            if ($phoneNumber === NULL) {
                $phoneNumber = "--";
            } else {
                $phoneNumber;
            }
            $address = $data['partnerAddress'];
            $link = $data['documentLink'];

            $profile = $data['userProfile'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_buffer($finfo, $profile);
            finfo_close($finfo);
            $image = 'data:' . $mimeType . ';base64,' . base64_encode($profile);
        }
        ?>

        <div class="card mb-3" style="max-width: 540px;">
            <div class="row g-0">
                <div class="col-md-4">
                    <img src="<?= htmlspecialchars($image) ?>" class="img-fluid rounded-start" alt="<?= htmlspecialchars($applicantName) ?> ">
                </div>
                <div class="col-md-8">
                    <div class="card-body">
                        <h3 class="card-title">Applicant</h3>
                        <div class="applicant-info">
                            <h4 class="card-title">Name</h4>
                            <p class="card-text"><?= $applicantName ?></p>
                        </div>
                        <div class="applicant-info">
                            <h4 class="card-title">Company Name</h4>
                            <p class="card-text"><?= $companyName ?></p>
                        </div>
                        <div class="applicant-info">
                            <h4 class="card-title">Business Email</h4>
                            <p class="card-text"><?= $businessEmail ?></p>
                        </div>
                        <div class="applicant-info">
                            <h4 class="card-title">Partner Type</h4>
                            <p class="card-text"><?= ucfirst($partnerType) ?></p>
                        </div>
                        <div class="applicant-info">
                            <h4 class="card-title">Business Address</h4>
                            <p class="card-text"><?= $address ?></p>
                        </div>
                        <div class="applicant-info">
                            <h4 class="card-title">Phone Number</h4>
                            <p class="card-text"><?= $phoneNumber ?></p>
                        </div>
                        <div class="applicant-info">
                            <h4 class="card-title">Document Link</h4>
                            <a href="<?= $link ?>"><?= $link ?></a>
                        </div>
                        <div class="button-container">
                            <button type="submit" class="btn btn-primary">Approve</button>
                            <button type="submit" class="btn btn-danger">Decline</button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>



    <!-- Bootstrap Link -->
    <script src="../../Assets/JS/bootstrap.bundle.min.js"></script>
</body>

</html>
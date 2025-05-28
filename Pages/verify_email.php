<?php

require '../Config/dbcon.php';
session_start();

if (isset($_SESSION['email'])) {
  $email = mysqli_real_escape_string($conn, $_SESSION['email']);
} else {
  echo 'No email in session';
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sign Up</title>
  <link rel="stylesheet" href="../Assets/CSS/modal.css" />
  <link rel="stylesheet" href="../Assets/CSS/bootstrap.min.css" />
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
  <link
    rel="stylesheet"
    href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" />
</head>

<body>
  <div class="modal-form">
    <form action="../Function/verification.php" method="POST">
      <!-- Modal -->
      <div class="modal fade" id="emailVerificationBox" data-bs-backdrop="static" tabindex="-1">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="emailVerificationTitle">
                Email Verification
              </h5>
            </div>

            <div class="modal-body">
              <h4 class="description">
                Please enter 6-digit verification code that was sent to your email
              </h4>
              <div class="errorMsg">
                <?php
                if (isset($_SESSION['OTP'])) {
                  echo htmlspecialchars($_SESSION['OTP']);
                  unset($_SESSION['OTP']);
                }

                if (isset($_SESSION['time'])) {
                  echo htmlspecialchars($_SESSION['time']);
                  unset($_SESSION['time']);
                }

                if (isset($_SESSION['error'])) {
                  echo htmlspecialchars($_SESSION['error']);
                  unset($_SESSION['error']);
                }
                ?>
              </div>
              <div class="pinContainer">
                <input type="text" class="form-control" maxlength="1" id="P1" name="pin1" oninput="" />
                <input type="text" class="form-control" maxlength="1" id="P2" name="pin2" oninput="" />
                <input type="text" class="form-control" maxlength="1" id="P3" name="pin3" oninput="" />
                <input type="text" class="form-control" maxlength="1" id="P4" name="pin4" oninput="" />
                <input type="text" class="form-control" maxlength="1" id="P5" name="pin5" oninput="" />
                <input type="text" class="form-control" maxlength="1" id="P6" name="pin6" oninput="" />

              </div>

              <button
                type="submit"
                class="btn btn-primary"
                data-bs-dismiss="modal"
                name="verify-btn">
                Verify
              </button>

              <p class="resendPin">Didn't receive a code? <button type="submit" class="btn btn-link resendLink" name="resend_code">Resend</button></p>

            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
  <script src="../Assets/JS/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      var myModal = new bootstrap.Modal(document.getElementById('emailVerificationBox'), {
        backdrop: 'static',
      });
      myModal.show();
    });

    const urlParams = new URLSearchParams(window.location.search);
    const page = urlParams.get('page');


    document.addEventListener("DOMContentLoaded", function() {
      const inputs = document.querySelectorAll(".form-control");

      inputs.forEach((input, index) => {
        input.addEventListener("input", (event) => {
          if (event.inputType !== "deleteContentBackward" && input.value.length === 1) {
            if (index < inputs.length - 1) {
              inputs[index + 1].focus();
            }
          }
        });

        input.addEventListener("keydown", (event) => {
          if (event.key === "Backspace" && input.value === "" && index > 0) {
            inputs[index - 1].focus();
          }
        });
      });
    });
  </script>
</body>

</html>
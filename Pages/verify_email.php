<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../Config/dbcon.php';
session_start();

if (isset($_SESSION['email'])) {
  $email = mysqli_real_escape_string($conn, $_SESSION['email']);
  $_SESSION['email'] = $email;
} else {
  error_log("No Email in Session");
  $_SESSION['loginError'] = "An error occurred. Please try again.";
  header("Location: register.php");
  exit;
}

if (isset($_SESSION['action'])) {
  $_SESSION['action'];
}


?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>OTP Verification</title>
  <link rel="shortcut icon" href="../Assets/Images/Icon/favicon.png" type="image/x-icon">
  <link rel="stylesheet" href="../Assets/CSS/modal.css" />
  <link rel="stylesheet" href="../Assets/CSS/bootstrap.min.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">

  <!-- icon libraries for font-awesome and box icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
    integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
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
                id="submitBtn"
                class="btn btn-primary"
                data-bs-dismiss="modal"
                name="verify-btn">
                Verify
              </button>

              <p class="resendPin">Didn't receive a code?
                <button type="submit" class="btn btn-link resendLink" name="resend_code">Resend</button>
              </p>

            </div>
          </div>
        </div>
      </div>
    </form>
  </div>


  <!-- Bootstrap JS -->
  <!-- <script src="../Assets/JS/bootstrap.bundle.min.js"></script> -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>

  <!-- Sweetalert Link -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- Script for loader -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const loaderOverlay = document.getElementById('loaderOverlay');
      const form = document.querySelector('form');

      if (form) {
        form.addEventListener('submit', function() {
          loaderOverlay.style.display = 'flex';
        });
      }
    });

    function hideLoader() {
      const overlay = document.getElementById('loaderOverlay');
      if (overlay) overlay.style.display = 'none';
    }

    // Hide loader on normal load
    window.addEventListener('load', hideLoader);

    // Hide loader on back/forward navigation (from browser cache)
    window.addEventListener('pageshow', function(event) {
      if (event.persisted) {
        hideLoader();
      }
    });
  </script>

  <script>
    const params = new URLSearchParams(window.location.search);
    const paramValue = params.get("action");

    if (paramValue === "sendOTPFailed") {
      Swal.fire({
        title: "Oops!",
        text: "We couldn’t send the OTP. Please try again.",
        icon: "warning"
      });
    } else if (paramValue === "expiredOTP") {
      Swal.fire({
        title: 'Oops!',
        text: "OTP expired. Please request a new code.",
        icon: "warning"
      })
    } else if (paramValue === "invalidOTP") {
      Swal.fire({
        title: 'Oops!',
        text: "Invalid OTP. Please try again!",
        icon: "warning"
      })
    }

    if (paramValue) {
      const url = new URL(window.location);
      url.search = '';
      history.replaceState({}, document.title, url.toString());
    }
  </script>

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
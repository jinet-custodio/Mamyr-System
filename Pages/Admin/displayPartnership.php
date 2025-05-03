 <?php
  require '../../Config/dbcon.php';

  // $session_timeout = 3600;

  // ini_set('session.gc_maxlifetime', $session_timeout);
  // session_set_cookie_params($session_timeout);
  session_start();
  // date_default_timezone_set('Asia/Manila');

  // if (!isset($_SESSION['userID']) || !isset($_SESSION['userType'])) {
  //   header("Location: ../register.php");
  //   exit();
  // }

  // if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $session_timeout) {
  //   $_SESSION['error'] = 'Session Expired';

  //   session_unset();
  //   session_destroy();
  //   header("Location: ../register.php?session=expired");
  //   exit();
  // }

  // $_SESSION['last_activity'] = time();

  // $userID = $_SESSION['userID'];
  // $userType = $_SESSION['userType']; 
  ?>

 <!DOCTYPE html>
 <html lang="en">

 <head>
     <meta charset="UTF-8" />
     <meta name="viewport" content="width=device-width, initial-scale=1.0" />
     <title>Mamyr Resort and Events Place</title>
     <link rel="icon" type="image/x-icon" href="../../Assets/Images/Icon/favicon.png " />

     <!-- Bootstrap Link -->
     <link rel="stylesheet" href="../../Assets/CSS/bootstrap.min.css" />
 </head>

 <body>

     <!-- Button choice -->
     <div class="button-container" id="choice-container">
         <a href="#" id="request-link" class="btn btn-primary">Request</a>
         <a href="#" id="partner-link" class="btn btn-primary">Partners</a>
     </div>

     <!-- Display when Partner is Click -->
     <div class="partner-container" id="partner-container" style="display: none;">
         <!-- Back Button -->
         <div>
             <a href="#" id="choice1-link" class="btn btn-primary"><img src="../../Assets/Images/Icon/backbtn_black.png"
                     alt="Back Button"></a>
             <h4>Partners</h4>
         </div>
         <!-- Partners Table  -->
         <div class="partnership-table">
             <table class="table">
                 <thead>
                     <tr>
                         <th scope="col">Name</th>
                         <th scope="col">Partner Type</th>
                         <th scope="col">Status</th>
                         <th scope="col">Action</th>
                     </tr>
                 </thead>
                 <tbody>
                     <!-- Select to display all the applicants  -->
                     <?php
            $selectQuery = "SELECT u.firstName, u.lastName, p.partnerType, p.status, p.partnershipID 
        FROM partnerships p
        INNER JOIN users u ON p.userID = u.userID
        WHERE status = 'Pending' OR status = 'Rejected'
        ";
            $result = mysqli_query($conn, $selectQuery);
            if (mysqli_num_rows($result) > 0) {
              foreach ($result as $applicants) {
                $name = $applicants['firstName'] . " " . $applicants['lastName'];
                $partnerID = $applicants['partnershipID'];
            ?>
                     <tr>
                         <td scope="row"><?= $name ?></td>

                         <td scope="row"><?= ucfirst($applicants['partnerType'])  ?></td>
                         <?php
                  if ($applicants['status'] == "Pending") {
                  ?>
                         <td scope="row" class="btn btn-warning">
                             <?= $applicants['status'] ?>
                         </td>
                         <?php
                  } else if ($applicants['status'] == "Rejected") {
                  ?>
                         <td scope="row" class="btn btn-danger">
                             <?= $applicants['status'] ?>
                         </td>
                         <?php
                  }
                  ?>
                         <td scope="row">
                             <form action="partnership.php?container=2" method="POST" style="display:inline;">
                                 <input type="hidden" name="partnerID" value="<?= $partnerID ?>">
                                 <button type="submit" class="btn btn-info">View</button>
                             </form>
                         </td>
                         </td>
                         <?php
              }
            } else {
                ?>
                         <td colspan="5">
                             <h5 scope="row" class="text-center">No record Found!</h5>
                         </td>
                         <?php
            } ?>
                 </tbody>
             </table>
         </div>
     </div>


     <!-- Display when Request is Click -->
     <div class="request-container" id="request-container" style="display: none;">
         <!-- Back Button -->
         <div>
             <a href="#" id="choice2-link" class="btn btn-primary"><img src="../../Assets/Images/Icon/backbtn_black.png"
                     alt="Back Button"></a>
             <h4>Applicant Request</h4>
         </div>
         <!-- Partnership Request Table  -->
         <div class="partnership-request-table">
             <table class="table">
                 <thead>
                     <tr>
                         <th scope="col">Name</th>
                         <th scope="col">Partner Type</th>
                         <th scope="col">Status</th>
                         <th scope="col">Action</th>
                     </tr>
                 </thead>
                 <tbody>
                     <!-- Select to display all the applicants  -->
                     <?php
            $selectQuery = "SELECT u.firstName, u.lastName, p.partnerType, p.status, p.partnershipID 
        FROM partnerships p
        INNER JOIN users u ON p.userID = u.userID
        WHERE status = 'Pending'
        ";
            $result = mysqli_query($conn, $selectQuery);
            if (mysqli_num_rows($result) > 0) {
              foreach ($result as $applicants) {
                $name = $applicants['firstName'] . " " . $applicants['lastName'];
                $partnerID = $applicants['partnershipID'];
            ?>
                     <tr>
                         <td scope="row"><?= $name ?></td>

                         <td scope="row"><?= ucfirst($applicants['partnerType'])  ?></td>

                         <td scope="row" class="btn btn-danger">
                             <?= $applicants['status'] ?>
                         </td>
                         <td scope="row">
                             <form action="partnership.php?container=2" method="POST" style="display:inline;">
                                 <input type="hidden" name="partnerID" value="<?= $partnerID ?>">
                                 <button type="submit" class="btn btn-info">View</button>
                             </form>
                         </td>
                         </td>
                         <?php
              }
            } else {
                ?>
                         <td colspan="5">
                             <h5 scope="row" class="text-center">No record Found!</h5>
                         </td>
                         <?php
            } ?>
                 </tbody>
             </table>
         </div>
     </div>
     <!-- Bootstrap Link -->
     <script src="../../Assets/JS/bootstrap.bundle.min.js"></script>

     <!-- Pages hide/show -->
     <script>
     document.addEventListener("DOMContentLoaded", function() {

         const requestLink = document.getElementById("request-link");
         const partnerLink = document.getElementById("partner-link");
         const choice1Link = document.getElementById("choice1-link");
         const choice2Link = document.getElementById("choice2-link");

         const choices = document.getElementById("choice-container");
         const partnerContainer = document.getElementById("partner-container");
         const requestContainer = document.getElementById("request-container");

         choice1Link.addEventListener('click', function(event) {
             choices.style.display = "block";
             partnerContainer.style.display = "none";
             requestContainer.style.display = "none";
         });

         choice2Link.addEventListener('click', function(event) {
             choices.style.display = "block";
             partnerContainer.style.display = "none";
             requestContainer.style.display = "none";
         });

         requestLink.addEventListener('click', function(event) {
             choices.style.display = "none";
             partnerContainer.style.display = "none";
             requestContainer.style.display = "block";
         });

         partnerLink.addEventListener('click', function(event) {
             choices.style.display = "none";
             partnerContainer.style.display = "block";
             requestContainer.style.display = "none";
         });
     });
     </script>
     <!-- Search URL -->
     <script>
     const params = new URLSearchParams(window.location.search);
     const paramValue = params.get('container');

     const choices = document.getElementById("choice-container");
     const partnerContainer = document.getElementById("partner-container");
     const requestContainer = document.getElementById("request-container");

     if (paramValue == 2) {
         choices.style.display = "none";
         partnerContainer.style.display = "block";
         requestContainer.style.display = "none";
     } else if (paramValue == 3) {
         choices.style.display = "none";
         partnerContainer.style.display = "none";
         requestContainer.style.display = "block";
     }

     if (paramValue) {
         const url = new URL(window.location);
         url.search = '';
         history.replaceState({}, document.title, url.toString());
     };
     </script>
 </body>

 </html>
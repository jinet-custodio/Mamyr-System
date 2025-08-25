<?php
function checkSessionTimeout($timeout = 3600, $redirectUrl = '', $errorMessage = 'Session Expired')
{

    if (empty($redirectUrl)) {
        $redirectUrl = '../register.php?session=expired';
    }

    if (!isset($_SESSION['last_activity'])) {
        $_SESSION['last_activity'] = time();
        return;
    }

    if ((time() - $_SESSION['last_activity']) > $timeout) {
        if ($errorMessage) {
            $_SESSION['error'] = $errorMessage;
        }

        session_unset();
        session_destroy();
        header("Location: $redirectUrl");
        exit();
    }

    $_SESSION['last_activity'] = time();
}

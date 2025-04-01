<?php

function resetExpiredOTPs($conn)
{
    $query = "UPDATE users SET userOTP = NULL, OTP_expiration_at = NULL 
              WHERE OTP_expiration_at IS NOT NULL AND OTP_expiration_at < NOW() - INTERVAL 5 MINUTE";

    if (!$conn->query($query)) {
        echo "Error updating OTPs: " . $conn->error;
    }
}

resetExpiredOTPs($conn);

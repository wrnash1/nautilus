<?php
$url = 'http://localhost:8080/store/login';
echo "Testing POST to $url\n";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, "email=admin@nautilus.local&password=admin123");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_HEADER, true); // Include headers in output

$response = curl_exec($ch);
$info = curl_getinfo($ch);

if ($response === false) {
    echo "Curl error: " . curl_error($ch) . "\n";
} else {
    echo "HTTP Code: " . $info['http_code'] . "\n";
    echo "Redirect URL: " . $info['redirect_url'] . "\n";
    echo "Response:\n" . $response . "\n";
}

curl_close($ch);

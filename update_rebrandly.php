<?php
// Script tu dong cap nhat link Cloudflare len Rebrandly
// Duoc goi tu file .bat moi khi co link moi

// ===== PHAN CAI DAT (Ban can dien thong tin vao day) =====
$rebrandlyKey = 'c4bd73eab7f34680a1e8376a73cd396b'; // API Key Rebrandly cua ban
$shortLink = 'rebrand.ly/nhapvattu'; // Link rut gon ban da tao tren Rebrandly
// =========================================================

if ($argc < 2) {
    echo "ERROR: Thieu tham so URL Cloudflare.\n";
    exit(1);
}

$newUrl = $argv[1];

if ($rebrandlyKey === '' || $rebrandlyKey === 'YOUR_REBRANDLY_API_KEY_HERE') {
    echo "WARNING: Ban chua dien API Key vao file update_rebrandly.php\n";
    exit(1);
}

// 1. Tach domain va slashtag tu link ngan
$parts = explode('/', str_replace(['http://', 'https://'], '', $shortLink));
$domain = $parts[0];
$slashtag = $parts[1];

// 2. Goi API tim Link ID cua link rut gon nay
$searchApi = "https://api.rebrandly.com/v1/links?domain.fullName=" . urlencode($domain) . "&slashtag=" . urlencode($slashtag);

$ch = curl_init($searchApi);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'apikey: ' . $rebrandlyKey,
    'Content-Type: application/json'
]);
$searchResponse = curl_exec($ch);
curl_close($ch);

$links = json_decode($searchResponse, true);

if (empty($links) || !isset($links[0]['id'])) {
    echo "ERROR: Khong tim thay link rut gon [$shortLink] tren tai khoan Rebrandly cua ban.\n";
    echo "Kiem tra lai: Ban da dung API Key chua? Va ban thuc su da tao link nay tren web Rebrandly chua?\n";
    exit(1);
}

$linkId = $links[0]['id'];

// 3. Goi API cap nhat lai URL dich tuyet doi
$updateApi = "https://api.rebrandly.com/v1/links/" . $linkId;
$updateData = json_encode([
    'destination' => $newUrl
]);

$ch2 = curl_init($updateApi);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch2, CURLOPT_POSTFIELDS, $updateData);
curl_setopt($ch2, CURLOPT_HTTPHEADER, [
    'apikey: ' . $rebrandlyKey,
    'Content-Type: application/json'
]);

$updateResponse = curl_exec($ch2);
$httpCode = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
curl_close($ch2);

if ($httpCode >= 200 && $httpCode < 300) {
    echo "SUCCESS: Da cap nhat Rebrandly [$shortLink] thanh cong voi link moi: " . $newUrl . "\n";
} else {
    echo "ERROR: Cap nhat Rebrandly that bai (HTTP $httpCode).\n";
    echo "Chi tiet Loi: " . $updateResponse . "\n";
}
?>
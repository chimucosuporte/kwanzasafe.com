<?php
/**
 * Downloads missing Composer package zips to the local files cache
 * using PHP curl (which works even when Composer's downloader fails).
 */

$lock     = json_decode(file_get_contents(__DIR__ . '/composer.lock'), true);
$cacheDir = 'C:/Users/mults/AppData/Local/Composer/files/';
$missing  = [];

foreach (array_merge($lock['packages'] ?? [], $lock['packages-dev'] ?? []) as $pkg) {
    $name = $pkg['name'];
    $dist = $pkg['dist'] ?? null;
    if (!$dist || $dist['type'] !== 'zip') continue;
    $distUrl  = $dist['url'] ?? '';
    if (!$distUrl) continue;
    // Composer files cache key = sha1 of the dist URL (not the reference hash)
    $cacheKey  = sha1($distUrl);
    $cacheFile = $cacheDir . $name . '/' . $cacheKey . '.zip';
    if (file_exists($cacheFile) && filesize($cacheFile) > 10000) continue;
    $missing[] = ['name' => $name, 'url' => $distUrl, 'cache' => $cacheFile];
}

$total = count($missing);
echo "Packages to download: $total\n";

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT        => 60,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_USERAGENT      => 'Composer/2.9 (PHP/8.3)',
    CURLOPT_HTTPHEADER     => ['Accept: application/vnd.github+json'],
]);

$ok = 0;
$fail = 0;

foreach ($missing as $i => $pkg) {
    $n = $i + 1;
    echo "[$n/$total] {$pkg['name']}... ";
    @mkdir(dirname($pkg['cache']), 0777, true);

    curl_setopt($ch, CURLOPT_URL, $pkg['url']);
    $data = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);

    if ($data && $httpCode === 200 && strlen($data) > 100) {
        file_put_contents($pkg['cache'], $data);
        echo "OK (" . number_format(strlen($data)) . " bytes)\n";
        $ok++;
    } else {
        echo "FAIL (HTTP $httpCode, err: $err, size: " . strlen($data) . ")\n";
        $fail++;
    }
    // Brief pause to avoid rate limiting
    usleep(100000); // 100ms
}

curl_close($ch);

echo "\nDone: $ok ok, $fail failed out of $total\n";

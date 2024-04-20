<?php
function cacheContents(array $callLogs): array
{
    $mainMemory = [];
    $cache = [];

    foreach ($callLogs as [$timestamp, $itemId]) {
        if (!isset($mainMemory[$itemId])) {
            $mainMemory[$itemId] = ['priority' => 0, 'lastAccessTime' => $timestamp, 'accessCount' => 0];
        }

        $timeDiff = $timestamp - $mainMemory[$itemId]['lastAccessTime'];
        $mainMemory[$itemId]['priority'] = max(0, $mainMemory[$itemId]['priority'] - $timeDiff);

        $mainMemory[$itemId]['priority'] += 2 * $mainMemory[$itemId]['accessCount'] + 2;
        $mainMemory[$itemId]['lastAccessTime'] = $timestamp;
        $mainMemory[$itemId]['accessCount']++;

        if ($mainMemory[$itemId]['priority'] > 5) {
            $cache[$itemId] = true;
        } elseif (isset($cache[$itemId]) && $mainMemory[$itemId]['priority'] <= 3) {
            unset($cache[$itemId]);
        }
    }

    ksort($cache);
    return empty($cache) ? [-1] : array_keys($cache);
}

$outputPath = getenv("OUTPUT_PATH") ?: "output.txt";

$fptr = fopen($outputPath, "w");

$callLogs_rows = intval(trim(fgets(STDIN)));
$callLogs_columns = intval(trim(fgets(STDIN)));

$callLogs = [];

for ($i = 0; $i < $callLogs_rows; $i++) {
    $callLogs_temp = rtrim(fgets(STDIN));
    $callLogs[] = array_map('intval', preg_split('/ /', $callLogs_temp, -1, PREG_SPLIT_NO_EMPTY));
}

$result = cacheContents($callLogs);

fwrite($fptr, implode("\n", $result) . "\n");

fclose($fptr);
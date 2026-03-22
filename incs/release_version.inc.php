<?php

function getLatestGitHubRelease(
    string $owner,
    string $repo,
    string $mode = 'latest',
    ?string $token = null
): ?string {
    if (!function_exists('curl_init')) {
        return null;
    }

    $mode = strtolower(trim($mode));
    if ($mode !== 'highest') {
        $mode = 'latest';
    }

    $request = static function (string $url) use ($token): ?array {
        $headers = [
            'Accept: application/vnd.github+json',
            'X-GitHub-Api-Version: 2022-11-28',
            'User-Agent: TicketsCAD-Version-Check',
        ];

        if ($token !== null && $token !== '') {
            $headers[] = 'Authorization: Bearer ' . $token;
        }

        $ch = @curl_init();
        if ($ch === false) {
            return null;
        }

        @curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_FAILONERROR => false,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        $response = @curl_exec($ch);

        if ($response === false) {
            @curl_close($ch);
            return null;
        }

        $httpCode = (int) @curl_getinfo($ch, CURLINFO_HTTP_CODE);
        @curl_close($ch);

        if ($httpCode < 200 || $httpCode >= 300) {
            return null;
        }

        $data = json_decode($response, true);

        return is_array($data) ? $data : null;
    };

    if ($mode === 'latest') {
        $data = $request("https://api.github.com/repos/{$owner}/{$repo}/releases/latest");

        if (!is_array($data) || empty($data['tag_name']) || !is_string($data['tag_name'])) {
            return null;
        }

        return trim($data['tag_name']);
    }

    $bestTag = null;
    $bestVersion = null;

    for ($page = 1; $page <= 5; $page++) {
        $data = $request("https://api.github.com/repos/{$owner}/{$repo}/tags?per_page=100&page={$page}");

        if (!is_array($data) || empty($data)) {
            break;
        }

        foreach ($data as $row) {
            if (!is_array($row) || empty($row['name']) || !is_string($row['name'])) {
                continue;
            }

            $tag = trim($row['name']);
            if ($tag === '') {
                continue;
            }

            $version = null;

            if (preg_match('/(\d+(?:\.\d+)+(?:[-+][0-9A-Za-z.\-]+)?)/', $tag, $matches)) {
                $version = $matches[1];
            } elseif (preg_match('/^v?(\d+)$/i', $tag, $matches)) {
                $version = $matches[1];
            }

            if ($version === null) {
                continue;
            }

            if ($bestVersion === null || version_compare($version, $bestVersion, '>')) {
                $bestVersion = $version;
                $bestTag = $tag;
            }
        }

        if (count($data) < 100) {
            break;
        }
    }

    return $bestTag;
}

if (
    isset($_SERVER['SCRIPT_FILENAME']) &&
    realpath($_SERVER['SCRIPT_FILENAME']) === __FILE__
) {
    $latest = getLatestGitHubRelease('openises', 'tickets');
    $highest = getLatestGitHubRelease('openises', 'tickets', 'highest');

    echo "Latest release: " . ($latest ?? 'unknown') . "\n<br>";
    echo "Highest numeric tag: " . ($highest ?? 'unknown') . "\n";
}

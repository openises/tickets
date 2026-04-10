<?php
/**
 * Fix misplaced intval() parentheses and negation precedence bugs.
 *
 * Three patterns fixed:
 * 1. intval(get_variable('xxx')==N)  => (intval(get_variable('xxx')) == N)
 * 2. !get_variable('xxx')==N         => (get_variable('xxx') != N)
 * 3. intval(get_variable('xxx')*N)   => intval(get_variable('xxx'))*N
 *
 * These all worked by accident in PHP 5/7 but are semantically wrong.
 * Pattern 1 was also a PHP 8 behavior change (see tables.php fix).
 *
 * Usage: php tools/fix_intval_parens.php
 * Safe to run multiple times — idempotent.
 */

$root = dirname(__DIR__);

$patterns = [
    // intval(get_variable('xxx')==N) => (intval(get_variable('xxx')) == N)
    '/intval\(get_variable\(([^)]+)\)\s*==\s*(\d+)\)/' => '(intval(get_variable($1)) == $2)',
    // !get_variable('xxx')==N => (get_variable('xxx') != N)
    '/!get_variable\(([^)]+)\)\s*==\s*(\d+)/' => '(get_variable($1) != $2)',
    // intval(get_variable('xxx')*N ) => intval(get_variable('xxx'))*N
    '/intval\(get_variable\(([^)]+)\)\s*\*\s*(\d+)\s*\)/' => 'intval(get_variable($1))*$2',
];

$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
$totalFixed = 0;

foreach ($it as $fileInfo) {
    if ($fileInfo->getExtension() !== 'php') continue;
    $path = $fileInfo->getPathname();
    if (strpos($path, 'vendor') !== false) continue;
    if (strpos($path, 'node_modules') !== false) continue;
    if (basename($path) === 'fix_intval_parens.php') continue;

    $content = file_get_contents($path);
    $fileCount = 0;

    foreach ($patterns as $pattern => $replace) {
        $content = preg_replace($pattern, $replace, $content, -1, $c);
        $fileCount += $c;
    }

    if ($fileCount > 0) {
        file_put_contents($path, $content);
        $rel = str_replace($root . DIRECTORY_SEPARATOR, '', $path);
        $rel = str_replace('\\', '/', $rel);
        echo "[FIXED] $rel ($fileCount)\n";
        $totalFixed += $fileCount;
    }
}

echo "\nTotal: $totalFixed fixes applied\n";

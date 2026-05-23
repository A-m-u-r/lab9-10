<?php

declare(strict_types=1);

if ($argc < 3) {
    fwrite(STDERR, "Usage: php scripts/check-coverage.php <clover.xml> <minimum-percent>\n");
    exit(2);
}

$path = $argv[1];
$minimum = (float) $argv[2];

if (! is_file($path)) {
    fwrite(STDERR, "Coverage report not found: {$path}\n");
    exit(2);
}

$xml = simplexml_load_file($path);
if ($xml === false) {
    fwrite(STDERR, "Cannot parse coverage report: {$path}\n");
    exit(2);
}

$metrics = $xml->project->metrics;
$statements = (int) ($metrics['statements'] ?? 0);
$coveredStatements = (int) ($metrics['coveredstatements'] ?? 0);

if ($statements === 0) {
    fwrite(STDERR, "Coverage report contains no statements.\n");
    exit(2);
}

$coverage = ($coveredStatements / $statements) * 100;
printf("Line coverage: %.2f%% (%d/%d statements)\n", $coverage, $coveredStatements, $statements);

if ($coverage < $minimum) {
    fwrite(STDERR, sprintf("Coverage gate failed: %.2f%% < %.2f%%\n", $coverage, $minimum));
    exit(1);
}

printf("Coverage gate passed: %.2f%% >= %.2f%%\n", $coverage, $minimum);

<?php
/**
 * Comprehensive Migration File Checker
 * Checks all migration files for common errors
 */

$migrationsDir = __DIR__ . '/database/migrations';
$files = glob($migrationsDir . '/*.sql');
sort($files);

echo "=== MIGRATION FILE CHECKER ===\n";
echo "Checking " . count($files) . " migration files\n\n";

$errors = [];
$warnings = [];
$tableDefinitions = []; // Track which tables are created in which migration

// First pass: Extract all table definitions
echo "=== PASS 1: Extracting Table Definitions ===\n";
foreach ($files as $file) {
    $filename = basename($file);
    $content = file_get_contents($file);

    // Find CREATE TABLE statements
    if (preg_match_all('/CREATE\s+TABLE(?:\s+IF\s+NOT\s+EXISTS)?\s+`?(\w+)`?/i', $content, $matches)) {
        foreach ($matches[1] as $table) {
            if (!isset($tableDefinitions[$table])) {
                $tableDefinitions[$table] = [];
            }
            $tableDefinitions[$table][] = $filename;
        }
    }
}

echo "Found " . count($tableDefinitions) . " unique tables\n\n";

// Second pass: Check for errors
echo "=== PASS 2: Checking for Errors ===\n\n";

foreach ($files as $file) {
    $filename = basename($file);
    $content = file_get_contents($file);
    $fileErrors = [];
    $fileWarnings = [];

    // Check 1: Syntax - look for common SQL errors
    if (preg_match('/CREATE\s+TABLE.*?\(/is', $content)) {
        // Check for unmatched parentheses
        $openParen = substr_count($content, '(');
        $closeParen = substr_count($content, ')');
        if ($openParen !== $closeParen) {
            $fileErrors[] = "Unmatched parentheses (open: $openParen, close: $closeParen)";
        }
    }

    // Check 2: Foreign key references
    if (preg_match_all('/FOREIGN\s+KEY\s*\([`\']?(\w+)[`\']?\)\s+REFERENCES\s+[`\']?(\w+)[`\']?\s*\([`\']?(\w+)[`\']?\)/i', $content, $fkMatches, PREG_SET_ORDER)) {
        foreach ($fkMatches as $match) {
            $fkColumn = $match[1];
            $refTable = $match[2];
            $refColumn = $match[3];

            // Check if referenced table is defined
            if (!isset($tableDefinitions[$refTable])) {
                $fileErrors[] = "FK references undefined table '$refTable' (column: $fkColumn → $refTable.$refColumn)";
            }
        }
    }

    // Check 3: Column type consistency for foreign keys
    if (preg_match_all('/`(\w+)`\s+(INT\s+UNSIGNED|INT|BIGINT\s+UNSIGNED|BIGINT|VARCHAR\(\d+\))[^,]*?,?\s+.*?FOREIGN\s+KEY\s*\([`\']?\1[`\']?\)\s+REFERENCES\s+[`\']?(\w+)[`\']?\s*\([`\']?(\w+)[`\']?\)/is', $content, $typeMatches, PREG_SET_ORDER)) {
        foreach ($typeMatches as $match) {
            $column = $match[1];
            $type = preg_replace('/\s+/', ' ', trim($match[2]));
            $refTable = $match[3];
            $refColumn = $match[4];

            // Store for cross-file checking
            if (!isset($tableDefinitions[$refTable])) {
                continue; // Already flagged above
            }

            $fileWarnings[] = "FK $column ($type) → $refTable.$refColumn (verify types match)";
        }
    }

    // Check 4: ALTER TABLE on non-existent tables
    if (preg_match_all('/ALTER\s+TABLE\s+[`\']?(\w+)[`\']?/i', $content, $alterMatches)) {
        foreach ($alterMatches[1] as $alterTable) {
            // Check if this table was created in an earlier migration
            if (!isset($tableDefinitions[$alterTable])) {
                $fileErrors[] = "ALTER TABLE on undefined table '$alterTable'";
            } else {
                // Check if any of the files that define this table come AFTER current file
                $currentIndex = array_search($filename, array_map('basename', $files));
                foreach ($tableDefinitions[$alterTable] as $definingFile) {
                    $definingIndex = array_search($definingFile, array_map('basename', $files));
                    if ($definingIndex > $currentIndex) {
                        $fileErrors[] = "ALTER TABLE '$alterTable' before it's created in $definingFile";
                    }
                }
            }
        }
    }

    // Check 5: DROP TABLE IF EXISTS followed by CREATE TABLE without IF NOT EXISTS
    if (preg_match_all('/DROP\s+TABLE\s+IF\s+EXISTS\s+[`\']?(\w+)[`\']?.*?CREATE\s+TABLE\s+[`\']?\1[`\']?\s+\(/is', $content, $dropCreate)) {
        // This is OK - explicit drop and recreate
    }

    // Check 6: Invalid nullable type hints (PHP-style errors that might be in comments)
    if (preg_match('/\?\?int|\?\?string/', $content)) {
        $fileErrors[] = "Invalid PHP nullable syntax found in SQL (??int or ??string)";
    }

    // Check 7: Missing ENGINE=InnoDB (required for FK support)
    if (preg_match_all('/CREATE\s+TABLE.*?\)(?!\s*ENGINE=InnoDB)/is', $content, $noEngine)) {
        if (!empty($noEngine[0])) {
            foreach ($noEngine[0] as $match) {
                if (preg_match('/CREATE\s+TABLE(?:\s+IF\s+NOT\s+EXISTS)?\s+`?(\w+)`?/i', $match, $tableMatch)) {
                    $fileWarnings[] = "Table '{$tableMatch[1]}' might be missing ENGINE=InnoDB";
                }
            }
        }
    }

    // Check 8: ON DELETE/UPDATE actions without proper syntax
    if (preg_match('/ON\s+(DELETE|UPDATE)\s+(?!CASCADE|SET NULL|SET DEFAULT|RESTRICT|NO ACTION)/i', $content, $invalidAction)) {
        $fileErrors[] = "Invalid ON DELETE/UPDATE action: " . trim($invalidAction[0]);
    }

    // Output results for this file
    if (!empty($fileErrors) || !empty($fileWarnings)) {
        echo "📄 $filename\n";

        if (!empty($fileErrors)) {
            foreach ($fileErrors as $error) {
                echo "  ❌ ERROR: $error\n";
                $errors[] = ['file' => $filename, 'error' => $error];
            }
        }

        if (!empty($fileWarnings)) {
            foreach ($fileWarnings as $warning) {
                echo "  ⚠️  WARNING: $warning\n";
                $warnings[] = ['file' => $filename, 'warning' => $warning];
            }
        }

        echo "\n";
    }
}

// Check 9: Check for duplicate table definitions
echo "=== PASS 3: Checking for Duplicate Table Definitions ===\n\n";
foreach ($tableDefinitions as $table => $files) {
    if (count($files) > 1) {
        // Check if any are CREATE TABLE (not IF NOT EXISTS)
        $createsWithoutIfNotExists = [];
        foreach ($files as $file) {
            $content = file_get_contents($migrationsDir . '/' . $file);
            if (preg_match('/CREATE\s+TABLE\s+`?' . preg_quote($table, '/') . '`?\s+\(/i', $content)) {
                $createsWithoutIfNotExists[] = $file;
            }
        }

        if (count($createsWithoutIfNotExists) > 1) {
            echo "❌ Table '$table' created multiple times without IF NOT EXISTS:\n";
            foreach ($createsWithoutIfNotExists as $f) {
                echo "     - $f\n";
            }
            echo "\n";
            $errors[] = ['file' => 'multiple', 'error' => "Table '$table' has conflicting CREATE statements"];
        } else if (count($files) > 1) {
            echo "⚠️  Table '$table' defined in multiple files:\n";
            foreach ($files as $f) {
                echo "     - $f\n";
            }
            echo "   (This is OK if using CREATE TABLE IF NOT EXISTS)\n\n";
        }
    }
}

// Summary
echo "\n=== SUMMARY ===\n";
echo "Total files checked: " . count($files) . "\n";
echo "Total tables found: " . count($tableDefinitions) . "\n";
echo "Total errors: " . count($errors) . "\n";
echo "Total warnings: " . count($warnings) . "\n\n";

if (count($errors) > 0) {
    echo "❌ ERRORS FOUND - These will cause migration failures:\n";
    foreach ($errors as $e) {
        echo "   {$e['file']}: {$e['error']}\n";
    }
    echo "\n";
}

if (count($warnings) > 0) {
    echo "⚠️  WARNINGS - These should be reviewed:\n";
    $uniqueWarnings = array_unique(array_column($warnings, 'warning'));
    echo "   " . count($uniqueWarnings) . " unique warnings found\n";
    echo "   Run with verbose flag to see all warnings\n\n";
}

if (count($errors) === 0 && count($warnings) === 0) {
    echo "✅ No errors or warnings found!\n";
}

// Exit with error code if errors found
exit(count($errors) > 0 ? 1 : 0);

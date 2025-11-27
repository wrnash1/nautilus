<?php
/**
 * Database Migration Converter
 * 
 * Converts MySQL migration files to PostgreSQL-compatible syntax
 * 
 * Usage:
 *   php scripts/convert-migrations.php --target=postgresql
 *   php scripts/convert-migrations.php --target=mysql --validate
 */

class MigrationConverter
{
    private string $inputDir = 'database/migrations';
    private string $outputDir = 'database/migrations/postgresql';
    private array $conversionLog = [];
    private int $filesConverted = 0;
    private int $errors = 0;

    public function __construct()
    {
        echo "╔══════════════════════════════════════════════════════════╗\n";
        echo "║         DATABASE MIGRATION CONVERTER                     ║\n";
        echo "║         MySQL → PostgreSQL                               ║\n";
        echo "╚══════════════════════════════════════════════════════════╝\n\n";
    }

    /**
     * Convert MySQL SQL to PostgreSQL
     */
    public function mysqlToPostgresql(string $sql): string
    {
        $original = $sql;
        
        // Step 1: Convert AUTO_INCREMENT to SERIAL
        // Pattern: `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
        // Replace with: "id" SERIAL PRIMARY KEY
        $sql = preg_replace(
            '/`(\w+)`\s+INT(?:\s+UNSIGNED)?\s+AUTO_INCREMENT\s+PRIMARY\s+KEY/i',
            '"$1" SERIAL PRIMARY KEY',
            $sql
        );
        
        // Step 2: Convert BIGINT AUTO_INCREMENT to BIGSERIAL
        $sql = preg_replace(
            '/`(\w+)`\s+BIGINT(?:\s+UNSIGNED)?\s+AUTO_INCREMENT\s+PRIMARY\s+KEY/i',
            '"$1" BIGSERIAL PRIMARY KEY',
            $sql
        );
        
        // Step 3: Remove MySQL-specific ENGINE and CHARSET clauses
        $sql = preg_replace(
            '/\)\s*ENGINE\s*=\s*InnoDB\s+DEFAULT\s+CHARSET\s*=\s*utf8mb4\s+COLLATE\s*=\s*utf8mb4_unicode_ci\s*;/i',
            ');',
            $sql
        );
        
        // Step 4: Convert backticks to double quotes
        $sql = str_replace('`', '"', $sql);
        
        // Step 5: Convert DATETIME to TIMESTAMP
        $sql = preg_replace(
            '/(\s+)DATETIME(\s+|\)|,)/i',
            '$1TIMESTAMP$2',
            $sql
        );
        
        // Step 6: Convert INT UNSIGNED to INTEGER
        $sql = preg_replace(
            '/\s+INT\s+UNSIGNED\s+/i',
            ' INTEGER ',
            $sql
        );
        
        // Step 7: Convert BIGINT UNSIGNED to BIGINT
        $sql = preg_replace(
            '/\s+BIGINT\s+UNSIGNED\s+/i',
            ' BIGINT ',
            $sql
        );
        
        // Step 8: Convert TINYINT to SMALLINT
        $sql = preg_replace(
            '/\s+TINYINT(?:\(\d+\))?\s+/i',
            ' SMALLINT ',
            $sql
        );
        
        // Step 9: Convert VARCHAR with collation
        $sql = preg_replace(
            '/VARCHAR\((\d+)\)\s+COLLATE\s+\w+/i',
            'VARCHAR($1)',
            $sql
        );
        
        // Step 10: Convert DEFAULT CURRENT_TIMESTAMP to DEFAULT CURRENT_TIMESTAMP
        // (Same syntax, but ensure proper formatting)
        $sql = preg_replace(
            '/DEFAULT\s+CURRENT_TIMESTAMP\s+ON\s+UPDATE\s+CURRENT_TIMESTAMP/i',
            'DEFAULT CURRENT_TIMESTAMP',
            $sql
        );
        
        // Step 11: Convert ENUM to CHECK constraint
        // This is complex - for now, keep ENUM (PostgreSQL supports it)
        // Future: Convert to CHECK constraints or custom types
        
        // Step 12: Remove AUTO_INCREMENT from non-PRIMARY KEY columns
        $sql = preg_replace(
            '/AUTO_INCREMENT/i',
            '',
            $sql
        );
        
        // Step 13: Convert INDEX syntax
        $sql = preg_replace(
            '/,\s*KEY\s+"(\w+)"\s*\("([^"]+)"\)/i',
            ', INDEX "$1" ("$2")',
            $sql
        );
        
        // Track if conversion made changes
        if ($original !== $sql) {
            $this->conversionLog[] = "File converted with " . substr_count($original, '`') . " backticks replaced";
        }
        
        return $sql;
    }

    /**
     * Convert a single migration file
     */
    public function convertFile(string $inputPath, string $outputPath): bool
    {
        try {
            if (!file_exists($inputPath)) {
                throw new Exception("Input file not found: $inputPath");
            }
            
            // Read MySQL migration
            $mysqlSql = file_get_contents($inputPath);
            
            // Convert to PostgreSQL
            $postgresqlSql = $this->mysqlToPostgresql($mysqlSql);
            
            // Ensure output directory exists
            $outputDir = dirname($outputPath);
            if (!is_dir($outputDir)) {
                mkdir($outputDir, 0755, true);
            }
            
            // Write PostgreSQL migration
            file_put_contents($outputPath, $postgresqlSql);
            
            $this->filesConverted++;
            return true;
        } catch (Exception $e) {
            $this->errors++;
            echo "✗ Error converting {$inputPath}: {$e->getMessage()}\n";
            return false;
        }
    }

    /**
     * Convert all migration files
     */
    public function convertAll(): void
    {
        echo "📁 Input Directory: {$this->inputDir}\n";
        echo "📁 Output Directory: {$this->outputDir}\n\n";
        
        // Get all SQL files
        $files = glob($this->inputDir . '/*.sql');
        
        if (empty($files)) {
            echo "⚠️  No SQL files found in {$this->inputDir}\n";
            return;
        }
        
        echo "Found " . count($files) . " migration files\n\n";
        echo "Converting...\n";
        
        foreach ($files as $inputPath) {
            $filename = basename($inputPath);
            $outputPath = $this->outputDir . '/' . $filename;
            
            echo "  • {$filename}...";
            
            if ($this->convertFile($inputPath, $outputPath)) {
                echo " ✓\n";
            }
        }
        
        echo "\n";
        $this->printSummary();
    }

    /**
     * Validate converted files
     */
    public function validate(): void
    {
        echo "\n📋 Validation Report:\n\n";
        
        $files = glob($this->outputDir . '/*.sql');
        $issues = [];
        
        foreach ($files as $file) {
            $content = file_get_contents($file);
            $filename = basename($file);
            
            // Check for remaining MySQL-specific syntax
            if (strpos($content, 'AUTO_INCREMENT') !== false) {
                $issues[] = "$filename: Still contains AUTO_INCREMENT";
            }
            if (strpos($content, 'ENGINE=') !== false) {
                $issues[] = "$filename: Still contains ENGINE clause";
            }
            if (strpos($content, 'CHARSET=') !== false) {
                $issues[] = "$filename: Still contains CHARSET clause";
            }
            if (strpos($content, '`') !== false) {
                $issues[] = "$filename: Still contains backticks";
            }
        }
        
        if (empty($issues)) {
            echo "✅ All validations passed!\n";
            echo "   No MySQL-specific syntax found.\n";
        } else {
            echo "⚠️  Found " . count($issues) . " potential issues:\n\n";
            foreach ($issues as $issue) {
                echo "   • $issue\n";
            }
        }
    }

    /**
     * Print conversion summary
     */
    private function printSummary(): void
    {
        echo "╔══════════════════════════════════════════════════════════╗\n";
        echo "║                  CONVERSION SUMMARY                      ║\n";
        echo "╚══════════════════════════════════════════════════════════╝\n\n";
        echo "  Files processed: {$this->filesConverted}\n";
        echo "  Errors: {$this->errors}\n";
        echo "  Success rate: " . ($this->filesConverted > 0 ? 
            round(($this->filesConverted - $this->errors) / $this->filesConverted * 100, 1) : 0) . "%\n\n";
        
        if ($this->errors === 0) {
            echo "✅ All migrations converted successfully!\n\n";
        } else {
            echo "⚠️  Some files had errors. Please review above.\n\n";
        }
    }
}

// CLI execution
if (php_sapi_name() === 'cli') {
    $options = getopt('', ['target:', 'validate', 'help']);
    
    if (isset($options['help'])) {
        echo "Database Migration Converter\n\n";
        echo "Usage:\n";
        echo "  php scripts/convert-migrations.php --target=postgresql\n";
        echo "  php scripts/convert-migrations.php --target=postgresql --validate\n\n";
        echo "Options:\n";
        echo "  --target=TYPE     Target database (currently only 'postgresql')\n";
        echo "  --validate        Validate converted files for remaining MySQL syntax\n";
        echo "  --help            Show this help message\n\n";
        exit(0);
    }
    
    $target = $options['target'] ?? 'postgresql';
    
    if ($target !== 'postgresql') {
        echo "Error: Only 'postgresql' target is currently supported\n";
        exit(1);
    }
    
    $converter = new MigrationConverter();
    $converter->convertAll();
    
    if (isset($options['validate'])) {
        $converter->validate();
    }
    
    echo "💡 Next Steps:\n";
    echo "   1. Review converted files in database/migrations/postgresql/\n";
    echo "   2. Test on a PostgreSQL database\n";
    echo "   3. Update InstallService.php to use PostgreSQL migrations\n\n";
}

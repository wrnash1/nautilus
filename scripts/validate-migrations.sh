#!/bin/bash
# Migration Validation Script
# Checks all SQL migration files for common errors

MIGRATION_DIR="/home/wrnash1/Developer/nautilus/database/migrations"
ERROR_LOG="/tmp/migration_errors.log"
> "$ERROR_LOG"

echo "=== Nautilus Database Migration Validator ==="
echo "Checking $MIGRATION_DIR..."
echo ""

total_files=0
error_count=0
warning_count=0

# Check 1: SQL Syntax - Missing semicolons at end of statements
echo "Check 1: SQL Syntax Validation..."
for file in "$MIGRATION_DIR"/*.sql; do
    ((total_files++))
    filename=$(basename "$file")
    
    # Check for CREATE TABLE without semicolon
    if grep -P "CREATE TABLE.*\)" "$file" | grep -v ";" > /dev/null 2>&1; then
        echo "ERROR in $filename: CREATE TABLE missing semicolon" | tee -a "$ERROR_LOG"
        ((error_count++))
    fi
    
    # Check for INSERT INTO without semicolon
    if grep -P "INSERT INTO.*\)" "$file" | grep -v ";" > /dev/null 2>&1; then
        echo "WARNING in $filename: INSERT statement may be missing semicolon" | tee -a "$ERROR_LOG"
        ((warning_count++))
    fi
done

# Check 2: Foreign Key References
echo ""
echo "Check 2: Foreign Key References..."
declare -A tables_created
declare -A fk_refs

# First pass: collect all table names
for file in "$MIGRATION_DIR"/*.sql; do
    while IFS= read -r line; do
        if [[ $line =~ CREATE[[:space:]]+TABLE[[:space:]]+IF[[:space:]]+NOT[[:space:]]+EXISTS[[:space:]]+\`?([a-zA-Z0-9_]+)\`? ]] || \
           [[ $line =~ CREATE[[:space:]]+TABLE[[:space:]]+\`?([a-zA-Z0-9_]+)\`? ]]; then
            table_name="${BASH_REMATCH[1]}"
            tables_created["$table_name"]=1
        fi
    done < "$file"
done

# Second pass: check foreign key references
for file in "$MIGRATION_DIR"/*.sql; do
    filename=$(basename "$file")
    while IFS= read -r line; do
        if [[ $line =~ REFERENCES[[:space:]]+\`?([a-zA-Z0-9_]+)\`?\( ]]; then
            ref_table="${BASH_REMATCH[1]}"
            if [[ -z "${tables_created[$ref_table]}" ]]; then
                echo "ERROR in $filename: Foreign key references non-existent table '$ref_table'" | tee -a "$ERROR_LOG"
                ((error_count++))
            fi
        fi
    done < "$file"
done

# Check 3: Duplicate Table Names
echo ""
echo "Check 3: Duplicate Table Definitions..."
declare -A table_files
for file in "$MIGRATION_DIR"/*.sql; do
    filename=$(basename "$file")
    while IFS= read -r line; do
        if [[ $line =~ CREATE[[:space:]]+TABLE[[:space:]]+(IF[[:space:]]+NOT[[:space:]]+EXISTS[[:space:]]+)?\`?([a-zA-Z0-9_]+)\`? ]]; then
            table_name="${BASH_REMATCH[2]}"
            if [[ -n "${table_files[$table_name]}" ]]; then
                echo "WARNING: Table '$table_name' defined in both ${table_files[$table_name]} and $filename" | tee -a "$ERROR_LOG"
                ((warning_count++))
            else
                table_files["$table_name"]="$filename"
            fi
        fi
    done < "$file"
done

# Check 4: Common Typos
echo ""
echo "Check 4: Common SQL Typos..."
for file in "$MIGRATION_DIR"/*.sql; do
    filename=$(basename "$file")
    
    # Check for common typos
    if grep -i "FORIEGN KEY" "$file" > /dev/null 2>&1; then
        echo "ERROR in $filename: Typo 'FORIEGN' should be 'FOREIGN'" | tee -a "$ERROR_LOG"
        ((error_count++))
    fi
    
    if grep -i "DEFUALT" "$file" > /dev/null 2>&1; then
        echo "ERROR in $filename: Typo 'DEFUALT' should be 'DEFAULT'" | tee -a "$ERROR_LOG"
        ((error_count++))
    fi
    
    if grep -i "PRIMAY KEY" "$file" > /dev/null 2>&1; then
        echo "ERROR in $filename: Typo 'PRIMAY' should be 'PRIMARY'" | tee -a "$ERROR_LOG"
        ((error_count++))
    fi
done

# Check 5: Character Set and Collation
echo ""
echo "Check 5: Character Set Consistency..."
for file in "$MIGRATION_DIR"/*.sql; do
    filename=$(basename "$file")
    
    if grep "CREATE TABLE" "$file" > /dev/null 2>&1; then
        if ! grep -q "utf8mb4" "$file"; then
            echo "WARNING in $filename: Missing utf8mb4 charset" | tee -a "$ERROR_LOG"
            ((warning_count++))
        fi
    fi
done

# Summary
echo ""
echo "=== Validation Summary ==="
echo "Total files checked: $total_files"
echo "Errors found: $error_count"
echo "Warnings found: $warning_count"
echo ""

if [ $error_count -eq 0 ] && [ $warning_count -eq 0 ]; then
    echo "✓ All migrations passed validation!"
    exit 0
elif [ $error_count -eq 0 ]; then
    echo "✓ No errors found (only warnings)"
    echo "Full log: $ERROR_LOG"
    exit 0
else
    echo "✗ Errors detected - see details above"
    echo "Full log: $ERROR_LOG"
    exit 1
fi

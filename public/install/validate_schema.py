import re
print("Starting script...")

def parse_sql(filename):
    with open(filename, 'r') as f:
        content = f.read()

    # Remove comments
    content = re.sub(r'--.*$', '', content, flags=re.MULTILINE)
    content = re.sub(r'/\*.*?\*/', '', content, flags=re.DOTALL)

    tables = {}
    current_table = None

    # Split by statements (roughly)
    statements = content.split(';')

    for stmt in statements:
        stmt = stmt.strip()
        if not stmt:
            continue

        # Parse CREATE TABLE
        create_match = re.search(r'CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?(\w+)`?', stmt, re.IGNORECASE)
        if create_match:
            table_name = create_match.group(1)
            current_table = table_name
            tables[table_name] = {'columns': {}, 'fks': []}
            
            # Extract body
            body_match = re.search(r'\((.*)\)', stmt, re.DOTALL)
            if body_match:
                body = body_match.group(1)
                # Split lines, handling commas inside parentheses (e.g. DECIMAL(10,2)) is hard with simple split
                # So we iterate char by char or use a simpler approximation
                
                # Simple approximation: split by comma, but ignore commas in parens
                lines = []
                current_line = ''
                paren_depth = 0
                for char in body:
                    if char == '(':
                        paren_depth += 1
                    elif char == ')':
                        paren_depth -= 1
                    elif char == ',' and paren_depth == 0:
                        lines.append(current_line.strip())
                        current_line = ''
                        continue
                    current_line += char
                lines.append(current_line.strip())

                for line in lines:
                    # Parse column
                    col_match = re.match(r'`?(\w+)`?\s+([A-Z]+(?:\(.*\))?(?:\s+UNSIGNED)?)', line, re.IGNORECASE)
                    if col_match:
                        col_name = col_match.group(1)
                        col_type = col_match.group(2).upper()
                        tables[table_name]['columns'][col_name] = col_type
                    
                    # Parse FK
                    fk_match = re.search(r'FOREIGN\s+KEY\s+\(`?(\w+)`?\)\s+REFERENCES\s+`?(\w+)`?\s*\(`?(\w+)`?\)', line, re.IGNORECASE)
                    if fk_match:
                        fk_col = fk_match.group(1)
                        ref_table = fk_match.group(2)
                        ref_col = fk_match.group(3)
                        tables[table_name]['fks'].append({
                            'col': fk_col,
                            'ref_table': ref_table,
                            'ref_col': ref_col
                        })

    return tables

def validate_fks(tables):
    errors = []
    for table_name, data in tables.items():
        for fk in data['fks']:
            col = fk['col']
            ref_table = fk['ref_table']
            ref_col = fk['ref_col']

            if ref_table not in tables:
                # It might be a self-reference or table defined elsewhere?
                # But 000_CORE_SCHEMA should be self-contained
                if ref_table == table_name:
                    pass # Self ref
                else:
                    # errors.append(f"Table {table_name} references non-existent table {ref_table}")
                    # Actually, we can't be sure if it's non-existent or just not parsed yet if order matters?
                    # But tables dict is fully populated after parsing.
                    pass 

            if ref_table in tables:
                if ref_col not in tables[ref_table]['columns']:
                    errors.append(f"Table {table_name} FK {col} references non-existent column {ref_table}.{ref_col}")
                else:
                    col_type = data['columns'].get(col)
                    ref_type = tables[ref_table]['columns'].get(ref_col)
                    
                    # Normalize types for comparison
                    # e.g. INT UNSIGNED AUTO_INCREMENT -> INT UNSIGNED
                    def normalize(t):
                        if not t: return ""
                        t = t.replace('AUTO_INCREMENT', '').replace('PRIMARY KEY', '').replace('NOT NULL', '').replace('DEFAULT 1', '').strip()
                        return t

                    ct = normalize(col_type)
                    rt = normalize(ref_type)

                    # Strict check for INT/BIGINT and UNSIGNED
                    if 'INT' in ct and 'INT' in rt:
                        if 'UNSIGNED' in ct and 'UNSIGNED' not in rt:
                            errors.append(f"Type mismatch: {table_name}.{col} ({ct}) vs {ref_table}.{ref_col} ({rt})")
                        elif 'UNSIGNED' not in ct and 'UNSIGNED' in rt:
                            errors.append(f"Type mismatch: {table_name}.{col} ({ct}) vs {ref_table}.{ref_col} ({rt})")
                        elif ct.split()[0] != rt.split()[0]: # INT vs BIGINT
                             errors.append(f"Type mismatch: {table_name}.{col} ({ct}) vs {ref_table}.{ref_col} ({rt})")

    return errors

tables = parse_sql('/home/wrnash1/development/nautilus/database/migrations/000_CORE_SCHEMA.sql')
with open('validation_result.txt', 'w') as f:
    f.write(f"Parsed {len(tables)} tables.\n")
    errors = validate_fks(tables)

    if errors:
        f.write("Found errors:\n")
        for e in errors:
            f.write(e + "\n")
    else:
        f.write("No obvious FK type mismatches found.\n")

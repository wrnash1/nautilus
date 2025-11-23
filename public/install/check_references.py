import re

def check_references(filename):
    with open(filename, 'r') as f:
        content = f.read()

    # Remove comments
    content = re.sub(r'--.*$', '', content, flags=re.MULTILINE)
    content = re.sub(r'/\*.*?\*/', '', content, flags=re.DOTALL)

    defined_tables = set()
    errors = []

    # Split by statements
    statements = content.split(';')

    for stmt in statements:
        stmt = stmt.strip()
        if not stmt:
            continue

        # Check for CREATE TABLE
        create_match = re.search(r'CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?(\w+)`?', stmt, re.IGNORECASE)
        if create_match:
            table_name = create_match.group(1)
            
            # Check for references in this statement
            # FOREIGN KEY (col) REFERENCES ref_table(ref_col)
            # We need to find all references
            refs = re.findall(r'REFERENCES\s+`?(\w+)`?', stmt, re.IGNORECASE)
            for ref_table in refs:
                if ref_table != table_name and ref_table not in defined_tables:
                    errors.append(f"Table '{table_name}' references '{ref_table}' before it is defined.")
            
            defined_tables.add(table_name)

    return errors

errors = check_references('/home/wrnash1/development/nautilus/database/migrations/000_CORE_SCHEMA.sql')
if errors:
    print("Found forward references:")
    for e in errors:
        print(e)
else:
    print("No forward references found.")

# Nautilus Setup Guide

## Quick Start

Run the automated setup script to configure your database:

```bash
cd /home/wrnash1/Developer/nautilus
./scripts/setup-database.sh
```

This script will:
- Test your database connection
- Run migrations 039, 040, and 041 (if not already completed)
- Seed certification agencies and certifications
- Display a summary of your database status

## What's New

### Recent Updates (Migrations 039-041)

#### Migration 039: Customer Travel & Contact Information
- **Multiple phone numbers** per customer (home, mobile, work, etc.)
- **Multiple email addresses** with marketing preferences
- **Emergency contacts** with relationships
- **Travel information**: passport details, weight, height, allergies, medications
- **Equipment sizing**: shoe size, wetsuit size
- **Custom fields** system for unlimited extensibility

#### Migration 040: Customer Tags & Linking
- **Customer tags** for segmentation (VIP, Wholesale, Instructor, etc.)
  - 10 pre-configured tags with colors and icons
  - Unlimited custom tags
- **Customer groups** for marketing campaigns
- **Customer relationships** to link family members, business partners
- **Enhanced notes** system with categories
- **Customer reminders** for follow-ups

#### Migration 041: Cash Drawer Management
- **Cash drawer sessions** with opening/closing workflows
- **Bill and coin counting** (all denominations)
- **Variance tracking** with overage/shortage detection
- **Session history** with detailed reporting
- **Bank deposit tracking**
- **Automatic discrepancy flagging** for amounts over $1.00

### New Features Available

1. **Cash Drawer Management** (`/store/cash-drawer`)
   - Open drawer sessions with bill/coin counting
   - Close sessions with variance calculation
   - View history of all cash drawer sessions
   - Real-time cash tracking

2. **Customer Tags** (`/store/customers/tags`)
   - Create custom tags with colors and icons
   - Assign tags to customers
   - Filter customers by tags
   - Track tag assignment history

3. **Enhanced Customer Profiles** (`/store/customers/{id}`)
   - New tabs for Contact Info, Travel Info, Tags
   - Multiple phones, emails, emergency contacts
   - Passport and medical information
   - Physical measurements for equipment

## Database Structure

### New Tables Created

**Customer Contact & Travel (Migration 039)**:
- `customer_addresses` - Multiple addresses per customer
- `customer_phones` - Phone numbers with types and preferences
- `customer_emails` - Email addresses with marketing opt-in/out
- `customer_contacts` - Emergency contacts
- `customer_custom_fields` - Custom field definitions
- `customer_custom_field_values` - Custom data storage

**Customer Segmentation (Migration 040)**:
- `customer_tags` - Tag definitions
- `customer_tag_assignments` - Customer-tag relationships
- `customer_relationships` - Link customers together
- `customer_groups` - Segmentation groups
- `customer_group_memberships` - Group assignments
- `customer_notes` - Categorized notes
- `customer_reminders` - Follow-up tasks

**Cash Management (Migration 041)**:
- `cash_drawers` - Physical drawer configuration
- `cash_drawer_sessions` - Session tracking with bill/coin counts
- `cash_drawer_transactions` - Transaction log
- `cash_deposits` - Bank deposit tracking
- `cash_variances` - Discrepancy investigations

### Enhanced Tables

**customers table** now includes:
- `passport_number`, `passport_expiration`
- `weight`, `height`
- `allergies`, `medications`, `medical_notes`
- `shoe_size`, `wetsuit_size`

## Certification Agencies

The seeder populates 15 major dive certification agencies:
- PADI (Professional Association of Diving Instructors)
- SSI (Scuba Schools International)
- NAUI, SDI, TDI, ERDI, PFI
- BSAC, CMAS, GUE, IANTD
- ACUC, IDA, PDIC, RAID

Plus 20+ common certifications including:
- Open Water Diver
- Advanced Open Water Diver
- Rescue Diver
- Divemaster
- Instructor Development Course
- Specialty certifications (Nitrox, Deep, Wreck, Night, Navigation)

## Manual Database Commands

If you need to run migrations manually:

```bash
# Run a specific migration
mysql -u root -p nautilus < database/migrations/039_customer_travel_and_contact_info.sql

# Run certification seeder
mysql -u root -p nautilus < database/seeders/certification_agencies.sql

# Check migration status
mysql -u root -p nautilus -e "SELECT id, filename, status, executed_at FROM migrations WHERE id >= 39 ORDER BY id;"
```

## Verification

After setup, verify the installation:

```bash
# Check tables were created
mysql -u root -p nautilus -e "SHOW TABLES LIKE 'customer_%';"
mysql -u root -p nautilus -e "SHOW TABLES LIKE 'cash_%';"

# Check seeded data
mysql -u root -p nautilus -e "SELECT COUNT(*) FROM certification_agencies;"
mysql -u root -p nautilus -e "SELECT COUNT(*) FROM certifications;"
mysql -u root -p nautilus -e "SELECT COUNT(*) FROM customer_tags;"
```

Expected results:
- 15 certification agencies
- 20+ certifications
- 10 default customer tags

## Navigation

Access new features via the sidebar:
- **Dashboard** → `/store`
- **Cash Drawer** → `/store/cash-drawer`
- **Customer Tags** → `/store/customers/tags`
- **Customers** → `/store/customers`
- **Settings** → `/store/admin/settings`

## Troubleshooting

### Database Connection Failed
Check your `.env` file has correct credentials:
```
DB_HOST=localhost
DB_NAME=nautilus
DB_USER=root
DB_PASSWORD=YourPassword
```

### Migration Already Exists
Migrations track themselves. If you need to re-run:
```bash
mysql -u root -p nautilus -e "DELETE FROM migrations WHERE filename = '039_customer_travel_and_contact_info.sql';"
```

### Permission Errors
The setup script needs to be executable:
```bash
chmod +x scripts/setup-database.sh
```

## Next Steps

1. **Configure Cash Drawers**:
   - Go to `/store/admin/settings` (future feature)
   - Or manually insert into `cash_drawers` table:
   ```sql
   INSERT INTO cash_drawers (name, location, starting_float, is_active)
   VALUES ('Main Register', 'Front Counter', 200.00, 1);
   ```

2. **Create Custom Tags**:
   - Visit `/store/customers/tags/create`
   - Use templates or create your own

3. **Test Cash Drawer**:
   - Open a session: `/store/cash-drawer`
   - Perform some POS transactions
   - Close session and verify variance tracking

4. **Import Customers**:
   - Existing customers automatically get new fields
   - New fields are NULL until populated

## Support

For issues or questions:
- Check logs: `/home/wrnash1/Developer/nautilus/storage/logs/`
- Review migrations: `/home/wrnash1/Developer/nautilus/database/migrations/`
- Check this repository: https://github.com/anthropics/nautilus

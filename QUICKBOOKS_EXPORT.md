# QuickBooks Export Documentation

## Overview
The QuickBooks Export feature allows you to export Nautilus data to QuickBooks Desktop (IIF format) and QuickBooks Online (QBO format). This enables seamless integration between your dive shop management system and your accounting software.

## Features

### Supported Export Types
1. **Customers** - Complete customer contact information
2. **Products/Inventory Items** - Product catalog with pricing and costs
3. **Sales Receipts/Invoices** - Complete transaction history with line items
4. **Payments** - Payment methods and amounts

### Export Formats

#### IIF (Intuit Interchange Format)
- **Platform**: QuickBooks Desktop
- **Format**: Tab-delimited text file
- **Import**: File → Utilities → Import → IIF Files in QuickBooks
- **Best For**: QuickBooks Desktop 2016-2024

#### QBO (QuickBooks Online XML)
- **Platform**: QuickBooks Online
- **Format**: XML (QBXML)
- **Import**: Settings → Import Data in QuickBooks Online
- **Best For**: QuickBooks Online accounts

## Implementation Files

### Backend Service
**File**: [`app/Services/Integrations/QuickBooksExportService.php`](app/Services/Integrations/QuickBooksExportService.php)

**Key Methods**:
```php
// Export customers within date range
exportCustomers(?DateTime $startDate, ?DateTime $endDate): array

// Export products within date range
exportProducts(?DateTime $startDate, ?DateTime $endDate): array

// Export invoices/sales receipts with line items
exportInvoices(?DateTime $startDate, ?DateTime $endDate): array

// Generate IIF file for QuickBooks Desktop
generateIIFFile(?DateTime $startDate, ?DateTime $endDate): string

// Generate QBO XML for QuickBooks Online
generateQBOFile(?DateTime $startDate, ?DateTime $endDate): string

// Export to file and log
exportToFile(string $format, ?DateTime $startDate, ?DateTime $endDate): array

// Save/load configuration
saveConfiguration(array $config): bool
getConfiguration(): array

// Get export history
getExportHistory(int $limit = 50): array
```

### Controller
**File**: [`app/Controllers/Integrations/QuickBooksController.php`](app/Controllers/Integrations/QuickBooksController.php)

**Routes**:
- `GET /integrations/quickbooks` - Configuration page
- `POST /integrations/quickbooks/config` - Save configuration
- `GET /integrations/quickbooks/export` - Export page with date range selection
- `POST /integrations/quickbooks/download` - Generate and download export file
- `POST /integrations/quickbooks/preview` - AJAX preview of export data
- `POST /integrations/quickbooks/delete/{id}` - Delete export file

### Views
1. **[index.php](app/Views/integrations/quickbooks/index.php)** - Configuration page
   - Company settings
   - Account mappings
   - Export options
   - Export history sidebar

2. **[export.php](app/Views/integrations/quickbooks/export.php)** - Export page
   - Quick date range selection (Today, This Week, This Month, etc.)
   - Custom date range picker
   - Format selection
   - Export preview
   - Download button

## Configuration

### Account Mappings
Map Nautilus accounts to your QuickBooks chart of accounts:

| Nautilus Account | Default QB Account | Purpose |
|---|---|---|
| Revenue | Sales | Income from product sales |
| Cost of Goods Sold | Cost of Goods Sold | Product costs |
| Inventory Asset | Inventory Asset | Inventory value |
| Sales Tax Payable | Sales Tax Payable | Collected sales tax |
| Accounts Receivable | Accounts Receivable | Customer balances |
| Deposit To | Undeposited Funds | Cash receipts |

**Important**: Update these to match your actual QuickBooks account names for accurate imports.

### Export Options
- **Include Customers**: Export customer contact information
- **Include Products**: Export product/inventory items
- **Include Invoices**: Export sales receipts with line items
- **Tax Rate**: Default sales tax percentage (used for calculations)

## Usage Instructions

### Step 1: Configure Settings
1. Navigate to **Integrations → QuickBooks**
2. Enter your company name
3. Select export format (IIF or QBO)
4. Map accounts to match your QuickBooks chart of accounts
5. Set tax rate
6. Choose what to export (customers, products, invoices)
7. Click **Save Configuration**

### Step 2: Export Data
1. Click **Export Data** button
2. Select a date range:
   - **Quick Ranges**: Today, This Week, This Month, Last Month, Year to Date, All Time
   - **Custom Range**: Pick specific start and end dates
3. Choose export format (IIF or QBO)
4. Click **Preview Export** to see summary:
   - Number of customers
   - Number of products
   - Number of invoices
   - Total revenue
5. Click **Generate & Download Export File**

### Step 3: Import to QuickBooks

#### QuickBooks Desktop (IIF)
1. Open QuickBooks Desktop
2. Go to **File** → **Utilities** → **Import** → **IIF Files**
3. Select the downloaded `.iif` file
4. QuickBooks will import the data
5. **Important**: Backup your company file before importing!

#### QuickBooks Online (QBO)
1. Log into QuickBooks Online
2. Go to **Settings** (gear icon) → **Import Data**
3. Select **Bank Data** or **General Import**
4. Choose the downloaded `.qbo` file
5. Follow the import wizard

## IIF File Structure

### Customers (CUST)
```
!CUST	NAME	BADDR1	BADDR2	BADDR3	PHONE1	EMAIL
CUST	John Doe	123 Main St			555-1234	john@example.com
```

### Products (INVITEM)
```
!INVITEM	NAME	INVITEMTYPE	DESC	PURCHASECOST	SALESPRICE	COGSACCT	ASSETACCT	INCOMEACCT
INVITEM	SKU-001	PART	Dive Mask	25.00	49.99	Cost of Goods Sold	Inventory Asset	Sales
```

### Sales Receipts (TRNS/SPL)
```
!TRNS	TRNSID	TRNSTYPE	DATE	ACCNT	NAME	AMOUNT	DOCNUM	MEMO
!SPL	SPLID	TRNSTYPE	DATE	ACCNT	NAME	AMOUNT	QNTY	PRICE	INVITEM	MEMO
!ENDTRNS

TRNS	1	SALES RECEIPT	12/15/2024	Undeposited Funds	John Doe	107.99	TXN-001
SPL	1	SALES RECEIPT	12/15/2024	Sales	John Doe	-99.99	2	49.995	SKU-001
SPL	1	SALES RECEIPT	12/15/2024	Sales Tax Payable	John Doe	-8.00				Sales Tax
ENDTRNS
```

## QBO XML Structure
The QBO format uses QBXML schema with the following structure:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<QBXML version="13.0">
  <QBXMLMsgsRq onError="stopOnError">
    <CustomerAddRq>
      <CustomerAdd>
        <Name>John Doe</Name>
        <FirstName>John</FirstName>
        <LastName>Doe</LastName>
        <Email>john@example.com</Email>
        <Phone>555-1234</Phone>
      </CustomerAdd>
    </CustomerAddRq>
    <!-- Products and invoices follow similar structure -->
  </QBXMLMsgsRq>
</QBXML>
```

## Database Tables

### integration_configs
Stores QuickBooks configuration:
```sql
CREATE TABLE integration_configs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    integration_type VARCHAR(50),    -- 'quickbooks'
    config_data JSON,                -- Configuration settings
    is_active BOOLEAN,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**Config Data Structure**:
```json
{
    "company_name": "Your Company Name",
    "format": "iif",
    "account_mappings": {
        "revenue_account": "Sales",
        "cogs_account": "Cost of Goods Sold",
        "inventory_asset_account": "Inventory Asset",
        "sales_tax_account": "Sales Tax Payable",
        "accounts_receivable": "Accounts Receivable",
        "deposit_to_account": "Undeposited Funds"
    },
    "tax_rate": 8.0,
    "include_customers": true,
    "include_products": true,
    "include_invoices": true
}
```

### export_logs
Tracks all exports:
```sql
CREATE TABLE export_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    export_type VARCHAR(50),         -- 'quickbooks'
    format VARCHAR(10),              -- 'iif' or 'qbo'
    filename VARCHAR(255),
    start_date DATE,
    end_date DATE,
    created_at TIMESTAMP
);
```

## File Storage
Export files are stored in:
```
storage/exports/quickbooks_export_YYYY-MM-DD_HHMMSS.{iif|qbo}
```

**File Naming Convention**:
- `quickbooks_export_2024-12-15_143025.iif`
- `quickbooks_export_2024-12-15_143025.qbo`

## Best Practices

### Export Frequency
- **Monthly**: Recommended for most businesses
- **Weekly**: For high-volume operations
- **Daily**: For real-time accounting needs
- **Date Ranges**: Always use specific date ranges to avoid duplicates

### Before Importing
1. **Backup QuickBooks**: Always backup before importing
2. **Verify Accounts**: Ensure account names match exactly
3. **Test Import**: Try importing a small date range first
4. **Check Duplicates**: QuickBooks will warn about duplicate customers/products

### After Importing
1. **Verify Data**: Review imported transactions in QuickBooks
2. **Check Totals**: Ensure revenue totals match between systems
3. **Reconcile**: Cross-check invoice numbers and dates
4. **Save Export File**: Keep export files for audit trail

## Troubleshooting

### Common Issues

#### Import Fails with "Account Not Found"
**Cause**: Account name mismatch
**Solution**: Update account mappings in configuration to match your QuickBooks chart of accounts exactly

#### Duplicate Customers
**Cause**: Re-importing same customers
**Solution**:
- Delete existing customers in QuickBooks first, OR
- Use date ranges to export only new customers

#### Sales Tax Incorrect
**Cause**: Wrong tax rate in configuration
**Solution**: Update tax rate in configuration to match your local rate

#### File Won't Download
**Cause**: Permission issues or missing storage directory
**Solution**:
```bash
mkdir -p storage/exports
chmod 755 storage/exports
```

### Error Messages

| Error | Cause | Solution |
|---|---|---|
| "Invalid export format" | Wrong format parameter | Use 'iif' or 'qbo' only |
| "Failed to save configuration" | Database error | Check database connection |
| "Export failed: Unknown error" | File write permissions | Check storage directory permissions |
| "No data to export" | Empty date range | Adjust date range or add transactions |

## Security Considerations

### File Access
- Export files contain sensitive financial data
- Files are stored in `/storage/exports/` which should NOT be web-accessible
- Only authenticated users with `manage_integrations` permission can export

### Data Privacy
- Customer personal information (name, email, phone) is included in exports
- Ensure compliance with data protection regulations (GDPR, CCPA)
- Delete old export files regularly

### Audit Trail
- All exports are logged in `export_logs` table
- Logs include: who exported, what was exported, when, and date range
- Export files can be deleted from UI (also deletes log entry)

## Advanced Usage

### Custom Date Ranges
Programmatically export for specific periods:
```php
$service = new QuickBooksExportService();
$startDate = new DateTime('2024-01-01');
$endDate = new DateTime('2024-12-31');

$result = $service->exportToFile('iif', $startDate, $endDate);
```

### Export Specific Data Types
Customize what gets exported by updating configuration:
```php
$config = $service->getConfiguration();
$config['include_customers'] = false;  // Skip customers
$config['include_products'] = true;    // Include products
$config['include_invoices'] = true;    // Include invoices
$service->saveConfiguration($config);
```

### Batch Processing
For large datasets, consider exporting in monthly batches:
```php
for ($month = 1; $month <= 12; $month++) {
    $start = new DateTime("2024-{$month}-01");
    $end = (clone $start)->modify('last day of this month');
    $service->exportToFile('iif', $start, $end);
}
```

## Future Enhancements

### Planned Features
- [ ] OAuth 2.0 authentication for QuickBooks Online API
- [ ] Direct API sync (bypass file export/import)
- [ ] Bi-directional sync (import from QuickBooks)
- [ ] Scheduled automatic exports (cron job)
- [ ] Email export files to accountant
- [ ] Export product images/descriptions
- [ ] Map custom fields
- [ ] Support for QuickBooks Enterprise

### API Integration (Future)
The current implementation uses file-based export. Future versions will include direct API integration:

**QuickBooks Desktop**: Web Connector SDK
**QuickBooks Online**: REST API with OAuth 2.0

## Support and Resources

### QuickBooks Documentation
- [IIF Format Documentation](https://developer.intuit.com/app/developer/qbdesktop/docs/file-formats/iif-files)
- [QuickBooks Online API](https://developer.intuit.com/app/developer/qbo/docs/get-started)
- [QBXML Documentation](https://developer.intuit.com/app/developer/qbdesktop/docs/api-reference/qbxml)

### Nautilus Support
For issues with the QuickBooks export feature:
1. Check this documentation
2. Review export logs in the UI
3. Verify configuration settings
4. Check file permissions
5. Contact support with export log ID

---

**Version**: 1.0
**Last Updated**: 2025-10-19
**Status**: ✅ Complete and Ready for Use

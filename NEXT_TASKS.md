# Next Tasks - Implementation Roadmap

## Completed Tasks ✅

1. ✅ **Multi-Certification Body Integration**
   - Database tables created
   - 10 agencies pre-loaded (PADI, SSI, SDI, NAUI, etc.)
   - 60+ certifications with prerequisites

2. ✅ **Course Prerequisite Verification**
   - PrerequisiteService.php completed
   - Intelligent checking of cert levels, age, dives, medical clearance
   - Available courses for customer

3. ✅ **Enhanced Customer Profiles**
   - Customer photo display with circular avatars
   - Highest certification badge with agency logo
   - Certification cards with agency branding

4. ✅ **Automated Service Reminders**
   - ServiceReminderService.php completed
   - 8 reminder templates pre-loaded
   - Cron scripts for automated scheduling
   - Email/SMS support

5. ✅ **Travel Packet Generator**
   - TravelPacketService.php completed
   - Comprehensive data compilation (passport, medical, certs, flights)
   - Email sending infrastructure (needs TCPDF for PDF generation)

6. ✅ **Mobile/Tablet Accessibility**
   - Mobile-responsive POS CSS (482 lines)
   - Touch-optimized interface
   - Floating Action Button for cart
   - Tested on iOS/Android

## Remaining Tasks 🔄

### 1. QuickBooks Export Functionality ⏳
**Priority**: High
**Database**: Ready (integration_configs, export_logs tables exist)
**Estimated Time**: 4-6 hours

**Requirements**:
- Create QuickBooks export controller
- Map Nautilus transactions to QuickBooks format (IIF or QBO)
- Export customers, invoices, payments, products
- Configuration UI for QuickBooks settings
- Export history and logs

**Files to Create**:
- `app/Controllers/Integrations/QuickBooksController.php`
- `app/Services/Integrations/QuickBooksExportService.php`
- `app/Views/integrations/quickbooks/index.php` (config UI)
- `app/Views/integrations/quickbooks/export.php` (export UI)

**Implementation Steps**:
1. Create QuickBooksExportService with methods:
   - `exportCustomers()` - Export customer list
   - `exportInvoices()` - Export sales transactions
   - `exportProducts()` - Export product catalog
   - `generateIIFFile()` - Generate IIF format file
   - `generateQBOFile()` - Generate QBO/QBX XML format
2. Create controller with routes:
   - `/integrations/quickbooks` - Config page
   - `/integrations/quickbooks/export` - Export UI
   - `/integrations/quickbooks/download/{type}` - Download export file
3. Create configuration UI:
   - QuickBooks company file settings
   - Account mappings (revenue, assets, tax)
   - Date range selection
   - Export format selection (IIF vs QBO)
4. Add export history tracking
5. Test with QuickBooks Desktop and Online

---

### 2. Vendor Product Catalog Import ⏳
**Priority**: Medium
**Database**: Ready (vendor_catalogs, vendor_catalog_items tables exist)
**Estimated Time**: 6-8 hours

**Requirements**:
- Upload vendor CSV/Excel files
- Map vendor columns to Nautilus fields
- Preview import before committing
- Bulk product creation/updates
- Vendor catalog management UI

**Files to Create**:
- `app/Controllers/VendorCatalogController.php`
- `app/Services/Inventory/VendorImportService.php`
- `app/Views/vendors/import/index.php` (upload UI)
- `app/Views/vendors/import/map.php` (field mapping)
- `app/Views/vendors/import/preview.php` (preview UI)
- `app/Views/vendors/catalogs/index.php` (catalog management)

**Implementation Steps**:
1. Create VendorImportService with methods:
   - `parseFile()` - Parse CSV/Excel files
   - `detectColumns()` - Auto-detect column mappings
   - `validateData()` - Validate imported data
   - `stageProducts()` - Save to vendor_catalog_items
   - `commitImport()` - Move staged products to inventory
2. Create file upload UI with drag-and-drop
3. Create column mapping interface:
   - Auto-detect common fields (SKU, name, price, etc.)
   - Manual override for custom vendor formats
   - Save mapping templates per vendor
4. Create preview UI:
   - Show first 50 rows
   - Highlight validation errors
   - Allow row-by-row exclusion
5. Add vendor catalog templates for major brands:
   - Scubapro, Aqua Lung, Mares, Suunto, etc.
   - Pre-configured column mappings
6. Test with real vendor files

---

### 3. PDF Generation for Travel Packets ⏳
**Priority**: Medium
**Database**: Complete (TravelPacketService ready)
**Estimated Time**: 3-4 hours

**Requirements**:
- Install and configure TCPDF library
- Design professional travel packet PDF template
- Include all participant information with photos
- Agency logos and certifications
- Medical and emergency contact info

**Files to Create/Modify**:
- `composer.json` - Add TCPDF dependency
- `app/Services/Travel/TravelPacketPDFService.php` - PDF generation
- `app/Views/pdf/travel_packet_template.php` - HTML template

**Implementation Steps**:
1. Install TCPDF via Composer:
   ```bash
   composer require tecnickcom/tcpdf
   ```
2. Create TravelPacketPDFService extending TCPDF:
   - Custom header with company logo
   - Custom footer with page numbers
   - Participant pages with photos
   - Certification cards display
   - QR codes for digital verification (optional)
3. Update TravelPacketService.php:
   - Replace placeholder in `generatePDF()` method
   - Call TravelPacketPDFService
   - Save PDF to `storage/travel_packets/`
4. Create PDF template:
   - Cover page with destination info
   - Participant roster
   - Individual participant pages:
     - Photo and personal info
     - Certifications with agency logos
     - Medical information
     - Flight details
     - Emergency contacts
5. Test PDF generation and email attachment

---

### 4. Wave Integration Enhancement 🔄
**Priority**: Low (exists, needs enhancement)
**Database**: Ready (integration_configs table)
**Estimated Time**: 4-6 hours

**Requirements**:
- Bi-directional sync with Wave accounting
- Auto-sync customers
- Auto-sync invoices
- Webhook support for real-time updates
- Conflict resolution

**Files to Create/Modify**:
- `app/Services/Integrations/WaveService.php` (enhance existing)
- `app/Controllers/Webhooks/WaveWebhookController.php`
- `app/Views/integrations/wave/index.php` (config UI)

**Implementation Steps**:
1. Review existing Wave integration
2. Add GraphQL API support:
   - Query customers from Wave
   - Query invoices from Wave
   - Push invoices to Wave
   - Push customers to Wave
3. Create webhook receiver for Wave events:
   - Invoice paid
   - Customer created/updated
   - Payment received
4. Add sync conflict resolution:
   - Last-modified timestamp comparison
   - Manual conflict resolution UI
5. Add sync logs and error handling
6. Test with Wave sandbox account

---

### 5. Dive Site Weather Tracking 🆕
**Priority**: Low
**Database**: Ready (dive_sites, dive_site_conditions tables exist)
**Estimated Time**: 4-5 hours

**Requirements**:
- Integrate with weather API (OpenWeatherMap or NOAA)
- Display current conditions for dive sites
- Track historical conditions
- Show forecast for upcoming trips
- Surface conditions and underwater visibility

**Files to Create**:
- `app/Services/DiveSites/WeatherService.php`
- `app/Controllers/DiveSitesController.php`
- `app/Views/dive_sites/index.php`
- `app/Views/dive_sites/show.php`

**Implementation Steps**:
1. Choose weather API:
   - OpenWeatherMap API (free tier)
   - NOAA API (US-focused, free)
   - Weather Underground API
2. Create WeatherService:
   - `getCurrentConditions($lat, $lng)` - Get current weather
   - `getForecast($lat, $lng, $days)` - Get forecast
   - `saveConditions($siteId, $conditions)` - Log to DB
   - `getHistoricalConditions($siteId, $dateRange)` - Query DB
3. Create dive site UI:
   - List of dive sites with map
   - Current conditions widget
   - 7-day forecast
   - Historical conditions chart
4. Add cron job to update conditions:
   - Run every 6 hours
   - Update all active dive sites
   - Store in dive_site_conditions table
5. Display conditions on trip planning page
6. Test with real dive site locations

---

## Optional Enhancements

### Email/SMS Service Integration
**Status**: Infrastructure ready, needs provider configuration
**Files**: `app/Services/Reminders/ServiceReminderService.php`
**Action Needed**:
1. Configure email provider (e.g., SendGrid, Mailgun)
2. Configure SMS provider (e.g., Twilio)
3. Update `.env` with API credentials
4. Replace placeholder methods in ServiceReminderService

### Payment Gateway Integration
**Status**: Not started
**Priority**: Medium
**Options**: Stripe, Square, PayPal
**Files to Create**:
- `app/Services/Payments/PaymentGatewayService.php`
- Integration with POS checkout

### Inventory Auto-Reordering
**Status**: Not started
**Priority**: Low
**Requirements**:
- Monitor low stock levels
- Auto-generate purchase orders
- Vendor integration
- Email notifications

### Customer Portal
**Status**: Not started
**Priority**: Medium
**Requirements**:
- Customer login
- View certifications
- Book courses
- View trip history
- Update personal info

---

## Recommended Implementation Order

1. **QuickBooks Export** (most requested accounting feature)
2. **PDF Travel Packets** (complete travel packet feature)
3. **Vendor Catalog Import** (streamline inventory management)
4. **Dive Site Weather** (enhance trip planning)
5. **Wave Enhancement** (complete accounting integration)
6. **Email/SMS Provider Setup** (activate reminder system)

---

## Time Estimates

| Task | Estimated Time | Priority |
|------|---------------|----------|
| QuickBooks Export | 4-6 hours | High |
| Vendor Import | 6-8 hours | Medium |
| PDF Travel Packets | 3-4 hours | Medium |
| Wave Enhancement | 4-6 hours | Low |
| Dive Site Weather | 4-5 hours | Low |
| **Total** | **21-29 hours** | |

---

## Dependencies

### Composer Packages Needed
```json
{
    "require": {
        "tecnickcom/tcpdf": "^6.6",           // PDF generation
        "phpoffice/phpspreadsheet": "^1.29",   // Excel import
        "guzzlehttp/guzzle": "^7.8"            // HTTP client for APIs
    }
}
```

### API Keys Needed
- OpenWeatherMap API key (free tier available)
- SendGrid or Mailgun API key (email)
- Twilio API key (SMS)
- QuickBooks Developer Account (optional, for OAuth)

### Configuration Required
- Email SMTP settings in `.env`
- SMS provider settings in `.env`
- Weather API key in `.env`
- QuickBooks credentials in `integration_configs` table

---

## Testing Checklist

Before deployment, ensure:
- [ ] All database migrations run successfully
- [ ] Seed data loaded (agencies, certs, dive sites, reminders)
- [ ] Cron jobs scheduled on server
- [ ] Email/SMS providers configured and tested
- [ ] File upload permissions set (storage/travel_packets, storage/imports)
- [ ] Mobile POS tested on iOS and Android
- [ ] Customer profile photos display correctly
- [ ] Certification badges show agency logos
- [ ] Service reminders send correctly
- [ ] Travel packets generate and email
- [ ] QuickBooks export produces valid files
- [ ] Vendor imports process correctly
- [ ] Weather data updates for dive sites

---

**Last Updated**: 2025-10-19
**Completed Features**: 6 of 10
**Progress**: 60% Complete

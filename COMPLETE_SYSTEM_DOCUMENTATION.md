# Nautilus Complete System Documentation

## Executive Summary

Nautilus is a comprehensive, enterprise-grade dive shop management system featuring 97 database migrations, 200+ tables, and a complete suite of tools for managing every aspect of dive shop operations. This document serves as the master reference for all system capabilities.

**System Version**: 1.0
**Last Updated**: January 2025
**Total Migrations**: 97
**Total Tables**: 200+
**Technology Stack**: PHP 8+, MySQL 8+, RESTful APIs

---

## Table of Contents

### Core Business Operations
1. [Customer Management](#customer-management)
2. [Course & Certification Management](#course--certification-management)
3. [Equipment & Rental Management](#equipment--rental-management)
4. [Booking & Scheduling](#booking--scheduling)
5. [Financial Management](#financial-management)

### Enterprise Features
6. [Inventory Control System](#inventory-control-system)
7. [Security & Surveillance](#security--surveillance)
8. [Communication Hub](#communication-hub)
9. [Point of Sale (POS)](#point-of-sale-pos)
10. [Loyalty & Rewards](#loyalty--rewards)
11. [Travel & Liveaboard Booking](#travel--liveaboard-booking)
12. [Business Intelligence](#business-intelligence)

### Advanced Capabilities
13. [Multi-Tenant Architecture](#multi-tenant-architecture)
14. [Mobile App Platform](#mobile-app-platform)
15. [Online Booking Portal](#online-booking-portal)
16. [API & Integrations](#api--integrations)
17. [Compliance & Certifications](#compliance--certifications)

---

## System Overview

### Core Modules (Migrations 001-075)

#### Customer Management
**Tables**: customers, customer_notes, customer_documents, customer_emergency_contacts

**Features**:
- Complete customer profiles with demographics
- Medical information and certifications
- Emergency contacts
- Document storage (waivers, medical forms)
- Customer notes and communication history
- Certification tracking (PADI, SSI, NAUI, etc.)
- Equipment size preferences

**Key Capabilities**:
- Medical questionnaire tracking
- Insurance information
- Referral source tracking
- Customer lifetime value
- Communication preferences
- Custom fields support

---

#### Course & Certification Management
**Tables**: courses, course_schedules, enrollments, certifications, course_materials, prerequisites

**Features**:
- Course catalog management
- Instructor scheduling
- Student enrollment
- Certification issuance
- Progress tracking
- Materials management
- Prerequisites enforcement

**Course Types**:
- Open Water Diver
- Advanced Open Water
- Rescue Diver
- Divemaster
- Specialty courses (Nitrox, Wreck, Deep, etc.)
- Refresher courses
- Professional development

**Certification Agencies**:
- PADI (Professional Association of Diving Instructors)
- SSI (Scuba Schools International)
- NAUI (National Association of Underwater Instructors)
- TDI/SDI (Technical Diving International)
- GUE (Global Underwater Explorers)

---

#### Equipment & Rental Management
**Tables**: equipment_inventory, rental_equipment, equipment_maintenance, equipment_inspections

**Features**:
- Equipment catalog
- Serial number tracking
- Rental management
- Maintenance scheduling
- Inspection records
- Service history
- Warranty tracking

**Equipment Categories**:
- Regulators and octopuses
- BCDs (Buoyancy Control Devices)
- Dive computers
- Wetsuits and drysuits
- Masks, fins, snorkels
- Tanks (aluminum, steel)
- Weights and weight systems
- Underwater cameras
- Safety equipment (SMBs, cutting tools, lights)

---

#### Booking & Scheduling
**Tables**: bookings, trip_schedules, boat_schedules, instructor_schedules, room_bookings

**Features**:
- Multi-channel booking (online, phone, walk-in)
- Real-time availability
- Instructor assignment
- Equipment reservation
- Boat charter scheduling
- Calendar integration
- Waitlist management
- Automated confirmations

**Booking Types**:
- Course enrollments
- Fun dives
- Boat trips
- Equipment rentals
- Private charters
- Pool sessions
- Classroom bookings

---

#### Financial Management
**Tables**: invoices, payments, refunds, expenses, payment_plans, discounts, taxes

**Features**:
- Invoice generation
- Payment processing
- Refund management
- Payment plans
- Discount codes
- Tax calculation
- Expense tracking
- Financial reporting

**Payment Methods**:
- Credit/debit cards
- Cash
- Check
- ACH/Bank transfer
- Digital wallets (Apple Pay, Google Pay)
- Gift cards
- Store credit
- Payment plans

---

## Enterprise Features

### Inventory Control System
**Migration**: 092
**Tables**: 12
**Documentation**: See detailed inventory guide

**Key Features**:

1. **Multi-Location Tracking**
   - Stores, warehouses, boats, vans
   - Real-time stock levels
   - Location-specific pricing
   - Transfer management

2. **RFID & Barcode Scanning**
   - Equipment tagging
   - Quick inventory counts
   - Asset tracking
   - Theft prevention

3. **Serialized Inventory**
   - Individual item tracking
   - Service history per item
   - Warranty management
   - Rental history

4. **Automated Reordering**
   - Min/max stock levels
   - Reorder points
   - Safety stock
   - Lead time tracking
   - Purchase order generation

5. **Inventory Analytics**
   - Turnover rates
   - ABC analysis
   - Slow-moving items
   - Stock valuation
   - Shrinkage tracking

**Tables**:
- inventory_locations
- product_master
- inventory_stock_levels
- serialized_inventory
- inventory_movements
- stock_transfers
- inventory_counts
- reorder_suggestions
- purchase_orders
- receiving_records
- barcode_scans
- inventory_adjustments

---

### Security & Surveillance
**Migration**: 093
**Tables**: 9
**Documentation**: See security system guide

**Key Features**:

1. **IP Camera System**
   - Live video streaming (RTSP/ONVIF)
   - Motion detection
   - AI object detection
   - Night vision
   - PTZ (Pan-Tilt-Zoom) control
   - Recording schedules
   - Cloud backup

2. **Access Control**
   - Door/gate control
   - RFID card readers
   - Keypad entry
   - Biometric (fingerprint, facial)
   - Access levels (public, employee, manager, admin)
   - Time-based access
   - Access logs

3. **Alarm System**
   - Intrusion detection
   - Panic buttons
   - Fire alarms
   - Water leak sensors
   - Zone management
   - Alert routing
   - Integration with monitoring services

4. **Incident Management**
   - Theft tracking
   - Burglary reports
   - Vandalism logs
   - Customer incidents
   - Employee incidents
   - Insurance claims
   - Investigation notes

**Supported Hardware**:
- **Cameras**: Hikvision, Dahua, Axis, Uniview
- **Access Control**: HID, Salto, Paxton, Brivo
- **Alarms**: DSC, Honeywell, Bosch

**Tables**:
- security_cameras
- camera_events
- access_control_points
- access_credentials
- access_events
- alarm_systems
- alarm_events
- security_incidents
- security_zones

---

### Communication Hub
**Migration**: 094
**Tables**: 9
**Documentation**: See communication guide

**Key Features**:

1. **Google Voice Integration**
   - Business phone number
   - SMS messaging
   - Voice calls
   - Voicemail transcription
   - Call routing
   - Auto-attendant
   - Call recording

2. **WhatsApp Business API**
   - Template messages
   - Rich media (images, videos)
   - Interactive buttons
   - Catalog sharing
   - Automated responses
   - Chatbot integration
   - Message templates

3. **Unified Inbox**
   - All channels in one place
   - Conversation threading
   - Team collaboration
   - Assignment routing
   - Response templates
   - Canned responses
   - Smart filters

4. **Multi-Channel Support**
   - Google Voice
   - WhatsApp
   - SMS (Twilio/Nexmo)
   - Email
   - Facebook Messenger
   - Instagram DM
   - Web chat

5. **Automation**
   - Auto-responses
   - Business hours detection
   - Keyword triggers
   - Appointment reminders
   - Booking confirmations
   - Review requests

**Tables**:
- communication_channels
- conversations
- messages
- message_templates
- whatsapp_templates
- call_logs
- voicemails
- automated_responses
- message_attachments

---

### Point of Sale (POS)
**Migration**: 095 (partial)
**Tables**: 3
**Documentation**: See POS guide

**Key Features**:

1. **Terminal Management**
   - iPad POS terminals
   - Android tablets
   - Desktop POS
   - Mobile POS
   - Kiosk mode
   - Offline capability

2. **Transaction Processing**
   - Multi-payment methods
   - Split payments
   - Partial payments
   - Refunds & exchanges
   - Voided transactions
   - Receipt printing
   - Email receipts

3. **Cash Drawer Management**
   - Opening float
   - Cash drops
   - Paid outs
   - Reconciliation
   - Discrepancy tracking
   - Shift reports

4. **Product Management**
   - Barcode scanning
   - Quick keys
   - Modifiers
   - Bundles
   - Discounts
   - Tax calculation
   - Price overrides

5. **Employee Features**
   - Staff login
   - Commission tracking
   - Sales attribution
   - Permissions
   - Clock in/out
   - Break tracking

**Tables**:
- pos_terminals
- pos_transactions
- cash_drawer_operations

---

### Loyalty & Rewards
**Migration**: 095 (partial)
**Tables**: 2
**Documentation**: See loyalty program guide

**Key Features**:

1. **Points Program**
   - Earn on purchases
   - Tiered multipliers
   - Bonus point events
   - Point expiration
   - Redemption catalog
   - Point transfers

2. **Membership Tiers**
   - Bronze, Silver, Gold, Platinum
   - Tier benefits
   - Tier progression
   - Anniversary rewards
   - Birthday bonuses
   - Tier protection

3. **Rewards**
   - Discount rewards
   - Free product rewards
   - Service upgrades
   - Early access
   - Exclusive events
   - Partner rewards

4. **Gift Cards**
   - Physical cards
   - Digital cards
   - Virtual (email) cards
   - Custom designs
   - Reloadable
   - Balance checking
   - Expiration tracking

**Tables**:
- loyalty_programs
- customer_loyalty_accounts
- gift_cards

---

### Travel & Liveaboard Booking
**Migration**: 089-091
**Tables**: 15+
**Service**: TravelBookingService.php

**Key Features**:

1. **Travel Packages**
   - Liveaboard trips
   - Resort packages
   - Dive cruises
   - Land-based diving
   - Multi-destination
   - Custom packages

2. **Destination Management**
   - 50+ dive destinations
   - Destination details
   - Water temperature
   - Visibility data
   - Marine life
   - Best seasons

3. **Liveaboard Boats**
   - Boat specifications
   - Cabin types
   - Amenities
   - Itineraries
   - Route maps
   - Safety features

4. **Dive Resorts**
   - Resort details
   - Accommodation types
   - Dive sites
   - Packages
   - Star ratings
   - Amenities

5. **Booking Management**
   - Traveler information
   - Deposit handling
   - Payment schedules
   - Balance due tracking
   - Cancellation policies
   - Travel insurance

6. **PADI Travel Integration**
   - API connection
   - Package sync
   - Availability check
   - Booking forwarding
   - Commission tracking

**Tables**:
- travel_packages
- travel_destinations
- liveaboard_boats
- dive_resorts
- travel_bookings
- travel_reviews
- travel_partner_apis

**Featured Destinations**:
- Great Barrier Reef, Australia
- Cozumel, Mexico
- Red Sea, Egypt
- Maldives
- Palau
- Galápagos Islands
- Raja Ampat, Indonesia
- Belize Barrier Reef
- And 40+ more...

---

### Business Intelligence
**Migration**: 097
**Tables**: 11
**Service**: BusinessIntelligenceService.php, CustomerAnalyticsService.php
**Documentation**: BUSINESS_INTELLIGENCE_GUIDE.md

**Key Features**:

1. **Interactive Dashboards**
   - Executive overview
   - Sales performance
   - Operations dashboard
   - Custom dashboards
   - Widget-based layout
   - Real-time updates
   - Auto-refresh

2. **Key Performance Indicators (KPIs)**
   - Monthly revenue
   - Customer acquisition
   - Average booking value
   - Course completion rate
   - Inventory turnover
   - Customer retention
   - Custom KPIs

3. **Custom Reports**
   - Report builder
   - 50+ pre-built templates
   - SQL query support
   - Multiple formats (PDF, Excel, CSV)
   - Scheduled delivery
   - Email distribution

4. **Customer Analytics**
   - RFM segmentation
   - Lifetime value
   - Churn prediction
   - Behavior analysis
   - Cohort analysis
   - Segment performance

5. **Revenue Analytics**
   - Revenue by category
   - Profitability metrics
   - Growth trends
   - YoY comparison
   - Forecasting
   - Budget variance

6. **Product Analytics**
   - Top sellers
   - ABC analysis
   - Inventory turnover
   - Profit margins
   - Stock performance
   - Seasonal trends

**Tables**:
- report_templates
- generated_reports
- scheduled_reports
- dashboards
- dashboard_widgets
- kpi_definitions
- kpi_values
- customer_analytics
- product_analytics
- revenue_analytics
- data_exports

---

## Advanced Capabilities

### Multi-Tenant Architecture

**Features**:
- Complete data isolation
- Tenant-specific branding
- Custom domains
- Separate databases (optional)
- Tenant admin portal
- Resource quotas
- Billing per tenant

**Security**:
- Row-level security
- Tenant ID in all queries
- Cross-tenant prevention
- Audit logging
- Data encryption
- Compliance ready (GDPR, CCPA)

---

### Mobile App Platform
**Migration**: 096 (partial)
**Tables**: 4

**Features**:

1. **iOS & Android Apps**
   - Native performance
   - Offline capability
   - Push notifications
   - Biometric login
   - Camera integration

2. **Customer Features**
   - Browse courses
   - Make bookings
   - View certifications
   - Equipment rentals
   - Trip registration
   - Logbook
   - Profile management

3. **Staff Features**
   - Check-in customers
   - Scan equipment
   - Update inventory
   - View schedule
   - Customer lookup
   - Quick checkout

4. **API Infrastructure**
   - RESTful APIs
   - JWT authentication
   - Rate limiting
   - Versioning
   - Webhooks
   - WebSockets

**Tables**:
- api_tokens
- mobile_app_sessions
- push_notifications
- push_notification_logs

---

### Online Booking Portal
**Migration**: 096 (partial)
**Tables**: 4

**Features**:

1. **Self-Service Booking**
   - Real-time availability
   - Instant confirmation
   - Calendar view
   - Filter/search
   - Package bundles
   - Add-ons

2. **Booking Management**
   - View bookings
   - Modify bookings
   - Cancellations
   - Waivers
   - Payment
   - Confirmation emails

3. **Customer Portal**
   - Account creation
   - Profile management
   - Certification upload
   - Medical forms
   - Emergency contacts
   - Payment methods

4. **Customization**
   - Custom branding
   - White-label
   - Custom domain
   - Theme colors
   - Logo upload
   - Terms & conditions

**Tables**:
- online_booking_settings
- bookable_items
- availability_slots
- online_bookings

---

### API & Integrations

**Built-in Integrations**:

1. **Payment Processors**
   - Stripe
   - Square
   - PayPal
   - Authorize.net
   - Clover

2. **Communication**
   - Twilio (SMS, Voice)
   - Google Voice
   - WhatsApp Business
   - SendGrid (Email)
   - Nexmo

3. **Accounting**
   - QuickBooks Online
   - Xero
   - FreshBooks
   - Wave

4. **Marketing**
   - Mailchimp
   - Constant Contact
   - HubSpot
   - Google Analytics
   - Facebook Pixel

5. **Travel**
   - PADI Travel
   - DiveSSI Travel
   - Custom travel APIs

6. **Hardware**
   - Barcode scanners
   - RFID readers
   - Receipt printers
   - Card readers
   - IP cameras
   - Access control systems

---

### Compliance & Certifications

**Regulatory Compliance**:

1. **PADI Standards**
   - Student-to-instructor ratios
   - Depth limits
   - Age requirements
   - Prerequisites
   - Medical requirements
   - Equipment standards

2. **Liability & Waivers**
   - Digital waivers
   - Risk acknowledgment
   - Assumption of risk
   - Release of liability
   - Medical disclosure
   - Document retention

3. **Safety Requirements**
   - Emergency action plans
   - Oxygen administration
   - First aid equipment
   - AED availability
   - Emergency contacts
   - Incident reporting

4. **Insurance**
   - Dive insurance tracking
   - DAN membership
   - Equipment insurance
   - Business insurance
   - Instructor insurance

5. **Environmental**
   - Marine protected areas
   - Carrying capacity
   - Environmental impact
   - Sustainability tracking
   - Green business certification

---

## System Architecture

### Technology Stack

**Backend**:
- PHP 8.0+
- MySQL 8.0+
- PDO for database access
- RESTful API architecture
- JWT authentication

**Frontend** (not included, reference only):
- React/Vue.js recommended
- Responsive design
- Progressive Web App
- Offline capability

**Infrastructure**:
- Linux (Ubuntu/Debian)
- Nginx/Apache
- PHP-FPM
- Redis (caching)
- Supervisor (queue workers)

**Security**:
- HTTPS/TLS encryption
- SQL injection prevention
- XSS protection
- CSRF tokens
- Rate limiting
- IP filtering

---

## Database Schema

### Total Statistics

- **Migrations**: 97
- **Tables**: 200+
- **Indexes**: 500+
- **Foreign Keys**: 300+
- **Stored Procedures**: Available for common operations
- **Views**: Reporting views for complex queries

### Key Design Patterns

1. **Multi-Tenancy**: tenant_id in all tables
2. **Soft Deletes**: deleted_at for recoverable records
3. **Audit Trails**: created_at, updated_at timestamps
4. **JSON Columns**: Flexible data storage
5. **Indexes**: Optimized for common queries
6. **Foreign Keys**: Referential integrity
7. **Enums**: Controlled value lists
8. **Partitioning**: Large tables partitioned by date

---

## Performance & Scalability

### Optimization Strategies

1. **Database**:
   - Proper indexing
   - Query optimization
   - Connection pooling
   - Read replicas
   - Partitioning
   - Archiving old data

2. **Caching**:
   - Redis for sessions
   - Query result caching
   - API response caching
   - Page caching
   - Object caching

3. **Application**:
   - Lazy loading
   - Eager loading (avoid N+1)
   - Background jobs
   - Queue processing
   - CDN for assets

4. **Monitoring**:
   - Application logs
   - Error tracking
   - Performance monitoring
   - Query profiling
   - Uptime monitoring

### Capacity

**Recommended Limits per Tenant**:
- Customers: Unlimited
- Bookings/year: 50,000+
- Transactions/day: 1,000+
- Concurrent users: 100+
- Storage: 100GB+ (with cleanup)

---

## Deployment Guide

### System Requirements

**Minimum**:
- CPU: 2 cores
- RAM: 4GB
- Storage: 50GB SSD
- PHP 8.0+
- MySQL 8.0+

**Recommended**:
- CPU: 4+ cores
- RAM: 8GB+
- Storage: 100GB+ SSD
- PHP 8.2+
- MySQL 8.0+
- Redis 6+

### Installation Steps

1. **Database Setup**:
   ```bash
   # Create database
   mysql -u root -p -e "CREATE DATABASE nautilus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

   # Run migrations in order
   cd database/migrations
   for file in *.sql; do
       mysql -u root -p nautilus < "$file"
   done
   ```

2. **PHP Configuration**:
   ```ini
   memory_limit = 256M
   upload_max_filesize = 20M
   post_max_size = 20M
   max_execution_time = 300
   ```

3. **Web Server**:
   ```nginx
   # Nginx configuration
   server {
       listen 80;
       server_name diveshop.example.com;
       root /var/www/nautilus/public;

       location / {
           try_files $uri $uri/ /index.php?$query_string;
       }

       location ~ \.php$ {
           fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
           fastcgi_index index.php;
           include fastcgi_params;
       }
   }
   ```

4. **SSL Certificate**:
   ```bash
   # Let's Encrypt
   certbot --nginx -d diveshop.example.com
   ```

---

## Migration Reference

### Migration List

**Core System (001-050)**:
- 001-010: Customers, bookings, payments
- 011-020: Courses, certifications, instructors
- 021-030: Equipment, rentals, maintenance
- 031-040: Inventory, products, suppliers
- 041-050: Financial, accounting, reporting

**Advanced Features (051-097)**:
- 051-060: Marketing, campaigns, loyalty
- 061-070: Staff, HR, payroll, scheduling
- 071-080: Medical, insurance, compliance
- 081-088: Advanced booking, trips, travel
- 089-091: Travel booking system
- 092: Advanced inventory control
- 093: Security & surveillance
- 094: Communication integrations
- 095: POS, loyalty, gift cards, memberships
- 096: Online booking & mobile APIs
- 097: Business intelligence & analytics

### Migration Dependencies

Some migrations depend on others:
- 092 (Inventory) requires product tables from 031-040
- 094 (Communications) integrates with customers from 001-010
- 095 (POS) requires inventory from 092
- 097 (BI) aggregates data from all modules

---

## API Documentation

### Authentication

```php
POST /api/auth/login
{
    "email": "user@diveshop.com",
    "password": "password"
}

Response:
{
    "success": true,
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "expires_in": 3600
}
```

### Common Endpoints

```
# Customers
GET    /api/customers
POST   /api/customers
GET    /api/customers/{id}
PUT    /api/customers/{id}
DELETE /api/customers/{id}

# Bookings
GET    /api/bookings
POST   /api/bookings
GET    /api/bookings/{id}
PUT    /api/bookings/{id}
DELETE /api/bookings/{id}

# Courses
GET    /api/courses
POST   /api/courses
GET    /api/courses/{id}/schedule

# Inventory
GET    /api/inventory
GET    /api/inventory/stock-levels
POST   /api/inventory/movements
POST   /api/inventory/transfers

# Analytics
GET    /api/analytics/dashboards/{id}
GET    /api/analytics/kpis/{id}
POST   /api/analytics/reports/generate

# Travel
GET    /api/travel/packages
GET    /api/travel/destinations
POST   /api/travel/bookings
```

---

## Support & Maintenance

### Regular Maintenance Tasks

**Daily**:
- Monitor error logs
- Check backup success
- Review failed jobs
- Monitor disk space

**Weekly**:
- Database optimization
- Clear old cache
- Review performance metrics
- Security updates

**Monthly**:
- Archive old data
- Review analytics
- Update documentation
- Test disaster recovery

**Quarterly**:
- Security audit
- Performance review
- Feature planning
- User training

---

## Troubleshooting

### Common Issues

1. **Slow Queries**:
   - Check missing indexes
   - Review query EXPLAIN
   - Add caching
   - Optimize table structure

2. **Failed Migrations**:
   - Check foreign key constraints
   - Verify table doesn't exist
   - Review error message
   - Rollback and retry

3. **API Errors**:
   - Verify JWT token
   - Check rate limits
   - Review request format
   - Check error logs

4. **Integration Failures**:
   - Verify API credentials
   - Check network connectivity
   - Review webhook URLs
   - Test in sandbox mode

---

## Roadmap

### Planned Features

**Q1 2025**:
- Advanced AI-powered recommendations
- Predictive analytics
- Mobile app v2.0
- Enhanced reporting

**Q2 2025**:
- IoT integration (smart tanks, sensors)
- Blockchain certifications
- AR/VR training modules
- Advanced automation

**Q3 2025**:
- Marketplace integration
- Franchise management
- International expansion tools
- Multi-currency support

**Q4 2025**:
- Machine learning forecasting
- Advanced CRM
- Social commerce integration
- Enterprise features

---

## Contributing

### Development Guidelines

1. **Code Standards**:
   - PSR-12 coding style
   - Type hints
   - DocBlocks
   - Unit tests

2. **Database Changes**:
   - New migration file
   - Rollback support
   - Index optimization
   - Documentation

3. **Testing**:
   - Unit tests
   - Integration tests
   - API tests
   - Performance tests

4. **Documentation**:
   - Update README
   - API documentation
   - User guides
   - Code comments

---

## License & Legal

**Copyright**: © 2025 Nautilus Dive Shop Management System
**License**: Proprietary/Commercial
**Support**: enterprise@nautilus-diving.com

---

## Appendices

### Appendix A: Complete Table List

(200+ tables organized by module)

### Appendix B: API Reference

(Complete API endpoint documentation)

### Appendix C: Sample Data

(Pre-seeded sample data for testing)

### Appendix D: Configuration Reference

(All configuration options and settings)

### Appendix E: Security Best Practices

(Security guidelines and recommendations)

---

**End of Documentation**

For specific module documentation, refer to:
- ENTERPRISE_FEATURES_COMPLETE.md
- BUSINESS_INTELLIGENCE_GUIDE.md
- Individual migration files
- Service class documentation

---

**Document Version**: 1.0
**Last Updated**: January 2025
**Next Review**: April 2025

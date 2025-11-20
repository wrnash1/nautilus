# Nautilus Dive Shop Management System - Complete Features Documentation

## Table of Contents
1. [Marketing & Campaign Management](#marketing--campaign-management)
2. [Customer Segmentation & RFM Analysis](#customer-segmentation--rfm-analysis)
3. [Marketing Automation & Workflows](#marketing-automation--workflows)
4. [SMS Marketing](#sms-marketing)
5. [A/B Testing Framework](#ab-testing-framework)
6. [Referral Programs](#referral-programs)
7. [Social Media Integration](#social-media-integration)
8. [Tax Reporting](#tax-reporting)
9. [Travel Agent System](#travel-agent-system)
10. [Training Tracking](#training-tracking)
11. [Employee Scheduling](#employee-scheduling)

---

## Marketing & Campaign Management

### Overview
Comprehensive multi-channel marketing campaign system with email, SMS, and social media support.

### Database Tables
- `marketing_campaigns` - Campaign master records
- `campaign_emails` - Email campaign content and variants
- `campaign_sms` - SMS campaign content
- `campaign_recipients` - Individual recipient tracking
- `campaign_link_clicks` - Click tracking and analytics
- `campaign_daily_stats` - Daily performance metrics

### Key Features

#### 1. Campaign Creation
```php
$campaignService = new CampaignBuilderService($db);

$campaign = $campaignService->createCampaign([
    'tenant_id' => 1,
    'name' => 'Spring Open Water Promotion',
    'campaign_type' => 'email',
    'objective' => 'conversion',
    'start_date' => '2024-03-01 09:00:00',
    'budget' => 500.00
]);
```

#### 2. Add Email Content
```php
$campaignService->addEmailContent($campaignId, [
    'tenant_id' => 1,
    'subject_line' => 'Get Certified This Spring - 20% Off!',
    'from_name' => 'Dive Shop',
    'from_email' => 'info@diveshop.com',
    'html_content' => '<h1>Spring Special</h1><p>Dear {{first_name}}...</p>',
    'personalization_tags' => ['first_name', 'last_name', 'email'],
    'track_opens' => true,
    'track_clicks' => true
]);
```

#### 3. Add Recipients from Segment
```php
$campaignService->addRecipientsFromSegment($campaignId, $segmentId, $tenantId);
```

#### 4. Launch Campaign
```php
$result = $campaignService->launchCampaign($campaignId);
// Queues all emails and sets campaign to active
```

### Performance Metrics

All campaigns track:
- **Delivery Rate**: Percentage of emails successfully delivered
- **Open Rate**: Percentage of delivered emails opened
- **Click-Through Rate**: Percentage clicking links
- **Conversion Rate**: Percentage completing desired action
- **ROI**: Revenue generated vs spend

### Analytics Dashboard

View: [app/Views/marketing/analytics_dashboard.php](app/Views/marketing/analytics_dashboard.php)

Features:
- Real-time performance metrics
- Trend analysis over time
- Channel performance comparison
- Conversion funnel visualization
- Segment performance breakdown

---

## Customer Segmentation & RFM Analysis

### Overview
Advanced customer segmentation engine with dynamic criteria, behavioral analysis, and RFM (Recency, Frequency, Monetary) scoring.

### Database Tables
- `customer_segments` - Segment definitions
- `segment_members` - Many-to-many membership
- `segment_criteria_library` - Reusable criteria
- `customer_rfm_scores` - RFM analysis results
- `customer_lifecycle_stages` - Lifecycle tracking

### Key Features

#### 1. Create Dynamic Segment
```php
$segmentService = new SegmentationService($db);

$segment = $segmentService->createSegment([
    'tenant_id' => 1,
    'name' => 'VIP Customers',
    'segment_type' => 'dynamic',
    'criteria' => [
        'rules' => [
            [
                'field' => 'lifetime_value',
                'operator' => 'greater_than',
                'value' => 5000
            ]
        ]
    ],
    'logic' => 'AND',
    'auto_refresh' => true,
    'refresh_frequency' => 'daily'
]);
```

#### 2. RFM Analysis
```php
// Calculate RFM scores for all customers
$rfm = $segmentService->calculateRFMScores($tenantId);

// RFM scores range from 1-5 for each dimension:
// - Recency: How recently customer purchased (5 = most recent)
// - Frequency: How often customer purchases (5 = most frequent)
// - Monetary: How much customer spends (5 = highest)

// RFM Segments:
// "555" = Champions (best customers)
// "Loyal Customers" = High frequency
// "At Risk" = Was good, declining
// "Lost" = Very low scores
```

#### 3. Pre-built Segments
10 segments pre-seeded:
1. VIP Customers ($5000+ LTV)
2. Recent Divers (last 30 days)
3. Certification Due (expiring in 60 days)
4. Open Water Graduates (ready for advanced)
5. At-Risk Customers (6+ months inactive)
6. New Subscribers (last 7 days)
7. Equipment Buyers (purchased in 12 months)
8. Dive Trip Enthusiasts (2+ trips booked)
9. Birthday This Month
10. High Engagement (50%+ open rate)

---

## Marketing Automation & Workflows

### Overview
Build automated customer journey workflows with multi-step sequences, conditional logic, and goal tracking.

### Database Tables
- `automation_workflows` - Workflow definitions
- `automation_workflow_steps` - Individual workflow steps
- `automation_workflow_members` - Customers in workflows
- `automation_step_executions` - Audit log
- `automation_workflow_goals` - Conversion tracking

### Key Features

#### 1. Create Workflow
```php
$automationService = new AutomationService($db);

$workflow = $automationService->createWorkflow([
    'tenant_id' => 1,
    'name' => 'New Customer Welcome Series',
    'workflow_type' => 'onboarding',
    'trigger_type' => 'event',
    'trigger_config' => [
        'event' => 'customer_created',
        'delay' => 0
    ],
    'can_re_enter' => false
]);
```

#### 2. Add Workflow Steps
```php
// Step 1: Immediate welcome email
$automationService->addWorkflowStep($workflowId, [
    'tenant_id' => 1,
    'step_order' => 1,
    'step_name' => 'Welcome Email',
    'step_type' => 'email',
    'delay_amount' => 0,
    'delay_unit' => 'minutes',
    'subject_line' => 'Welcome to {{shop_name}}!',
    'email_content' => '<h1>Welcome {{first_name}}!</h1>...'
]);

// Step 2: Wait 2 days
$automationService->addWorkflowStep($workflowId, [
    'step_order' => 2,
    'step_name' => 'Wait Period',
    'step_type' => 'wait',
    'delay_amount' => 2,
    'delay_unit' => 'days'
]);

// Step 3: Getting started guide
$automationService->addWorkflowStep($workflowId, [
    'step_order' => 3,
    'step_name' => 'Getting Started Guide',
    'step_type' => 'email',
    'delay_amount' => 2,
    'delay_unit' => 'days',
    'subject_line' => 'Your Guide to Getting Started',
    'email_content' => '...'
]);
```

#### 3. Enroll Customers
```php
$automationService->enrollCustomer($workflowId, $customerId, $tenantId, 'customer_signup');
```

#### 4. Track Goal Achievement
```php
$automationService->trackGoalAchievement($workflowId, $customerId, $goalId, $value);
```

### Pre-built Workflows
8 workflows pre-seeded:
1. New Customer Welcome Series (5 emails over 14 days)
2. Abandoned Course Booking
3. Certification Expiry Reminder (60-30-7 days before)
4. Post-Course Follow-up
5. Win-Back Dormant Customers
6. Birthday Club
7. Equipment Maintenance Reminder
8. Dive Log Encouragement

### Workflow Processing

Background job processes pending workflow actions:
```php
$automationService->processPendingActions(100); // Process up to 100 actions
```

---

## SMS Marketing

### Overview
Complete SMS marketing system with provider integration, templates, queue management, and delivery tracking.

### Database Tables
- `sms_providers` - Provider configuration (Twilio, Nexmo, etc.)
- `sms_queue` - SMS sending queue
- `sms_templates` - Reusable SMS templates

### Key Features

#### 1. Queue SMS
```php
$smsService = new SMSService($db);

$result = $smsService->queueSMS([
    'tenant_id' => 1,
    'to_phone' => '+15551234567',
    'message' => 'Hi! Your Open Water course is confirmed for tomorrow at 9am. See you soon!',
    'customer_id' => 123,
    'priority' => 'high'
]);

// Returns:
// - sms_id
// - segment_count (number of SMS segments)
// - estimated_cost
```

#### 2. Use Template
```php
$result = $smsService->queueFromTemplate($templateId, '+15551234567', [
    'first_name' => 'John',
    'course_name' => 'Open Water',
    'date' => 'March 15',
    'time' => '9:00 AM'
], $tenantId);
```

#### 3. Process SMS Queue
```php
$smsService->processQueue(100); // Send up to 100 messages
```

### Pre-seeded SMS Templates
1. Booking Confirmation
2. Appointment Reminder
3. Flash Sale Alert
4. Certification Expiry
5. Welcome New Customer

### Provider Support
- Twilio
- Nexmo/Vonage
- MessageBird
- AWS SNS
- Custom providers

---

## A/B Testing Framework

### Overview
Statistical A/B testing for email subject lines, content, send times, and CTAs.

### Database Tables
- `ab_test_experiments` - Test definitions
- `ab_test_variants` - Test variations (A, B, C, Control)
- `ab_test_participants` - Customer assignments
- Tracks engagement and conversions per variant

### Key Features

#### 1. Create Experiment
```php
$abTestService = new ABTestingService($db);

$experiment = $abTestService->createExperiment([
    'tenant_id' => 1,
    'name' => 'Subject Line Test - Open Water Promo',
    'experiment_type' => 'email_subject',
    'test_channel' => 'email',
    'traffic_split' => ['A' => 50, 'B' => 50],
    'primary_metric' => 'conversion_rate',
    'auto_declare_winner' => true,
    'confidence_level' => 95.00
]);
```

#### 2. Add Variants
```php
// Variant A
$abTestService->addVariant($experimentId, [
    'tenant_id' => 1,
    'variant_name' => 'A',
    'is_control' => true,
    'email_subject_line' => 'Get Certified This Spring',
    'traffic_percentage' => 50.00
]);

// Variant B
$abTestService->addVariant($experimentId, [
    'variant_name' => 'B',
    'email_subject_line' => 'Spring Special: 20% Off Open Water Certification!',
    'traffic_percentage' => 50.00
]);
```

#### 3. Start Experiment
```php
$abTestService->startExperiment($experimentId);
```

#### 4. Track Engagement
```php
// Automatically assigns customers to variants
$assignment = $abTestService->assignToVariant($experimentId, $customerId, $tenantId);

// Track events
$abTestService->trackEngagement($experimentId, $customerId, 'email_opened');
$abTestService->trackEngagement($experimentId, $customerId, 'email_clicked');
$abTestService->trackEngagement($experimentId, $customerId, 'converted', [
    'conversion_value' => 399.00
]);
```

#### 5. Analyze Results
```php
// Automatically calculates statistical significance
$winner = $abTestService->analyzeAndDeclareWinner($experimentId);
// Returns winner when confidence level reached (default 95%)
```

### Metrics Tracked
- Sent, Delivered, Opened, Clicked, Converted
- Open rate, Click rate, Conversion rate
- Revenue per recipient
- Statistical significance

---

## Referral Programs

### Overview
Complete referral marketing with tracking, rewards, and multi-channel sharing.

### Database Tables
- `referral_programs` - Program configurations
- `customer_referral_codes` - Individual referral codes
- `referrals` - Individual referral tracking

### Key Features

#### 1. Referral Program Types
- Discount percentage (e.g., 20% off)
- Fixed discount (e.g., $50 off)
- Store credit
- Free course
- Free product
- Points

#### 2. Dual Rewards
- **Referrer reward**: Reward for person who refers
- **Referee reward**: Reward for new customer

#### 3. Conversion Events
Programs can trigger rewards on:
- Signup
- First purchase
- First dive
- Certification complete

#### 4. Performance Tracking
Each referral code tracks:
- Total clicks
- Total referrals
- Successful referrals
- Revenue generated
- Rewards earned
- Sharing method (email, SMS, social)

### Pre-seeded Programs
1. **Dive Buddy Referral**: $50 off for both referrer and referee
2. **Open Water Graduate Referral**: 20% off Advanced course for referrals

---

## Social Media Integration

### Overview
Multi-platform social media management with post scheduling, lead capture, and influencer tracking.

### Database Tables
- `social_media_accounts` - Connected accounts
- `social_media_posts` - Scheduled and posted content
- `social_media_leads` - Lead form submissions
- `influencer_partnerships` - Partnership management

### Supported Platforms
- Facebook
- Instagram
- Twitter/X
- LinkedIn
- YouTube
- TikTok
- Pinterest

### Key Features

#### 1. Account Connection
Stores OAuth tokens for:
- Auto-posting
- Performance tracking
- Lead form integration

#### 2. Post Scheduling
- Draft, schedule, or immediately post
- Multi-platform posting
- Media attachments
- Hashtag management
- Link tracking

#### 3. Lead Form Integration
Automatically imports leads from Facebook/Instagram Lead Ads:
- Captures form submissions
- Creates customer records
- Assigns to staff for follow-up
- Tracks conversion

#### 4. Influencer Management
Track partnerships with:
- Compensation details (flat fee, commission, affiliate)
- Performance metrics
- Affiliate code tracking
- ROI measurement

---

## Tax Reporting

### Overview
Comprehensive tax calculation, reporting, and compliance system for US taxes.

### Database Tables
- `tax_jurisdictions` - Federal, state, county, city tax rates
- `tax_transactions` - Individual transaction tax records
- `tax_returns` - Periodic tax filings
- `tax_exempt_customers` - Tax exemption certificates
- `contractor_1099_payments` - 1099 contractor reporting

### Key Features

#### 1. Automatic Tax Calculation
```php
$taxService = new TaxReportingService($db);

$taxCalc = $taxService->calculateTax([
    'tenant_id' => 1,
    'subtotal' => 399.00,
    'location' => 'CA-LA', // Los Angeles County
    'customer_id' => 123
]);

// Returns:
// - taxable_amount
// - tax_amount
// - tax_rate
// - jurisdiction details
// - tax_exempt (if applicable)
```

#### 2. Record Tax Transaction
```php
$taxService->recordTaxTransaction([
    'tenant_id' => 1,
    'transaction_id' => 'ORD-12345',
    'transaction_type' => 'sale',
    'transaction_date' => '2024-03-15',
    'customer_id' => 123,
    'subtotal' => 399.00
]);
```

#### 3. Generate Sales Tax Report
```php
$report = $taxService->generateSalesTaxReport($tenantId, '2024-03');

// Returns:
// - gross_sales
// - taxable_sales
// - exempt_sales
// - total_tax_collected
// - total_refunds
// - net_tax_due
```

#### 4. Create Tax Return
```php
$return = $taxService->createTaxReturn($tenantId, 'sales_tax', '2024-03', 'monthly');

// Creates tax return with:
// - Calculated totals
// - Due date
// - Marks transactions as reported
```

#### 5. Generate 1099 Report
```php
$report1099 = $taxService->generate1099Report($tenantId, 2024);

// Returns all contractors with $600+ payments
// Breakdown by box categories
```

### Pre-seeded Tax Jurisdictions
- Federal (US)
- States: California, Texas, Florida, New York, Nevada
- Counties/Cities: Los Angeles, San Francisco, Miami-Dade

### Tax Return Types
- Sales tax (monthly/quarterly/annual)
- Use tax
- Quarterly 941 (payroll)
- Annual 1099 (contractors)
- Annual W-2 (employees)
- VAT (international)

---

## Travel Agent System

### Overview
Complete dive travel booking system with liveaboards, resorts, cruises, and PADI Travel integration.

### Database Tables
- `travel_destinations` - Dive destinations worldwide
- `dive_resorts` - Resort/hotel properties
- `liveaboard_boats` - Liveaboard dive boats
- `travel_packages` - Complete travel packages
- `travel_bookings` - Customer bookings
- `travel_reviews` - Customer reviews
- `travel_partner_apis` - PADI Travel, Ocean First integration

### Key Features

#### 1. Search Packages
```php
$travelService = new TravelBookingService($db);

$results = $travelService->searchPackages([
    'tenant_id' => 1,
    'package_type' => 'liveaboard',
    'destination_id' => 3, // Maldives
    'max_price' => 3000.00,
    'min_days' => 7,
    'max_days' => 10,
    'sort_by' => 'price_low'
]);
```

#### 2. Create Booking
```php
$booking = $travelService->createBooking([
    'tenant_id' => 1,
    'package_id' => 5,
    'customer_id' => 123,
    'departure_date' => '2024-06-15',
    'return_date' => '2024-06-22',
    'number_of_travelers' => 2,
    'primary_traveler' => [
        'name' => 'John Doe',
        'passport_number' => 'US123456',
        'date_of_birth' => '1985-04-20'
    ],
    'additional_travelers' => [
        ['name' => 'Jane Doe', ...]
    ]
]);

// Returns:
// - booking_id
// - booking_reference (e.g., TRV-A8F3D2C1)
// - total_price
// - deposit_amount
// - balance_due
// - balance_due_date (typically 60 days before departure)
```

#### 3. Record Payment
```php
$travelService->recordPayment($bookingId, 500.00, 'deposit');
// Updates payment status, calculates balance
```

#### 4. Submit Review
```php
$travelService->submitReview([
    'tenant_id' => 1,
    'booking_id' => 123,
    'customer_id' => 456,
    'review_type' => 'liveaboard',
    'liveaboard_id' => 2,
    'overall_rating' => 5,
    'dive_sites_rating' => 5,
    'accommodation_rating' => 4,
    'food_rating' => 5,
    'staff_rating' => 5,
    'title' => 'Amazing Maldives Experience',
    'review_text' => 'The best liveaboard trip ever...',
    'traveled_with' => 'partner'
]);
```

### Pre-seeded Data

**Destinations:**
1. Cozumel, Mexico
2. Great Barrier Reef, Australia
3. Maldives
4. Raja Ampat, Indonesia
5. Red Sea, Egypt
6. Galapagos Islands, Ecuador

**Liveaboards:**
1. MV Nautilus Explorer
2. Emperor Elite
3. Aggressor IV

**Resorts:**
1. Cozumel Palace
2. Cocoa Island Resort (Maldives)
3. Lizard Island Resort (GBR)

**Sample Packages:**
1. Maldives Liveaboard - 7 Nights ($2,495)
2. Cozumel All-Inclusive Dive Package ($1,799)
3. Raja Ampat Explorer - 10 Days ($3,995)
4. Red Sea Week - Emperor Fleet ($1,895)

### PADI Travel & Ocean First Integration

API connectors pre-configured for:
- **PADI Travel**: Affiliate ID, commission tracking
- **Ocean First Travel**: Booking synchronization

```php
$padiPackages = $travelService->searchPADITravel([
    'tenant_id' => 1,
    'destination' => 'Cozumel',
    'dates' => '2024-06-15'
]);
```

### Booking Management

Track complete booking lifecycle:
- Pending → Confirmed → Completed
- Payment tracking (pending, deposit paid, paid in full)
- Document collection (passports, medical forms, insurance)
- Special requests (dietary, accessibility)
- Internal notes and customer communications

---

## Training Tracking

### Overview
Complete certification training management with PADI/SSI compliance, skills assessment, and student progress tracking.

### Database Tables
- `training_programs` - Course definitions
- `training_sessions` - Scheduled classes
- `training_enrollments` - Student enrollments
- `skills_assessments` - Skills evaluation records
- `instructor_qualifications` - Instructor certifications
- `student_certifications` - Issued certifications
- `training_materials` - Course materials and eLearning

### Key Features

#### 1. Training Programs

Pre-seeded with 12 courses:
- **Certifications**: Open Water, Advanced, Rescue, Divemaster
- **Specialties**: Nitrox, Deep, Wreck, Night, Navigation, Photography
- **Safety**: EFR, Emergency Oxygen

Each program tracks:
- Prerequisites
- Duration (classroom, pool, open water)
- Skills and knowledge topics
- Materials required
- Pricing (course fee, materials, certification)

#### 2. Session Management

Schedule training sessions with:
- Start/end dates and times
- Locations (classroom, pool, open water)
- Instructor assignments
- Student capacity (min/max)
- Day-by-day schedule
- Registration deadlines

#### 3. Student Enrollment

Track complete student journey:
- Prerequisites verification
- Medical clearance
- Liability waivers
- Materials issued
- eLearning progress (0-100%)
- Attendance tracking
- Quiz and exam scores
- Skills assessment
- Certification issuance

#### 4. Skills Assessment

Detailed skills evaluation with:
- Assessment type (confined water, open water, checkout)
- Individual skill pass/fail
- Overall performance rating
- Confidence and comfort levels
- Remediation requirements
- Digital signatures

#### 5. Instructor Qualifications

Track instructor credentials:
- Certification type and level
- Teaching ratings
- Specialties authorized
- Renewal dates
- Liability insurance
- Teaching statistics

#### 6. Certification Issuance

Issue certifications with:
- Certification number
- Agency submission
- eCard generation
- Physical card printing
- Expiration tracking
- Renewal management

### PADI Compliance

System includes PADI Form integration:
- Form 10234 - Training Record
- Form 10346 - Medical Statement
- Certification reporting to PADI

---

## Employee Scheduling

### Overview
Complete workforce management with scheduling, time tracking, shift swaps, and labor cost budgeting.

### Database Tables
- `employees` - Employee master records
- `work_schedules` - Weekly/monthly schedules
- `shifts` - Individual shift assignments
- `time_off_requests` - PTO management
- `employee_availability` - Availability templates
- `shift_swap_requests` - Shift trading
- `labor_budgets` - Budget tracking
- `attendance_records` - Attendance monitoring

### Key Features

#### 1. Employee Management

Track:
- Personal information
- Employment type (full-time, part-time, contract, seasonal)
- Position and department
- Pay rate (hourly/salary)
- Availability preferences
- Skills and certifications
- Store opening/closing permissions
- Cash handling authorization

#### 2. Schedule Creation

Build schedules with:
- Weekly, bi-weekly, or monthly periods
- Multiple locations
- Position assignments
- Break times
- Status (draft, published, archived)
- Employee notifications

#### 3. Shift Management

Each shift includes:
- Date, start time, end time
- Position and location
- Employee assignment
- Coverage and swap requests
- Check-in/check-out times
- GPS location verification
- Actual hours worked
- Pay calculation (including overtime, tips, commission)

#### 4. Time Off Requests

Manage PTO with:
- Request types (vacation, sick, personal, unpaid)
- Multi-day or partial day
- Approval workflow
- Conflict detection
- Shift impact analysis

#### 5. Availability Management

Weekly availability templates:
- Recurring schedules by day of week
- Available/unavailable times
- Preferred vs available designation
- Effective date ranges

#### 6. Shift Swaps

Employee-initiated shift trading:
- Give away shifts
- Trade shifts
- Request coverage
- Manager approval required
- Automatic expiration

#### 7. Labor Budgets

Track labor costs:
- Monthly/weekly/daily budgets
- Budgeted vs actual hours
- Department breakdown
- Variance reporting
- Alert thresholds

#### 8. Attendance Tracking

Monitor attendance with:
- Present, absent, late, left early, no show
- Automated late/early departure calculation
- Absence reasons
- Excused vs unexcused
- Disciplinary action tracking

### Pre-seeded Sample Data

**5 Employees:**
1. Sarah Johnson - Store Manager
2. Mike Chen - PADI Instructor
3. Jessica Martinez - Sales Associate (PT)
4. Tom Wilson - Divemaster (PT)
5. Emily Davis - Retail Manager

**Sample Schedule:**
Week of March 15-21, 2024 with shifts across main store, pool, and boat locations.

### Mobile Features

System supports:
- GPS check-in/check-out
- Mobile schedule viewing
- Shift swap requests via mobile
- Push notifications for schedule changes

---

## System Integration Points

### Email Integration
- EmailQueueService handles all email sending
- Priority queuing
- Retry logic
- Template system
- Tracking pixels

### Payment Integration
- Links with existing order/payment system
- Supports deposits and payment plans
- Refund processing
- Commission tracking

### PADI API Integration
- Certification submission
- eCard generation
- Student verification
- Incident reporting

### Third-Party APIs
- **SMS**: Twilio, Nexmo, MessageBird
- **Email**: SMTP, SendGrid, Mailgun
- **Social**: Facebook, Instagram APIs
- **Travel**: PADI Travel, Ocean First
- **Payment**: Stripe, Square (existing)

---

## Performance & Scalability

### Database Optimization
- Indexed for common queries
- JSON columns for flexible data
- Partitioning-ready for large datasets
- Optimized for read-heavy analytics

### Cron Jobs/Background Processing

Recommended schedule:
```
# Every 5 minutes - Process email and SMS queues
*/5 * * * * php process_email_queue.php
*/5 * * * * php process_sms_queue.php

# Every hour - Refresh dynamic segments
0 * * * * php refresh_segments.php

# Every hour - Process automation workflows
0 * * * * php process_workflows.php

# Daily - Calculate RFM scores
0 2 * * * php calculate_rfm.php

# Daily - Sync social media metrics
0 3 * * * php sync_social_media.php

# Weekly - Generate tax reports
0 1 * * 1 php generate_tax_reports.php
```

### Scalability Considerations
- Queue-based processing for campaigns
- Batch processing for large segments
- API rate limiting
- Database connection pooling
- Cache frequently accessed data

---

## Security Features

### Data Protection
- AES-256-GCM encryption for sensitive data (EncryptionService)
- API credentials stored encrypted
- PCI compliance for payment data
- GDPR-ready with data export/deletion

### Access Control
- Role-based permissions
- Tenant isolation
- API authentication
- Audit logging

### Privacy Compliance
- Unsubscribe management
- GDPR consent tracking
- Data retention policies
- Right to be forgotten

---

## Getting Started

### 1. Run Migrations
```bash
# Run all migrations in order
php migrate.php
```

### 2. Configure Services

**Email:**
```php
$_ENV['MAIL_FROM_ADDRESS'] = 'info@yourdiveshop.com';
$_ENV['MAIL_FROM_NAME'] = 'Your Dive Shop';
$_ENV['SMTP_HOST'] = 'smtp.gmail.com';
$_ENV['SMTP_PORT'] = 587;
$_ENV['SMTP_USERNAME'] = 'your-email@gmail.com';
$_ENV['SMTP_PASSWORD'] = 'your-app-password';
```

**SMS (Twilio):**
```php
$_ENV['TWILIO_ACCOUNT_SID'] = 'your-account-sid';
$_ENV['TWILIO_AUTH_TOKEN'] = 'your-auth-token';
$_ENV['TWILIO_PHONE_NUMBER'] = '+15551234567';
```

**PADI API:**
```php
$_ENV['PADI_API_KEY'] = 'your-padi-api-key';
$_ENV['PADI_API_SECRET'] = 'your-padi-secret';
```

### 3. Set Up Cron Jobs

Add cron jobs for background processing (see Performance section above).

### 4. Import Initial Data

Use pre-seeded data or import:
- Customer segments
- Email templates
- SMS templates
- Training programs
- Travel destinations

---

## Support & Documentation

### Additional Resources
- API Documentation: `/docs/api`
- User Guides: `/docs/guides`
- Video Tutorials: `/docs/videos`
- Knowledge Base: `/docs/kb`

### Getting Help
- GitHub Issues: https://github.com/yourdiveshop/nautilus/issues
- Support Email: support@nautilus.com
- Community Forum: https://community.nautilus.com

---

## Version History

**Version 2.0** (Current)
- Complete marketing automation suite
- Travel agent system
- Training tracking
- Employee scheduling
- Tax reporting
- Social media integration

**Version 1.0**
- Basic dive shop management
- Equipment rental
- Course booking
- Inventory management

---

*Generated with Claude Code - Last Updated: March 2024*

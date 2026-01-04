-- =====================================================
-- Tax Reporting System
-- Comprehensive tax calculation and reporting
-- =====================================================

-- Tax Jurisdictions
CREATE TABLE IF NOT EXISTS "tax_jurisdictions" (
    "id" SERIAL PRIMARY KEY,
    "tenant_id" INTEGER NOT NULL,
    "jurisdiction_name" VARCHAR(255) NOT NULL,
    "jurisdiction_type" ENUM('federal', 'state', 'county', 'city', 'district') NOT NULL,
    "jurisdiction_code" VARCHAR(50) NULL COMMENT 'State code, zip code range, etc.',

    -- Tax Rates
    "sales_tax_rate" DECIMAL(5, 4) DEFAULT 0.0000 COMMENT 'e.g., 0.0825 for 8.25%',
    "use_tax_rate" DECIMAL(5, 4) DEFAULT 0.0000,
    "excise_tax_rate" DECIMAL(5, 4) DEFAULT 0.0000,

    -- Applicability
    "applies_to_services" BOOLEAN DEFAULT TRUE,
    "applies_to_products" BOOLEAN DEFAULT TRUE,
    "applies_to_rentals" BOOLEAN DEFAULT TRUE,
    "applies_to_courses" BOOLEAN DEFAULT TRUE,

    -- Exemptions
    "exempt_categories" JSON NULL COMMENT 'Product/service categories that are exempt',

    -- Effective Dates
    "effective_from" DATE NOT NULL,
    "effective_to" DATE NULL,

    "is_active" BOOLEAN DEFAULT TRUE,
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY ("tenant_id") REFERENCES "tenants"("id") ON DELETE CASCADE,
    INDEX idx_tenant_jurisdiction ("tenant_id", "jurisdiction_code")
);

-- Tax Transactions (detailed tax calculation per transaction)
CREATE TABLE IF NOT EXISTS "tax_transactions" (
    "id" BIGSERIAL PRIMARY KEY,
    "tenant_id" INTEGER NOT NULL,
    "transaction_id" VARCHAR(100) NOT NULL COMMENT 'Order ID, Invoice ID, etc.',
    "transaction_type" ENUM('sale', 'refund', 'rental', 'course', 'trip', 'service') NOT NULL,
    "transaction_date" DATE NOT NULL,

    -- Customer
    "customer_id" INTEGER NULL,
    "customer_name" VARCHAR(255) NULL,
    "customer_tax_id" VARCHAR(50) NULL COMMENT 'SSN, EIN, VAT number',
    "customer_tax_exempt" BOOLEAN DEFAULT FALSE,
    "exemption_certificate_number" VARCHAR(100) NULL,

    -- Location
    "billing_address" JSON NULL,
    "shipping_address" JSON NULL,
    "tax_jurisdiction_id" INTEGER NULL,

    -- Amounts
    "subtotal" DECIMAL(10, 2) NOT NULL,
    "discount_amount" DECIMAL(10, 2) DEFAULT 0.00,
    "taxable_amount" DECIMAL(10, 2) NOT NULL,
    "non_taxable_amount" DECIMAL(10, 2) DEFAULT 0.00,

    -- Tax Breakdown
    "sales_tax" DECIMAL(10, 2) DEFAULT 0.00,
    "use_tax" DECIMAL(10, 2) DEFAULT 0.00,
    "excise_tax" DECIMAL(10, 2) DEFAULT 0.00,
    "total_tax" DECIMAL(10, 2) NOT NULL,
    "tax_rate_applied" DECIMAL(5, 4) NOT NULL,

    -- Tax Details (for audit)
    "tax_calculation_details" JSON NULL COMMENT 'Breakdown by jurisdiction',

    -- Total
    "grand_total" DECIMAL(10, 2) NOT NULL,

    -- Reporting
    "reporting_period" VARCHAR(7) NOT NULL COMMENT 'YYYY-MM',
    "reported" BOOLEAN DEFAULT FALSE,
    "reported_at" TIMESTAMP NULL,
    "tax_return_id" INTEGER NULL,

    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY ("tenant_id") REFERENCES "tenants"("id") ON DELETE CASCADE,
    FOREIGN KEY ("customer_id") REFERENCES "customers"("id") ON DELETE SET NULL,
    FOREIGN KEY ("tax_jurisdiction_id") REFERENCES "tax_jurisdictions"("id") ON DELETE SET NULL,
    INDEX idx_transaction ("transaction_id"),
    INDEX idx_transaction_date ("transaction_date"),
    INDEX idx_reporting_period ("reporting_period", "reported")
);

-- Tax Returns (periodic tax filing)
CREATE TABLE IF NOT EXISTS "tax_returns" (
    "id" SERIAL PRIMARY KEY,
    "tenant_id" INTEGER NOT NULL,
    "return_type" ENUM('sales_tax', 'use_tax', 'quarterly_941', 'annual_1099', 'annual_w2', 'vat') NOT NULL,
    "reporting_period" VARCHAR(7) NOT NULL COMMENT 'YYYY-MM or YYYY-QN',
    "jurisdiction_id" INTEGER NULL,

    -- Filing Details
    "filing_frequency" ENUM('monthly', 'quarterly', 'annual') NOT NULL,
    "due_date" DATE NOT NULL,
    "filed_date" DATE NULL,
    "status" ENUM('draft', 'pending_review', 'ready_to_file', 'filed', 'amended', 'late') DEFAULT 'draft',

    -- Financial Summary
    "total_gross_sales" DECIMAL(12, 2) DEFAULT 0.00,
    "total_taxable_sales" DECIMAL(12, 2) DEFAULT 0.00,
    "total_exempt_sales" DECIMAL(12, 2) DEFAULT 0.00,
    "total_tax_collected" DECIMAL(12, 2) DEFAULT 0.00,
    "total_tax_owed" DECIMAL(12, 2) DEFAULT 0.00,
    "total_refunds" DECIMAL(12, 2) DEFAULT 0.00,
    "adjustments" DECIMAL(12, 2) DEFAULT 0.00,
    "net_tax_due" DECIMAL(12, 2) DEFAULT 0.00,

    -- Payment
    "payment_status" ENUM('unpaid', 'partial', 'paid', 'overpaid') DEFAULT 'unpaid',
    "payment_date" DATE NULL,
    "payment_confirmation" VARCHAR(255) NULL,

    -- Filing Information
    "confirmation_number" VARCHAR(100) NULL,
    "filed_by" INTEGER NULL COMMENT 'User who filed',
    "filing_method" ENUM('online', 'mail', 'api', 'third_party') NULL,

    -- Attachments
    "return_document_url" VARCHAR(500) NULL,
    "supporting_documents" JSON NULL,

    -- Penalties
    "late_filing_penalty" DECIMAL(10, 2) DEFAULT 0.00,
    "late_payment_penalty" DECIMAL(10, 2) DEFAULT 0.00,
    "interest_charges" DECIMAL(10, 2) DEFAULT 0.00,

    -- Notes
    "notes" TEXT NULL,

    "created_by" INTEGER NULL,
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY ("tenant_id") REFERENCES "tenants"("id") ON DELETE CASCADE,
    FOREIGN KEY ("jurisdiction_id") REFERENCES "tax_jurisdictions"("id") ON DELETE SET NULL,
    INDEX idx_tenant_period ("tenant_id", "reporting_period"),
    INDEX idx_due_date ("due_date"),
    UNIQUE KEY unique_tenant_type_period ("tenant_id", "return_type", "reporting_period", "jurisdiction_id")
);

-- Tax Exempt Customers
CREATE TABLE IF NOT EXISTS "tax_exempt_customers" (
    "id" SERIAL PRIMARY KEY,
    "tenant_id" INTEGER NOT NULL,
    "customer_id" INTEGER NOT NULL,

    -- Exemption Details
    "exemption_type" ENUM('resale', 'nonprofit', 'government', 'educational', 'religious', 'other') NOT NULL,
    "exemption_reason" TEXT NULL,
    "certificate_number" VARCHAR(100) NULL,
    "issuing_jurisdiction" VARCHAR(100) NULL,

    -- Validity
    "effective_date" DATE NOT NULL,
    "expiration_date" DATE NULL,
    "is_active" BOOLEAN DEFAULT TRUE,

    -- Documentation
    "certificate_document_url" VARCHAR(500) NULL,
    "verified" BOOLEAN DEFAULT FALSE,
    "verified_by" INTEGER NULL,
    "verified_at" TIMESTAMP NULL,

    -- Applicability
    "exempt_from_sales_tax" BOOLEAN DEFAULT TRUE,
    "exempt_from_use_tax" BOOLEAN DEFAULT FALSE,
    "exempt_jurisdictions" JSON NULL COMMENT 'Specific jurisdictions where exempt',

    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY ("tenant_id") REFERENCES "tenants"("id") ON DELETE CASCADE,
    FOREIGN KEY ("customer_id") REFERENCES "customers"("id") ON DELETE CASCADE,
    INDEX idx_customer ("customer_id"),
    INDEX idx_expiration ("expiration_date")
);

-- Tax Rate Changes History
CREATE TABLE IF NOT EXISTS "tax_rate_history" (
    "id" SERIAL PRIMARY KEY,
    "jurisdiction_id" INTEGER NOT NULL,
    "tenant_id" INTEGER NOT NULL,

    -- Rate Change
    "old_rate" DECIMAL(5, 4) NOT NULL,
    "new_rate" DECIMAL(5, 4) NOT NULL,
    "rate_type" ENUM('sales_tax', 'use_tax', 'excise_tax') NOT NULL,

    -- Effective Dates
    "effective_from" DATE NOT NULL,
    "change_reason" TEXT NULL,

    -- Change Tracking
    "changed_by" INTEGER NULL,
    "changed_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY ("jurisdiction_id") REFERENCES "tax_jurisdictions"("id") ON DELETE CASCADE,
    FOREIGN KEY ("tenant_id") REFERENCES "tenants"("id") ON DELETE CASCADE,
    INDEX idx_jurisdiction ("jurisdiction_id"),
    INDEX idx_effective_date ("effective_from")
);

-- 1099 Contractor Payments (for contractor reporting)
CREATE TABLE IF NOT EXISTS "contractor_1099_payments" (
    "id" BIGSERIAL PRIMARY KEY,
    "tenant_id" INTEGER NOT NULL,
    "contractor_id" INTEGER NULL COMMENT 'Link to vendors/contractors table',
    "tax_year" INT NOT NULL,

    -- Contractor Information
    "contractor_name" VARCHAR(255) NOT NULL,
    "contractor_tin" VARCHAR(20) NOT NULL COMMENT 'Tax ID Number (SSN or EIN)',
    "contractor_address" JSON NULL,
    "contractor_type" ENUM('individual', 'business') DEFAULT 'individual',

    -- Payment Details
    "payment_date" DATE NOT NULL,
    "payment_amount" DECIMAL(10, 2) NOT NULL,
    "payment_type" ENUM('non_employee_compensation', 'rent', 'royalties', 'other_income', 'medical_payments') DEFAULT 'non_employee_compensation',
    "payment_description" TEXT NULL,

    -- Box Numbers for 1099-NEC/MISC
    "box_1_nonemployee_comp" DECIMAL(10, 2) DEFAULT 0.00,
    "box_2_payers_direct_sales" DECIMAL(10, 2) DEFAULT 0.00,
    "box_4_federal_income_tax" DECIMAL(10, 2) DEFAULT 0.00,

    -- Filing
    "form_1099_id" INTEGER NULL,
    "filed" BOOLEAN DEFAULT FALSE,
    "filed_at" TIMESTAMP NULL,

    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY ("tenant_id") REFERENCES "tenants"("id") ON DELETE CASCADE,
    INDEX idx_tax_year ("tax_year"),
    INDEX idx_contractor ("contractor_tin", "tax_year")
);

-- =====================================================
-- Pre-seeded Tax Jurisdictions (US Examples)
-- =====================================================

INSERT INTO "tax_jurisdictions" (
    "tenant_id", "jurisdiction_name", "jurisdiction_type", "jurisdiction_code",
    "sales_tax_rate", "applies_to_services", "applies_to_products", "effective_from"
) VALUES
-- Federal
(1, 'United States Federal', 'federal', 'US', 0.0000, FALSE, FALSE, '2024-01-01'),

-- State Examples
(1, 'California', 'state', 'CA', 0.0725, TRUE, TRUE, '2024-01-01'),
(1, 'Texas', 'state', 'TX', 0.0625, FALSE, TRUE, '2024-01-01'),
(1, 'Florida', 'state', 'FL', 0.0600, TRUE, TRUE, '2024-01-01'),
(1, 'New York', 'state', 'NY', 0.0400, TRUE, TRUE, '2024-01-01'),
(1, 'Nevada', 'state', 'NV', 0.0685, FALSE, TRUE, '2024-01-01'),

-- County/City Examples
(1, 'Los Angeles County', 'county', 'CA-LA', 0.0125, TRUE, TRUE, '2024-01-01'),
(1, 'San Francisco', 'city', 'CA-SF', 0.0125, TRUE, TRUE, '2024-01-01'),
(1, 'Miami-Dade County', 'county', 'FL-MD', 0.0100, TRUE, TRUE, '2024-01-01');

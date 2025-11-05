# Nautilus Testing Checklist

## ✅ Completed
- [x] SSL/HTTPS setup
- [x] Database configuration
- [x] Admin user creation
- [x] Login functionality
- [x] Dashboard display
- [x] Alpha warning banner
- [x] Storage permissions
- [x] Database schema fixes (cash_drawer_sessions)

## 🧪 Test Each Module

### Navigation & UI
- [ ] Sidebar navigation works
- [ ] Sidebar collapse/expand works
- [ ] Top navigation shows user info
- [ ] Logout works
- [ ] Flash messages display correctly

### Dashboard
- [x] Dashboard loads without errors
- [ ] Metrics display correctly (may be 0 for empty database)
- [ ] Charts render
- [ ] Recent transactions section
- [ ] Alerts section

### Customers (CRM)
- [ ] View customers list
- [ ] Create new customer
- [ ] Edit customer
- [ ] View customer details
- [ ] Add customer certifications
- [ ] Customer tags system

### Products & Inventory
- [ ] View products list
- [ ] Create new product
- [ ] Edit product
- [ ] Adjust inventory
- [ ] Categories management
- [ ] Vendors management

### Point of Sale (POS)
- [ ] POS interface loads
- [ ] Product search works
- [ ] Add items to cart
- [ ] Process transaction
- [ ] Payment methods

### Cash Drawer
- [ ] View cash drawer sessions
- [ ] Open cash drawer
- [ ] Close cash drawer with counting
- [ ] View variance reports

### Reports
- [ ] Sales reports
- [ ] Customer reports
- [ ] Inventory reports
- [ ] Payment reports
- [ ] Export to CSV/PDF

### Settings
- [ ] View system settings
- [ ] Update business information
- [ ] Change admin password

## 🐛 Known Issues to Document

### Database Schema Mismatches
- Fixed: `cash_drawer_sessions.status` column was missing
- Fixed: `cash_drawer_sessions.variance` vs `difference` column name

### Incomplete Features (Alpha Status)
- Email notifications not implemented
- PDF generation for travel packets incomplete
- Some integrations not configured

## 🔧 Quick Tests

Run these URLs to test key features:

```
https://nautilus.local/store                    # Dashboard
https://nautilus.local/store/customers          # Customer list
https://nautilus.local/store/products           # Product list
https://nautilus.local/store/pos                # POS system
https://nautilus.local/store/cash-drawer        # Cash drawer
https://nautilus.local/store/reports/sales      # Sales report
https://nautilus.local/store/admin/settings     # Settings
```

## 🎯 Priority Fixes Needed

1. **High Priority**
   - Test all major modules for similar schema issues
   - Verify all tables have required columns
   - Check for other `difference` vs `variance` mismatches

2. **Medium Priority**
   - Complete email functionality with graceful degradation
   - Add error handling for missing features
   - Update demo data/credentials

3. **Low Priority**
   - Remove all debug files
   - Update documentation
   - Create deployment guide

## 📝 Testing Process

For each module:
1. Navigate to it in the UI
2. Note any errors in browser console
3. Note any PHP errors displayed
4. Try basic CRUD operations (Create, Read, Update, Delete)
5. Document any issues found

## 🚀 Next Steps After Testing

1. Fix any critical errors found
2. Clean up debug files
3. Update LICENSE to open source
4. Create deployment documentation
5. Document all known limitations in Alpha warning

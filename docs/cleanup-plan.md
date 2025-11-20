# Documentation Cleanup Plan

## Current State: 21 Markdown Files in Root Directory

### Files to KEEP in Root (Essential):
1. ✅ **README.md** - Main project introduction
2. ✅ **LICENSE** - Legal requirement
3. ✅ **INSTALL_SIMPLE.md** - Primary installation guide for non-technical users

### Files to MOVE to docs/ (Installation Documentation):
4. 📁 **INSTALL.md** → `docs/INSTALL_ADVANCED.md`
5. 📁 **INSTALLATION_IMPROVEMENTS.md** → `docs/installation/`
6. 📁 **INSTALLATION_FOR_NON_TECHNICAL_USERS.md** → `docs/installation/`
7. 📁 **WEB_INSTALLER_FEATURES.md** → `docs/installation/`

### Files to MOVE to docs/ (Feature Documentation):
8. 📁 **AI_FEATURES_COMPLETE.md** → `docs/features/`
9. 📁 **BUSINESS_INTELLIGENCE_GUIDE.md** → `docs/features/`
10. 📁 **COMPLETE_FEATURES_DOCUMENTATION.md** → `docs/features/`
11. 📁 **COMPLETE_SYSTEM_DOCUMENTATION.md** → `docs/features/`
12. 📁 **ENTERPRISE_FEATURES_COMPLETE.md** → `docs/features/`
13. 📁 **PROFESSIONAL_FEATURES_V2.md** → `docs/features/`
14. 📁 **NEW_FEATURES_ADDED.md** → `docs/features/`

### Files to MOVE to docs/ (User Guides):
15. 📁 **QUICK_START_GUIDE.md** → `docs/guides/`
16. 📁 **SIMPLE_USAGE_GUIDE.md** → `docs/guides/`
17. 📁 **NAVIGATION.md** → `docs/guides/`

### Files to MOVE to docs/ (Deployment):
18. 📁 **DEPLOYMENT_CHECKLIST.md** → `docs/deployment/`
19. 📁 **READY_TO_DEPLOY.md** → `docs/deployment/`

### Files That Are DUPLICATES/OBSOLETE (Review for deletion):
20. ⚠️ **FINAL_FEATURE_SUMMARY.md** - Likely duplicate of COMPLETE_FEATURES_DOCUMENTATION
21. ⚠️ **PROJECT_COMPLETE.md** - Likely obsolete status file
22. ⚠️ **PANGOLIN_WORK_QUICK_REFERENCE.md** - Appears to be developer-specific

## Proposed Directory Structure

```
/nautilus/
├── README.md                          (Keep - Main introduction)
├── LICENSE                            (Keep - Legal)
├── INSTALL_SIMPLE.md                  (Keep - Primary install guide)
├── docs/
│   ├── installation/
│   │   ├── INSTALL_ADVANCED.md        (Moved from INSTALL.md)
│   │   ├── web-installer-features.md
│   │   ├── installation-improvements.md
│   │   └── for-non-technical-users.md
│   ├── features/
│   │   ├── ai-features.md
│   │   ├── business-intelligence.md
│   │   ├── enterprise-features.md
│   │   ├── complete-features.md
│   │   └── professional-features.md
│   ├── guides/
│   │   ├── quick-start.md
│   │   ├── usage-guide.md
│   │   └── navigation.md
│   └── deployment/
│       ├── checklist.md
│       └── ready-to-deploy.md
```

## Action Items

1. Create subdirectories in docs/
2. Move files to appropriate locations
3. Convert filenames to lowercase with hyphens
4. Update README.md with documentation links
5. Delete duplicate/obsolete files after review
6. Create docs/README.md as documentation index

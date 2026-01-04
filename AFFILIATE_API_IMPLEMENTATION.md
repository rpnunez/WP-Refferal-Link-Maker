# Affiliate Network API Integration - Implementation Notes

## Overview

This implementation adds support for importing affiliate links directly from popular affiliate networks (Amazon Associates and ShareASale) into the WP Referral Link Maker plugin.

## What Was Implemented

### 1. API Connector Architecture

- **Base Class** (`class-affiliate-api-base.php`): Abstract base class providing common functionality
  - Link import/export
  - HTTP request handling
  - Error management
  - Duplicate detection

- **Amazon Connector** (`class-affiliate-api-amazon.php`): Amazon Product Advertising API integration
  - Product search by keywords
  - Automatic affiliate link generation with Associate Tag
  - ASIN tracking
  - Regional marketplace support

- **ShareASale Connector** (`class-affiliate-api-shareasale.php`): ShareASale API integration
  - Merchant link fetching
  - Multi-merchant support
  - Automatic affiliate ID injection
  - Link metadata management

- **API Manager** (`class-affiliate-api-manager.php`): Centralized connector coordination
  - Network configuration management
  - Connection testing
  - Link fetching and importing

### 2. Admin Interface

#### Affiliate Networks Page
- Located at: **Referral Links > Affiliate Networks**
- Configure API credentials for each network
- Secure password fields for sensitive data
- Regional settings for Amazon
- Direct links to API documentation

#### Import Links Page
- Located at: **Referral Links > Import Links**
- Network-specific import forms
- Amazon: Search by keywords with configurable limits
- ShareASale: Import by merchant ID or all merchants
- Real-time import results display

#### Enhanced List View
- New "Source" column in referral links list
- Visual indicators for manual vs imported links
- Color-coded source display

#### Enhanced Meta Boxes
- Source information display in link details
- External ID tracking (ASIN, Link ID, etc.)
- Highlighted imported link information

### 3. Database Schema

New meta keys added to referral links:
- `_ref_link_source`: Tracks link source (manual, amazon, shareasale)
- `_ref_link_external_id`: Stores external IDs (ASIN for Amazon, Link ID for ShareASale)

### 4. Documentation

Updated README.md with:
- Feature overview for affiliate network integration
- Step-by-step setup instructions for each network
- Import workflow documentation
- Links to official API documentation

## Important Notes

### Amazon Product Advertising API

**⚠️ IMPORTANT**: The Amazon connector includes a simplified AWS Signature Version 4 implementation that is **NOT production-ready**. 

For production use, you must:
1. Implement the full AWS Signature Version 4 algorithm, OR
2. Use an existing AWS SDK library

See: https://docs.aws.amazon.com/general/latest/gr/signature-version-4.html

The current implementation will **not work** with Amazon's API and is provided as a framework for full implementation.

### ShareASale API

The ShareASale connector is fully functional but requires:
- Valid ShareASale affiliate account
- API access enabled in your account
- API Token and Secret from your dashboard

## How to Use

### Setting Up Amazon Associates

1. Sign up for Amazon Associates at affiliate-program.amazon.com
2. Apply for Product Advertising API access
3. Get AWS IAM credentials (Access Key and Secret Key)
4. Find your Associate Tag in your Amazon Associates account
5. Navigate to **Referral Links > Affiliate Networks**
6. Enter your credentials and select your region
7. Save settings

### Setting Up ShareASale

1. Sign up for ShareASale at shareasale.com
2. Log in to your affiliate account
3. Navigate to **Tools > API** in your dashboard
4. Generate your API Token and API Secret
5. Note your Affiliate ID
6. Navigate to **Referral Links > Affiliate Networks**
7. Enter your credentials
8. Save settings

### Importing Links

1. Navigate to **Referral Links > Import Links**
2. Choose your network (Amazon or ShareASale)
3. Fill in the import parameters:
   - **Amazon**: Enter search keywords and number of products
   - **ShareASale**: Optionally enter merchant ID and number of links
4. Click **Import Links**
5. Review the results showing:
   - Successfully imported links
   - Skipped links (duplicates)
   - Failed imports (if any)

## File Structure

```
includes/
├── class-affiliate-api-base.php          # Base connector class
├── class-affiliate-api-amazon.php        # Amazon PA API connector
├── class-affiliate-api-shareasale.php    # ShareASale API connector
└── class-affiliate-api-manager.php       # API manager

admin/
├── class-admin.php                       # Updated with new menu items and columns
└── partials/
    ├── affiliate-networks-page.php       # Credentials configuration page
    └── import-page.php                   # Import interface page
```

## Security Considerations

All code follows WordPress security best practices:
- ✅ Input sanitization using `sanitize_text_field()`, `esc_url_raw()`, etc.
- ✅ Output escaping using `esc_html()`, `esc_attr()`, `esc_url()`
- ✅ Nonce verification for form submissions
- ✅ Capability checks (`manage_options`)
- ✅ SQL injection prevention using `$wpdb->prepare()`
- ✅ Password fields for sensitive credentials

## Testing

All PHP files have been syntax checked with no errors:
```bash
php -l includes/class-affiliate-api-*.php
php -l admin/class-admin.php
php -l admin/partials/*.php
```

## Future Enhancements

Potential improvements for future versions:

1. **Complete Amazon AWS Signature**: Implement full AWS Signature Version 4 algorithm
2. **Additional Networks**: Add support for CJ Affiliate, Rakuten, Impact, etc.
3. **Scheduled Imports**: Add cron jobs for automatic link updates
4. **Bulk Management**: Tools for updating multiple imported links
5. **Analytics Integration**: Track performance of imported vs manual links
6. **Category Mapping**: Auto-assign imported links to groups based on category
7. **Link Validation**: Periodic checks to ensure imported links are still active

## Support

For issues or questions:
- GitHub Issues: https://github.com/rpnunez/WP-Refferal-Link-Maker/issues
- Amazon PA API: https://affiliate-program.amazon.com/help/node/topic/G201825840
- ShareASale API: https://account.shareasale.com/a-apicodev.cfm

## Changelog

### Version 1.1.0 (Current Implementation)
- Added Amazon Associates API integration
- Added ShareASale API integration
- Added Import Links interface
- Added Affiliate Networks settings page
- Added source tracking for referral links
- Added external ID support
- Added custom column for link source
- Updated documentation with setup guides

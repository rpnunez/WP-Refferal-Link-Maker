# WP Referral Link Maker

A comprehensive WordPress plugin that seamlessly integrates referral links into existing posts using AI capabilities from the Meow Apps AI Engine plugin.

## Features

### Custom Post Types

The plugin registers two custom post types for organizing and managing referral links:

1. **Referral Link Group** - Categorize and organize referral links into groups for better management
2. **Referral Link Maker** - Define individual referral links with AI automation settings

### Admin Dashboard Integration

- **Overview Page**: Manage global values and view statistics
  - View counts of link groups, referral links, and processed posts
  - Configure default group settings
  - Set global link prefix and suffix for tracking parameters
  - Quick action buttons for common tasks

- **Settings Page**: Configure AI Engine integration and automation
  - Enable/disable AI Engine integration
  - Set API keys for AI Engine
  - Configure automatic post processing
  - Set cron intervals (hourly, twice daily, daily, weekly)
  - Choose post status after AI processing (pending, draft, published)

### Process Automation

- **WordPress Cron Integration**: Automated post processing on configurable schedules
- **AI-Assisted Editing**: Posts are automatically updated with referral links
- **Pending Approval Workflow**: AI-edited posts can be moved to "Pending Review" for manual approval
- **Safe Defaults**: All automation features include safe default settings

### Referral Link Management

Each referral link includes:
- **Keyword**: The text to be linked in posts
- **URL**: The full referral URL with tracking parameters
- **Group Assignment**: Organize links by category
- **Priority**: Control which links are inserted first (0-100)
- **Max Insertions**: Limit how many times a link appears per post
- **AI Context**: Provide context to help AI understand when to use the link
- **AI Enable/Disable**: Toggle AI automation per link

### Link Groups

Organize your referral links with groups that include:
- **Title and Description**: Name and describe each group
- **Color Coding**: Visual identification with hex colors
- **Icon**: Assign dashicon for visual representation

## Installation

1. Upload the `wp-referral-link-maker` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to 'Referral Links' in the admin menu to configure the plugin

**Note**: The plugin is distributed with the Composer autoloader and all dependencies in the `vendor/` directory.

## Configuration

### Initial Setup

1. Go to **Referral Links > Settings**
2. Configure AI Engine settings:
   - Enable AI Engine integration
   - Enter your API key
3. Configure automation settings:
   - Enable automatic updates if desired
   - Set your preferred update interval
   - Choose post status after AI editing

### Creating Referral Links

1. Go to **Referral Links > All Links**
2. Click **Add New**
3. Enter a title for the link
4. Fill in the referral link details:
   - Keyword or phrase to link
   - Full referral URL
   - Select a group (optional)
   - Set priority and max insertions
5. Configure AI automation (optional):
   - Enable AI for this link
   - Provide context for the AI
6. Publish the referral link

### Creating Link Groups

1. Go to **Referral Links > All Groups**
2. Click **Add New**
3. Enter a title and description
4. Configure group settings:
   - Choose a color
   - Select an icon
5. Publish the group

### Setting Global Values

1. Go to **Referral Links > Overview**
2. Configure:
   - Default link group
   - Global prefix (tracking parameters)
   - Global suffix (tracking parameters)
3. Click **Save Global Values**

## AI Engine Integration

This plugin is designed to work with the [Meow Apps AI Engine](https://wordpress.org/plugins/ai-engine/) plugin. The AI Engine provides intelligent analysis and link insertion capabilities.

### Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- Composer (for development and autoloading)
- AI Engine plugin (optional, for AI features)

### How It Works

1. The plugin identifies posts marked for AI processing
2. Using the AI Engine's `simpleTextQuery()` API, it sends post content and referral link context to the AI
3. The AI intelligently analyzes the content and determines optimal placement for each referral link
4. Links are inserted naturally where they are contextually relevant, respecting maximum insertion limits
5. Processed posts are moved to the configured status (default: pending review)
6. Administrators review and approve the changes
7. If AI Engine is unavailable, the plugin falls back to simple keyword matching

## Cron Automation

The plugin uses WordPress cron to automate processing:

- **Custom Schedules**: Hourly, twice daily, daily, and weekly options
- **Safe Processing**: Only processes posts marked for AI automation
- **Batch Processing**: Handles up to 10 posts per cron run
- **Duplicate Prevention**: Marks processed posts to avoid re-processing

### Manual Cron Testing

To manually trigger the cron job for testing:

```php
do_action('wp_referral_link_maker_process_posts');
```

## Security

The plugin follows WordPress security best practices:

- Nonce verification for all form submissions
- Capability checks for admin functions
- Input sanitization and validation
- Output escaping
- SQL injection prevention with $wpdb->prepare (where applicable)

## Development

### File Structure

```
wp-referral-link-maker/
├── admin/
│   ├── css/
│   │   └── admin.css             # Admin styles
│   ├── js/
│   │   └── admin.js              # Admin JavaScript
│   └── partials/
│       ├── overview-page.php     # Overview page template
│       └── settings-page.php     # Settings page template
├── src/                           # PSR-4 autoloaded classes (NunezReferralEngine namespace)
│   ├── Plugin.php                # Core plugin class
│   ├── Activator.php             # Plugin activation
│   ├── Deactivator.php           # Plugin deactivation
│   ├── PostTypes.php             # Custom post types
│   ├── MetaBoxes.php             # Meta box handlers
│   ├── Cron.php                  # Cron job handlers
│   ├── Admin.php                 # Admin overview functionality
│   ├── Settings.php              # Settings page functionality
│   └── Services/
│       ├── AIEngine.php          # AI Engine integration
│       └── PromptManager.php     # AI prompt building
├── vendor/                        # Composer autoloader (generated)
├── composer.json                  # Composer configuration
├── languages/                     # Translation files
├── wp-referral-link-maker.php    # Main plugin file
└── README.md                      # This file
```

### PSR-4 Autoloading

The plugin uses Composer for PSR-4 autoloading with the namespace `NunezReferralEngine`. All classes are located in the `src/` directory and follow PSR-4 naming conventions.

To regenerate the autoloader after changes:
```bash
composer dump-autoload
```

### Hooks and Filters

#### Actions

- `wp_referral_link_maker_post_processed` - Fired after a post is processed
  ```php
  add_action('wp_referral_link_maker_post_processed', 'my_callback', 10, 1);
  function my_callback($post_id) {
      // Your code here
  }
  ```

#### Filters

- `cron_schedules` - Modified to add custom cron intervals

## Support

For issues, questions, or contributions, please visit:
https://github.com/rpnunez/WP-Refferal-Link-Maker

## License

This plugin is licensed under the GPL v2 or later.

## Credits

Developed by rpnunez
Designed to integrate with Meow Apps AI Engine

## Changelog

### 1.0.1
- **Code Architecture Refactoring**
  - Migrated to PSR-4 autoloading with `NunezReferralEngine` namespace
  - Renamed folder structure: `includes/` → `src/`
  - Removed `WP_Referral_Link_Maker_` class name prefix
  - Renamed all class files to follow PSR-4 conventions (e.g., `Plugin.php`, `Admin.php`)
  - Added Composer with autoloader configuration
  
- **Constants Refactoring**
  - Renamed constants: `WP_REFERRAL_LINK_MAKER_*` → `NRE_*`
  - Updated: `NRE_VERSION`, `NRE_PLUGIN_DIR`, `NRE_PLUGIN_URL`
  
- **Code Organization**
  - Extracted Settings functionality into separate `Settings.php` class
  - Removed Loader abstraction class in favor of native WordPress hooks API
  - Direct use of `add_action()` and `add_filter()` throughout
  
- **Class Renames**
  - `WP_Referral_Link_Maker` → `Plugin`
  - `WP_Referral_Link_Maker_AI_Engine` → `AIEngineService` (now `Services\AIEngine`)
  - `WP_Referral_Link_Maker_Admin` → `Admin`
  - All other classes simplified with namespace
  
- **Code Quality Improvements**
  - Added fully qualified class names for global namespace classes
  - Added safety checks for autoloader and AI Engine responses
  - Improved error messages for plugin dependencies
  - Maintained `WP_Query` usage for consistency
  
- **Documentation**
  - Updated README with new PSR-4 structure
  - Simplified installation instructions (plugin distributed with vendor directory)
  - Updated file structure documentation

### 1.0.0
- Initial release
- Custom post types for referral links and groups
- Admin interface with overview and settings pages
- WordPress cron automation
- AI Engine integration support
- Meta boxes for link and group management
- Global values configuration
- Security and sanitization


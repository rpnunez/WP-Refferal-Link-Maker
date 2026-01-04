# WP Referral Link Maker

A comprehensive WordPress plugin that seamlessly integrates referral links into existing posts using AI capabilities from the Meow Apps AI Engine plugin.

## Features

### Custom Post Types

The plugin registers two custom post types for organizing and managing referral links:

1. **Referral Link Group** - Categorize and organize referral links into groups for better management
2. **Referral Link Maker** - Define individual referral links with AI automation settings

### Block Editor Integration

- **Live Preview**: Preview AI-suggested referral links directly in the Gutenberg block editor before publishing
- **Smart Link Suggestions**: Get intelligent link suggestions based on your post content
- **One-Click Apply**: Apply suggested links to your content with a single click
- **Sidebar Panel**: Dedicated sidebar panel in the block editor for easy access to referral link features
- **Real-time Analysis**: Analyze your content to see which keywords match your referral links
- **Context-Aware**: See how many times each keyword appears in your content

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

### Using the Block Editor Integration

The plugin provides seamless integration with the Gutenberg block editor for real-time link suggestions and previews:

1. **Open the Referral Links Panel**:
   - While editing a post in the block editor, click the three-dot menu (⋮) in the top-right corner
   - Select "Referral Links" from the menu to open the sidebar panel

2. **Get Link Suggestions**:
   - After writing your post content, click the "Suggest Links" button
   - The plugin will analyze your content and show relevant referral links
   - See how many times each keyword appears in your content
   - View contextual information about each suggested link

3. **Preview Links**:
   - Click the "Preview with Links" button to see how your content will look with referral links inserted
   - A preview modal will display your content with all links applied
   - Review the placement and styling of the links before applying them

4. **Apply Links**:
   - If you're satisfied with the preview, click the "Apply Links" button
   - The content in your post editor will be updated with the referral links
   - Continue editing or publish your post

5. **Benefits**:
   - Preview changes before committing them
   - Ensure links are placed naturally in your content
   - Save time with intelligent link suggestions
   - Maintain full control over which links to apply

## AI Engine Integration

This plugin is designed to work with the [Meow Apps AI Engine](https://wordpress.org/plugins/ai-engine/) plugin. The AI Engine provides intelligent analysis and link insertion capabilities.

### Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
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
│   ├── class-admin.php           # Admin functionality
│   ├── css/
│   │   └── admin.css             # Admin styles
│   ├── js/
│   │   └── admin.js              # Admin JavaScript
│   └── partials/
│       ├── overview-page.php     # Overview page template
│       └── settings-page.php     # Settings page template
├── includes/
│   ├── class-activator.php       # Plugin activation
│   ├── class-deactivator.php     # Plugin deactivation
│   ├── class-loader.php          # Hook loader
│   ├── class-wp-referral-link-maker.php  # Core plugin class
│   ├── class-post-types.php      # Custom post types
│   ├── class-meta-boxes.php      # Meta box handlers
│   ├── class-block-editor.php    # Block editor integration
│   ├── class-ai-engine.php       # AI Engine integration
│   └── class-cron.php            # Cron job handlers
├── languages/                     # Translation files
├── wp-referral-link-maker.php    # Main plugin file
└── README.md                      # This file
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

### 1.1.0
- **NEW**: Block editor (Gutenberg) integration
- **NEW**: Live preview of AI-suggested referral links before publishing
- **NEW**: Smart link suggestions based on post content
- **NEW**: Sidebar panel for easy access to referral link features
- **NEW**: One-click apply functionality for suggested links
- **IMPROVED**: Real-time content analysis showing keyword matches
- **IMPROVED**: Enhanced user experience with visual feedback

### 1.0.0
- Initial release
- Custom post types for referral links and groups
- Admin interface with overview and settings pages
- WordPress cron automation
- AI Engine integration support
- Meta boxes for link and group management
- Global values configuration
- Security and sanitization


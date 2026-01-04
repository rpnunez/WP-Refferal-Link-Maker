# Copilot Instructions for WP Refferal Link Maker / NunezRefferalEngine

Always read the README.md file before starting. Make note of the changelog. When submitting a version-incrementing Pull Request (which is only when you're asked to increment the version number) ensure you are appending a summarized list of changes to the Changelog (following the dame format, along with a the date) to the README.md file as a part of the Pull Request.  
When working on this project, which is a WordPress plugin, always follow best practices to ensure the plugin remains efficient, secure, and compatible with other plugins, themes, and WordPress updates. Here’s a comprehensive list of best practices:

---

### **1. General Coding Practices**
- **Follow WordPress Coding Standards**:
  - Use the [WordPress PHP coding standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/) and tools like PHP_CodeSniffer to enforce them.
  - Follow standards for JavaScript, CSS, and HTML as well.

- **Namespace Everything**:
  - Use unique, descriptive namespaces (`VendorName\PluginName`) to avoid conflicts with other plugins and WordPress core files.

- **Use Prefixes for Global Identifiers**:
  - Prefix functions, classes, and constants with a unique string (e.g., `myplugin_`) to avoid conflicts.

- **Follow PSR Standards**:
  - Use [PSR-4 autoloading](https://www.php-fig.org/psr/psr-4/) for class file organization.
  - Adhere to PSR-1 and PSR-12 recommendations for code style.

---

### **2. Security Best Practices**
- **Sanitize, Escape, and Validate**:
  - Sanitize all data before saving into the database (`sanitize_text_field`, `esc_url_raw`, e.g.).
  - Escape data before outputting it to HTML (`esc_html`, `esc_url`, `esc_attr`, etc.).

- **Use Nonces to Prevent CSRF**:
  - Use WordPress nonces in forms and AJAX requests to verify user actions.

- **Validate and Secure Input**:
  - Rigorously validate `_POST` and `_GET` inputs before processing.
  - Never trust user input.

- **Enforce WordPress Roles and Capabilities**:
  - Use the `current_user_can()` function to check for proper permissions before executing sensitive operations.

- **Avoid Including User-Provided File Paths**:
  - Avoid `include`, `require`, or `file_get_contents` with user-provided paths.

- **Ensure Safe Queries**:
  - Use `$wpdb->prepare()` to prevent SQL injection.

- **Load Dependencies Securely**:
  - Enqueue CSS/JS files via `wp_enqueue_script()` and `wp_enqueue_style()` with appropriate versioning.

---

### **3. Performance Practices**
- **Optimize Plugin Performance**:
  - Avoid unnecessary database queries on every request.
  - Use transients or caching for expensive computations or external API calls.
  - Load scripts and styles only when needed (e.g., only on admin pages or when shortcodes are used).

- **Don’t Load Code Until Required**:
  - Use hooks (`add_action`) to load functionality only when necessary.

- **Run Cron Jobs Efficiently**:
  - Use `wp_schedule_event` instead of hooking into every page load.

---

### **4. Compatibility and Environment**
- **Follow WordPress API Standards**:
  - Use WordPress APIs and functions (`add_settings_section`, custom post types, taxonomies, etc.) instead of custom solutions.

- **Test for Backward Compatibility**:
  - Test your plugin on multiple WordPress versions (targeting the last few major versions).

- **Ensure PHP Compatibility**:
  - Ensure your plugin works with the minimum PHP version supported by WordPress (currently PHP 7.4+).
  
- **Declare Plugin Requirements**:
  - Use the `Requires PHP` and `Requires at least` headers in the plugin to denote minimum requirements.

- **Respect Multisite Compatibility**:
  - Test and ensure your plugin doesn't break when installed on a WordPress Multisite network.

---

### **5. Plugin Architecture Best Practices**
- **Separate Logic and Display**:
  - Use the MVC (Model-View-Controller) design pattern to keep business logic, template files, and user interface code organized.

- **Avoid Hardcoding**:
  - Make plugin settings user-configurable via admin pages or filters.

- **Use Dependency Injection**:
  - Manage dependencies by injecting them into classes, which makes testing and extending functionality easier.

- **Modular Design**:
  - Divide your plugin functionality into small, reusable modules.

- **Avoid Overwriting Defaults**:
  - Avoid modifying WordPress core behavior directly. Use hooks (`add_filter` or `add_action`) instead.

---

### **6. Plugin Updates and Maintenance**
- **Increment Versions Properly**:
  - Follow [semantic versioning](https://semver.org/) when releasing new updates (e.g., `MAJOR.MINOR.PATCH` notation).

- **Use WP-CLI Commands**:
  - Add WP-CLI commands to make your plugin management easier from the command line for advanced users.

- **Provide Documentation**:
  - Write clear documentation for your plugin’s functionality, hooks, and filters.

- **Use Git for Version Control**:
  - Use Git and a platform like GitHub to manage version history, issues, and contributions.

---

### **7. User-Friendliness and Accessibility**
- **Provide an Intuitive User Interface**:
  - Use the WordPress Settings API to manage admin settings pages.
  - Don't clutter the WordPress admin dashboard with unnecessary menus.

- **Make Plugins Accessible**:
  - Follow [WordPress Accessibility Guidelines](https://make.wordpress.org/accessibility/handbook/) for user interaction.

- **Provide Detailed Error Messages**:
  - Log errors but provide human-readable messages to users explaining what went wrong.

- **Fail Gracefully**:
  - Ensure your plugin doesn't break the site even under unforeseen errors. Graceful degradation is key.

- **Add Multilingual Support**:
  - Use the `__()` and `_e()` functions to allow translation of text strings.

---

### **8. Licensing and Distribution**
- **License Correctly**:
  - Use open-source licenses compatible with the GPL (e.g., MIT, Apache, GPL) for your plugin and third-party dependencies.

- **Optimize Distribution Packages**:
  - Exclude development files (`composer.json`, `.gitignore`, etc.) from the distribution.

- **Include a `readme.txt` File**:
  - Follow the [WordPress.org readme.txt standard](https://wordpress.org/support/article/readme-txt/) for SEO, compatibility, and clarity.

---

### **9. Testing and Debugging**
- **Test Extensively**:
  - Test your plugin in different environments (local/dev/prod), PHP versions, and WordPress configurations.

- **Write Unit Tests**:
  - Use PHPUnit for automated testing of plugin functionality.

- **Enable Debug Mode**:
  - Enable `WP_DEBUG` during development to catch errors early.

- **Log Errors During Development**:
  - Use `error_log()` or a custom logging mechanism to debug issues.

---

### **10. Hooks and Extensibility**
- **Provide Filters and Actions**:
  - Allow other developers to hook into your plugin functionality using `apply_filters` and `do_action`.

- **Document Hooks**:
  - Provide clear documentation for custom hooks you define.

---

history

## Support and Resources

- WordPress Plugin Handbook: https://developer.wordpress.org/plugins/
- WordPress Coding Standards: https://developer.wordpress.org/coding-standards/
- PHPUnit Documentation: https://phpunit.de/documentation.html
- WordPress Plugin Unit Tests: https://make.wordpress.org/core/handbook/testing/automated-testing/phpunit/


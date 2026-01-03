# Contributing to WP Referral Link Maker

Thank you for your interest in contributing to WP Referral Link Maker! This document provides guidelines for contributing to the project.

## Getting Started

1. Fork the repository
2. Clone your fork: `git clone https://github.com/YOUR-USERNAME/WP-Refferal-Link-Maker.git`
3. Create a new branch: `git checkout -b feature/your-feature-name`
4. Make your changes
5. Test your changes
6. Commit your changes: `git commit -m "Add your descriptive commit message"`
7. Push to your fork: `git push origin feature/your-feature-name`
8. Create a pull request

## Code Standards

### PHP

- Follow [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/)
- Use proper indentation (tabs, not spaces)
- Add PHPDoc comments for all functions and classes
- Escape all output using WordPress functions (esc_html, esc_attr, esc_url, etc.)
- Sanitize all input
- Use nonces for form submissions
- Check user capabilities before performing privileged operations

### JavaScript

- Follow [WordPress JavaScript Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/javascript/)
- Use jQuery in no-conflict mode
- Prefix all global variables and functions

### CSS

- Follow [WordPress CSS Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/css/)
- Use meaningful class names with the `wp-rlm-` prefix
- Avoid !important unless absolutely necessary

## Testing

Before submitting a pull request:

1. Test your changes in a clean WordPress installation
2. Verify PHP syntax: `php -l your-file.php`
3. Test with WP_DEBUG enabled
4. Check for PHP notices and warnings
5. Verify that existing functionality still works

## Pull Request Guidelines

- Provide a clear description of the changes
- Reference any related issues
- Include screenshots for UI changes
- Ensure all tests pass
- Keep pull requests focused on a single feature or bug fix
- Update documentation if needed

## Reporting Issues

When reporting issues, please include:

- WordPress version
- PHP version
- Plugin version
- Steps to reproduce the issue
- Expected behavior
- Actual behavior
- Screenshots if applicable
- Any error messages

## Feature Requests

We welcome feature requests! Please:

- Check if the feature has already been requested
- Clearly describe the feature and its use case
- Explain why it would be useful to most users
- Be open to discussion and feedback

## Questions?

If you have questions, feel free to:

- Open an issue for discussion
- Contact the maintainers

## License

By contributing, you agree that your contributions will be licensed under the GPL v2 or later.

# Varabit SEO Audit

A WordPress plugin that provides a simple yet powerful SEO audit tool for websites. The plugin allows users to analyze their websites for common SEO issues and provides recommendations for improvement.

## Features

- **Page Speed Analysis**: Analyzes page loading performance for both desktop and mobile using Google PageSpeed Insights API
- **Meta Tags Analysis**: Checks title tags, meta descriptions, and other important meta elements
- **Headings Structure Analysis**: Evaluates H1, H2, H3 heading structure and hierarchy
- **Image Alt Text Analysis**: Identifies images missing alt text attributes
- **Mobile-Friendliness Check**: Assesses mobile usability and responsiveness
- **Keyword Analysis**: Identifies top keywords and their density on the page
- **Downloadable PDF Reports**: Option to download a comprehensive PDF report

## Installation

1. Upload the `varabit-seo-audit` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings > Varabit SEO Audit to configure your Google PageSpeed Insights API key (optional)
4. Add the shortcode `[varabit_seo_audit]` to any page or post where you want the SEO audit tool to appear

## Usage

### Basic Usage

Simply add the shortcode `[varabit_seo_audit]` to any page or post. This will display the SEO audit form where users can enter a URL to analyze.

### Customization

You can customize the title of the audit tool by using the title attribute:

```
[varabit_seo_audit title="My Custom SEO Audit Tool"]
```

## Google PageSpeed Insights API

While the plugin can work without an API key, for production use and to avoid rate limiting, it's recommended to obtain a Google PageSpeed Insights API key:

1. Go to the [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select an existing one
3. Enable the PageSpeed Insights API
4. Create credentials to get an API key
5. Enter this API key in the plugin settings page

## Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
- JavaScript enabled in the browser

## Support

For support or feature requests, please contact us at support@varabit.com

## License

This plugin is licensed under the GPL v2 or later.

```
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
```
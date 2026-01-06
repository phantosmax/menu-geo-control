![Banner](./.github/banner.png)
# Menu Geo Control - WordPress Plugin

A WordPress plugin that adds country-based visibility controls to WordPress menu items.

## Features

- **Show for Countries** - Display menu items only for specific countries
- **Hide for Countries** - Hide menu items from specific countries
- **Three Targeting Modes** - Show/Hide (Default), Show Only, or Hide Only
- **Visual Interface** - Fields appear directly in the WordPress menu editor
- **Automatic Caching** - 24-hour cache to minimize API calls
- **No API Keys Required** - Uses free geolocation services (ip-api.com by default)
- **Works with All Menus** - Primary, footer, mobile menus, etc.
- **Nested Menu Support** - Hide parent items and all children are hidden too

## Installation

### Method 1: Upload via WordPress Admin

1. Download the `menu-geo-control.zip` file
2. Go to **Plugins → Add New** in WordPress admin
3. Click **Upload Plugin**
4. Choose the zip file and click **Install Now**
5. Click **Activate Plugin**

### Method 2: Manual FTP Upload

1. Extract the zip file
2. Upload the `menu-geo-control` folder to `/wp-content/plugins/`
3. Go to **Plugins** in WordPress admin
4. Activate **Menu Geo Control**

## Configuration

1. Go to **Settings → Menu Geo Control**
2. Select your preferred geolocation service
3. Set a default country code for local development
4. Save settings

## How to Use

### Adding Geo Targeting to Menu Items

1. Go to **Appearance → Menus**
2. Select the menu you want to edit
3. Expand any menu item by clicking the dropdown arrow
4. Scroll down to see the **Geo Targeting** section with three fields:

#### Show for Countries
Enter country codes separated by commas to show the menu item ONLY for those countries.
- Example: `AU,NZ,SG`
- Result: Menu item only visible to Australia, New Zealand, and Singapore visitors
- Leave empty to show for all countries

#### Hide for Countries
Enter country codes separated by commas to hide the menu item for those countries.
- Example: `US,GB`
- Result: Menu item hidden from US and UK visitors
- Leave empty to not hide for any countries

#### Geo Targeting Mode
- **Show/Hide (Default)**: Both fields work together
- **Show Only**: Only uses "Show for Countries" field
- **Hide Only**: Only uses "Hide for Countries" field

5. Click **Save Menu**

## Use Case Examples

### Example 1: Regional Product Menus

Create region-specific product menu items:

**Products (Australia/NZ)**
- Show for Countries: `AU,NZ`
- Links to: `/products-anz`

**Products (Southeast Asia)**
- Show for Countries: `SG,MY,ID,TH`
- Links to: `/products-sea`

**Products (North America)**
- Show for Countries: `US,CA`
- Links to: `/products-na`

Result: Each visitor sees only the product menu relevant to their region.

### Example 2: Regional Support Centers

**Sydney Support**
- Show for Countries: `AU`
- Links to: `/support/sydney`

**Singapore Support**
- Show for Countries: `SG,MY,ID,TH`
- Links to: `/support/singapore`

**Auckland Support**
- Show for Countries: `NZ`
- Links to: `/support/auckland`

### Example 3: Compliance Pages

**GDPR Information**
- Show for Countries: `GB,DE,FR,ES,IT,NL,BE`
- Links to: `/gdpr-compliance`

### Example 4: Hide Features for Specific Markets

**Enterprise Plans**
- Hide for Countries: `CN`
- Links to: `/enterprise`

Result: Enterprise menu item is hidden from Chinese visitors.

### Example 5: Regional Pricing Pages

**Pricing (AUD)**
- Show for Countries: `AU,NZ`
- Links to: `/pricing-aud`

**Pricing (SGD)**
- Show for Countries: `SG,MY,ID,TH`
- Links to: `/pricing-sgd`

**Pricing (USD)**
- Show for Countries: `US,CA`
- Links to: `/pricing-usd`

## Country Codes

Use standard ISO 3166-1 alpha-2 country codes (2 letters):

### Asia Pacific
- **AU** - Australia
- **NZ** - New Zealand
- **SG** - Singapore
- **MY** - Malaysia
- **ID** - Indonesia
- **TH** - Thailand
- **JP** - Japan
- **CN** - China
- **IN** - India
- **HK** - Hong Kong
- **PH** - Philippines
- **VN** - Vietnam
- **KR** - South Korea

### Americas
- **US** - United States
- **CA** - Canada
- **MX** - Mexico
- **BR** - Brazil
- **AR** - Argentina

### Europe
- **GB** - United Kingdom
- **DE** - Germany
- **FR** - France
- **ES** - Spain
- **IT** - Italy
- **NL** - Netherlands
- **BE** - Belgium
- **CH** - Switzerland
- **AT** - Austria
- **SE** - Sweden

[See full list of country codes](https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2)

## Nested Menu Items

When you hide a parent menu item, all of its child (sub-menu) items are automatically hidden as well. This ensures logical menu structure.

**Example:**
- Parent: "Solutions" (Hidden for CN)
  - Child: "Cloud Hosting"
  - Child: "Managed Services"
  - Child: "Support"

If a Chinese visitor views the menu, neither the parent nor any of the children will appear.

## Geolocation Services

The plugin supports three free geolocation services:

### ip-api.com (Default)
- **Free**: Yes, no API key required
- **Rate Limit**: 45 requests per minute
- **Accuracy**: Good
- **Recommended for**: Most use cases

### ipapi.co
- **Free Tier**: 1,000 requests per day
- **Rate Limit**: Generous
- **Accuracy**: Very good
- **Recommended for**: Higher traffic sites

### ipinfo.io
- **Free Tier**: 50,000 requests per month
- **Rate Limit**: Very generous
- **Accuracy**: Excellent
- **Recommended for**: Production sites with high traffic

## Performance

- Country detection results are cached for 24 hours per IP address
- Only one API call per unique visitor per day
- Minimal performance impact due to efficient caching
- Cache can be manually cleared from settings page

## Local Development

When developing locally (127.0.0.1 or private IP addresses), the plugin uses the default country code set in settings. This allows you to test country-specific menus during development.

### Testing Different Countries

1. Set your default country in **Settings → Menu Geo Control**
2. Clear the cache
3. View your site to see the menu for that country
4. Change default country and clear cache to test another country

## Troubleshooting

### Menu items not showing/hiding correctly?

1. Check **Settings → Menu Geo Control** to see detected country
2. Verify country codes are correct (2 letters, uppercase, comma-separated)
3. Clear the cache from the settings page
4. Make sure you saved your menu after adding geo targeting
5. Check if parent menu items have conflicting settings

### Fields not appearing in menu editor?

1. Make sure the plugin is activated
2. Try expanding/collapsing the menu item
3. Save and refresh the Menus page
4. Check browser console for JavaScript errors

### Menu looks broken on frontend?

1. Some themes require specific menu item structures
2. Test with a default WordPress theme to isolate theme issues
3. Check if your theme caches menus

## Multiple Menus

The plugin works with all registered menus in WordPress:
- Primary navigation
- Footer menus
- Mobile menus
- Custom menus
- Widget area menus

Each menu can have its own geo-targeting rules per menu item.

## Combining with Other Plugins

This plugin can be used alongside:
- **WPBakery Geo Control** - For page content geo-targeting
- **Geo Content Control** - For shortcode-based geo-targeting
- Other menu plugins - Compatible with most menu extensions

## Privacy & GDPR

This plugin:
- Detects visitor country based on IP address
- Uses third-party geolocation APIs
- Caches results for 24 hours
- Does not store personal information
- Does not use cookies

Ensure your privacy policy mentions use of geolocation services.

## Advanced Usage

### Combining Show and Hide

You can use both fields together in "Show/Hide (Default)" mode:

**Example**: Show menu item ONLY for AU/NZ but explicitly hide for US
- Show for Countries: `AU,NZ`
- Hide for Countries: `US`
- Result: Only AU/NZ visitors see it (US already excluded but explicitly hidden as safety)

### Dynamic Menu Switching

Create multiple versions of the same menu item:
1. "Contact Us (Sydney)" - Show for AU
2. "Contact Us (Singapore)" - Show for SG,MY,ID,TH
3. "Contact Us (Auckland)" - Show for NZ

All link to different pages with regional contact info.

## Support

No Support

For WordPress menu system questions, visit WordPress documentation.

## Changelog

### Version 1.0.0
- Initial release
- Show/Hide country targeting for menu items
- Three targeting modes
- Visual interface in menu editor
- Multiple geolocation service providers
- Automatic caching
- Admin settings page

## License

GPL v2 or later

## Credits

Developed for Phantosmax infrastructure and cloud hosting services.

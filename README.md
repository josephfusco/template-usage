# Template Usage

Display info on what page templates are currently being used within a WordPress multisite.

## Usage

1. Download & network activate this plugin.
2. Go to the "Template Usage" network admin page.
3. Choose a theme to display all available page templates for all sites that have that theme activated and click "Select".

Note that page templates that are added via plugins will also be listed for the sites using the selected theme.

## Internationalization

For the text in the plugin to be able to be translated easily the text should not be hardcoded in the plugin but be passed as an argument through one of the localization functions in WordPress.

[How to Internationalize Your Plugin →](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/)

This plugin contains a ready to go `.pot` can be found within `languages/`. This file can be updated using the following command:

```sh
npm run i18n
```

## License

This plugin is licensed under [GNU General Public License v2 (or later)](./LICENSE).

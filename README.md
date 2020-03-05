<p align="center">
<a href="https://rtcamp.com/" target="_blank"><img width="200"src="https://rtcamp.com/wp-content/themes/rtcamp-v9/assets/img/site-logo-black.svg"></a>
</p>

# PWA Extension

[![Project Status: Active – The project has reached a stable, usable state and is being actively developed.](https://www.repostatus.org/badges/latest/active.svg)](https://www.repostatus.org/#active)

An extension to [PWA](https://wordpress.org/plugins/pwa/) to enable `add to homescreen` and `offline reading` features of PWA.

**Author:** [rtCamp](https://github.com/rtCamp/)

**Tags:** [pwa](https://wordpress.org/plugins/tags/pwa)
**Requires at least:** 4.9
**Tested up to:** 5.1
**License:** [GPLv2 or later](http://www.gnu.org/licenses/gpl-2.0.html)
**Requires PHP:** 5.4+

## Requirements

- The Official [PWA](https://wordpress.org/plugins/pwa/) Plugin

## Features

- `Add to Home Screen` popup for mobiles with custom icon setup in WP theme `(your-theme//assets/img/icon-{width x height}.png'`.
- Offline reading by caching assets and pages.

### Offline Form Submission.
- Submit gravity form even if you are offline. Plugin will send form response once you get back online.
- Add form routes to enable offline form submission in plugin settings page.
e.g Enable offline form submission with following settings for the forms available on routes `example.com/contact` and `example.com/feedback`
```
/contact
/feedback
```

## Steps to setup add to home screen for your site.

- Add app icon images of all sizes in your WordPress theme assets folder with `your-theme/assets/img/icon-{width x height}.png` path
- The valid size of the images can be 72x72, 96x96, 128x128, 144x144, 152x152, 192x192, 384x384, 512x512.
- Example image file name `icon-192x192.png`.
- The path of the icons can be changed with `rt_pwa_extensions_app_icon_{height}_{width}` filter
- Example snippet to change icon src.
```php
add_filter( 'rt_pwa_extensions_app_icon_72_72', 'update_icon_path' );
/**
 * Updates icon src.
 *
 * @return string
 */
function update_icon_src() {
	return 'www.example.com/new-image.png';
}
```

## Integration with izooto plugin.

- Registers izooto service worker using `wp_front_service_worker` hook to main service worker file
- Removes Service worker code of izooto from `?izooto=sw` to prevent conflicting it with main service worker.

## Contributors
- [Chandra Patel](https://github.com/chandrapatel/)
- [Pradeep Sonawane](https://github.com/pradeep910/)
- [Sagar Nasit](https://github.com/sagarnasit/)

## Contribute

### Reporting a bug 🐞

Before creating a new issue, do browse through the [existing issues](https://github.com/rtCamp/pwa-extension/issues) for resolution or upcoming fixes. 

If you still need to [log an issue](https://github.com/rtCamp/pwa-extension/issues/new), making sure to include as much detail as you can, including clear steps to reproduce your issue if possible.

### Creating a pull request

Want to contribute a new feature? Start a conversation by logging an [issue](https://github.com/rtCamp/pwa-extension/issues).

Once you're ready to send a pull request, please run through the following checklist: 

1. Browse through the [existing issues](https://github.com/rtCamp/pwa-extension/issues) for anything related to what you want to work on. If you don't find any related issues, open a new one.

1. Fork this repository.

1. Create a branch from `develop` for each issue you'd like to address and commit your changes.

1. Push the code changes from your local clone to your fork.

1. Open a pull request and that's it! We'll with feedback as soon as possible (Isn't collaboration a great thing? 😌)

1. Once your pull request has passed final code review and tests, it will be merged into `develop` and be in the pipeline for the next release. Props to you! 🎉

## Change Log

### v1.0.3 (4-02-2020)

- Add support for gravity form offline submission.
- Add `rt_pwa_extensions_app_icon_{height}_{width}` filter to change app icon path.

### v1.0.2 (24-12-2019)

- Restructure plugin.

### v1.0.1 (24-12-2019)

- izooto push notifications plugin integration.

### v1.0.0 (12-09-2019)

- `Add to Home Screen` popup for mobiles with custom icon setup in WP theme `(your-theme//assets/img/icon-{width x height}.png'`.
- Offline reading by caching assets and pages.

## Unit testing

- Setup local unit test environment by running script from terminal

```./bin/install-wp-tests.sh <db-name> <db-user> <db-pass> [db-host] [wp-version] [skip-database-creation]```

- Execute `phpunit` in terminal from repository to run all test cases.

- Execute `phpunit ./tests/inc/test-class.php` in terminal with file path to run specific tests.

## Does this interest you?

<a href="https://rtcamp.com/"><img src="https://rtcamp.com/wp-content/uploads/2019/04/github-banner@2x.png" alt="Join us at rtCamp, we specialize in providing high performance enterprise WordPress solutions"></a>

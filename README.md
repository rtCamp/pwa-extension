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

## Integration with izooto plugin.

- Registers izooto service worker using `wp_front_service_worker` hook to main service worker file
- Removes Service worker code of izooto from `?izooto=sw` to prevent conflicting it with main service worker.

## Contributors
- [Chandra Patel](https://github.com/chandrapatel/)
- [Pradeep Sonawane](https://github.com/pradeep910/)
- [Sagar Nasit](https://github.com/sagarnasit/)

## Contributing

### Report a Bug

Before you create a new issue, please search [existing issues](https://github.com/rtCamp/blank-theme/issues) to see if there’s an existing resolution to it, or if it’s already been fixed in a newer version.

Once you’ve done a bit of searching and discovered there isn’t an open or fixed issue for your bug, please [create a new issue](https://github.com/rtCamp/blank-theme/issues/new). Include as much detail as you can, and clear steps to reproduce if possible.

### Create a pull request

Want to contribute a new feature? Please first open a new issue to discuss whether the feature is a good fit for the project.

Once you've decided to commit the time to seeing your pull request through, please follow our guidelines for creating a pull request.

1. Search existing issues. If you can’t find anything related to what you want to work on, open a new issue.

1. Fork the repository.

1. Create a branch from `develop` for each issue you’d like to address. Commit your changes.

1. Push the code changes from your local clone to your fork.

1. Open a pull request.

1. Respond to code review feedback in a timely manner, recognizing development is a collaborative process.

1. You need at least one approval and Once your pull request has passed code review and tests, it will be merged into `develop` and be in the pipeline for the next release.


## Change Log

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

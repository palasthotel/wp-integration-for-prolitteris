# Palasthotel Integration for ProLitteris (WordPress-Plugin)

Counting pixels and automated text reports for "Onlinewerke entschädigen" (Verteilung Online)
of [ProLitteris](https://prolitteris.ch), the Swiss collective rights management organization.
Until 1.6.4 the plugin was called "ProLitteris" (`pro-litteris`); see
[Moving from pro-litteris](#moving-from-pro-litteris).

On WordPress.org: [palasthotel-integration-for-prolitteris](https://wordpress.org/plugins/palasthotel-integration-for-prolitteris/) (from 2.0.0).

## What it does

- keeps a pool of unassigned counting pixels, refilled hourly from the ProLitteris web
  service (`POST /rest/api/1/pixel`)
- assigns a pixel to a post when an editor opens it or the post is read through the REST
  API, and renders it in `wp_footer` together with the `referrer` meta tag ProLitteris
  requires
- builds the report from title, plain text (via [html2text](https://github.com/mtibben/html2text)),
  authors and image originators and sends it (`POST /rest/api/1/message`) - from the editor
  sidebar, automatically every hour, or with WP-CLI
- user profile fields for the ProLitteris member number, first and last name; an image
  originator field on attachments
- a ProLitteris column in the posts list and a dashboard widget for the pixel pool

## Configuration

Settings → ProLitteris, or constants in `wp-config.php`, which win over the settings page:

| Constant | Setting | |
|---|---|---|
| `PRO_LITTERIS_ENABLED` | Enabled | `true` to use the integration |
| `PRO_LITTERIS_SYSTEM` | API URL | default `https://owen.prolitteris.ch` |
| `PRO_LITTERIS_CREDENTIALS` | Member number, username, password | `"member number:username:password"`, sent base64-encoded as `Authorization: OWEN …` |
| `PRO_LITTERIS_AUTO_MESSAGES` | Automatic reporting | `true` to report hourly |

Nothing is requested from ProLitteris before the integration is enabled and has credentials.
The password entered on the settings page is stored in the options table and never sent
back to the browser.

## REST

`pro_litteris` on every enabled post type. Users who cannot edit the post - visitors
included - get the pixel only:

```json
{ "pixel": { "uid": "…", "domain": "…", "url": "https://…/na/…" } }
```

Editors additionally get `message` (the stored report) or `messageDraft` (title,
participants, plain text), and `pushError`/`pushErrorData`. Writing
`{"pro_litteris": {"pushMessage": true}}` reports a published or private post; it needs
the post type's `publish_posts` capability.

`pro_litteris_author` on attachments (editors only): the image originator's user id.

## Hooks

The names are constants of `Palasthotel\ProLitteris\Plugin`.

| Constant | Hook | Type |
|---|---|---|
| `FILTER_POST_TYPES` | `pro_litteris_post_types` | filter, default `["post"]` |
| `FILTER_POST_AUTHORS` | `pro_litteris_post_authors` | filter, user ids reported as authors |
| `FILTER_POST_MESSAGE_CONTENT` | `pro_litteris_post_message_content` | filter, the HTML the plain text is made from |
| `FILTER_PREVENT_PIXEL_ASSIGN` | `pro_litteris_prevent_pixel_assign` | filter, return a `Model\PreventPixelAssign` with `prevent = true` |
| `FILTER_RENDER_PIXEL` | `pro_litteris_render_pixel` | filter, `false` keeps the pixel out of `wp_footer` |
| `FILTER_POST_HAS_PAYWALL` | `pro_litteris_post_has_paywall` | filter, `true` for the paywall pixel variant (`/pw/`) |
| `ACTION_BEFORE_MESSAGE_CONTENT` | `pro_litteris_before_message_content` | action, before the content is rendered for the report |
| `ACTION_AFTER_MESSAGE_CONTENT` | `pro_litteris_after_message_content` | action, after it |

```php
add_filter( \Palasthotel\ProLitteris\Plugin::FILTER_POST_TYPES, fn( $types ) => [ ...$types, 'news' ] );
```

## WP-CLI

```sh
wp pro-litteris refillPool [--to=<size>]
wp pro-litteris reportContents --year=<year>
```

## Moving from pro-litteris

Version 2.0.0 has a new folder (`palasthotel-integration-for-prolitteris`), main file and text domain.
PHP namespace, class names, hooks, REST fields, options, post and user meta and the
database tables are unchanged, so pixels, reports and project code keep working.

1. Rename the constants in `wp-config.php` - the old names are no longer read, so
   without this the integration stays switched off:

   | up to 1.6.4 | from 2.0.0 |
   |---|---|
   | `PH_PRO_LITTERIS` | `PRO_LITTERIS_ENABLED` |
   | `PH_PRO_LITTERIS_SYSTEM` | `PRO_LITTERIS_SYSTEM` |
   | `PH_PRO_LITTERIS_CREDENTIALS` | `PRO_LITTERIS_CREDENTIALS` |
   | `PRO_LITTERIS_AUTO_MESSAGES` | unchanged |

2. Install the new plugin.
3. Deactivate "ProLitteris", then activate "Palasthotel Integration for ProLitteris". As long as the
   old one is active, the new one does not load and says so in wp-admin.
4. Delete the old plugin.

## Repository layout

`public/` is exactly what ships to WordPress.org. Everything outside it is repository-only.

| Path | Description |
|---|---|
| `public/palasthotel-integration-for-prolitteris.php` | plugin header and bootstrap |
| `public/classes/` | the plugin's PHP |
| `public/cli/` | WP-CLI commands |
| `public/dist/` | compiled editor sidebar - **generated**, not in the repository |
| `public/vendor/` | composer dependencies (html2text) - **generated**, not in the repository |
| `public/composer.json`, `public/composer.lock` | what `public/vendor/` is installed from |
| `src/` | editor sidebar JavaScript source |
| `languages/` | the translations up to 2.0, not shipped - translate.wordpress.org provides them, these are for importing there |
| `palasthotel-integration-for-prolitteris.php` | DEV wrapper, loads `public/` when the repository is checked out into `wp-content/plugins/` |
| `.github/workflows/` | CI/CD, calling the shared workflows - see [.github/WORKFLOWS.md](.github/WORKFLOWS.md) |

## Development

```sh
npm ci
npm run build                                   # → public/dist/
(cd public && composer install)                 # → public/vendor/
npx wp-env start                                # http://localhost:8888, admin / password
npm run pack                                    # → build/palasthotel-integration-for-prolitteris/ and the zip
```

`npm run pack` runs the shared `pack.sh` from
[palasthotel/github-workflows](https://github.com/palasthotel/github-workflows), which has to
be checked out next to this repository.

## Releasing

Releases are automated with [release-please](https://github.com/googleapis/release-please)
and deployed to the WordPress.org SVN repository. Commit with
[conventional commits](https://www.conventionalcommits.org/) and merge the release PR:

```
fix: …   → patch    feat: …  → minor    feat!: … → major
```

What is specific to this plugin is in [.github/WORKFLOWS.md](.github/WORKFLOWS.md), commit
conventions in [CONTRIBUTING.md](CONTRIBUTING.md).

## License

GNU General Public License v3.0 or later - see [LICENSE](LICENSE). This plugin is not
affiliated with or endorsed by ProLitteris.

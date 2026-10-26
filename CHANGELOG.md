# Changelog

All notable changes to this project are documented here. The format follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and this project uses
0.x versioning until the public API is frozen at 1.0.

## [Unreleased]

## [0.4.6] - 2026-10-26

### Changed

- Renamed to **Tribar Connector for TypeSafe Jev**, slug and text domain
  `tribar-connector-for-typesafe-jev`, main file
  `tribar-connector-for-typesafe-jev.php`. The directory review pended
  "Connector for TypeSafe Jev" as too generic (a descriptive word plus a
  third-party name) and asked for a distinctive term at the front. PHP API,
  `jevc_` hooks, setting names and the `jev/v1` REST namespace are unchanged.
- `readme.txt` Contributors is `juanml`, the WordPress.org account that owns
  the submission; `juanlentino` is not a WordPress.org user.
- GitHub repo renamed to match the slug; GitHub redirects the old URLs.

### Added

- `'long_context' => true` per-call argument for `JevConnector\ask()` / `Client::ask()`, sending the `TypeSafe-Danger-Zone-Long-Context` header (64k/32k to 262k tokens) for that call only. The flag is part of the cache key, so a long-context answer is never served as a plain one.

## [0.4.5] - 2026-10-21

Documentation only. No code change. Ships because `readme.txt` and the
`ask()` docblock do.

### Changed

- The "In five lines" example holds a hostile comment for review
  (`wp_set_comment_status( $comment, 'hold' )`) instead of marking spam.
  Leading with spam set Jev against Akismet, which sees signals from millions
  of sites and is better at it; the example now shows a judgement a spam
  filter does not make. The readme.txt example, the `ask()` docblock and the
  pinning test follow.

### Added

- "Common uses" in README and readme.txt: ten everyday WordPress jobs
  (comment moderation, form routing, tagging, pre-publish checks, policy
  checks, checking AI drafts, product listings, review triage, related-post
  ranking, lead qualification), each with the question to ask, its shape and
  what to do with the answer, drawn from TypeSafe's use-case map.


## [0.4.4] - 2026-10-21

Checked against the WordPress security handbook (validating, sanitizing,
escaping, nonces). It prefers validating and rejecting over sanitizing; three
inputs were cleaned or trusted instead.

### Changed

- REST `model` is validated against `^[A-Za-z0-9._-]{1,64}$`
  (`REST_Controller::MODEL_PATTERN`) instead of passed through
  `sanitize_text_field()`, so a malformed id is refused, not silently altered.
- REST `state` declares its type (string, object or array).
- `Question::validate_map()` requires `instructions` to be a non-empty string.
  REST callers could send an array or a number, which went to TypeSafe and
  came back as a 422.

### Unchanged, checked

- No output is echoed anywhere; errors leave as `WP_Error`, and exception
  messages escape caller-supplied ids with `esc_html()`.
- Every route has a capability check (`jevc_rest_capability`); cookie-auth
  REST calls carry core's `wp_rest` nonce, which `apiFetch` adds.
- The API key never appears in a REST response; requests use
  `redirection => 0` so the bearer header cannot follow a redirect.
- No SQL, no `$_GET`/`$_POST` reads, no `unserialize()`, every file guards
  `ABSPATH`, uninstall guards `WP_UNINSTALL_PLUGIN`.

### Documentation

- `readme.txt` FAQ points contributors to the GitHub repo; CONTRIBUTING has a
  "Where to start" section; the new-issue page links Discussions (now on).
- `screenshot-1` and the README's Connectors card image retaken on 0.4.3:
  the card shows the plugin's own icon, no TypeSafe mark.

## [0.4.3] - 2026-10-21

### Added

- The plugin's own icon, `assets/images/icon.svg`: three confidence bars on
  the dark tile of the social preview, original artwork under the plugin's
  GPL. It is the Connectors card's `logo_url` again (core's placeholder since
  0.4.2) and the directory icon (`.wordpress-org/icon.svg`, 256 and 128 PNG).
  No TypeSafe mark anywhere.
- `readme.txt` Upgrade Notice for 0.4.3.

### Documentation

- `docs/RELEASING.md`: dropped the stale note about the logo on the
  Connectors card (removed in 0.4.2), and documented deploying to the
  directory's SVN from the release workflow after approval.

## [0.4.2] - 2026-10-21

### Removed

- The TypeSafe logo (`assets/images/typesafe.png`) and the connector's
  `logo_url`. The Connectors card shows core's generic placeholder. Checked
  against the directory guidelines before submission: Guideline 1 requires
  every image a plugin ships to be GPL-compatible, which a third party's mark
  is not, and TypeSafe's Master Customer Agreement (16.4) grants no right to
  its logo. The unofficial plugins that do show a vendor logo were not a
  reason to keep it. `readme.txt` and README say the plugin ships no logo.

### Added

- `readme.txt` `== Upgrade Notice ==` section, the one section of the
  official readme template that was missing.


## [0.4.1] - 2026-10-21

Directory readiness. No behaviour change.

### Changed

- `readme.txt` states that the plugin is independent and not affiliated with,
  endorsed by, or sponsored by TypeSafe, and that the TypeSafe mark appears
  only on the Connectors card. Most unofficial integrations in the directory
  carry this line (Cloudflare Turnstile, Stripe, OpenAI clients); until now
  only `README.md` said it, and the directory shows `readme.txt`.
- The GitHub repo is renamed from `jev-connector` to
  `connector-for-typesafe-jev`, the plugin's slug, so the public name no
  longer starts with TypeSafe's mark (the spirit of Guideline 17). GitHub
  redirects old URLs. `Plugin URI`, `composer.json`, badges, clone commands,
  the security advisory link and the social preview are updated.


## [0.4.0] - 2026-10-21

TypeSafe shipped Jev 1.14 (`jev-preview`) and `GET /v1/models` on
2026-10-14. The plugin already follows new releases through the `jev-latest`
alias; this adds the model list, built the way the core Anthropic, Google and
OpenAI connectors build theirs: live from the API, cached for a day, never
hardcoded, and never used to reject a model id.

### Added

- `JevConnector\models( bool $refresh = false )` and the `Models` class:
  `GET /v1/models`, parsed to `name`, `description`, `release_date`, cached
  in a transient for a day per API key. Failures are not cached.
- `GET /wp-json/jev/v1/models` (same capability as `/ask`; `refresh` skips
  the cache).
- README "Models" section on the `jev-latest` default, pinning a versioned
  id, and `jev-preview`. The pinning example now uses a real id
  (`jev-1.13.0`, not `jev-1.13`).

### Changed

- `Client::headers()`, `Client::not_configured()` and
  `Client::error_for_status()` are public static so the model list shares the
  auth header, User-Agent and error mapping with `ask()`.
- `readme.txt` External services covers the new request.

### Not added

- Images and `box` questions. TypeSafe marks them experimental behind a
  `TypeSafe-Danger-Zone-Images` header, and only `jev-preview` answers box
  questions. `Question::validate_map()` still accepts only `noul`, `choice`
  and `score`, so a box question is refused before it is sent. Support comes
  when TypeSafe calls them stable, as a release of its own.


## [0.3.1] - 2026-10-05

Documentation and packaging only. No behaviour change. The release exists
because `readme.txt` and the `Connector` docblocks ship, and the 0.3.0 FAQ
stated the `ai_provider` mechanism that was retracted on Core Trac #66146.

### Documentation

- Corrected why the plugin registers as `ai_decision` rather than
  `ai_provider`. Earlier docs said core validates `ai_provider` keys "against
  generative operations". It does not: on save, core asks the provider class
  registered under the same id in the PHP AI Client whether it is configured
  and clears the key unless the answer is exactly `true`, and with no provider
  class the answer is `null`. The Jev connector has no provider class because
  the AI Client has no capability yet for a model that returns probabilities,
  choices and scores. Jev not being an LLM is unchanged and still stated. The
  docs now say this is interim: the Core AI team's direction is decision-model
  support inside the AI Client (php-ai-client#296), after which the plugin
  becomes an `ai_provider`. Raised by Nik McLaughlin and Jason Adams on Trac
  #66146 and in #core-ai. Touches the README, `readme.txt` FAQ,
  `docs/ARCHITECTURE.md`, `docs/HOOKS.md`, the `Connector` docblocks and
  `CLAUDE.md`. No behaviour change.
- PHPCS ignores `.claude/`. Local agent worktrees under it hold full copies of
  the source and made `composer lint` report every class as a duplicate.
- README opens with an "In five lines" example: the smallest honest call, a
  comment spam check. It is executed by
  `ConsumerContractTest::test_readme_five_line_example`, so the snippet at the
  top of the page cannot drift from `ask()`, `Question::noul()` or
  `Response::noul()`.
- The example gates on `noul( 'spam' ) >= 0.9` rather than
  `is_confident( 'spam', 0.9 )`. For a noul, `is_confident()` measures
  distance from 0.5 in either direction, so a comment scoring 0.02 (clearly
  not spam) passes it; using it as the spam gate would flag innocent
  comments. `is_confident()` is right for `choice` and `score`, and the
  README says so.
- "In production" records that juanlentino.com runs six typed readings over
  its corpus through the connector, with Signal & Noise Tools as the consumer
  that owns the decisions, and links that plugin's public `AI.md`, where each
  reading's rubric, its unsure-case behaviour and its cost are written down.
  The two repositories now point at each other: S&N Tools already credited
  the connector.
- The README section on the connector type links Core Trac #66146 and
  php-ai-client#296.
- A "Screenshots" section. The first image is in: the Connectors screen on a
  live WordPress 7.1 site, with TypeSafe Jev connected among the Anthropic and
  Google provider cards, cropped to the card list. It is also
  `.wordpress-org/screenshot-1.png` for the directory listing. The second is
  one real `wp eval` call on that site returning a noul, a choice and a score
  with their confidences, cropped so the shell prompt, the document root and
  the server IP in the status bar are out of frame;
  `.github/images/README.md` says exactly what to capture and what must not
  appear in the frame. `readme.txt` gains the matching `== Screenshots ==`
  captions and `.wordpress-org/README.md` lists the second file.
- `readme.txt` mirrors the five-line hook in the directory's voice. The short
  description, `Stable tag` and version are untouched.
- `.github/images/social-preview.png` (1280 × 640) plus the SVG it is
  generated from, for GitHub's social preview. Neither ships: `.github` is
  excluded by `.distignore`.

### Fixed

- Docs said the Connectors screen groups cards by `type` and that this
  plugin's card sits in its own group. It does not: the screen is one flat
  list sorted by id, and `type` is only ever compared against `ai_provider`
  (core: key validation, default providers, logos; screen: the install
  callout; AI plugin: the credential count). Corrected in `CLAUDE.md`,
  `SUBMITTING.md` and `docs/HOOKS.md`. The same error went into Core Trac
  #66146 and was corrected there.

## [0.3.0] - 2026-09-19

A pure connector. This is the shape the core AI provider connectors have:
the card on Settings → Connectors, a client, a REST proxy, hooks, and no
screens of its own. What a site does with Jev's answers belongs to the
plugin that owns the content; Signal & Noise Tools, the one consumer, was
already built that way and never used what is removed here.

### Removed

- The **Settings → TypeSafe Jev** screen. The default model and the REST
  capability it held are set with the existing `jevc_default_model` and
  `jevc_rest_capability` filters, which now carry the defaults
  (`jev-latest`, `edit_posts`) directly.
- The **comment guardrail** and **term suggestions** modules, the module
  registry, and the editor script. Both were off by default and neither had
  run against real traffic. They remain in git history at `v0.2.5` should a
  separate plugin want them.
- Hooks `jevc_modules`, `jevc_guardrail_decision`, `jevc_guardrail_state`,
  `jevc_guardrail_unavailable`. Routes `/jev/v1/suggest-terms` and
  `/jev/v1/apply-terms`.

### Changed

- `readme.txt` External services now states that the plugin itself never
  initiates a request; only calling code and the REST proxy do.
- `uninstall.php` still deletes `jevc_settings`, the option the removed
  screen wrote, so a site upgraded from 0.2.x is left clean.
- Shipped file count drops from 19 to 13. The consumer contract
  (`tests/test-consumer-contract.php`) is unchanged and still passes.

## [0.2.5] - 2026-09-19

### Changed

- The not-connected notice now reads "Manage the key under Settings →
  Connectors." with only "Connectors" linked. Shells that open cross-page
  links in their own window (OpenStation does, by its bridge protocol's
  rule 3, for every plugin's settings page including core's own AI settings)
  title that window from the link text, so the window is now called
  "Connectors" rather than the whole sentence. Reads better in classic
  wp-admin too. Nothing else about the link changed; OpenStation opening a
  separate window is the shell's behaviour, not the plugin's, and there is
  no sanctioned per-link way to opt out of it.

## [0.2.4] - 2026-09-19

### Fixed

- The card's "Get your API key" link (`credentials_url`) pointed at
  `console.typesafe.ai/settings/keys`. The console's keys page is
  `console.typesafe.ai/keys`. Same URL corrected in `readme.txt` and README.
  A test pins the value.

## [0.2.3] - 2026-09-19

### Fixed

- `Connector::settings_url()` pointed at `options-general.php?page=connectors`,
  which core answers with "you are not allowed to access this page". Core
  registers the Connectors screen as its own admin file,
  `options-connectors.php` (`wp-admin/menu.php`). Every "manage the key" link
  on the settings screen and in the not-connected notice was dead since 0.1.0.
  Reported in #8 from a live 7.1 site; a test now pins the path.
- The test bootstrap never loaded `includes/functions.php`, so
  `JevConnector\ask()`, the entry point consumers call, had no coverage.

### Added

- Repository documentation, none of which ships in the plugin zip:
  `CONTRIBUTING.md`, `SECURITY.md`, `docs/ARCHITECTURE.md`, `docs/HOOKS.md`
  (every filter and action with its exact arguments), `docs/REST-API.md`
  (every route and error code), `docs/MODULES.md` (the module contract and
  the guardrail's decision matrix), `docs/RELEASING.md` (version bump, tag,
  zip, SVN). README gained badges, a requirements section and an index.
- GitHub issue forms, a pull request template, and weekly grouped Dependabot
  for Composer dev tooling and workflow actions.
- Private vulnerability reporting enabled on the repository; `SECURITY.md`
  points there rather than at an email address.
- `composer check` runs lint and tests together.
- `tests/test-consumer-contract.php` pins the surface other plugins depend
  on, by the same `function_exists()` / `class_exists()` lookups a consumer
  uses: `JevConnector\ask()` and its `Response|WP_Error` return,
  `Response::to_array()` as the untouched body, `WP_Error` data carrying
  `status`, `Connector::get_api_key()` and `Connector::SETTING_NAME`. Signal &
  Noise Tools 16.5.3 is the first consumer. Changing anything in that file is
  a breaking change.

### Changed

- The default branch is `trunk`, the name every WordPress-org repository
  (core, Gutenberg, OpenStation, Plugin Check) carries over from SVN.
  Release branches are not created ahead of need; a numbered branch such as
  `0.2` is cut from its tag only if that line needs a fix after a newer
  minor exists, which is also the WordPress convention.

## [0.2.2] - 2026-09-18

### Added

- `logo_url` on the connector registration, pointing at
  `assets/images/typesafe.png` (TypeSafe's published favicon mark, 192px,
  transparent). The card under **Settings → Connectors** showed core's generic
  plug icon before. `plugins_url()` is used the same way core resolves its own
  provider logos.

### Verified live

First run on a real WordPress 7.1 site, which retires two items from the
"still unverified" list: the card renders under its own type, separate from
the AI providers, and saving a key shows **Connected** rather than being wiped
by AI Client validation.

## [0.2.1] - 2026-09-18

Directory readiness. No behaviour change.

### Fixed

- Caller-supplied question ids are escaped before they are interpolated into
  validation messages (`WordPress.Security.EscapeOutput.ExceptionNotEscaped`,
  flagged by both PHPCS and Plugin Check).
- Removed the `Domain Path` header. It pointed at a `languages/` folder that
  does not exist, which Plugin Check flags, and wordpress.org serves language
  packs without it.
- `Tested up to` raised to 7.1, the current WordPress release. Plugin Check
  rejects a value behind the current major.
- The External services section of `readme.txt` now discloses that the
  User-Agent header carries the plugin version and the site's home URL, which
  the client has always sent.
- Renamed a `$default` parameter in `Module::setting()`; `default` is a
  reserved word and WPCS warns on it.

### Changed

- `.distignore` is now the single source of truth for what ships. The Release
  workflow builds the zip from it, and CI stages the same set into a folder
  named `connector-for-typesafe-jev` before running Plugin Check. Previously
  Plugin Check scanned the raw checkout, derived the slug `jev-connector` from
  the repo folder, and reported 58 false text-domain mismatches plus findings
  in test and tooling files that never ship. CI had been red on every push.
- PHPCS: the WordPress ruleset no longer applies to `tests/`, whose bootstrap
  must redeclare core function names as stubs. PHPCompatibility still does.
  `minimum_wp_version` now matches the plugin header. Array properties use the
  `<element>` syntax PHPCS 4 requires.
- CI: every job has a `timeout-minutes`; the PHPUnit matrix adds 8.5.

## [0.2.0] - 2026-09-18

### Added

- Module system: `Modules` registry plus a `Module` base class. Every module is
  off by default and switched on with a checkbox under
  **Settings → TypeSafe Jev**. Nothing in the client depends on a module
  existing.
- **Comment guardrail** module. Filters `pre_comment_approved` and asks two
  independent questions, a `noul` on spam and a `choice` on routing. Both must
  agree at or above the threshold before a comment is marked spam, and both
  must agree before one skips moderation. Everything else is held. It never
  returns `trash`, and a `WP_Error` from the API returns the comment's existing
  status untouched. Each decision is written to comment meta as
  `_jevc_decision` with probabilities, confidence and model, so thresholds can
  be tuned against real traffic.
- **Term suggestions** module. A post editor panel that fans out one `noul` per
  existing term in a single call and lists what clears the threshold, ordered
  by probability. Terms are applied only by an explicit click, through a
  capability-checked REST route. Nothing hooks `save_post`.
- Routes `POST /jev/v1/suggest-terms` and `POST /jev/v1/apply-terms`, both
  gated on `edit_post` for the specific post.

## [0.1.0] - 2026-09-18

### Added

- Registration with the WordPress 7.0 Connectors API, so core owns the
  credential: `Settings → Connectors` UI, masking in the admin and in REST
  responses, and resolution order of `TYPESAFE_API_KEY` environment variable,
  then constant, then stored option.
- Response cache keyed by a hash of the request payload, with a
  `jevc_cache_ttl` filter and a per-call `cache` argument. Failures are never
  cached.
- `Client` for `POST https://api.typesafe.ai/v1/systemone`, with bearer auth,
  a 20 second default timeout, and bounded exponential backoff on 429, 529 and
  5xx responses (honoring `Retry-After`).
- `Question::noul()`, `Question::choice()`, `Question::score()` builders with
  shape validation before anything leaves the site.
- `Response` reader: `noul()`, `choice()`, `score()`, `confidence()`,
  `probabilities()`, `legend()`, `usage()`, and `is_confident()`.
- Settings screen under **Settings → TypeSafe Jev** for the default model and
  the REST capability, with a connection status notice that names the key's
  source and links to the Connectors screen.
- REST routes `POST /jev/v1/ask` and `GET /jev/v1/status`, capability gated.
- Filters `jevc_default_model`, `jevc_http_timeout`, `jevc_request_payload`,
  `jevc_request_args`, `jevc_retry_delay`, `jevc_rest_capability`; actions
  `jevc_after_response`, `jevc_request_failed`.
- PHPUnit suite covering the request contract, error mapping, and retries.

# Releasing

Releases are cut from `trunk`, which is protected: nothing lands there except
through a pull request with every CI check green. A release is a version-bump
PR followed by a tag.

## Versioning

Semantic versioning, 0.x until the public API (the `ask()` signature, the
`Question` and `Response` methods, the hook names and arguments) is frozen at
1.0.

| Bump | When |
| --- | --- |
| PATCH | Fixes, packaging, CI, docs that ship, performance, refactors |
| MINOR | A new user-visible capability: a module, a hook, a route, a setting |
| MAJOR (post-1.0) | Removed or renamed public API, a settings change without migration, or a behaviour change that needs user action |

Documentation-only changes to files that do not ship (`README.md`, `docs/`,
`CONTRIBUTING.md`) do not bump the version. They go under `## [Unreleased]`
in `CHANGELOG.md` and ride the next release.

## Branches

`trunk` is the only long-lived branch, as in every WordPress-org repository.
Feature work is a short branch off `trunk` merged by pull request. A release
is a tag on `trunk`. A numbered branch (`0.2`) is created only when that line
needs a fix after a newer minor has shipped: cut it from the tag, fix, tag
`v0.2.x` from it, and leave it. Do not create one in advance.

## The version lives in three places

They must move together. The directory serves whatever `Stable tag` points at.

```
tribar-connector-for-typesafe-jev.php    * Version:           X.Y.Z
tribar-connector-for-typesafe-jev.php    const VERSION     = 'X.Y.Z';
readme.txt                        Stable tag: X.Y.Z
```

## Procedure

1. Branch from `trunk`. Bump the three version strings.
2. `CHANGELOG.md`: rename `## [Unreleased]` to `## [X.Y.Z] - YYYY-MM-DD` and
   start a fresh empty `## [Unreleased]` above it.
3. `readme.txt`: add a `= X.Y.Z =` entry under `== Changelog ==`. Shorter and
   user-facing; this is what the directory shows.
4. `readme.txt`: confirm `Tested up to` is the current WordPress release
   (`https://api.wordpress.org/core/version-check/1.7/`). Plugin Check fails
   if it is behind the current major.
5. If anything now sends data it did not before, `== External services ==`
   in `readme.txt` must already say so.
6. `composer check`. Open the PR, wait for green, merge.
7. Tag the merge commit and push the tag:

   ```bash
   git fetch origin trunk
   git tag -a vX.Y.Z origin/trunk -m "X.Y.Z: one line"
   git push origin vX.Y.Z
   ```

8. The Release workflow builds `tribar-connector-for-typesafe-jev.zip` from
   `.distignore` and attaches it to a GitHub release with generated notes.
   Download it and check the file list and the three version strings before
   doing anything else with it.

## What ships

`.distignore` is the single definition. Anything not listed there is in the
zip. The CI Plugin Check job stages the same file set, so a file that
should not ship will be flagged before the release, not after.

## Publishing to the directory

Approved 2026-10-28. SVN: https://plugins.svn.wordpress.org/tribar-connector-for-typesafe-jev
(user `juanml`, case sensitive). Listing:
https://wordpress.org/plugins/tribar-connector-for-typesafe-jev/

**Every release deploys itself.** Pushing the `vX.Y.Z` tag (step 7) runs the
Release workflow, which builds the zip and then commits the same `.distignore`
file set to SVN `trunk/`, copies it to `tags/X.Y.Z`, and syncs
`.wordpress-org/` to SVN `assets/`, via
[10up/action-wordpress-plugin-deploy](https://github.com/10up/action-wordpress-plugin-deploy).
It refuses to run if `Stable tag` does not match the version, and skips a
version already in SVN `tags/`. Needs repo secrets `SVN_USERNAME` (`juanml`)
and `SVN_PASSWORD` (the SVN password from the wordpress.org profile, Account &
Security, not the login password). The repo is public, so it costs no Actions
minutes.

**Deploying without a new tag** (e.g. the first deploy of a version tagged
before the deploy step existed, or after fixing the secrets): run the Release
workflow by hand on `trunk` with `deploy_version` set to the current
`Stable tag`. It deploys `trunk` as that version, so only do it when `trunk`
has no shipping changes since the tag.

Listing images (icon, banner, screenshots) live in `.wordpress-org/`; sizes in
its README. Do not put the TypeSafe mark on them; the directory treats
third-party branding on listing assets as an implied endorsement. The plugin
itself ships no TypeSafe logo either since 0.4.2 (Guideline 1: shipped images
must be GPL-compatible).

**By hand, if Actions is unavailable:**

```bash
svn co https://plugins.svn.wordpress.org/tribar-connector-for-typesafe-jev/ svn-plugin
cd svn-plugin
rm -rf trunk/* && unzip -q ../tribar-connector-for-typesafe-jev.zip && mv tribar-connector-for-typesafe-jev/* trunk/ && rmdir tribar-connector-for-typesafe-jev
svn add --force trunk
svn cp trunk tags/X.Y.Z
svn ci -m "Release X.Y.Z" --username juanml
```

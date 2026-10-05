# Zenodo integration for Nextcloud

Publish your work on [Zenodo.org](https://zenodo.org) directly from the Nextcloud
Files app.

Based on files_zenodo from Lars Naesbye Christensen, DeIC for ownCloud, and on
the original Nextcloud zenodo app by Maxence Lange. This fork modernizes the app
for Nextcloud 33 to 36 and migrates it to the current Zenodo (InvenioRDM) REST
API.

![example screenshot](screenshots/dialogpopup.png)

## What it does

From the file actions menu of any file, users can:

- **New Zenodo deposition**: create a draft record on Zenodo with the file
  attached (metadata, creators, access rights, license, embargo). The draft is
  then reviewed and published by the user on zenodo.org.
- **Add file to a Zenodo deposition**: attach the file to one of their existing
  unpublished depositions. Only the owner of a deposition can add files to it.

Files already sent to the production Zenodo cannot be published again.

## Requirements

- Nextcloud 33, 34, 35 or 36
- A Zenodo account (one for sandbox, one for production)

## Installation

Copy the app files to the **nextcloud/apps/** directory and enable it. Release
tarballs already contain the compiled frontend (`js/`).

When installing from a git checkout, build the frontend first:

```bash
npm install
npm run build
```

### Tokens

In the admin interface (**Additional settings → Zenodo**), store one access
token per environment:

- Sandbox: create an account on sandbox.zenodo.org, then generate a personal
  access token with the **deposit:write** scope
  (Settings → Applications → Personal access tokens).
- Production: same on zenodo.org.

Tokens are stored as server-wide app configuration; all requests to Zenodo are
performed with the token owner's account. Sandbox and production are separate
services, so the tokens are not interchangeable.

## Development

```bash
npm install
npm run build    # production build into js/ (committed)
npm run dev      # rebuild on file change
```

Lint the PHP sources (no local PHP required, e.g. via docker):

```bash
find lib appinfo -name '*.php' -exec php -l {} \;
```

There is no composer.json: the app only uses the `OCP` interfaces provided by
the Nextcloud server itself.

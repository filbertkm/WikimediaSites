WikimediaSites
===============

Manage Wikimedia sites in MediaWiki's SiteStore.

Includes script to populate the SiteStore based on data from a site matrix api.

## Usage

To populate the SiteStore from the site matrix on [Meta Wiki](https://meta.wikimedia.org), via it's api with https urls for all sites:

```
php maintenance/populateSites.php --force-protocol https
```

### Options

* --article-path: Article path for wikis in the site matrix.  (e.g. "/wiki/$1")
* --force-protocol: Force a specific protocol for all URLs (like http/https).
* --load-from: Full URL to the API of the wiki to fetch the site info from. Default is https://meta.wikimedia.org/w/api.php
* --no-expand-group: Do not expand site group codes in site matrix. By default, "wiki" is expanded to "wikipedia".
* --script-path: Script path to use for wikis in the site matrix. (e.g. "/w/$1")
* --site-group: Site group that this wiki is a member of.  Used to populate  local interwiki identifiers in the site identifiers table.  If not set and --wiki is set, the script will try to determine which site group the wiki is part of and populate interwiki ids for sites in that group.
* --strip-protocols: Strip http/https from URLs to make them protocol relative.

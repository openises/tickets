# TicketsCAD v3.44.5 Release Notes

**Release Date:** August 19, 2026
**Priority:** Medium — fixes reverse geocoding on every map click across five screens. No security or schema changes.

---

## Summary

Clicking the map to place an incident (or on the other screens that reverse-geocode
a click) cleared the City and State fields and drew an empty popup bubble, while
lat/lng and the USNG field updated correctly. The empty lat/lng/USNG fields hid
the bug, since they come from the click itself rather than the geocoder's response.

Two independent defects, both in how the bundled `Control.Geocoder` is called and
its Nominatim response is read:

1. **Wrong argument type.** `Control.Geocoder`'s `reverse()` takes a map *scale* as
   its second argument, not a zoom level, converting it internally with
   `Math.round(Math.log(scale / 256) / Math.log(2))`. Five call sites passed the
   literal `20` — read as a scale, that evaluates to **zoom -4**, and Nominatim
   answers a zoom -4 reverse lookup with only `{"country": "United States"}` — no
   road, city, or state, so every field the handler then wrote was empty. The
   correct value, `67108864` (scale for zoom 18), was already present twice
   elsewhere in this codebase — the bundled Leaflet routing machine and the Places
   screens' dynamic scale calculation — confirming this was a bug, not intent.

2. **City overwritten by neighbourhood.** With the zoom fixed, a second defect
   surfaced: the Nominatim branch overwrote a correct `city` unconditionally with
   `neighbourhood` or `suburb` when either was present. For a point in Cleveland,
   Nominatim returns `city: "Cleveland"` *and* `neighbourhood: "Gordon Square"`, so
   a correct municipality was replaced by a district name. City now resolves
   `city` / `town` / `village`, and neighbourhood/suburb populate the existing
   Address About field instead — only when it's empty, so a dispatcher's own note
   is never overwritten.

A third, related defect was found and fixed in the same pass: `getTheAddress()`
(the geolocation path on the New Incident form) read the parsed address directly
off `results[0]`, but Nominatim's result carries it at
`results[0].properties.address` — the same shape `newGetAddress()` already handles
correctly elsewhere in the file. Every field this function read came back
undefined on Nominatim regardless of the zoom fix.

Reported and fixed by **Ron Jones** ([PR #12](https://github.com/openises/tickets/pull/12)),
tested on 4.x legacy running Windows/IIS with Nominatim as the provider.

## What changed

| File | Change |
|---|---|
| `incs/config.setcenter.inc.php` | Reverse-geocode call now passes `67108864` (zoom 18) instead of the literal `20` |
| `js/member.js` | Same scale fix, member address lookup |
| `js/osm_map_functions.js` | Scale fix on both `newGetAddress()` (New Incident form) and `getTheAddress()` (geolocation path); city no longer overwritten by neighbourhood/suburb, which now populate Address About instead; `getTheAddress()` reads the correct per-provider result shape; City/State fields are only written when actually resolved, so a partial miss no longer blanks a value the dispatcher already typed; map attribution corrected from a fixed "© 2011 CloudMade" credit to a proper OpenStreetMap attribution |
| `rm/forms/nomapindex.php` | Same scale fix, resource management form |
| `incs/versions.inc.php` | `TICKETS_CURRENT_VERSION` bumped `v3.44.4` → `v3.44.5` |
| `RELEASE-NOTES-3.44.5.md` | This file |

## Upgrade path

### Traditional installs (non-Docker)

```bash
git pull
# Then visit /install.php in your browser to run the upgrade flow.
```

Because the version constant moved to `v3.44.5`, the next admin login detects the
mismatch and redirects to `install.php`. There are **no schema changes** in this
release, so the upgrade simply records the new version and returns you to the app.

### Docker users

```bash
docker compose pull
docker compose up -d
```

## Verification

```bash
# The five call sites should now pass the zoom-18 scale, not the literal 20:
grep -c "geocoder.reverse(.*67108864" incs/config.setcenter.inc.php js/member.js js/osm_map_functions.js rm/forms/nomapindex.php
# -> 1 each (osm_map_functions.js has two call sites and should show 2)

# Version constant:
grep TICKETS_CURRENT_VERSION incs/versions.inc.php | head -1
# -> define('TICKETS_CURRENT_VERSION', 'v3.44.5');
```

Then, with the geocoding provider set to Nominatim: open **New Incident**, click
anywhere on the map, and confirm street, city, state and the popup bubble all
populate (previously only lat/lng/USNG did).

## What did NOT change

- No security fixes
- No schema changes
- No new features
- No breaking changes
- No PHP / MariaDB / Apache compatibility changes

## Two related issues known but deliberately not addressed here

Both flagged by Ron Jones in PR #12, left out to keep this a focused bug-fix
release:

- **Nominatim is called from the browser with no identifying User-Agent.** OSM's
  usage policy asks for one, and a JSONP `<script>` load cannot set one. An
  install that gets rate-limited shows the same symptom as this release's bug —
  empty City/State and a blank bubble. The real fix needs a small server-side
  proxy so the header can be set (this is what NewUI v4 already does).
- **Script tags carry no cache-busting version string**, and IIS sends no
  Cache-Control for them, so a browser can hold stale JS after an update until a
  hard refresh. Fixing it touches 71 tags across 37 files.

Also noted in passing, not fixed: `js/osm_map_functions.js` still calls
`tile.cloudmade.com` and `routes.cloudmade.com` over plain HTTP for routing
arrows and directions. That service has been gone for years, so those requests
simply fail; the `cloudmade_api` setting feeding them is dead.

## Recommendation

**Recommended for everyone** using the map-click / reverse-geocode workflow on
New Incident or the other affected screens with Nominatim as the geocoding
provider — this is the everyday path for placing an incident by clicking the
map. Harmless and low-risk to apply regardless.

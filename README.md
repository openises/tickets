# Tickets CAD 3.44 — legacy version

> ### TicketsCAD 4 is the current version. This repository is version 3.44.
>
> This repository holds **Tickets CAD 3.44**, the original PHP application
> (framesets, jQuery 1.4, PHP 7.4–8.5). It is still maintained for security
> and bug fixes, and if you are running 3.44 today you can keep running it —
> the rest of this file still applies to you.
>
> **New development happens in TicketsCAD 4**, a rewrite with a
> keyboard-first Bootstrap 5 interface on PHP 8.2. It keeps the same
> MariaDB schema, so an existing 3.44 install can be upgraded in place.
>
> | | |
> |---|---|
> | **TicketsCAD 4 (current)** | https://github.com/openises/TicketsCAD |
> | Latest 4.x release | https://github.com/openises/TicketsCAD/releases/latest |
> | Install guide | [docs/INSTALL.md](https://github.com/openises/TicketsCAD/blob/main/docs/INSTALL.md) |
> | Docker quick start | [docs/DOCKER.md](https://github.com/openises/TicketsCAD/blob/main/docs/DOCKER.md) |
> | **Upgrading from 3.44 to 4** | [docs/UPGRADING-FROM-V3.md](https://github.com/openises/TicketsCAD/blob/main/docs/UPGRADING-FROM-V3.md) |
> | New to this? Start here | [docs/GETTING-STARTED-FOR-BEGINNERS.md](https://github.com/openises/TicketsCAD/blob/main/docs/GETTING-STARTED-FOR-BEGINNERS.md) |
>
> **Getting help (either version):**
> - Discussion and questions: [open-source-cad Google Group](https://groups.google.com/g/open-source-cad)
> - Bug reports for 3.44: https://github.com/openises/tickets/issues
> - Bug reports for 4: https://github.com/openises/TicketsCAD/issues
>
> Latest 3.44 release: https://github.com/openises/tickets/releases/latest ·
> Installation and administration notes for this version:
> [docs/INSTALL-ADMIN-GUIDE.md](docs/INSTALL-ADMIN-GUIDE.md) ·
> [wiki](https://github.com/openises/tickets/wiki)

---

## About Tickets CAD 3.44

Tickets is a web-based dispatch management and tracking application for the
public safety community, with particular attention to the needs of
volunteer-staffed agencies. All dispatch, tracking and search functions are
reached through an ordinary web browser, so distributed teams can use it
without specialised client software.

It is general-purpose rather than specific to police, fire, 9-1-1 or emergency
medicine, though the internal design allows expansion toward more specialised
requirements.

Tickets is written in PHP and JavaScript on a MySQL/MariaDB database, and runs
on a conventional web server such as Apache, Nginx or IIS.

### Background

Tickets began as a major upgrade to PHP-Ticket, a mature ticket-tracking
application. The original author drew on the OpenTicket system used at KTHNOC,
the network operations centre at the Royal Institute of Technology in
Stockholm, Sweden, where SUNET, NORDUNET and national ISPs interconnect.

## Features

- Mapping through [Leaflet](https://leafletjs.com/) with OpenStreetMap raster
  tiles — zoom, pan, and configurable tile sources including a caching proxy
  and fully offline tile servers (see [docs/TILE_SETTINGS.md](docs/TILE_SETTINGS.md))
- Address lookup showing the map location of a street address or town
- Automatic map centering based on the calculated centre of current tickets
- Selective map view by incident severity
- Automated installation through `install.php`
- User management and login using sessions
- Most configuration is reachable through the interface; no database knowledge
  is needed
- Email notification on ticket changes
- Printable tickets
- Search, with results highlighted
- ICS forms (202, 205, 205a, 206, 213, 213RR, 214)

## Requirements

- A PHP-capable web server (Apache, Nginx, or Microsoft IIS)
- PHP 7.4 through 8.5
- MySQL 5.7+ or MariaDB 10.3+ (MySQL 8.0+ or MariaDB 10.11 recommended)
- Clients must accept cookies for login state and support CSS

Maps work out of the box using OpenStreetMap tiles; no map API key is required
for normal use. A Google Maps API key is only needed if you enable the optional
Google-based address lookup, and is set from
**Configuration → Edit Settings → `gmaps_api_key`**.

## Installation

Full instructions are in [INSTALL.txt](INSTALL.txt) and
[docs/INSTALL-ADMIN-GUIDE.md](docs/INSTALL-ADMIN-GUIDE.md). In brief:

1. Unpack the files into your intended Tickets directory.
2. Create an empty MySQL/MariaDB database and note the connection parameters.
3. Point your browser at `install.php` in that directory and complete the form.
4. The install script creates two accounts, `admin/admin` and `guest/guest`.
5. **Log in as `admin` and change the administrator password immediately.**
6. **Move, delete, or restrict permissions on `install.php`** so it cannot be
   run again.
7. Set your default map centre under **Configuration → Set Default Map**, and
   review the other settings there.

A Docker deployment is also available — see [docs/DOCKER-DEPLOY.md](docs/DOCKER-DEPLOY.md).

## Support and bug reports

- Discussion and questions: [open-source-cad Google Group](https://groups.google.com/g/open-source-cad)
- Bug reports: https://github.com/openises/tickets/issues
- Wiki: https://github.com/openises/tickets/wiki

## License

Tickets is free software released under the GNU General Public License. The GPL
guarantees your right to use the software without charge, to redistribute it,
and to modify it to meet your needs. Software derived from Tickets is likewise
bound by the GPL. See [COPYING.txt](COPYING.txt) for the full text.

## Authors

Tickets was written by Arnie Shore as an adaptation of the original by
Daniel Netz, using PHP, Apache and MySQL on Linux.

Development has been led by Eric Osterberg since 5 May 2023.
Contributions are welcome.

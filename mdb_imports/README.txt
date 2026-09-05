Place a trusted .sql dump here before running import_mdb.php?backup=<filename>.
Admin-only (is_administrator()), and the filename is restricted to this
directory (no path traversal, no stream-wrapper tricks) -- see the fix for
the unauthenticated arbitrary-SQL-execution issue found 2026-09-04.

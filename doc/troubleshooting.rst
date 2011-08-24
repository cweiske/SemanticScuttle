===============================
SemanticScuttle Troubleshooting
===============================


Godaddy error: "No input file specified."
=========================================
Opening personalized URLs like ``bookmarks.php/username`` on a godaddy
hosted server leads to the error ::

 No input file specified.

We do not have a real solution yet, but changing the PHP handler from
`CGI` to `mod_php` in the control center makes it work:

- Hosting Control Center

  - Settings

    - File extensions

      - Change ``.php`` from `FastCGI` to `PHP 5.x`

Thanks to CESgeekbook__ for the hint.

__ http://www.cesgeekbook.com/2010/07/php-no-input-file-specified-godaddy.html


Number of bookmarks always 0: "0 bookmark(s)"
=============================================
This issue occurs when debug mode is enabled.
Technically, this is because the database layers ``DEBUG_EXTRA`` gets
enabled through debug mode.

======================
SemanticScuttle Themes
======================
SemanticScuttle may be changed visually by supplying custom "themes" that
modify the visual appearance.


Changing the current theme
==========================
In ``data/config.php``, set your theme like this: ::

    $theme = 'darkmood';


Creating your own theme
=======================

CSS and image files
-------------------
Since both file types need to be accessible via the web server directly,
they are located in the ``www/`` folder: ::

    www/themes/$themename/

The main CSS file that automatically gets included is ::

    www/themes/$themename/scuttle.css


Template files
--------------
The templates of the default file are located in ::

    data/templates/default/

You may put your theme template files into ::

    data/templates/$themename/


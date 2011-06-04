======================
SemanticScuttle Themes
======================
SemanticScuttle may be changed visually by supplying custom "themes" (skins)
that modify the visual appearance.


Changing the current theme
==========================
In ``data/config.php``, set your theme like this: ::

    $theme = 'darkmood';

The available themes are the folders in ``www/themes/``.
By default, SemanticScuttle ships only one usable theme ("default") and one
to demonstrate how to create your own theme ("testdummy").


Creating your own theme
=======================
Have a look at the "testdummy" theme in ``www/themes/testdummy/``.

CSS and image files
-------------------
Since both file types need to be accessible via the web server directly,
they are located in the ``www/`` folder: ::

    www/themes/$themename/

The main CSS file that automatically gets included is ::

    www/themes/$themename/scuttle.css

Several template files in SemanticScuttle include image files. If they do not
exist in your theme, the default ones are used automatically.
Note that this is not true for images that are specified in the CSS files.


Template files
--------------
The templates of the default file are located in ::

    data/templates/default/

You may put your theme template files into ::

    data/templates/$themename/


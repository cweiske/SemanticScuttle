- SemanticScuttle_Filter
  -> class with static filter functions to filter
     ids, usernames, passwords, sorting etc.
- when a user gets deleted from database, he should not be
  logged in anymore (name not shown on top right)
- Make users inactive by default when registered newly
  - have to be activated by admins
- Add RDFa to user profile page
- use recaptcha or alike -> quickform
- tutorial about sidebar
- update php-gettext
- index on bookmarks->modified, since created is not used in selects/sort
  - how to optimize sorts, to prevent mysql filesort? -> index enough?
  - how to optimize DISTINCT bHash


Tracker items:
#1908041 - klick counter
  a counter how many people klicked one link
#1964906 - Page numbers list / alternative pagination
  I would love to have page numbers like for example google has for terms
  with many hits. You can have a look at what exactly I'm talking about here:
  http://www.jenst.se/wp-content/uploads/2008/04/wp-page-numbers-themes.gif .
  With this you can more easily browse through the pages by clicking the
  numbers and not those 'next' and 'previous' buttons.
#1989984 - Branding Improvements
  Create an ability to brand the installed site on several levels:
  Level 1 - add a logo and text to the header
  Level 2 - add a logo and text to the top header and add html (logos, text,
   flickr widgets, youtube widgets, etc.) to the left and right side bars
   through an interface similar to the theme editor on Wordpress. Also
   include easy interface to Google AdWords.
  Level 3 - add the items in Level 2 plus a banner ad manager.
  Requirements:
  - add comments to the CSS to allow for the novice to edit the CSS based on
   known widget or image size. This is an important addition.
#1969682 - private bookmarks
  I think the handling of private bookmarks could be improved. Now it is so
  that you just see a smaller amount of bookmarks as a visitor as you would
  see when you are logged in as a member (and if had added some private
  bookmarks to the site).
  There should be a separate count of this private bookmarks to indicate that
  there are some. Something like '80 public/total bookmark(s) - 5 private
  bookmarks(s)'. It also should be easier to find them. Maybe the above
  mentioned line counting the private bookmarks could be a link to a page
  listing them.
#2035563 - Delete bookmarks based on user votes
  I'm using Semantic Scuttle for ONE topic based bookmarks sharing. Sometimes
  I, as admin is not sure if the bookmark submitted is right for or not.
  I'l like to propose a function, where 5 (numbers can be selected)of top
  users (or sub admins if they can be created) of the site marks the bookmark
  not applicable to the site, then that bookmark is deleted.
#2862548 - Disable email TLD verification
  It should be possible to disable the top level domain verification in the
  email verification for new users. Use cases are
  a) new top level domains (TLD) that are created in the future
  b) company-internal non-standard TLDs
#2830219 - Edit by other people
  We use SemanticScruttle to share bookmarks in the company I work for.
  It'd be helpful if there was a way all people could edit public bookmarks.
#1969705 - adjustable thumbnail height and width in config.inc.php
  I put two vars in the config file to adjust the size of the thumbnails and
  accordingly changed the bookmarks.tpl.php file:
  bookmarks.tpl.php
  $websiteThumbnailsWidth = 90; # width of thumbnails in pixel, max value
  240
  $websiteThumbnailsHeight = 68; # height of thumbnails in pixel, max value
  180
#1933227 - custom maximum number of items in RSS-feeds
  for Mozilla Firefox's Live Bookmarks feature, the current 15 items
  delivered by RSS may not be enough.
  note: by changing getPerPageCount() in functions.inc.php in a similar
  manner, it should be possible to make the max-entries-per-page
  customizable
#1926991 - Admin Approval of New Users
  Create a way for admins to select if they have to approve all new users.
  Then have the following methods for new user approval:
  - by email to selected admin on a per instance basis
  - through an admin panel on a per instance basis
  - through an admin panel on a batch basis
  - through an admin panel by setting up rules for approval (for example:
    user admin must have a specific domain - helpful for big companies)
  This would help reduce the amount of spam for some users.
#1932109 - tag counting: count each URL only once
  I don't know if it's only me, but I have would prefer another way of
  calculating a tag's weight:
  current behaviour:
   existing tags are currently counted by summing up the entries in the system
   that have that tag.
  example:
   one user added youtube.com with the tag "videos".
   one user added video.google.com with the tag "videos".
   ten users added amazon.com with the tag "books".
  the tags are now weighed like this: 2 videos, 10 books.
  although there are more unique URLs in the system with the tag "videos".
  preferred behaviour:
   for tag-weighing, count the unique URLs, instead of the entries.
   in the example above: count 2 for "videos", one for "books".
#2830224 - Shorturl/tinyurl service
  It would be cool if SemanticScruttle could be used as shorturl service with
  configurable short urls.
  So I'd define "freddy" as short url name in the bookmark, and anyone could
  access it e.g. via our-bookmarks.com/s/freddy and get redirected to the
  real url. Useful to get permanent URLs to moving targets.

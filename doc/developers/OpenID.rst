**************
OpenID support
**************

Since SemanticScuttle 0.99.0, users may log in via OpenID.

=============
Login Process
=============

::

 .
   +------------------+
  / OpenID URL entry /
 +------------------+
          |
          v
 +----------------------------+           Cancel          +------------+
 | Request to OpenID provider | ------------------------> | Login fail |
 +----------------------------+                           +------------+
          | Success                                             ^
    _____ v ________________________                            | No
   /                                \     No      +----------------------------+
   | OpenID associated with a user? | ----------> | User registration enabled? |
   \________________________________/             +----------------------------+
          |                                                     | Yes
          | Yes                                                 v
          |                                          +-------------------------+
          |                                         /  Registration form      /
          |                                        /  prefilled with data    /
          |                                       /  from OpenID provider   /
          |                                      /                         /
          |                                     /   Password not required /
          |                                    /    Email not required   /
          |                                   +-------------------------+
          |                                                     |
          v                                                     v
     +-------+                                            +----------------+
     | Login | <----------------------------------------- | Create account |
     +-------+                                            +----------------+
          |
     _____v_______________
    /                     \  Yes         +------------------+
    | Data update active? |------------> | Update user data |
    \_____________________/              |  from OpenID     |
          |                              +------------------+
          v                                       |
    +----------------+                            |
    | Login finished | <--------------------------+
    +----------------+


Automatic registration?
=======================
No. We show the user the login form prefilled with data.

1. It prevents users from creating several accounts when they use multiple
   OpenIDs and forgot which they used in this instance.
2. In case the user name is already taken, we need to ask for a new user name
   anyway.
3. People may not want to use the data from their OpenID provider when
   registering

Discussion on this topic happened in http://drupal.org/node/637850


=====
Links
=====

- http://stackoverflow.com/questions/4564337/guidance-on-flow-for-openid-registration-form
- http://stackoverflow.com/questions/1316983/to-openid-or-not-to-openid-is-it-worth-it
- http://wiki.openid.net/w/page/12995223/Relying%20Party%20Best%20Practices

====
TODO
====
Blog post:

- automatic registration
- openid login/registration process flow chart

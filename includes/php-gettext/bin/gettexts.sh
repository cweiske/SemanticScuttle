#!/bin/sh
xgettext -kT_ngettext:1,2 -kT_ -L PHP -o ../../../locales/messages.po ../../../*.php ../../../services/*.php ../../../templates/*.php

if [ -f "../../../locales/$1/LC_MESSAGES/messages.po" ]
then
msgmerge -o ../../../locales/$1/LC_MESSAGES/messages.po ../../../locales/$1/LC_MESSAGES/messages.po ../../../locales/messages.po

msgfmt --statistics "../../../locales/$1/LC_MESSAGES/messages.po" -o "../../../locales/$1/LC_MESSAGES/messages.mo"
else 
echo "gettexts.sh LANGUAGE_CODE"
echo "example: 'gettexts fr_FR' to get text for French"
fi

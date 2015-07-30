#!/usr/bin/env bash

drush -y rr
drush cache-clear drush
drush -y govdash-sync-modules
drush -y features-revert-all
drush -y updatedb
drush cc all

# modules
#drush -y e some-module

# theme
drush -y en govdash_core bootstrap
drush -y vset theme_default govdash_core
drush -y vset admin_theme bootstrap

# disable automated cron
drush -y vset cron_safe_threshold 0

# update and clean
drush -y updatedb
drush cc all
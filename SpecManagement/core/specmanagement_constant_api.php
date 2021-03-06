<?php
define( 'SPECMANAGEMENT_PLUGIN_URL', config_get_global( 'path' ) . 'plugins/' . plugin_get_current() . '/' );
define( 'SPECMANAGEMENT_PLUGIN_URI', config_get_global( 'plugin_path' ) . plugin_get_current() . DIRECTORY_SEPARATOR );
define( 'SPECMANAGEMENT_CORE_URI', SPECMANAGEMENT_PLUGIN_URI . 'core/' );
define( 'SPECMANAGEMENT_FILES_URI', SPECMANAGEMENT_PLUGIN_URI . 'files/' );
define( 'PLUGINS_SPECMANAGEMENT_THRESHOLD_LEVEL_DEFAULT', ADMINISTRATOR );
define( 'PLUGINS_SPECMANAGEMENT_READ_LEVEL_DEFAULT', REPORTER );
define( 'PLUGINS_SPECMANAGEMENT_WRITE_LEVEL_DEFAULT', DEVELOPER );
define( 'PLUGINS_SPECMANAGEMENT_COLUMN_AMOUNT', 1 );
define( 'PLUGINS_SPECMANAGEMENT_MAX_COLUMNS', 10 );
define( 'PLUGINS_SPECMANAGEMENT_COLUMN_STAT_DEFAULT', 50 );

define( 'PLUGINS_SPECMANAGEMENT_RES_OPEN', 10 );
define( 'PLUGINS_SPECMANAGEMENT_RES_FIXED', 20 );
define( 'PLUGINS_SPECMANAGEMENT_RES_REOPENED', 30 );
define( 'PLUGINS_SPECMANAGEMENT_RES_UNABLETOREPRODUCE', 40 );
define( 'PLUGINS_SPECMANAGEMENT_RES_NOTFIXABLE', 50 );
define( 'PLUGINS_SPECMANAGEMENT_RES_DUPLICATE', 60 );
define( 'PLUGINS_SPECMANAGEMENT_RES_NOCHANGEREQUIRED', 70 );
define( 'PLUGINS_SPECMANAGEMENT_RES_SUSPENDED', 80 );
define( 'PLUGINS_SPECMANAGEMENT_RES_WONTFIX', 90 );

define( 'PLUGINS_SPECMANAGEMENT_STAT_NEW', 10 );
define( 'PLUGINS_SPECMANAGEMENT_STAT_FEEDBACK', 20 );
define( 'PLUGINS_SPECMANAGEMENT_STAT_ACHKNOWLEDGED', 30 );
define( 'PLUGINS_SPECMANAGEMENT_STAT_CONFIRMED', 40 );
define( 'PLUGINS_SPECMANAGEMENT_STAT_ASSIGNED', 50 );
define( 'PLUGINS_SPECMANAGEMENT_STAT_RESOLVED', 80 );
define( 'PLUGINS_SPECMANAGEMENT_STAT_CLOSED', 90 );
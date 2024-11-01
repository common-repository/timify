<?php
/**
 * Initilize admin directory files
 *
 * @subpackage Admin interface
 * @var version - plugin version
 * @since 1.0.0
 * @var settings - plugin options
 */

//plugin settings page
require_once( TIMIFY_INCLUDES . '/admin/settings/class.settings.php' );
new Timify_Option();

//register metabox
require_once( TIMIFY_INCLUDES . '/admin/class.metabox.php' );
new Timify_MetaBox();
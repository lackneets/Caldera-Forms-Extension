<?php
/**
 * Plugin Name: Caldera Forms Extension
 * Plugin URI:  
 * Description: An extension for Caldera Forms, store form-entries into PostType
 * Version:     0.1.0
 * Author:      Lackneets
 * Author URI:  http://lackneets.tw/
 * License:     MIT
 */

ini_set('display_errors', true);

define('CFX_PLUGIN_NAME', 'Caldera Forms Extension');
define('CFX_PLUGIN_COREPATH', plugin_basename(__FILE__));
define('CFX_CALDERA_COREPATH', 'caldera-forms/caldera-core.php');

require 'dependency.php';
require 'submission.php';
require 'processor.php';
require 'helpers/cfx_get_entries.php';
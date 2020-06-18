<?php
/**
 * PHP versions 5 and 7
 * @category   Plugins
 * @package    Mtii_Utilities
 * @subpackage Mtii_Utilities/includes
 * @author     Josbiz - Michael Adewunmi <d.devignersplace@gmail.com>
 * @license    GPL-2.0+ http://www.gnu.org/licenses/gpl-2.0.txt
 * @link       http://josbiz.com.ng
 * @since      1.0.0
 *
 * @wordpress-plugin
 *
 * Plugin Name:       Ministry of Trades Industries and Investment Plugin
 * Plugin URI:        http://josbiz.com/miit/
 * Description:       A Plugin to control the various registration features for Ministry of Trades Industries and Investment
 * Version:           1.0.0
 * Requires at least: 5.2
 * Author:            Josbiz
 * Author URI:        http://josbiz.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       mtii-utilities-josbiz
 * Domain Path:       /languages
 *
 *  * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 */

require 'vendor/autoload.php';

use MtiiUtilities\MtiiLoadersAll;
use MtiiUtilities\DbTableBusinessPremise;
use MtiiUtilities\DbTableCertReplacement;
use MtiiUtilities\DbTableCoopMainForm;
use MtiiUtilities\DbTableLegalSearch;
use MtiiUtilities\DbTableNgoAndCbo;
use MtiiUtilities\DbTablePaidInvoices;
use MtiiUtilities\DbTableSignatoriesInfo;
use MtiiUtilities\PluginActivationTasks;
use MtiiUtilities\PluginDeactivationTasks;

if (!defined('WPINC')) {
    die;
}

/**
 * Current plugin version.
 * Starting at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('MTII_UTILITIES_VERSION', '1.0.0');
define('MTII_UTILITIES_PLUGIN_NAME', 'mtii-utilities');


/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-mtii-utilities-activator.php
 *
 * @return void
 */
function activate_mtii_utilities()
{
    PluginActivationTasks::activate();
}

/**
 * The code that runs during plugin deactivation.
 *
 * @return void
 */
function deactivate_mtii_utilities()
{
    PluginDeactivationTasks::deactivate();
}

register_activation_hook(__FILE__, 'activate_mtii_utilities');
register_deactivation_hook(__FILE__, 'deactivate_mtii_utilities');


global $mtii_db_invoice;
global $mtii_db_coop_main_form;
global $mtii_signatories_template_db;
global $mtii_biz_prem_db_main;
global $mtii_ngo_cbo_db_table;
global $mtii_cert_replacement_table;
global $mtii_legal_search_table;

$mtii_db_invoice = new DbTablePaidInvoices;
$mtii_db_coop_main_form = new DbTableCoopMainForm;
$mtii_signatories_template_db = new DbTableSignatoriesInfo;
$mtii_biz_prem_db_main = new DbTableBusinessPremise;
$mtii_ngo_cbo_db_table = new DbTableNgoAndCbo;
$mtii_cert_replacement_table =  new DbTableCertReplacement;
$mtii_legal_search_table = new DbTableLegalSearch;

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since  1.0.0
 * @return void
 */
function run_mtii_utilities()
{
    $plugin = new MtiiLoadersAll;
    $plugin->run();
}
run_mtii_utilities();


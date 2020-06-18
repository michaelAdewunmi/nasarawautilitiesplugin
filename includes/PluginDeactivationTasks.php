<?php
/**
 * Fired during plugin deactivation.
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @category   Plugins
 * @package    Mtii_Utilities
 * @subpackage Mtii_Utilities/includes
 * @author     Josbiz - Michael Adewunmi <d.devignersplace@gmail.com>
 * @license    GPL-2.0+ http://www.gnu.org/licenses/gpl-2.0.txt
 * @link       http://josbiz.com.ng
 * @since      1.0.0
 */
namespace MtiiUtilities;


/**
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @category   Plugins
 * @package    Mtii_Utilities
 * @subpackage Mtii_Utilities/includes
 * @author     Josbiz - Michael Adewunmi <d.devignersplace@gmail.com>
 * @license    GPL-2.0+ http://www.gnu.org/licenses/gpl-2.0.txt
 * @link       http://josbiz.com.ng
 * @since      1.0.0
 */
class PluginDeactivationTasks
{
    /**
     * Run these codes during plugin deactivation
     *
     * This might be used for deleting options tables and
     * doing other plugin specific functionalities towards
     * cleaning the database if the plugin is not to be used
     * anymore
     *
     * @since  1.0.0
     * @return void
     */
    public static function deactivate()
    {
        $self::clear_scheduled_cron_jobs();
    }

    private function clear_scheduled_cron_jobs()
    {
        wp_clear_scheduled_hook('josbiz_mtii_daily_tasks');
    }
}
?>
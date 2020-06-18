<?php
/**
 * The file for adding of custom registration field in the admin area when
 * registrating a new user from the admin page
 *
 * @category   Plugins
 * @package    Mtii_Utilities
 * @subpackage Mtii_Utilities/admin
 * @author     Josbiz - Michael Adewunmi <d.devignersplace@gmail.com>
 * @license    GPL-2.0+ http://www.gnu.org/licenses/gpl-2.0.txt
 * @link       http://josbiz.com.ng
 * @since      1.0.0
 */

namespace MtiiUtilities;


/**
 * The class for adding of custom registration field in the admin area when
 * registrating a new user from the admin page
 *
 * @category   Plugins
 * @package    Mtii_Utilities
 * @subpackage Mtii_Utilities/admin
 * @author     Josbiz - Michael Adewunmi <d.devignersplace@gmail.com>
 * @license    GPL-2.0+ http://www.gnu.org/licenses/gpl-2.0.txt
 * @link       http://josbiz.com.ng
 * @since      1.0.0
 */
class UserFunctionsInAdminArea
{
    /**
     * The ID of this plugin.
     *
     * @since  1.0.0
     * @access private
     * @var    string    $plugin_name    The ID of this plugin.
     */
    private $_plugin_name;

    /**
     * The version of this plugin.
     *
     * @since  1.0.0
     * @access private
     * @var    string  $version  The current version of this plugin.
     */
    private $_version;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of the plugin.
     * @param string $version     The version of this plugin.
     *
     * @since 1.0.0
     */
    public function __construct( $plugin_name, $version )
    {
        $this->_plugin_name = $plugin_name;
        $this->_version = $version;
    }

    /**
     * Adds extra fields into the registration page on the Admin Page
     *
     * @param string $operation The type of operation to be performed (is it adding new user or deleting or updating)
     *
     * @since 1.0.0
     * @return void
     */
    public function mtii_utilities_admin_reg_form($operation) {
        if ('add-new-user' !== $operation) {
            return;
        }

        $gender = (!empty($_POST['gender'])) ? trim($_POST['gender']) : '';
        $phone_number = (!empty($_POST['phone_number'])) ? trim(intval($_POST['phone_number'])) : '';
        $state_city = (!empty($_POST['state_city'])) ? trim($_POST['state_city']) : '';
    ?>
        <h3><?php esc_html_e('Personal Information', 'mtii-utilities-josbiz'); ?></h3>
        <table class="form-table">
            <tr>
                <th>
                    <label for="gender"><?php _e( 'Gender', 'mtii-utilities-josbiz') ?></label>
                    <span class="description"><?php esc_html_e('(required)', 'mtii-utilities-josbiz'); ?></span>
                </th>
                <td>
                    <select name="gender" id="gender" class="input">
                        <option value="<?php _e('', 'mtii-utilities-josbiz'); ?>">Pick a Gender</option>
                        <option value="<?php _e('Male', 'mtii-utilities-josbiz'); ?>">Male</option>
                        <option value="<?php _e('Female', 'mtii-utilities-josbiz'); ?>">Female</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="phone_number"><?php _e( 'Phone Number', 'mtii-utilities-josbiz') ?></label>
                    <span class="description"><?php esc_html_e('(required)', 'mtii-utilities-josbiz'); ?></span>
                </th>
                <td>
                    <input
                        type="number" name="phone_number" id="phone_number" class="input"
                        value="<?php echo esc_attr(wp_unslash($phone_number)); ?>" size="25" />
                </td>
            </tr>
            <tr>
                <th>
                    <label for="state_city"><?php _e( 'State/City', 'mtii-utilities-josbiz') ?></label>
                    <span class="description"><?php esc_html_e('(required)', 'mtii-utilities-josbiz'); ?></span>
                </th>
                <td>
                    <input
                        type="text" name="state_city" id="state_city" class="input"
                        value="<?php echo esc_attr(wp_unslash($state_city)); ?>" size="25" />
                </td>
            </tr>
        </table>
        <?php
    }


    /**
     * Validates all extra fields. we make sure all fuelds are required.
     *
     * @param string $errors An Array of the default errors sent by wordpress
     * @param string $update holds information if its an update or new user creation
     * @param string $user   The Wordpress User Object

     * @since  1.0.0
     * @return [array] $errors An array of all errors from both default and custom validation
     */
    function mtii_utilities_profile_update_errors($errors, $update, $user) {
        if ($update) {
            return;
        }
        if (empty($_POST['gender']) || ! empty($_POST['gender']) && trim($_POST['gender']) == '') {
            $errors->add(
                'gender_error',
                __(
                    '<strong>ERROR</strong>: Please select your Gender', 'mtii-utilities-josbiz'
                )
            );
        }
        if (empty($_POST['phone_number']) || ! empty($_POST['phone_number']) && trim(intval($_POST['phone_number'])) == '') {
            $errors->add(
                'phone_error',
                __(
                    '<strong>ERROR</strong>: Please input a valid phone number', 'mtii-utilities-josbiz'
                )
            );
        }
        if (empty($_POST['state_city']) || ! empty($_POST['state_city']) && trim($_POST['state_city']) == '') {
            $errors->add(
                'city_error',
                __('<strong>ERROR</strong>: Please input a City', 'mtii-utilities-josbiz'
                )
            );
        }
    }

    /**
     * Shows the extra fields in the user profile region in the admin area
     *
     * @param string $user The Wordpress User Object
     *
     * @since  1.0.0
     * @return void
     */
    public function mtii_utilities_show_extra_profile_fields($user) {
        $gender = get_the_author_meta('gender', $user->ID);
        $gender_label = $gender=='' ? 'Pick a Gender' : $gender;
        $phone_number = get_the_author_meta('phone_number', $user->ID);
        $state_city = get_the_author_meta('state_city', $user->ID);
    ?>
        <h3><?php esc_html_e('Personal Information (for Mtii Utilities User)', 'mtii-utilities-josbiz'); ?></h3>

        <table class="form-table">
            <tr>
                <th>
                    <label for="gender"><?php _e( 'Gender', 'mtii-utilities-josbiz') ?></label>
                    <span class="description"><?php esc_html_e('(required)', 'mtii-utilities-josbiz'); ?></span>
                </th>
                <td>
                    <select name="gender" id="gender" class="input">
                        <option disabled value="<?php _e($gender, 'mtii-utilities-josbiz'); ?>"><?php echo $gender_label; ?></option>
                        <option value="<?php _e('Male', 'mtii-utilities-josbiz'); ?>">Male</option>
                        <option value="<?php _e('Female', 'mtii-utilities-josbiz'); ?>">Female</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="phone_number"><?php _e( 'Phone Number', 'mtii-utilities-josbiz') ?></label>
                    <span class="description"><?php esc_html_e('(required)', 'mtii-utilities-josbiz'); ?></span>
                </th>
                <td>
                    <input
                        type="number" name="phone_number" id="phone_number" class="input"
                        value="<?php echo esc_attr(wp_unslash($phone_number)); ?>" size="25" />
                </td>
            </tr>
            <tr>
                <th>
                    <label for="state_city"><?php _e( 'State/City', 'mtii-utilities-josbiz') ?></label>
                    <span class="description"><?php esc_html_e('(required)', 'mtii-utilities-josbiz'); ?></span>
                </th>
                <td>
                    <input
                        type="text" name="state_city" id="state_city" class="input"
                        value="<?php echo esc_attr(wp_unslash($state_city)); ?>" size="25" />
                </td>
            </tr>
        </table>
    <?php
    }

}

?>
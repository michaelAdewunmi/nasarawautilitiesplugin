<?php
/**
 * The file handling the addition of custom registration field in the
 * registration page
 *
 * @category   Plugins
 * @package    Mtii_Utilities
 * @subpackage Mtii_Utilities/public
 * @author     Josbiz - Michael Adewunmi <d.devignersplace@gmail.com>
 * @license    GPL-2.0+ http://www.gnu.org/licenses/gpl-2.0.txt
 * @link       http://josbiz.com.ng
 * @since      1.0.0
 */


namespace MtiiUtilities;

/**
 * The class handles the addition of custom registration field in the
 * registration page
 *
 * @category   Plugins
 * @package    Mtii_Utilities
 * @subpackage Mtii_Utilities/public
 * @author     Josbiz - Michael Adewunmi <d.devignersplace@gmail.com>
 * @license    GPL-2.0+ http://www.gnu.org/licenses/gpl-2.0.txt
 * @link       http://josbiz.com.ng
 * @since      1.0.0
 */
class UserRegistrationMain
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
     * Adds extra fields into the registration page on the frontend
     *
     * @since 1.0.0
     * @return void
     */
    public function mtii_utilities_reg_form()
    {
        ?>
        <?php
        $first_name = (!empty($_POST['first_name'])) ? trim($_POST['first_name']) : '';
        $last_name = (!empty($_POST['last_name'])) ? trim($_POST['last_name']) : '';
        $gender = (!empty($_POST['gender'])) ? trim($_POST['gender']) : '';
        $phone_number = (!empty($_POST['phone_number'])) ? trim(intval($_POST['phone_number'])) : '';
        $state_city = (!empty($_POST['state_city'])) ? trim($_POST['state_city']) : '';
        ?>
        <div class="flex-it">
        <p>
            <label for="first_name"><?php _e( 'First Name', 'mtii-utilities-josbiz') ?></label>
            <input type="text" name="first_name" id="first_name"
                class="input"
                value="<?php echo esc_attr(wp_unslash($first_name)); ?>"
                size="25"
            />
        </p>

        <p>
            <label for="last_name"><?php _e( 'Last Name', 'mtii-utilities-josbiz') ?></label>
            <input type="text" name="last_name" id="last_name"
                class="input"
                value="<?php echo esc_attr(wp_unslash($last_name)); ?>"
                size="25"
            />
        </p>
        </div>
        <div class="flex-it">
        <p>
            <label for="gender"><?php _e( 'Gender', 'mtii-utilities-josbiz') ?></label>
            <select name="gender" id="gender" class="input">
                <option value="<?php _e('', 'mtii-utilities-josbiz'); ?>">Pick a Gender</option>
                <option value="<?php _e('Male', 'mtii-utilities-josbiz'); ?>">Male</option>
                <option value="<?php _e('Female', 'mtii-utilities-josbiz'); ?>">Female</option>
            </select>
        </p>
        <p>
            <label for="phone_number"><?php _e( 'Phone Number (e.g 08023456789)', 'mtii-utilities-josbiz') ?></label>
            <input type="number" name="phone_number" id="phone_number"
                class="input"
                value="<?php echo esc_attr(wp_unslash($phone_number)); ?>"
                size="25"
            />
        </p>
        </div>
        <p>
            <label for="state_city"><?php _e( 'State/City', 'mtii-utilities-josbiz') ?></label>
            <input type="text" name="state_city" id="state_city"
                class="input"
                value="<?php echo esc_attr(wp_unslash($state_city)); ?>"
                size="25"
            />
        </p>
        <?php
    }

    /**
     * Validates all extra fields. we make sure all fuelds are required.
     *
     * @param string $errors               An Array of the default errors sent by wordpress
     * @param string $sanitized_user_login Sanitized username from the registration fielfd
     * @param string $user_email           Userr Email from the registration field

     * @since  1.0.0
     * @return [array] $errors An array of all errors from both default and custom validation
     */
    public function mtii_utilities_reg_errors( $errors, $sanitized_user_login, $user_email )
    {
        if (empty($_POST['first_name']) || ! empty($_POST['first_name']) && trim($_POST['first_name']) == '') {
            $errors->add(
                'first_name_error', __(
                    '<strong>ERROR</strong>: You must include a First name.', 'mtii-utilities-josbiz'
                )
            );
        }
        if (empty($_POST['last_name']) || ! empty($_POST['last_name']) && trim($_POST['first_name']) == '') {
            $errors->add(
                'last_name_error',
                __(
                    '<strong>ERROR</strong>: You must include a Last name.', 'mtii-utilities-josbiz'
                )
            );
        }
        if (empty($_POST['gender']) || ! empty($_POST['gender']) && trim($_POST['gender']) == '') {
            $errors->add(
                'gender_error',
                __('<strong>ERROR</strong>: Please select your Gender', 'mtii-utilities-josbiz'
                )
            );
        }

        if (empty($_POST['phone_number']) || ! empty($_POST['phone_number']) && trim(intval($_POST['phone_number'])) == '' ) {
            $errors->add(
                'phone_error',
                __('<strong>ERROR</strong>: Please input a valid phone number', 'mtii-utilities-josbiz'
                )
            );
        }
        if (empty($_POST['state_city']) || ! empty($_POST['state_city']) && trim($_POST['state_city']) == '' ) {
            $errors->add(
                'city_error',
                __('<strong>ERROR</strong>: Please input a City', 'mtii-utilities-josbiz'
                )
            );
        }
        return $errors;
    }

    /**
     * Register Users extra fields as meta after wordpress has added user to the DB
     *
     * @param string $user_id The ID of the new user added to the DB by wordpress
     *
     * @since  1.0.0
     * @return void
     */
    public function mtii_utilities_user_register_meta( $user_id )
    {
        $f_name = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';
        $l_name = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';
        $gender = isset($_POST['gender']) ? trim($_POST['gender']) : '';
        $phone = isset($_POST['phone_number']) ? trim($_POST['phone_number']) : '';
        $state = isset($_POST['state_city']) ? trim($_POST['state_city']) : '';

        update_user_meta($user_id, 'first_name', $f_name);
        update_user_meta($user_id, 'last_name', $l_name);
        update_user_meta($user_id, 'gender', $gender);
        update_user_meta($user_id, 'phone_number', $phone);
        update_user_meta($user_id, 'state_city', $state);
    }

}
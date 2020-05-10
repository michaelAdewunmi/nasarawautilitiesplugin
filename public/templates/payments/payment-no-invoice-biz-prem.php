<?php require_once WP_CONTENT_DIR . '/plugins/mtii-utilities/public/class-mtii-parameters-setter-and-getters.php'; ?>
<p class="" id="catg-payment-category">
    <label for="payment-type-category">Fresh Registration or Renewal</label>
    <select name="payment-type-category" id="payment-type-category" class="input">
        <option value="">Select registration type</option>
        <option value="Fresh Registration">Fresh Registration</option>
        <option value="Registration Renewal">Registration Renewal</option>
    </select>
</p>

<p class="" id="catg-payment-type">
    <label for="payment-type">Payment type</label>
    <select name="payment-type" id="payment-type" class="input">
        <option value="">Select Payment Type</option>
        <?php
            $biz_prem = new Mtii_Parameters_Setter_And_Getter;
            $biz_prem_new_reg = $biz_prem->get_all_biz_premises_amount('mtii_new_registration');
            $options_html_output =  join(
                "",
                array_map(
                    function ($key, $value) {
                        $biz_prem = new Mtii_Parameters_Setter_And_Getter;
                        $biz_prem_new_reg = $biz_prem->get_all_biz_premises_amount('mtii_new_registration');
                        $biz_prem_renewal = $biz_prem->get_all_biz_premises_amount('mtii_renewal');

                        $option_html = '';
                        if ($value=='is_group_description') {
                            $option_html .= '<option style="background-color: #cfcfcf; color: #fff" value="" disabled>'.$key.'</option>';
                        } else {
                            $option_html .= '<option value="'.$key.'">'.$key.' Fresh Registration (NGN '.number_format($value).')</option>';
                            $option_html .= '<option value="'.$key.'">'.$key.
                                ' Registration Renewal (NGN '.number_format($biz_prem_renewal[$key]).')</option>';
                        }
                        return $option_html;
                    },
                    array_keys($biz_prem_new_reg),
                    $biz_prem_new_reg
                )
            );
            echo $options_html_output;
        ?>
    </select>
</p>
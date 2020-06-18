<?php

?>
<section id="ct-theme-customizer">
    <div class="section-wrapper">
        <h1 class="admin-section-title">Nasarawa Admin Settings Page</h1>
        <form action="options.php" method="post">
            <?php
                settings_fields("mtii-general-settings");
                do_settings_sections("mtii-utilities");
                submit_button();
            ?>
        </form>
    </div>
</section>
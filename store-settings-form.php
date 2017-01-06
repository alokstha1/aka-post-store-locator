<?php

$stores_options = get_option( 'aka_store_options' );
$option_postTypes = $stores_options['post_type'];
$aka_store_setting = $stores_options;
// echo "<pre>";
// print_r($stores_options);
// echo "</pre>";
?>

<div class="wrap">
    <h1>Store Locator Settings</h1>

    <form action="options.php" method="post">

        <?php submit_button( 'Save Changes', 'primary', 'submit_store', false ); ?>
<?php settings_fields( 'aka_settings' ); ?>
    </form>
</div>
<div class="clear"></div>
<?php
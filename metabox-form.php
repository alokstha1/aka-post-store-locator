<?php

global $aka_store_setting;

$count = get_post_meta( get_the_ID(), 'aka_fields_count', true );

$aka_locators = get_post_meta( get_the_ID(), 'aka_saved_locators', true );
$fields_count = ( isset( $aka_locators ) && !empty( $aka_locators ) ) ? count( $aka_locators ) : 0;

// pre_debug( $aka_store_setting );

?>

<div id="aka-store-stuff">
    <!-- <table id="aka-list-table">
        <thead>
            <tr>
                <th class="left">Name</th>
                <th>Location</th>
                <?php
                if ( $aka_store_setting['show_url_field'] ) {
                    ?>
                    <th><label for="aka-url">Url</label></th>
                    <?php
                }
                if ( $aka_store_setting['show_phone_field'] ) {
                    ?>
                    <th><label for="aka-phone">Phone</label></th>
                    <?php
                }
                if ( $aka_store_setting['show_description_field'] ) {
                    ?>
                    <th><label for="aka-description">Description</label></th>
                    <?php
                }
                ?>
                <th class="aka-del-edit"></th>
            </tr>
        </thead>
        <tbody id="aka-the-list" data-wp-lists="list:meta">

        </tbody>
    </table> -->
    <p>
        <strong>Add New Store location:</strong>
    </p>
    <table id="aka-newmeta">
        <thead>
            <tr>
                <th class="left"><label for="metakeyselect">Name</label></th>
                <th><label for="metavalue">Location</label></th>
                <?php
                if ( $aka_store_setting['show_url_field'] ) {
                    ?>
                    <th><label for="aka-url">Url</label></th>
                    <?php
                }
                if ( $aka_store_setting['show_phone_field'] ) {
                    ?>
                    <th><label for="aka-phone">Phone</label></th>
                    <?php
                }
                if ( $aka_store_setting['show_description_field'] ) {
                    ?>
                    <th><label for="aka-description">Description</label></th>
                    <?php
                }
                ?>
                <th class="aka-del-edit"></th>
            </tr>
        </thead>

        <tbody class="list-meta-body">
            <?php
            if ( isset( $aka_locators ) && !empty( $aka_locators ) ) {
                foreach ($aka_locators as $aka_key => $aka_value) {

                    ?>
                    <tr>
                        <td>
                            <span>
                                <?php echo $aka_value['aka_name']; ?>
                            </span>
                            <div class="aka-input-wrap"><input class="hidden" type="text" name="aka_store_meta[<?php echo $aka_key; ?>][aka_name]" value="<?php echo $aka_value['aka_name']; ?>"></div>
                        </td>
                        <td>
                            <span>
                                <?php echo $aka_value['aka_location']; ?>
                            </span>
                            <div class="aka-input-wrap"><input class="hidden" type="text" name="aka_store_meta[<?php echo $aka_key; ?>][aka_location]" value="<?php echo $aka_value['aka_location']; ?>"><input type="hidden" name="aka_store_meta[<?php echo $aka_key; ?>][aka_location_latn]" value="<?php echo $aka_value['aka_location_latn']; ?>"></div>
                        </td>
                        <?php
                        if ( $aka_store_setting['show_url_field'] ) {
                        // aka_url
                        // aka_phone
                            ?>
                            <td>
                                <span>
                                    <?php echo $aka_value['aka_url']; ?>

                                </span>
                                <div class="aka-input-wrap"><input class="hidden" type="text" name="aka_store_meta[<?php echo $aka_key; ?>][aka_url]" value="<?php echo $aka_value['aka_url']; ?>">
                                </div>
                            </td>
                            <?php
                        }
                        if ( $aka_store_setting['show_phone_field'] ) {
                            ?>
                            <td>
                                <span>
                                    <?php echo $aka_value['aka_phone']; ?>

                                </span>
                                <div class="aka-input-wrap"><input class="hidden" type="text" name="aka_store_meta[<?php echo $aka_key; ?>][aka_phone]" value="<?php echo $aka_value['aka_phone']; ?>">
                                </div>
                            </td>
                            <?php
                        }
                        if ( $aka_store_setting['show_description_field'] ) {
                            ?>
                            <td>
                                <span>
                                    <?php echo $aka_value['aka_description']; ?>

                                </span>
                                <div class="aka-input-wrap">
                                    <textarea class="hidden" name="aka_store_meta[<?php echo $aka_key; ?>][aka_description]"><?php echo $aka_value['aka_description']; ?></textarea>
                                </div>
                            </td>
                            <?php
                        }
                        ?>

                        <td class="aka-del-edit">
                            <a href="#" data-list="<?php echo $aka_key; ?>" class="aka-button-delete"></a>
                        </td>

                    </tr>
                    <?php
                }
            }
            ?>
            <tr>
                <td class="left">
                    <input type="text" id="aka-name" placeholder="Name" class="aka-fields" name="aka_name">
                </td>
                <td>
                    <input type="text" name="aka_location" class="aka-fields" id="aka-location">
                </td>
                <?php
                if ( $aka_store_setting['show_url_field'] ) {
                    ?>
                    <td>
                        <input type="text" name="aka_url" placeholder="http://" class="aka-fields" id="aka_url">
                    </td>
                    <?php
                }

                if ( $aka_store_setting['show_phone_field'] ) {
                    ?>
                    <td>
                        <input type="text" name="aka_phone" placeholder="Phone No." class="aka-fields" id="aka_phone">
                    </td>
                    <?php
                }

                if ( $aka_store_setting['show_description_field'] ) {
                    ?>
                    <td>
                        <textarea name="aka_description" class="aka-fields" id="aka_description" rows="5" cols="4"></textarea>
                    </td>
                    <?php
                }

                ?>
                <td colspan="2">
                    <div class="submit">
                        <input type="hidden" name="aka_fields_count" id="aka_fields_count" value="<?php echo $fields_count; ?>">
                        <input name="aka_submitmeta" id="aka-newmeta-submit" class="button" value="Submit" type="button">
                        <!-- <input name="aka_addmeta" id="aka-newmeta-field" class="button" value="Add Stores" type="button"> -->
                    </div>
                </td>
            </tr>
            <!-- <tr>

        </tr> -->
        <!-- </tr> -->
    </tbody>
</table>
</div>
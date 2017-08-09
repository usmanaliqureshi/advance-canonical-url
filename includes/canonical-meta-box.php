<?php
/**
 * Intruders aren't allowed.
 */
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('advance_canonical_meta_box')) {

    class advance_canonical_meta_box
    {

        /**
         * Hook into the appropriate actions when the class is constructed.
         */
        public function __construct()
        {
            add_action('load-post.php', array($this, 'acu_meta_box_init'));
            add_action('load-post-new.php', array($this, 'acu_meta_box_init'));
        }

        /**
         * Meta Box Initialization
         */
        public function acu_meta_box_init()
        {
            add_action('add_meta_boxes', array($this, 'acu_add_meta_box'));
            add_action('save_post', array($this, 'acu_save_meta_box'));
        }

        /**
         * Adds the meta box container.
         * @param $post_type
         */
        public function acu_add_meta_box($post_type)
        {
            /**
             * Limit meta box to certain post types.
             */
            $post_types = array('post', 'page');

            if (in_array($post_type, $post_types)) {
                add_meta_box(
                    'acu_canonical_meta_box',
                    __('Advance Canonical URL Setting', 'acu'),
                    array($this, 'acu_render_meta_box'),
                    $post_type,
                    'advanced',
                    'high'
                );
            }
        }

        /**
         * Save the meta when the post is saved.
         * @param int $post_id The ID of the post being saved.
         * @return mixed
         */
        public function acu_save_meta_box($post_id)
        {

            /*
            * We need to verify this came from the our screen and with proper authorization,
            * because save_post can be triggered at other times.
            */

            /**
             * Check if our nonce is set.
             */
            if (!isset($_POST['acu_canonical_meta_box_nonce'])) {
                return $post_id;
            }

            $nonce = $_POST['acu_canonical_meta_box_nonce'];

            /**
             * Verify that the nonce is valid.
             */
            if (!wp_verify_nonce($nonce, 'acu_inner_custom_box')) {
                return $post_id;
            }

            /*
            * If this is an autosave, our form has not been submitted,
            * so we don't want to do anything.
            */
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return $post_id;
            }

            /**
             * Check the user's permissions.
             */
            if ('page' == $_POST['post_type']) {
                if (!current_user_can('edit_page', $post_id)) {
                    return $post_id;
                }
            } else {
                if (!current_user_can('edit_post', $post_id)) {
                    return $post_id;
                }
            }

            /* OK, it's safe for us to save the data now. */
            /**
             * Sanitize the user input.
             */
            $mydata = sanitize_text_field($_POST['acu_new_field']);

            /**
             * Update the meta field.
             */
            update_post_meta($post_id, '_acu_can_url_value', $mydata);
        }


        /**
         * Render Meta Box content.
         * @param WP_Post $post The post object.
         */
        public function acu_render_meta_box($post)
        {

            /**
             * Add an nonce field so we can check for it later.
             */
            wp_nonce_field('acu_inner_custom_box', 'acu_canonical_meta_box_nonce');

            /**
             * Use get_post_meta to retrieve an existing value from the database.
             */
            $value = get_post_meta($post->ID, '_acu_can_url_value', true);

            $default_can_url = get_permalink();

            /**
             * Display the form, using the current value.
             */
            ?>
            <div class="acu_default_can_url" style="height: auto; overflow: auto; margin: 10px 0;">
                <label for="default_can_url"
                       style="position: relative;top: 11px;margin-right: 7px;float: left;font-weight: 500;">
                    <?php _e('Default Canonical URL: ', 'acu'); ?>
                </label>

                <p id="default_can_url" style="width: 178px;font-size: 14px;float: left;position: relative;top: -8px;padding: 3px 6px;
    border: 1px solid #ddd;
    -webkit-box-shadow: inset 0 1px 2px rgba( 0, 0, 0, 0.07 );
    box-shadow: inset 0 1px 2px rgba( 0, 0, 0, 0.07 );
    background-color: #fff;
    color: #32373c;
    outline: none;
    -webkit-transition: 0.05s border-color ease-in-out;
    transition: 0.05s border-color ease-in-out;
"><?php echo esc_attr($default_can_url); ?></p>
            </div>
            <div class="acu_meta_box_container" style="height: auto; overflow: auto;">
                <label for="acu_new_field"
                       style="position: relative;top: 5px;margin-right: 54px;float: left;font-weight: 500;">
                    <?php _e('Canonical URL: ', 'acu'); ?>
                </label>
                <input type="text" id="acu_new_field" name="acu_new_field"
                       value="<?php echo esc_attr($value); ?>"
                       size="25" style="float: left;margin-right: 10px;"/>

                <p style="float: left;margin: 4px 0 0 0;">Add a custom canonical url.</p>
            </div>
            <?php
        }
    }

}

$ACU_Meta_Box = new advance_canonical_meta_box();
<?php
/**
 * Plugin Name: WordPress Emoji Remover & Sanitizer
 * Plugin URI:  https://wordpress.org/plugins/wp-emoji-sanitizer/
 * Description: WordPress Emoji Remover & Sanitizer helps you disable and remove the in-built WordPress Emoji feature by clearing the JavaScript and CSS from the wp-head.
 * Version:     2.3
 * Author:      PrajnaBytes
 * Author URI:  https://www.prajnabytes.com/
 * License:     GPL2
 * Domain Path: /languages
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wpemojisanitizer
 * Tested up to: 6.6
 */

class WP_Emoji_Sanitizer_Plugin {

    public function __construct() {
        add_action('init', array($this, 'disable_wpemojisanitizer'));

        add_filter('tiny_mce_plugins', array($this, 'wpemojisanitizer_disable_emojis_support_in_tinymce'));

        add_filter('wp_resource_hints', array($this, 'wpemojisanitizer_disable_emojis_remove_dns_prefetch'), 10, 2);

        add_action('admin_menu', array($this, 'wpemojisanitizer_add_settings_page'));

        add_action('admin_init', array($this, 'wpemojisanitizer_register_settings'));

        register_activation_hook(__FILE__, array($this, 'wpemojisanitizer_activate'));
        register_uninstall_hook(__FILE__, array(__CLASS__, 'wpemojisanitizer_uninstall'));
    }

    public function disable_wpemojisanitizer() {
        if (get_option('wpemojisanitizer_options')['disable_detection_script']) {
            remove_action('wp_head', 'print_emoji_detection_script', 7);
            remove_action('admin_print_scripts', 'print_emoji_detection_script');
            remove_action('wp_print_styles', 'print_emoji_styles');
            remove_action('admin_print_styles', 'print_emoji_styles');
            remove_filter('the_content_feed', 'wp_staticize_emoji');
            remove_filter('comment_text_rss', 'wp_staticize_emoji');
            remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
        }
    }

    public function wpemojisanitizer_disable_emojis_support_in_tinymce($plugins) {
        if (is_array($plugins)) {
            return array_diff($plugins, array('wpemoji'));
        } else {
            return array();
        }
    }

    public function wpemojisanitizer_disable_emojis_remove_dns_prefetch($urls, $relation_type) {
        if (get_option('wpemojisanitizer_options')['disable_dns_prefetch']) {
            if ('dns-prefetch' === $relation_type) {
                $emoji_svg_url = apply_filters('emoji_svg_url', 'https://s.w.org/images/core/emoji/2/svg/');
                $urls = array_diff($urls, array($emoji_svg_url));
            }
        }
        return $urls;
    }

    public function wpemojisanitizer_add_settings_page() {
        add_options_page(
            __('Emoji Sanitizer Settings', 'wpemojisanitizer'),
            __('Emoji Sanitizer', 'wpemojisanitizer'),
            'manage_options',
            'wpemojisanitizer_settings',
            array($this, 'wpemojisanitizer_settings_page_callback')
        );
    }

    public function wpemojisanitizer_settings_page_callback() {
        $options = get_option('wpemojisanitizer_options');
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form method="post" action="options.php">
                <?php settings_fields('wpemojisanitizer_options_group'); ?>
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row"><?php _e('Disable Emoji Detection Script', 'wpemojisanitizer'); ?></th>
                            <td>
                                <input type="checkbox" name="wpemojisanitizer_options[disable_detection_script]" <?php checked($options['disable_detection_script']); ?>>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Disable Emoji Styles', 'wpemojisanitizer'); ?></th>
                            <td>
                                <input type="checkbox" name="wpemojisanitizer_options[disable_styles]" <?php checked($options['disable_styles']); ?>>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Disable Static Emoji', 'wpemojisanitizer'); ?></th>
                            <td>
                                <input type="checkbox" name="wpemojisanitizer_options[disable_static_emoji]" <?php checked($options['disable_static_emoji']); ?>>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Disable TinyMCE Emoji', 'wpemojisanitizer'); ?></th>
                            <td>
                                <input type="checkbox" name="wpemojisanitizer_options[disable_tinymce_emoji]" <?php checked($options['disable_tinymce_emoji']); ?>>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Disable DNS Prefetch', 'wpemojisanitizer'); ?></th>
                            <td>
                                <input type="checkbox" name="wpemojisanitizer_options[disable_dns_prefetch]" <?php checked($options['disable_dns_prefetch']); ?>>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    public function wpemojisanitizer_register_settings() {
        register_setting(
            'wpemojisanitizer_options_group',
            'wpemojisanitizer_options'
        );
    }

    public function wpemojisanitizer_activate() {
        add_option('wpemojisanitizer_options', array(
            'disable_detection_script' => true,
            'disable_styles' => true,
            'disable_static_emoji' => true,
            'disable_tinymce_emoji' => true,
            'disable_dns_prefetch' => true
        ));
    }

    public static function wpemojisanitizer_uninstall() {
        delete_option('wpemojisanitizer_options');
    }
}

$emoji_sanitizer_plugin = new WP_Emoji_Sanitizer_Plugin();
?>

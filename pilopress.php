<?php
/**
 * Plugin Name: Pilo'Press
 * Plugin URI: www.pilot-in.com
 * Description: Awesome WordPress Framework
 * Version: 0.1
 * Author: Pilot'In
 * Author URI: www.pilot-in.com
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Requires PHP: 5.6 or higher
 * WC requires at least: 4.9 or higher
 * WC tested up to: 5.3.2
 */

defined( 'ABSPATH' ) || exit;

if ( !class_exists( 'PiloPress' ) ) {
    class PiloPress {

        // Plugin version
        var $version = '0.1';

        // ACF
        var $acf = false;

        // ACFE
        var $acfe = false;

        /**
         * Pilo'Press constructor.
         */
        public function __construct() {
            // Do nothing.
        }

        /**
         * Initialize plugin
         */
        public function initialize() {
            // Constants
            $this->define( 'PIP_FILE', __FILE__ );
            $this->define( 'PIP_PATH', plugin_dir_path( __FILE__ ) );
            $this->define( 'PIP_URL', plugin_dir_url( __FILE__ ) );
            $this->define( 'PIP_BASENAME', plugin_basename( __FILE__ ) );
            $this->define( 'PIP_THEME_STYLE_PATH', get_stylesheet_directory() . '/pilopress/' );
            $this->define( 'PIP_THEME_STYLE_URL', get_stylesheet_directory_uri() . '/pilopress/' );
            $this->define( 'PIP_THEME_LAYOUTS_PATH', get_stylesheet_directory() . '/pilopress/layouts/' );
            $this->define( 'PIP_THEME_LAYOUTS_URL', get_stylesheet_directory_uri() . '/pilopress/layouts/' );

            // Init
            include_once( PIP_PATH . 'init.php' );

            // Load
            add_action( 'acf/include_field_types', array( $this, 'load' ) );
        }

        /**
         * Load classes
         */
        public function load() {
            // Check if ACF Pro and ACFE are activated
            if ( !$this->has_acf() || !$this->has_acfe() ) {
                return;
            }

            // Includes
            add_action( 'acf/init', array( $this, 'includes' ) );

            // Activation actions
            add_action( 'wp_loaded', array( $this, 'activation' ), 20 );

            // Sync JSON
            pilopress_include( 'includes/classes/admin/class-json-sync.php' );

            // PILO_TODO: remove
            remove_post_type_support( 'post', 'editor' );
            remove_post_type_support( 'page', 'editor' );
        }

        /**
         * Include files
         */
        public function includes() {
            // Main
            pilopress_include( 'includes/classes/main/class-flexible.php' );
            pilopress_include( 'includes/classes/main/class-flexible-mirror.php' );
            pilopress_include( 'includes/classes/main/class-layouts.php' );
            pilopress_include( 'includes/classes/main/class-layouts-categories.php' );

            // Admin
            pilopress_include( 'includes/classes/admin/class-admin.php' );
            pilopress_include( 'includes/classes/admin/class-admin-layouts.php' );
            pilopress_include( 'includes/classes/admin/class-admin-options-page.php' );
            pilopress_include( 'includes/classes/admin/class-styles-settings.php' );
            pilopress_include( 'includes/classes/admin/class-tinymce.php' );
            pilopress_include( 'includes/classes/admin/class-shortcodes.php' );
            pilopress_include( 'includes/classes/admin/class-fields.php' );

            // SCSS - PHP
            pilopress_include( 'includes/classes/scssphp/class-scss-php.php' );

            // Components
            pilopress_include( 'includes/classes/components/class-components.php' );
            pilopress_include( 'includes/classes/components/class-component-field-type.php' );
        }

        /**
         * Activation actions
         */
        public static function activation() {
            if ( !class_exists( 'PIP_Flexible_Mirror' ) && !class_exists( 'PIP_Styles_Settings' ) ) {
                return;
            }

            // Generate flexible mirror field group
            $class = new PIP_Flexible_Mirror();
            $class->generate_flexible_mirror();

            // Compile styles
            $theme_style_path = PIP_THEME_STYLE_PATH;
            if ( file_exists( $theme_style_path )
                 && !file_exists( $theme_style_path . 'style-pilopress.css' )
                 && !file_exists( $theme_style_path . 'style-pilopress-admin.css' ) ) {
                PIP_Styles_Settings::compile_styles_settings();
            }
        }

        /**
         * Define constants
         *
         * @param $name
         * @param bool $value
         */
        private function define( $name, $value = true ) {
            if ( !defined( $name ) ) {
                define( $name, $value );
            }
        }

        /**
         * Check if ACF Pro is activated
         * @return bool
         */
        public function has_acf() {
            // If ACF already available, return
            if ( $this->acf ) {
                return true;
            }

            // Check if ACF Pro is activated
            $this->acf = class_exists( 'ACF' ) && defined( 'ACF_PRO' ) && defined( 'ACF_VERSION' ) && version_compare( ACF_VERSION, '5.7.10', '>=' );

            return $this->acf;
        }

        /**
         * Check if ACFE is activated
         * @return bool
         */
        public function has_acfe() {
            // If ACFE already available, return
            if ( $this->acfe ) {
                return true;
            }

            // Check if ACFE activated
            $this->acfe = class_exists( 'ACFE' );

            return $this->acfe;
        }
    }
}

/**
 * Instantiate Pilo'Press
 * @return PiloPress
 */
function pilopress() {
    global $pilopress;

    if ( !isset( $pilopress ) ) {
        $pilopress = new PiloPress();
        $pilopress->initialize();
    }

    return $pilopress;
}

// Instantiate
pilopress();
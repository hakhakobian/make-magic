<?php
/**
 *  Plugin Name: Make magic
 *  Description: Simple plugin to have a shortcode to display a form that accept user input, and then insert it into the custom table and have a shortcode to display the data with search functionality.
 *  Version: 1.0.0
 *  Requires at least: 4.6
 *  Requires PHP: 7.4
 *  Author: Hakob Hakobyan
 *  License: http://www.gnu.org/licenses/gpl-2.0.html
 *  Text Domain: hh_make_magic
 */

final class MAKEMAGIC {
  protected static $_instance = null;
  private string $prefix = "make_magic";
  private string $option = "username";
  private string $version = "1.0.0";
  private string $plugin_url = '';
  private array $rest_root = [];

  /**
   * Ensures only one instance is loaded or can be loaded.
   *
   * @return  self|null
   */
  public static function instance() {
    if ( is_null( self::$_instance ) ) {
      self::$_instance = new self();
    }
    return self::$_instance;
  }

  public function __construct() {
    $this->plugin_url = plugins_url(plugin_basename(dirname(__FILE__)));
    $this->rest_root = [
      'namespace' => $this->prefix . '/v1',
      'route' => '/data',
    ];
    $this->add_actions();
  }

  /**
   * Actions.
   */
  private function add_actions(): void {
    // Register shortcodes.
    add_shortcode('MAKEMAGICFORM', [$this, 'form_shortcode']);
    add_shortcode('MAKEMAGICLIST', [$this, 'list_shortcode']);

    // Register rout to write tha data to the custom table or read tha data from the custom table.
    add_action( 'rest_api_init', function () {
      register_rest_route( $this->rest_root['namespace'], $this->rest_root['route'], [
        'methods' => WP_REST_Server::READABLE . ", " . WP_REST_Server::EDITABLE,
        'callback' => [ $this, 'data'],
      ]);
    } );

    // Register js file.
    add_action('wp_enqueue_scripts', [$this, 'register_scripts']);

    // Register activation/deactivation hooks.
    register_activation_hook(__FILE__, [$this, 'activate']);
    register_deactivation_hook( __FILE__, [$this, 'deactivate']);
  }

  /**
   * Write or read the data depends on method.
   *
   * @param WP_REST_Request|NULL $request
   *
   * @return void
   */
  public function data(WP_REST_Request $request = null): void {
    if ( !is_null($request) ) {
      switch ( $request->get_method() ) {
        case "GET": {
          $this->get_data($request);
          break;
        }
        case "POST":
        case "PUT":
        case "PATCH": {
          $this->add_data($request);
          break;
        }
        default: {
          break;
        }
      }
    }
  }

  /**
   * Write the data to the custom table.
   *
   * @param $request
   *
   * @return WP_Error|WP_REST_Response
   */
  private function add_data($request) {
    $data = $request->get_body();

    if ( empty($data) ) {
      return new WP_Error( '404', __( 'Nothing to save.', 'make_magic' ) );
    }
    $data = (array) json_decode($data);
    array_walk($data, function (&$value) {
      $value = sanitize_text_field(stripslashes($value));
    });
    global $wpdb;
    $saved = $wpdb->insert($wpdb->prefix . "make_magic_things", [$this->option => $data[$this->option]], ['%s']);

    if ( $saved !== FALSE ) {
      return new WP_REST_Response( wp_send_json(__( 'Successfully saved.', 'make_magic' )), 200 );
    }
    else {
      return new WP_REST_Response( wp_send_json(__( 'Nothing saved.', 'make_magic' )), 400 );
    }
  }

  /**
   * Read the data from the custom table.
   *
   * @param $json
   *
   * @return array|null
   */
  private function get_data($json = TRUE) {
    global $wpdb;
    $username = !empty($_GET['username']) ? esc_sql($_GET['username']) : '';
    $data = $wpdb->get_col($wpdb->prepare("SELECT `username` FROM `" . $wpdb->prefix . "make_magic_things` WHERE `username` LIKE %s", "%" . $username . "%"));

    return $json ? wp_send_json($data) : $data;
  }

  /**
   * Form shortcode output.
   *
   * @return false|string
   */
  public function form_shortcode() {
    wp_enqueue_script($this->prefix . '_main');
    ob_start();
    ?><form method="POST">
     <input placeholder="<?php echo __('User name', 'make_magic'); ?>" type="text" name="<?php echo esc_attr($this->prefix . "_" . $this->option); ?>" />
     <input type="submit" id="<?php echo esc_attr($this->prefix . "_submit"); ?>" />
    </form><?php
    return ob_get_clean();
  }

  /**
   * List shortcode output.
   *
   * @return false|string
   */
  public function list_shortcode() {
    wp_enqueue_script($this->prefix . '_main');
    $data = $this->get_data(FALSE);
    ob_start();
    ?>
    <form>
    <input placeholder="<?php echo __('Search', 'make_magic'); ?>" type="text" name="<?php echo esc_attr($this->prefix . "_search" . $this->option); ?>" />
    <input type="submit" id="<?php echo esc_attr($this->prefix . "_search"); ?>" />
    </form>
    <div id="<?php echo esc_attr($this->prefix . "_items"); ?>">
    <?php
    foreach ($data as $value) {
    ?><div><?php echo esc_html($value); ?></div><?php
    }
    ?>
    </div>
    <?php
    return ob_get_clean();
  }

  public function activate(): void {
    $this->create_db_table();
  }

  public function register_scripts(): void {
    wp_register_script($this->prefix . '_main', $this->plugin_url . '/assets/main.js', ['jquery'], $this->version);
    wp_localize_script( $this->prefix . '_main', 'make_magic', array(
      'rest_root' => esc_url_raw( rest_url($this->rest_root['namespace'] . $this->rest_root['route']) ),
      'prefix' => $this->prefix,
      'nonce' => wp_create_nonce( 'wp_rest' ),
    ) );
  }
  private function create_db_table() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $wpdb->query("CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "make_magic_things` (
    `id` bigint(20) NOT NULL AUTO_INCREMENT,
    `username` mediumtext NOT NULL,
    PRIMARY KEY (`id`)
  ) " . $charset_collate . ";");
  }

  public function deactivate(): void {
    global $wpdb;
    $wpdb->query("DROP TABLE IF EXISTS `" . $wpdb->prefix . "make_magic_things`");
  }
}

/**
 * Main instance of MAKEMAGIC.
 *
 * @return MAKEMAGIC The main instance to prevent the need to use globals.
 */
function MAKEMAGIC() {
  return MAKEMAGIC::instance();
}

MAKEMAGIC();

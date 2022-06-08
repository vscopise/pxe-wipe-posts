<?php
/**
 * Plugin Name:       PXE Wipe Posts
 * Plugin URI:        
 * Description:       Permite limpiar post de la base de datos cuyo archivo estático está presente
 * Version:           1.0.0
 * Author:            Pixie
 * Author URI:        http://www.pixie.com.uy/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       pxe-wipe-posts
 * Domain Path:       /languages/
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'PXE_Wipe_Posts' ) ) :
    
    class PXE_Wipe_Posts {
    
            protected static $instance = NULL;
            
            public static function get_instance() {
                    if ( null === self::$instance ) {
                            self::$instance = new self;
                    }
                    return self::$instance; 
            }
            
            public function __construct() {
                    add_action( 'admin_init', __CLASS__ . '::check_wpsc_active' );
                    add_action( 'admin_menu', __CLASS__ . '::tool_menu' );
                    add_action( 'admin_enqueue_scripts', __CLASS__ . '::admin_scripts' );
                    add_action( 'wp_ajax_process_wipe_post', __CLASS__ . '::process_wipe_post');
            }
            
            public function process_wipe_post() {
                    $post_id = filter_input( INPUT_POST, 'post_id' );
                    
                    $post_query = new WP_Query( array( 'p' => $post_id ) );
                    if( $post_query->have_posts() ) : while ( $post_query->have_posts() ) : $post_query->the_post();
                            $data['title'] = get_the_title();

                            $file_contents = self::file_get_contents_curl( get_the_permalink() );
                            //$file_contents = file_get_contents( get_the_permalink() );
                            
                            $access_type = get_filesystem_method();
                            
                            if ( $access_type === 'direct' ) {

                                    global $wp_filesystem;
                                    
                                    //$content = $wp_filesystem->get_contents( get_the_permalink() );
                                    
                                    $path = str_replace( home_url() . '/', '', get_the_permalink() );
                                    
                                    if ( ! file_exists( get_home_path() . $path ) ) {
                                            mkdir( get_home_path() . $path, 0755, TRUE );
                                    }
                                    
                                    if ( $wp_filesystem->put_contents( get_home_path() . $path . 'index.html', $file_contents, 0644) ) {
                                            wp_delete_post( $post_id, TRUE );
                                            //wp_delete_post( $post_id );
                                            $data['result'] = 'ok';
                                    } else {
                                            $data['result'] = 'error';
                                    }
                                    
                            } else {
                                    $data['result'] = 'error';
                            }
                           
                    endwhile; endif;
                    wp_send_json_success( $data );
                    wp_die();
            }
            
            public function file_get_contents_curl($url) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Set curl to return the data instead of printing it to the browser.
                curl_setopt($ch, CURLOPT_URL, $url);
                $data = curl_exec($ch);
                curl_close($ch);
                return $data;
            }
            
            public function connect_fs($url, $method, $context, $fields = null) {
                global $wp_filesystem;
                if(false === ($credentials = request_filesystem_credentials($url, $method, false, $context, $fields))) 
                {
                    return false;
                }

                //check if credentials are correct or not.
                if(!WP_Filesystem($credentials)) 
                {
                    request_filesystem_credentials($url, $method, true, $context);
                    return false;
                }

                return true;
            }
            
            public function copy_directory($src,$dst) {
                $dir = opendir( $src );
                wp_mkdir_p( $dst );
                while(false !== ( $file = readdir($dir)) ) {
                    if (( $file != '.' ) && ( $file != '..' )) {
                        if ( is_dir($src . '/' . $file) ) {
                            recurse_copy($src . '/' . $file,$dst . '/' . $file);
                        }
                        else {
                            copy($src . '/' . $file, $dst . '/' . $file);
                        }
                    }
                }
                closedir($dir);
            }
            
            public static function deleteDir($dirPath) {
                if (! is_dir($dirPath)) {
                    throw new InvalidArgumentException("$dirPath must be a directory");
                }
                if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
                    $dirPath .= '/';
                }
                $files = glob($dirPath . '*', GLOB_MARK);
                foreach ($files as $file) {
                    if (is_dir($file)) {
                        self::deleteDir($file);
                    } else {
                        unlink($file);
                    }
                }
                rmdir($dirPath);
            }
            
            public function admin_scripts( $hook ) {
                    if ( 'tools_page_pxe-wipe-posts' != $hook ) return;
                    
                    $object_array = array(
                        'ajax_url' => admin_url( 'admin-ajax.php' ),
                        'return_link' => admin_url( 'tools.php?page=pxe-wipe-posts' ),
                    );

                    wp_register_script( 'pxe-wp-scripts', plugins_url('/includes/pxe-wp-scripts.js', __FILE__) );
                    wp_localize_script( 'pxe-wp-scripts', 'pxe_wp_object', $object_array );
                    wp_enqueue_script( 'pxe-wp-scripts' );
                    
            }
            
            public function tool_menu() {
                    add_submenu_page( 
                            'tools.php', 
                            'Purgar Entradas', 
                            'Purgar Entradas', 
                            'edit_pages', 
                            'pxe-wipe-posts', 
                            __CLASS__ . '::wipe_posts'
                    );
            }
            
            public function wipe_posts() {
                include_once dirname(__FILE__) . '/includes/wipe-post-tool.php';;
                ?>
                <div class="wrap"></div>
                <?php
            }
            
            public function check_wpsc_active_notice() {
                    if ( ! is_plugin_active( 'wp-super-cache/wp-cache.php' ) ) {
                            $wpsc_url = 'https://wordpress.org/plugins/wp-super-cache/';
                            ?>
                                    <div class="notice error is-dismissible" >
                                            <p>
                                                    El plugin <a href="<?php echo $wpsc_url ?>" target="_blank">WP Super Cache</a> es imprescindible para el funcionamiento de este plugin
                                            </p>
                                    </div>
                            <?php
                    }
            }
            
            public function check_wpsc_active( ) {
                    if ( ! is_plugin_active( 'wp-super-cache/wp-cache.php') && is_plugin_active( plugin_basename( __FILE__ ) ) ) {
                            deactivate_plugins( plugin_basename( __FILE__ ) );
                            add_action( 'admin_notices', __CLASS__ . '::check_wpsc_active_notice' );
                            if ( isset( $_GET['activate'] ) ) {
                                    unset( $_GET['activate'] );
                            }
                    }
            }
    
    }
    
    $PXE_Wipe_Posts = new PXE_Wipe_Posts;
    
    
    
endif;
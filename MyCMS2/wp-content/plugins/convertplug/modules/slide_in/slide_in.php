<?php
// Prohibit direct script loading.
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );
if( !defined( 'CP_BASE_DIR_SLIDEIN' ) ) {
	define( 'CP_BASE_DIR_SLIDEIN', plugin_dir_path( __FILE__ ));
}
require_once(CP_BASE_DIR_SLIDEIN.'/functions/functions.php');

if( !class_exists('Smile_Slide_Ins') ) {
	class Smile_Slide_Ins extends Convert_Plug{
		public static $settings = array();
		public static $options = array();
		function __construct(){
			add_action( 'wp_enqueue_scripts',array($this,'enqueue_front_scripts' ), 100);
			add_action( 'admin_enqueue_scripts',array($this,'enqueue_admin_scripts' ) );
			add_action( 'admin_menu',array($this,'add_admin_menu_page' ), 999);
			add_action( 'admin_head',array($this,'load_customizer_scripts' ) );
			add_action( 'wp_footer', array( $this, 'load_slide_in_globally' ) );
			add_action( 'init', array( $this, 'register_theme_templates') );
			add_filter( 'admin_body_class', array( $this, 'cp_admin_body_class') );
			require_once(CP_BASE_DIR_SLIDEIN.'slide_in_preset.php');
		}

		function cp_admin_body_class( $classes ) {

			if( isset( $_GET['style-view']) && $_GET['style-view'] == "new" ){
				$classes = str_replace( "cp-add-new-style", "", $classes );
	        	$classes .= 'cp-add-new-style';
	        }
			return $classes;
		}

		function register_theme_templates(){
			$dir = plugin_dir_path( __FILE__ );
			$themes = glob($dir . 'themes/*.php');
			foreach( $themes as $theme ){
				require_once( $theme );
			}
		}

		function add_admin_menu_page(){
			$page = add_submenu_page(
				CP_PLUS_SLUG,
				'Slide In Designer',
				'Slide In',
				'access_cp',
				'smile-slide_in-designer',
				array($this,'slide_in_dashboard') );
			$obj = new parent;
			add_action( 'admin_print_scripts-' . $page, array($obj,'convert_admin_scripts'));
			add_action( 'admin_print_scripts-' . $page, array($this,'slide_in_admin_scripts'));
			add_action( 'admin_footer-'. $page, array($this,'cp_admin_footer') );
		}

		function slide_in_admin_scripts(){
			if( ( isset( $_GET['style-view'] ) && ( $_GET['style-view'] == "edit" || $_GET['style-view'] == "variant" ) ) || !isset( $_GET['style-view'] ) ) {

				wp_enqueue_script( 'smile-slide_in-receiver', 			CP_PLUGIN_URL.'modules/slide_in/assets/js/receiver.js' );
				wp_enqueue_style( 'cp-contacts', 					CP_PLUGIN_URL.'admin/contacts/css/cp-contacts.css' );
				wp_enqueue_media();
				wp_enqueue_script( 'smile-slide_in-importer', CP_PLUGIN_URL.'modules/assets/js/admin-media.js',array( 'jquery' ),'',true);
			}

			if( isset($_GET['style-view']) && $_GET['style-view'] == 'analytics' ) {
				wp_enqueue_style( 'css-select2',					CP_PLUGIN_URL.'admin/assets/select2/select2.min.css' );
				wp_enqueue_script( 'convert-select2',				CP_PLUGIN_URL.'admin/assets/select2/select2.min.js' );
				wp_enqueue_script( 'bsf-charts-js',					CP_PLUGIN_URL.'admin/assets/js/chart.js' );
				wp_enqueue_script( 'bsf-charts-bar-js',				CP_PLUGIN_URL.'admin/assets/js/chart.bar.js' );
				wp_enqueue_script( 'bsf-charts-donut-js',			CP_PLUGIN_URL.'admin/assets/js/chart.donuts.js' );
				wp_enqueue_script( 'bsf-charts-line-js',			CP_PLUGIN_URL.'admin/assets/js/Chart.Line.js' );
				wp_enqueue_script( 'bsf-charts-polararea-js',		CP_PLUGIN_URL.'admin/assets/js/Chart.PolarArea.js' );
				wp_enqueue_script( 'bsf-style-analytics-js',		CP_PLUGIN_URL.'modules/assets/js/style-analytics.js');
			}
		}

		function slide_in_dashboard(){
			$page = isset($_GET['style-view']) ? esc_attr( $_GET['style-view'] ) : 'main';

			// load default option set
			if( is_admin() ){
				require_once(CP_BASE_DIR_SLIDEIN.'/functions/functions.options.php');
			}
		
			switch($page){
				case 'main':
					require_once(CP_BASE_DIR_SLIDEIN.'/views/main.php');
					break;
				case 'new':
					$default_google_fonts = array (
						"Lato",
						"Open Sans",
						"Libre Baskerville",
						"Montserrat",
						"Neuton",
						"Raleway",
						"Roboto",
						"Sacramento",
						"Varela Round",
						"Pacifico",
						"Bitter"
					);
					$gfonts = implode( ",", $default_google_fonts );
					require_once(CP_BASE_DIR_SLIDEIN.'/functions/functions.php');
					if( function_exists( "cp_enqueue_google_fonts" ) ){
						cp_enqueue_google_fonts( $gfonts );
					}
					require_once(CP_BASE_DIR_SLIDEIN.'/views/new-style.php');
					break;
				case 'edit':
					require_once(CP_BASE_DIR_SLIDEIN.'/views/edit.php');
					break;
				case 'variant':
					require_once(CP_BASE_DIR_SLIDEIN.'/views/variant.php');
					break;
				case 'analytics':
					require_once(CP_BASE_DIR_SLIDEIN.'/views/analytics.php');
					break;
			}
		}

		function load_slide_in_globally(){

			if(!isset($_GET['hidemenubar'])){
				?>
	            <script type="text/javascript" id="slidein">
				jQuery(window).on( 'load', function(){
					startclock();
				});
				function stopclock (){
				  if(timerRunning) clearTimeout(timerID);
				  timerRunning = false;
				  document.cookie="time=0";
				}

				function showtime () {
				  var now = new Date();
				  var my = now.getTime() ;
				  now = new Date(my-diffms) ;
				  document.cookie="time="+now.toLocaleString();
				  timerID = setTimeout('showtime()',10000);
				  timerRunning = true;
				}

				function startclock () {
				  stopclock();
				  showtime();
				}
				var timerID = null;
				var timerRunning = false;
				var x = new Date() ;
				var now = x.getTime() ;
				var gmt = <?php echo time(); ?> * 1000 ;
				var diffms = (now - gmt) ;
				</script>
	            <?php
				$slide_in_style = $slide_in_style_delay = $slide_in_cookie_delay = '';
				$live_styles = cp_get_live_styles('slide_in');
				$prev_styles = get_option('smile_slide_in_styles');
				$smile_variant_tests = get_option('slide_in_variant_tests');
				
				if( is_array($live_styles) && !empty( $live_styles ) ) {

					global $post;
					$slide_in_arrays = $live_styles;
					$taxonomies = get_post_taxonomies( $post );

					foreach( $slide_in_arrays as $key => $slide_in_array ){
						$display = false;
						$settings_encoded = '';

						$style_settings = array();
						$global_display = $pages_to_exclude = $cats_to_exclude = $exclusive_pages = $exclusive_cats = $show_for_logged_in = '';
						$settings_array = unserialize($slide_in_array[ 'style_settings' ]);
						foreach($settings_array as $key => $setting){
							$style_settings[$key] = apply_filters( 'smile_render_setting',$setting );
						}

						$style_id = $slide_in_array[ 'style_id' ];
						$slide_in_style = $style_settings[ 'style' ];

						if( is_array($style_settings) && !empty($style_settings) ) {

							$settings = unserialize( $slide_in_array[ 'style_settings' ] );
							$css = isset( $settings['custom_css'] ) ? urldecode($settings['custom_css']) : '';


							$display = cp_is_style_visible($settings);

							// remove back slashes from settings
							$settings = stripslashes_deep( $settings );

							$settings = serialize( $settings );
							$settings_encoded 	= base64_encode( $settings );
						}

						if( $display ) {						

							// developer mode
							if( self::$cp_dev_mode == '1' ) {

								$script_handlers = array(
									'smile-slide-in-common',
									'smile-cp-common-script',
									'smile-slide-in-script',
									'cp-ideal-timer-script',
									'cp-slide-in-mailer-script',
									'cp-frosty-script'
								);

	   							$list = 'enqueued';

	   							foreach( $script_handlers as $handler ) {
	   								if ( !wp_script_is( $handler, $list ) ) {
								       wp_enqueue_script( $handler );
								    }
	   							}

	   						} else {

	   							if ( !wp_script_is( 'cp-frosty-script', 'enqueued' ) ) {
								    wp_enqueue_script( 'cp-frosty-script' );
								}

								if ( !wp_script_is( 'cp-ideal-timer-script', 'enqueued' ) ) {
								    wp_enqueue_script( 'cp-ideal-timer-script' );
								}

								if ( !wp_style_is( 'cp-frosty-style', 'enqueued' ) ) {
								    wp_enqueue_style( 'cp-frosty-style' );
								}

								if ( !wp_script_is( 'smile-slide-in-script', 'enqueued' ) ) {
								    wp_enqueue_script( 'smile-slide-in-script' );
								}

	   						}

							wp_enqueue_script( 'cp-perfect-scroll-js-back', CP_PLUGIN_URL.'admin/assets/js/perfect-scrollbar.jquery.js', array( "jquery" ) );

							//	Generate style ID
							$id = $slide_in_style . '-' . $style_id;

							//	Individual Style Path
							$file_name = '/assets/demos/'. $slide_in_style . '/' . $slide_in_style . '.min.css';
							$url = CP_PLUGIN_URL.'modules/slide_in/'.$file_name ;

							//	Check file exist or not - and append to the head
							echo '<link rel="stylesheet" id="'.$id.'" href="' . $url .'" type="text/css" media="all" />';

							echo '<!-- slide_in Shortcode -->';
							echo do_shortcode('[smile_slide_in style_id = '.$style_id.' style="'.$slide_in_style.'" settings_encoded="' . $settings_encoded . ' "][/smile_slide_in]');
							$css = isset( $settings['custom_css'] ) ? urldecode($settings['custom_css']) : '';
							apply_filters('cp_custom_css',$style_id, $css);
						}
					}
				}
			}
		}

		function load_customizer_scripts(){

			if( ( isset( $_GET['hidemenubar'] ) && isset( $_GET['module'] ) && $_GET['module'] == "slide_in" ) ) {
				wp_enqueue_script( 'cp-perfect-scroll-js-back', CP_PLUGIN_URL.'admin/assets/js/perfect-scrollbar.jquery.js', array( "jquery" ) );
				wp_enqueue_script( 'cp-common-functions-js' );
				wp_enqueue_script( 'cp-admin-customizer-js', CP_PLUGIN_URL.'modules/assets/js/admin.customizer.js' );
				wp_enqueue_script( 'smile-slide_in-editor', CP_PLUGIN_URL.'modules/assets/js/ckeditor/ckeditor.js', array('smile-customizer-js') );
				wp_register_script( 'cp-frosty-script', CP_PLUGIN_URL.'admin/assets/js/frosty.js', array( 'jquery' ), null, null, true );
				wp_enqueue_script( 'cp-frosty-script' );
				
				require_once( CP_BASE_DIR_SLIDEIN.'/functions/functions.options.php' );
				$demo_html = $customizer_js = '';
				$settings = $this::$options;
				foreach( $settings as $style => $options ){
					if( $style == $_GET['theme'] ){
						$customizer_js = $options['customizer_js'];
					}
				}
				if( $customizer_js !== "" ){
					wp_enqueue_script( 'cp-style-customizer-js', $customizer_js , array( 'jquery' ), "", true );
				}
			}

		}

		function enqueue_admin_scripts($hook) {

			if( ( isset( $_GET['hidemenubar'] ) && $_GET['module'] == 'slide_in' )
			     || (isset($_GET['style-view']) && $_GET['style-view'] == 'new' && $hook == CP_PLUS_SLUG.'_page_smile-slide_in-designer' ) ) {

				wp_enqueue_style( 'smile-slide_in', CP_PLUGIN_URL.'modules/slide_in/assets/css/slide_in.min.css' );

			    wp_localize_script( 'jquery', 'slide_in', array( 'demo_dir' => CP_PLUGIN_URL.'modules/slide_in/assets/demos' ) );

				wp_register_script( 'smile-slide_in-common', CP_PLUGIN_URL.'modules/slide_in/assets/js/slide_in.common.js', array( 'jquery' ), null, true );
				wp_register_script( 'cp-common-functions-js', CP_PLUGIN_URL.'modules/slide_in/assets/js/functions-common.js', 'smile-slide_in-common', null, true );
				wp_enqueue_script( 'smile-slide_in-common' );
				wp_localize_script( 'smile-slide_in-common', 'cp', array(
					'demo_dir' => CP_PLUGIN_URL.'modules/slide_in/assets/demos',
					'module' => 'slide_in',
					"module_img_dir" => CP_PLUGIN_URL."modules/assets/images" )
				);

				//	Add 'Theme Name' as a class to <html> tag
				//	To provide theme compatibility
				$theme_name = wp_get_theme();
				$theme_name = $theme_name->get( "Name" );
				$theme_name = strtolower( preg_replace("/[\s_]/", "-", $theme_name ) );

				wp_localize_script( 'jquery', 'cp_active_theme', array( 'slug' => $theme_name ) );
				wp_localize_script( 'smile-slide_in-common', 'smile_ajax', array( 'url' => admin_url( 'admin-ajax.php' ) ) );
			}
		}

		function enqueue_front_scripts(){

			wp_localize_script( 'jquery', 'slide_in', array( 'demo_dir' => CP_PLUGIN_URL.'modules/slide_in/assets/demos' ) );

			$live_styles = cp_get_live_styles('slide_in');

			// if any style is live or modal is in live preview mode then only enqueue scripts and styles
			if( $live_styles && count($live_styles) > 0 ) {


				if( self::$cp_dev_mode == '1' ) {

					// register styles
					wp_enqueue_style( 'smile-slide-in-style', CP_PLUGIN_URL.'modules/slide_in/assets/css/slide_in.css' );
					wp_enqueue_style( 'cp-animate-style', CP_PLUGIN_URL.'modules/assets/css/animate.css' );
					wp_enqueue_style( 'cp-social-media-style', CP_PLUGIN_URL.'modules/assets/css/cp-social-media-style.css' );
					wp_enqueue_style( 'cp-social-icon-style', CP_PLUGIN_URL.'modules/assets/css/social-icon-css.css' );
					wp_enqueue_style( 'convertplug-style', CP_PLUGIN_URL.'modules/assets/css/convertplug.css' );
					wp_enqueue_style( 'cp-social-icon-dev-style', CP_PLUGIN_URL.'modules/assets/css/social-icon.css' );

					// register scripts
					wp_enqueue_style( 'cp-frosty-style', CP_PLUGIN_URL.'admin/assets/css/frosty.css' );
					wp_register_script( 'smile-slide-in-common', CP_PLUGIN_URL.'modules/slide_in/assets/js/slide_in.common.js', null , null, true );
					wp_register_script( 'smile-cp-common-script', CP_PLUGIN_URL.'modules/assets/js/convertplug-common.js', array( 'jquery' ), null, null, true );
					wp_register_script( 'smile-slide-in-script', CP_PLUGIN_URL.'modules/slide_in/assets/js/slide_in.js', array( 'jquery' ,'smile-cp-common-script'), null, null, true );
					wp_register_script( 'cp-ideal-timer-script', CP_PLUGIN_URL.'modules/slide_in/assets/js/idle-timer.min.js', array( 'jquery' ), null, null, true );
					wp_register_script( 'cp-slide-in-mailer-script', CP_PLUGIN_URL.'modules/slide_in/assets/js/mailer.js', array( 'jquery' ), null,
							null, true );
					wp_register_script( 'cp-frosty-script',CP_PLUGIN_URL.'admin/assets/js/frosty.js', array( 'jquery' ), null, null, true );

				} else {
					wp_register_script( 'cp-frosty-script',CP_PLUGIN_URL.'admin/assets/js/frosty.js', array( 'jquery' ), null, null, true );
					wp_enqueue_style( 'cp-frosty-style', CP_PLUGIN_URL.'admin/assets/css/frosty.css' );
					wp_register_script( 'smile-slide-in-script', CP_PLUGIN_URL.'modules/slide_in/assets/js/slide_in.min.js', array( 'jquery' ), null, null, true );
					wp_enqueue_style( 'smile-slide-in-style', CP_PLUGIN_URL.'modules/slide_in/assets/css/slide_in.min.css' );
				}

				wp_localize_script( 'smile-slide-in-script', 'smile_ajax', array( 'url' => admin_url( 'admin-ajax.php' ) ) );
			}

			wp_enqueue_style( 'cp-perfect-scroll-style', CP_PLUGIN_URL.'admin/assets/css/perfect-scrollbar.min.css' );

		}
	}

	$Smile_Slide_Ins = new Smile_Slide_Ins;
}

if (!function_exists('smile_slide_in_popup')) {
	function smile_slide_in_popup( $atts, $content = null ) {
		$style = '';
		extract(shortcode_atts(array(
			'style' 				=> '',
			'style_name'			=> '',
		), $atts));
		$output = '';
		$func = 'slide_in_theme_'.$style;

		$settings = base64_decode( $atts['settings_encoded'] );
		$style_settings = unserialize( $settings );

		// remove back slashes from settings
		$settings = stripslashes_deep( $style_settings );

		$settings = serialize( $settings );
		$settings_encoded 	= base64_encode( $settings );
		$atts['settings_encoded'] = $settings_encoded;

		if( function_exists( $func ) ) {
			$output = $func( $atts );
		}
		echo $output;
	}
	add_shortcode('smile_slide_in', 'smile_slide_in_popup');
}


if (!function_exists('cp_slide_in_custom')) {
	function cp_slide_in_custom( $atts, $content = null ) {
		ob_start();
		$id = $display = '';
		extract(shortcode_atts(array(
			'id' 				=> '',
			'display'			=> '',
		), $atts));
		$live_styles = cp_get_live_styles('slide_in');
		$live_array = $settings = '';
		foreach( $live_styles as $key => $slide_in_array ){
			$style_id = $slide_in_array[ 'style_id' ];

			$settings = unserialize( $slide_in_array[ 'style_settings' ] );
			if(isset($settings['variant_style_id']) && $id == $settings['style_id']){
				$id = $settings['variant_style_id'];
			}

			if( $id == $style_id )
			{
				$live_array = $slide_in_array;
				$settings = unserialize( $slide_in_array[ 'style_settings' ] );
				$settings_array = unserialize($slide_in_array[ 'style_settings' ]);
				foreach($settings_array as $key => $setting){
					$style_settings[$key] = apply_filters( 'smile_render_setting',$setting );
				}
				$slide_in_style = $style_settings[ 'style' ];
				$global = $style_settings[ 'global' ];
				if( $display ){
					$global = false;
				} else {
					$style_settings[ 'global' ] = true;
				}
				$style_settings[ 'display' ] = $display;
				$style_settings['custom_class'] .= isset( $style_settings['custom_class']) ? $style_settings['custom_class'].',cp-trigger-'.$style_id : 'cp-trigger-'.$style_id;

				$display = cp_is_style_visible($settings);

				// remove back slashes from settings
				$settings = stripslashes_deep( $settings );

				$encode_settings = serialize( $style_settings );
				$settings_encoded = base64_encode( $encode_settings );

				echo '<span class="cp-trigger-shortcode cp-trigger-'.$style_id.' cp-'.$style_id.'">'.do_shortcode( $content ).'</span>';
				if( $display ) {

					//	Individual Style Path
					$file_name = '/assets/demos/'. $slide_in_style . '/' . $slide_in_style . '.min.css';
					$url = CP_PLUGIN_URL.'modules/slide_in/'. $file_name ;

					echo '<link rel="stylesheet" id="'.$id.'" href="' . $url .'" type="text/css" media="all" />';
					echo do_shortcode('[smile_slide_in manual="true" style_id = '.$style_id.' style="'.$slide_in_style.'" settings_encoded="' . $settings_encoded . ' "][/smile_slide_in]');
					$css = isset( $settings['custom_css'] ) ? urldecode($settings['custom_css']) : '';
					apply_filters('cp_custom_css',$style_id, $css);
				}
				break;
			}
		}
		return ob_get_clean();
	}
	add_shortcode('cp_slide_in', 'cp_slide_in_custom');
}
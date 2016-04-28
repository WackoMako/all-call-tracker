<?php
/**
 * Plugin Name: All Call Tracker
 * Plugin URI: http://waynemcmahon.com
 * Description: This plugin accurately tracks calls made from your site.
 * Version: 1.0.0
 * Author: Wayne McMahon
 * Author URI: http://waynemcmahon.com
 * License: GPL2
 */

class trackCalls {
 
 	private $options;
    /*--------------------------------------------*
     * Constructor
     *--------------------------------------------*/
 
    /**
     * Initializes the plugin by setting localization, filters, and administration functions.
     */
    function __construct() {
    	global $options;

    	//Add Google Analytics
         add_action( 'wp_enqueue_scripts', array( $this, 'add_analytics' ) );    

        add_action( 'wp_enqueue_scripts', array( $this, 'add_scripts' ) );
       
		// Include the Ajax library on the front end
		add_action( 'wp_head', array( $this, 'add_ajax_library' ) );
		//Create database on activation
		register_activation_hook( __FILE__, array( $this, 'all_calls_create_db') );
        // Register site styles and scripts
        add_action( 'wp_enqueue_scripts', array( $this, 'add_stylesheets' ) );
        
        //Add AJAX for both logged in and logged out users
        add_action( 'wp_ajax_nopriv_track_phone', array( $this, 'track_phone' ) );
        add_action( 'wp_ajax_track_phone', array( $this, 'track_phone' ) );

         /*Options Menu*/
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );

        add_action('admin_head', array( $this, 'admin_css'));
    } // end constructor

    function add_analytics(){
        $this->options = get_option( 'my_option_name' );
        $analytics = $this->options['analytics'];
        // //Add Analytics Tracking Code
        if($analytics == 1 )//if google
            {
                add_action( 'wp_head', array( $this,'add_google_analytics') );
            }
        elseif($analytics == 2) //if clicky
            {
                add_action( 'wp_footer', array( $this, 'add_clicky_analytics' ) );
            }
        $options_id= json_encode( $analytics );
        $encode = "<script type=\"text/javascript\">
                        var analytics_option = " . $options_id .  ";
                    </script>";

        echo $encode;
    }

    function admin_css() {
	  echo '<style>
	      #table-call-tracker th {
		  text-align: center !important;
	    } 
	    #table-call-tracker td{
	    	background-color: #fff;
		}
	  </style>';
	}

    	    /**
	 * Enqueue plugin stylesheets
	 */
	function add_stylesheets() {
	    wp_register_style( 'prefix-style', plugins_url('css/call-tracker.css', __FILE__) );
	    wp_enqueue_style( 'prefix-style' );
	}
	 
	/**
	 * Enqueue plugin scripts
	 */
	function add_scripts(){
        wp_enqueue_script('call-tracker', plugin_dir_url(__FILE__) . 'js/call-tracker.js', array('jquery'));
		wp_localize_script( 'frontend-ajax', 'frontendajax', array('ajax_url' => admin_url( 'admin-ajax.php' )));
	}

	/**
	 * Adds the WordPress Ajax Library to the frontend.
	 */
	public function add_ajax_library() {
	    $html = '<script type="text/javascript">';
	    $html .= 'var ajaxurl = "' . admin_url( 'admin-ajax.php' ) . '"';
	    $html .= '</script>';
	    echo $html;
	 
	} // end add_ajax_library

	/**
	 * Adds Google Analytics Tracking Code
	 */
	public function add_google_analytics() {

        $this->options = get_option( 'my_option_name' );
        $id = $this->options['id_number'];


		$analytics = "<script type='text/javascript'>
        
        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
        (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

        ga('create', '" . $id . "', 'auto');
        ga('send', 'pageview');
        </script>";

		echo $analytics;
	}

	/**
	 * Adds Clicky Analytics Tracking Code
	 */
	function add_clicky_analytics() {
        $this->options = get_option( 'my_option_name' );
        $id = $this->options['title'];

	    $html = '<a title="Google Analytics Alternative" href="http://clicky.com/100943154"><img alt="Google Analytics Alternative" src="//static.getclicky.com/media/links/badge.gif" border="0" /></a>
            <script src="//static.getclicky.com/js" type="text/javascript"></script>
            <script type="text/javascript">try{ clicky.init(' . $id . '); }catch(e){}</script>
            <noscript><p><img alt="Clicky" width="1" height="1" src="//in.getclicky.com/' . $id . 'ns.gif" /></p></noscript>';
	    echo $html;
	} // end add_ajax_library

	//Create table when plugin activated
	function all_calls_create_db() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'all_calls';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "DROP TABLE IF EXISTS $table_name;
				CREATE TABLE IF NOT EXISTS $table_name (
				  `id` int(20) NOT NULL AUTO_INCREMENT,
				  `number` varchar(20) NOT NULL,
				  `date` datetime NOT NULL,
				  `url` varchar(255) NOT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Settings Admin', 
            'Call Tracker Settings', 
            'manage_options', 
            'call-tracker-setting', 
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'my_option_name' );
        ?>
        <div class="wrap">
            <h2>Call Tracker Settings</h2>           
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'my_option_group' );   
                do_settings_sections( 'call-tracker-setting' );
            ?>
            </form>
        </div>
        <?php
    }

     /**
     * Register and add settings
     */
    public function page_init()
    {        
        register_setting(
            'my_option_group', // Option group
            'my_option_name', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'setting_section_id', // ID
            'External Analytics', // Title
            array( $this, 'print_section_info' ), // Callback
            'call-tracker-setting' // Page
        );  

        add_settings_field(
        	'analytics', 
        	'External Analytics', 
        	array( $this,'choose_analytics_callback'),
        	'call-tracker-setting', 
        	'setting_section_id'
    	 );    

        add_settings_field(
            'id_number', // ID
            'Google Analyics Key', // Title 
            array( $this, 'id_number_callback' ), // Callback
            'call-tracker-setting', // Page
            'setting_section_id' // Section           
        );      

        add_settings_field(
            'title', 
            'Clicky Analytics Key', 
            array( $this, 'title_callback' ), 
            'call-tracker-setting', 
            'setting_section_id'
        );
        add_settings_field(
        	"table_calls", 
        	"Call Table", 
        	array( $this,"table_callback"),
        	"call-tracker-setting", 
        	"setting_section_id"
    	 );       
    }

     /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();
        if( isset( $input['id_number'] ) )
            $new_input['id_number'] = sanitize_text_field( $input['id_number'] );

        if( isset( $input['title'] ) )
            $new_input['title'] = sanitize_text_field( $input['title'] );

        if( isset( $input['analytics'] ) )
            $new_input['analytics'] = absint( $input['analytics'] );

        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function print_section_info()
    {
        print 'Enter your settings below:';
    }

    public function choose_analytics_callback()
    {
    	$google ="";
    	$clicky = "";
    	$none = "";
    	$selected ="selected=\"selected\"";

    	if($this->options['analytics']==1 )
    		{$google=$selected;}
    	elseif($this->options['analytics']==2) 
    		{$clicky=$selected;}
    	else{$none=$selected;}
    	?>

        <label>Do you want use external analytics?</label>
		<select id="analytics" name="my_option_name[analytics]" value="">
			<option <?php echo $none ?> value="">None</option>
			<option <?php echo $google ?> value="1">Google Analytics</option>
			<option <?php echo $clicky ?> value="2">Clicky Analytics</option>
		</select>
		<?php

    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function id_number_callback()
    {
        printf(
            '<input type="text" id="id_number" name="my_option_name[id_number]" value="%s" />',
            isset( $this->options['id_number'] ) ? esc_attr( $this->options['id_number']) : ''
        );
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function title_callback()
    {
        printf(
            '<input type="text" id="title" name="my_option_name[title]" value="%s" />',
            isset( $this->options['title'] ) ? esc_attr( $this->options['title']) : ''
        );
        submit_button();
    }

    public function table_callback()
	{
		?>	
	    	<table id="table-call-tracker" border="1">
				<tr>
				 <th>Number</th>
				 <th>Date</th>
				 <th>URL</th>
				</tr>
				  <?php
				    global $wpdb;
				    $table_name = $wpdb->prefix . "all_calls";
				    $result = $wpdb->get_results ( "SELECT * FROM " . $table_name  );
				    foreach ( $result as $print )   {
				    ?>
				    <tr>
				    <td><?php echo $print->number;?></td>
				    <td><?php echo $print->date;?></td>
				    <td><?php echo $print->url;?></td>
				    </tr>
				        <?php }
	  ?>	</table>
	    <?php
	}

	/**
	 * Ajax response
	 */
	public function track_phone() {
	    // Make sure post has value
	    if( isset( $_POST['post_no'] ) ) {

	    	//Make sure time is set to GMT
	    	date_default_timezone_set("Europe/London");
	    	$date = date("Y-m-d H:i:s");
	    	global $wpdb;
	    	$table_name = $wpdb->prefix . 'all_calls';
	 		
	 		//Insert into database
	        if($wpdb->insert($table_name,array(
				'number'=>$_POST['post_no'],
				'url'=>$_POST['post_url'],
				'date'=>$date
				))===FALSE){

				echo "Error";

				}
				else {
				echo "Customer '".$date. "' successfully added, row ID is ".$wpdb->insert_id;

				};
	 
	    } // end if
	 
	    die();
	 
	} // end mark_as_read 

} // end class
 
new trackCalls();
?>
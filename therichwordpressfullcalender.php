<?php
	/*
	Plugin Name: TheRich WP Fullcalendar
	Plugin URI: https://wordpress.org/therich-wp-fullcalendar
	Description: This is plugin will help you to add your events, save and show it into fullcalendar.
	Version: 1.0.0
	Author: Therichpost
	Author URI: https://therichpost.com/wp-plugin-dev
	*/
	
	define( 'Theich_WF', '1.0.0' );
	define( 'Therich_WF' , plugin_dir_path( __FILE__ ));
	
	
	/**  Register Custom Post Type Event **/
	
	add_action( 'init', 'activate_myplugin' );
        function activate_myplugin() {

                $args=array(
					'labels' => array(
						'name'          => __( 'TheRich Events' ),
						'singular_name' => __( 'TheRich Event' ),
						'add_new_item'  => __( 'Add New Event') ,
						'edit_item'     => __( 'Edit Event' ),
						'search_items'  => __( 'Search Events' ),
						'add_new'       => __( 'Add New Event' ),
					),
					'public' => true,
					'show_ui' => true,
					'capability_type' => 'post',
					'hierarchical' => false,
					'rewrite' => array(
						'slug' => 'therichpostevent',
						'with_front' => false
						),
					'query_var' => true,
					'supports' => array(
						'title',
						'editor',
						'excerpt',
						'trackbacks',
						'custom-fields',
						'revisions',
						'thumbnail',
						'author',
						'page-attributes'
						)
            ); 
               register_post_type( 'therichevent', $args );

        }



        function myplugin_flush_rewrites() {
                activate_myplugin();
                flush_rewrite_rules();
        }

        register_activation_hook( __FILE__, 'myplugin_flush_rewrites' );

        register_uninstall_hook( __FILE__, 'my_plugin_uninstall' );
        function my_plugin_uninstall() {
          // Uninstallation stuff here
             unregister_post_type( 'therichevent' );
        }
		
		/**  TheRich Wordpress Fullcalnder **/
		
		///////////////////////////////////////
		    /** Add styles and scripts **/
			wp_register_script('rich_bootstrap_js', plugins_url('dist/bootstrap.min.js', __FILE__), '', filemtime( plugin_dir_path( __FILE__ ) . 'dist/bootstrap.min.js' ));
			wp_register_script('rich_moment', plugins_url('dist/moment.min.js', __FILE__), '', filemtime( plugin_dir_path( __FILE__ ) . 'dist/moment.min.js' ));
			wp_register_script('rich_fullCalendar', plugins_url('dist/fullCalendar.js', __FILE__), '', filemtime( plugin_dir_path( __FILE__ ) . 'dist/fullCalendar.js' ));
			
			wp_register_style( 'rich_bootstrap_css', plugins_url('dist/bootstrap.min.css', __FILE__), '', filemtime( plugin_dir_path( __FILE__ ) . 'dist/bootstrap.min.css' ));
			
			wp_register_style( 'rich_fullcalendar_css', plugins_url('dist/fullcalendar.min.css', __FILE__), '', filemtime( plugin_dir_path( __FILE__ ) . 'dist/fullcalendar.min.css' ));
			
			function therich_scripts_load() {
				wp_enqueue_style('rich_bootstrap_css');
				wp_enqueue_style('rich_fullcalendar_css');
				wp_enqueue_script('jquery'); //wp code jquery
				wp_enqueue_script('rich_bootstrap_js');
				wp_enqueue_script('rich_moment');
				wp_enqueue_script('rich_fullCalendar');
			}
			add_action( 'wp_enqueue_scripts', 'therich_scripts_load' );
			
			
			/** This is for datepicker in admin section **/
			function load_custom_wp_admin_style() {
					wp_register_script('rich_jqueryui_js', plugins_url('dist/jquery-ui.js', __FILE__), '', filemtime( plugin_dir_path( __FILE__ ) . 'dist/jquery-ui.js' ));

			        wp_register_style( 'rich_jqueryui_css', plugins_url('dist/jquery-ui.css', __FILE__), '', filemtime( plugin_dir_path( __FILE__ ) . 'dist/jquery-ui.css' ));
					wp_enqueue_script('rich_jqueryui_js');
					wp_enqueue_style( 'rich_jqueryui_css' );
					
			}
			add_action( 'admin_enqueue_scripts', 'load_custom_wp_admin_style' );
			
			
			
	  ///////////////////////////////////////
			
		
		function therichwordpressfullcalendar()
		{
			/** Get Events Data **/
				$associativeArray = array();
				$next_args = array(
					'post_type' => 'therichevent',
					'post_status' => 'publish',
					'posts_per_page'=>-1,
					'order'=>'DESC',
					'orderby'=>'ID'
					);
					$next_the_query = new WP_Query( $next_args );

					// The Loop
				   
				   
					if ( $next_the_query->have_posts() ) {
					while ( $next_the_query->have_posts() ) {
						
						$next_the_query->the_post();
						$associativeArray[] = array('id' => get_the_ID(), 'start' => date('Y-m-d', strtotime(get_post_meta( get_the_ID(), 'my_meta_box_event_start', TRUE ))), 'end' => date('Y-m-d', strtotime(get_post_meta( get_the_ID(), 'my_meta_box_event_end', TRUE ))), 'title' => get_the_title());
				   } }
				   
					wp_reset_postdata();
			
			/** Get Events Data **/
			
			/** Show Fullcalnder and Events Details In Modal Popup **/
			$html  = '<div class="container calendardiv"><div id="calendar"></div>
					<!--Event Data PoPup--->
					<div class="modal fade" id="eventdata" tabindex="-1" role="dialog" aria-labelledby="successModalLabel" aria-hidden="true">
						<div class="modal-dialog" role="document">
							<div class="modal-content">
							<div class="modal-body text-center">
							<div class="eventdetail"></div>
							<a class="btn btn-warning" href="#" data-dismiss="modal" aria-label="Close">Done</a>
							</div>
							</div>
						</div>
					</div>
					</div>';
					
					/** JS code to call fullCalendar and open title in modal popup **/
			$html .= "<script>
				jQuery(document).ready(function($) { 
				 $('#calendar').fullCalendar({  
					   header: {
						  left: 'prev,next today',
						  center: 'title',
						  right: 'agendaDay,agendaWeek,month'
						},
					   timeFormat: 'H:mm',
					   events: ".json_encode($associativeArray).",
					   eventClick: function(calEvent, jsEvent, view, resourceObj) {
						   console.log(calEvent);
						   $('#eventdata').modal('show');
						   $('.modal-body .eventdetail').html('');
						   $('.modal-body .eventdetail').html('<h1>'+calEvent.title+'</h1><br><strong>Event Start: </strong>'+moment(calEvent.start).format('YYYY-MM-DD')+'<br><strong>Event End: </strong>'+moment(calEvent.end).format('YYYY-MM-DD'));
						   }
				   });
				});
			</script>";
			return $html;
			
		}
		add_shortcode( 'TheRicHWordpressFullcalendar', 'therichwordpressfullcalendar' );
		/** Happy Coding **/
		
		/** Add Custom Fields Start and End date **/
		add_action( 'add_meta_boxes', 'therich_meta_box_add' );
		function therich_meta_box_add()
		{ 
			add_meta_box( 'therich-meta-box-id', 'Event Start Date & End Date', 'therich_meta_box', 'therichevent', 'normal', 'high' );
		}
		function therich_meta_box()
		{
				global $post;
				$values = get_post_custom( $post->ID );
				$eventstart = isset( $values['my_meta_box_event_start'] ) ? esc_attr( $values['my_meta_box_event_start'][0] ) : '';
				$eventend = isset( $values['my_meta_box_event_end'] ) ? esc_attr( $values['my_meta_box_event_end'][0] ) : '';
				wp_nonce_field( 'my_meta_box_nonce', 'meta_box_nonce' );
				?>
			    <script>
			    $( function() {
				 $( "#datepickerstart, #datepickerend" ).datepicker();
			    } );
			    </script>
				<p>
				<label for="my_meta_box_event_start">Event Start Date: </label>
				<input type="text" name="my_meta_box_event_start" id="datepickerstart" value="<?php echo $eventstart; ?>" />
				<span><strong>(Date must be equal or greater then today)</strong></span>
				</p>
				<p>
				<label for="my_meta_box_event_end">Event End Date:  </label>
				<input type="text" name="my_meta_box_event_end" id="datepickerend" value="<?php echo $eventend; ?>" />
				<span><strong>(Date must be greater then Event Start Date)</strong></span>
				</p>
				<?php    
		}

		add_action( 'save_post', 'therich_meta_box_save' );

		function therich_meta_box_save( $post_id )
		{
			// Bail if we're doing an auto save
			if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
			 
			// if our nonce isn't there, or we can't verify it, bail
			if( !isset( $_POST['meta_box_nonce'] ) || !wp_verify_nonce( $_POST['meta_box_nonce'], 'my_meta_box_nonce' ) ) return;
			 
			// if our current user can't edit this post, bail
			if( !current_user_can( 'edit_post' ) ) return;

			// now we can actually save the data
			$allowed = array( 
				'a' => array( // on allow a tags
					'href' => array() // and those anchors can only have href attribute
				)
			);
			 
			// Make sure your data is set before trying to save it
			if( isset( $_POST['my_meta_box_event_start'] ) )
				update_post_meta( $post_id, 'my_meta_box_event_start', wp_kses( $_POST['my_meta_box_event_start'], $allowed ) );

			if( isset( $_POST['my_meta_box_event_end'] ) )
				update_post_meta( $post_id, 'my_meta_box_event_end', wp_kses( $_POST['my_meta_box_event_end'], $allowed ) );
		}
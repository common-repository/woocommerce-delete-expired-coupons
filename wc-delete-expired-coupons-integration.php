<?php
/**
 * Integration Demo Integration.
 *
 * @package  WC_Integration_Demo_Integration
 * @category Integration
 * @author   Patrick Rauland
 */
 
if ( ! class_exists( 'WC_Delete_Expired_Coupons_Integration' ) ) :
 
class WC_Delete_Expired_Coupons_Integration extends WC_Integration {
	/**
     * Define how often this plugin should run.
     *
     * @access private
     * @var int
     */
    private $_run_delay;

    /**
     * Define the time to keep the expired coupons before to trash/delete them.
     *
     * @access private
     * @var int
     */
    private $_keeping_time;
 
	/**
	 * Init and hook in the integration.
	 */
	public function __construct() { 
		$this->id                 = 'delete_expired_coupons';
		$this->method_title       = __( 'Expired Coupons', 'woocommerce-delete-expired-coupons' );
		$this->method_description = __( 'Manage settings of automatic expired coupons deletion.', 'woocommerce-delete-expired-coupons' );
 
		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();
 
		// Define user set variables.
		$this->enable_deletion            = $this->get_option( 'enable_deletion' );
		$this->force_deletion             = $this->get_option( 'force_deletion' );
		$this->keep_expired_coupons       = $this->get_option( 'keep_expired_coupons' );
		$this->keep_expired_coupons_type  = $this->get_option( 'keep_expired_coupons_type' );
		$this->events_frequency           = $this->get_option( 'events_frequency' );
		$this->events_frequency_type      = $this->get_option( 'events_frequency_type' );

		$this->_calculate_frequency();
		$this->_calculate_keeping_time();
 
		// Actions.
		add_action( 'woocommerce_update_options_integration_delete_expired_coupons', array( $this, 'process_admin_options' ) );
		add_action( 'admin_init', array( $this, 'scheduled_run_notice_ignore' ) );

        if( 'yes' == $this->enable_deletion ) {
            // Let's start the game!
            add_action( 'init', array( $this, 'delete_expired_coupons' ) );
        }

        if ( ! get_transient( 'woocommerce_unable_to_delete_expired_coupons' ) ) {
            add_action( 'admin_notices', array( $this, 'undeleted_coupons_notice' ) );
        }
	}
 
	/**
	 * Initialize integration settings form fields.
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enable_deletion' => array(
				'title'       => __( 'Enable automatic deletion', 'woocommerce-delete-expired-coupons' ),
				'type'        => 'checkbox',
				'label'       => __( 'Enable', 'woocommerce-integration-demo' ),
				'default'     => 'no',
				'description' => __( 'Enable automatic deletion scheduled events.', 'woocommerce-delete-expired-coupons' ),
				'desc_tip'    => true
			),
			'force_deletion' => array(
				'title'       => __( 'Force deletion', 'woocommerce-delete-expired-coupons' ),
				'type'        => 'checkbox',
				'label'       => __( 'Force', 'woocommerce-delete-expired-coupons' ),
				'default'     => 'no',
				'description' => __( 'Force deletion instead of moving expired coupons in the trash. It recommended to leave this unchecked.', 'woocommerce-delete-expired-coupons' ),
				'desc_tip'    => true
			),
			'keep_expired_coupons' => array(
				'title'             => __( 'Keep expired coupons for', 'woocommerce-delete-expired-coupons' ),
				'type'              => 'number',
				'css'               => 'width:50px',
				'default'           => 0,
				'custom_attributes' => array(
					'min'     => 0,
					'max'     => 31
				),
				'description'       => __( 'Choose how many minutes, hours, etc. to keep the expired coupons before deletion.', 'woocommerce-delete-expired-coupons' ),
				'desc_tip'          => true
			),
			'keep_expired_coupons_type' => array(
				'title'    => __( 'Type of "Keep expired coupons for"', 'woocommerce-delete-expired-coupons' ),
				'type'     => 'select',
				'options'  => array(
					'minutes' => __( 'Minutes', 'woocommerce-delete-expired-coupons' ),
					'hours'   => __( 'Hours', 'woocommerce-delete-expired-coupons' ),
					'days'    => __( 'Days', 'woocommerce-delete-expired-coupons' ),
					'weeks'   => __( 'Weeks', 'woocommerce-delete-expired-coupons' ),
					'years'   => __( 'Years', 'woocommerce-delete-expired-coupons' )
				),
				'default'     => 'days',
				'description' => __( 'Keep expired coupons for the number of minutes, hours, days selected here.', 'woocommerce-delete-expired-coupons' ),
				'desc_tip'    => true
			),
			'events_frequency' => array(
				'title'             => __( 'Frequency', 'woocommerce-delete-expired-coupons' ),
				'type'              => 'number',
				'css'               => 'width:50px',
				'default'           => 1,
				'custom_attributes' => array(
					'min'     => 1,
					'max'     => 31
				),
				'description'       => __( 'Choose the frequency of the events. You can choose in the option below if this number must be considered as minutes, hours, days, etc.', 'woocommerce-delete-expired-coupons' ),
				'desc_tip'          => true
			),
			'events_frequency_type' => array(
				'title'    => __( 'Frequency type', 'woocommerce-delete-expired-coupons' ),
				'type'     => 'select',
				'options'  => array(
					'minutes' => __( 'Minutes', 'woocommerce-delete-expired-coupons' ),
					'hours'   => __( 'Hours', 'woocommerce-delete-expired-coupons' ),
					'days'    => __( 'Days', 'woocommerce-delete-expired-coupons' ),
					'weeks'   => __( 'Weeks', 'woocommerce-delete-expired-coupons' ),
					'years'   => __( 'Years', 'woocommerce-delete-expired-coupons' )
				),
				'default'     => 'days',
				'description' => __( 'The type of frequency to use.', 'woocommerce-delete-expired-coupons' ),
				'desc_tip'    => true
			)
		);
	}

	/**
     * Show a notice if some coupon can't be deleted.
     *
     * @return void
     */
    public function undeleted_coupons_notice() {
        $coupons_list = get_transient( 'woocommerce_unable_to_delete_expired_coupons' );

        if( ! empty( $coupons_list ) ) {
            $coupons_list = array_map( function( $c ) { return "<strong>$c</strong>";}, $coupons_list );
            $coupons_list = implode( ', ', $coupons_list );

            echo '<div class="error"><p>WooCommerce Delete Expired Coupons ' . sprintf( __('can\'t trash the following coupons: %s', 'woocommerce-delete-expired-coupons' ), $coupons_list ) . '</p></div>';
        }
    }

    /**
     * Delete expired coupons everytime somebody visit the web site
     *
     * @return void
     */
    public function delete_expired_coupons() {
        global $pagenow;

        if  ( //Do nothing if on new/edit coupon pages
            ( 'post-new.php' == $pagenow || 'post.php' == $pagenow || ( isset( $_GET['post_type'] ) && 'shop_coupon' == $_GET['post_type'] ) ) ||
            //Do nothing if in trash page
            ( 'edit.php' == $pagenow && isset( $_GET['post_status'] ) && 'trash' == $_GET['post_status'] ) ||
            //Do nothing if doing autosave
            ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ||
            //Doing AJAX
            ( is_admin() && defined('DOING_AJAX') && DOING_AJAX ) )
        {
            return;
        }


        $args = array(
            'posts_per_page' => -1,
            'post_type' => 'shop_coupon',
            'post_status' => 'publish',
            'meta_query' => array(
        		'relation' => 'AND',
        		array(
        			'key' => 'expiry_date',
        			'value' => current_time( 'Y-m-d' ),
        			'compare' => '<='
        		),
        		array(
        			'key' => 'expiry_date',
        			'value' => '',
        			'compare' => '!='
        		)
            )
        );

        $coupons = get_posts( $args );

        if ( ! empty( $coupons ) ) {

            if ( ! $this->_should_run() ) {
                add_action( 'admin_notices', array( $this, 'scheduled_run_notice' ) );
                return;
            }

            //Delete the undeleted coupons transient
            delete_transient( 'woocommerce_unable_to_delete_expired_coupons' );
            $unable_to_delete = array();
            foreach( $coupons as $coupon ) {
            	//Check if the expired coupons should be kept for X time and if this time expired, then trash/delete them
            	$current_time = current_time( 'timestamp' );
            	if ( $current_time - $this->_keeping_time >= strtotime( get_post_meta( $coupon->ID, 'expiry_date', true ) ) ) {
	                if ( 'no' === $this->force_deletion ) {
	                	//Trash coupon
	                    if ( false === wp_trash_post( $coupon->ID ) ) {
	                        $unable_to_delete[ $coupon->ID ] = $coupon->post_title;
	                    }
	                } else {
	                	//Delete coupon
	                    if ( false === wp_delete_post( $coupon->ID ) ) {
	                        $unable_to_delete[ $coupon->ID ] = $coupon->post_title;
	                    }
	                }
	            }
            }

            if ( ! empty( $unable_to_delete ) ) {
                set_transient( 'woocommerce_unable_to_delete_expired_coupons', $unable_to_delete );
            }
        }
    }

    /**
     * Add a notice to let you know about the next scheduled run and if there are undeleted expired coupons.
     *
     * @return void
     */
    public function scheduled_run_notice() {
    	global $current_user;
    	$user_id = $current_user->ID;

    	if ( ! get_user_meta( $user_id, 'scheduled_run_notice_ignore' ) ) {
	        $next_run = date( wc_date_format(), get_transient( 'woocommerce_delete_expired_coupons_last_run' ) + $this->_run_delay );
	        echo '<div class="error"><p>' . sprintf( __( 'There are expired coupons which will be automatically deleted on %1$s. Please visit the <a href="%2$s">settings page here</a> to change the expiry date if required, or manually delete them. | <a href="%3$s" title="Dismiss">Dismiss</a>', 'woocommerce-delete-expired-coupons' ), $next_run, admin_url( 'edit.php?post_type=shop_coupon' ), '?scheduled_run_notice_ignore=yes' ) . '</p></div>';
	    }
    }

    /**
     * Dismiss the admin scheduled run notice.
     *
     * @return void
     */
	public function scheduled_run_notice_ignore() {
		global $current_user;
	        $user_id = $current_user->ID;
	        /* If user clicks to ignore the notice, add that to their user meta */
	        if ( isset($_GET['scheduled_run_notice_ignore']) && 'yes' == $_GET['scheduled_run_notice_ignore'] ) {
	             add_user_meta($user_id, 'scheduled_run_notice_ignore', 'true', true);
		}
	}
 	
 	/**
     * Check if the run delay passed or not.
     *
     * @access private
     * @return bool
     */
    private function _should_run() {
        $last_run     = get_transient( 'woocommerce_delete_expired_coupons_last_run' );
        $current_time = current_time( 'timestamp' );

        if ( false === $last_run || $current_time - $this->_run_delay >= $last_run ) {
            set_transient( 'woocommerce_delete_expired_coupons_last_run', $current_time );
            return true;
        }

        return false;
    }

    /**
     * Calculate the keeping time.
     *
     * @access private
     * @return void
     */
    private function _calculate_keeping_time() {
    	$keep_expired_coupons      = $this->keep_expired_coupons;
        $keep_expired_coupons_type = $this->keep_expired_coupons_type;

        switch( $keep_expired_coupons_type ) {
            case 'minutes': $this->_keeping_time = MINUTE_IN_SECONDS * $keep_expired_coupons; break;
            case 'hours':   $this->_keeping_time = HOUR_IN_SECONDS * $keep_expired_coupons; break;
            case 'days':    $this->_keeping_time = DAY_IN_SECONDS * $keep_expired_coupons; break;
            case 'weeks':   $this->_keeping_time = WEEK_IN_SECONDS * $keep_expired_coupons; break;
            case 'years':   $this->_keeping_time = YEAR_IN_SECONDS * $keep_expired_coupons; break;
        }
    }

    /**
     * Calculate the events frequency.
     *
     * @access private
     * @return void
     */
    private function _calculate_frequency() {
        $frequency      = $this->events_frequency;
        $frequency_type = $this->events_frequency_type;

        switch( $frequency_type ) {
            case 'minutes': $this->_run_delay = MINUTE_IN_SECONDS * $frequency; break;
            case 'hours':   $this->_run_delay = HOUR_IN_SECONDS * $frequency; break;
            case 'days':    $this->_run_delay = DAY_IN_SECONDS * $frequency; break;
            case 'weeks':   $this->_run_delay = WEEK_IN_SECONDS * $frequency; break;
            case 'years':   $this->_run_delay = YEAR_IN_SECONDS * $frequency; break;
        }
    }
}
 
endif;
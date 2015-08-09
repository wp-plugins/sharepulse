<?php
/*
	Plugin Name: SharePulse
	Plugin URI: http://sharepulse.net/
	Description: SharePulse ranks in a sidebar widget your site&#39;s posts which have had the greatest social impact. Stats are tabulated from post comment counts, Twitter, LinkedIn and Facebook APIs.
	Author: Jack Reichert
	Version: 3.2
	Author URI: http://www.jackreichert.com/
	License: GPLv2
*/

$sharepulse = new SharePulse();

add_action( 'widgets_init', 'register_sharepulse_widget' );
function register_sharepulse_widget() {
	register_widget( 'SharePulse_widget' );
}

class SharePulse {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'wp_ajax_sharepulse-build-stats-admin', array( $this, 'build_stats_admin_ajax' ) );
		add_action( 'wp_ajax_sharepulse-build-stats', array( $this, 'build_stats_ajax' ) );
		add_action( 'wp_ajax_nopriv_sharepulse-build-stats', array( $this, 'build_stats_ajax' ) );
		add_action( 'wp_ajax_sharepulse-build-done', array( $this, 'build_stats_done' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'buildstats_scripts' ) );
	}

	public function buildstats_scripts() {
		if ( is_singular() ) {
			global $post;
			wp_enqueue_script( 'sharepulse-single-build-stats', plugin_dir_url( __FILE__ ) . '/js/build-single.js', array( 'jquery' ) );
			wp_localize_script( 'sharepulse-single-build-stats', 'sp_Ajax', array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'spNonce' => wp_create_nonce( 'sp-ajax-nonce' ),
				'id'      => $post->ID
			) );
		}
	}

	public function add_plugin_page() {
		// This page will be under "Settings"
		add_menu_page( 'SharePulse Stats', 'SharePulse', 'edit_others_posts', 'sharepulse', array(
			$this,
			'main_admin_page'
		), 'dashicons-share', 61 );
		add_submenu_page( 'sharepulse', 'Build SharePulse Stats', 'Build Stats', 'manage_options', 'sharepulse-build', array(
			$this,
			'rebuild_stats_page'
		) );
	}

	public function main_admin_page() { ?>
		<h1>SharePulse Stats</h1>
		<p><a href="http://sharepulse.net/contact/" target="_blank">What features would you like to see?</a></p>
		<p>Don't see stats? <a href="<?php echo admin_url( 'admin.php?page=sharepulse-build' ); ?>">Build them.</a></p>
		<?php
		$range = array( 'day', 'week', 'month', 'year', 'all' );
		foreach ( $range as $r ) :
			$data[ $r ] = SharePulse::get_stats( $r, 5, 'no' ); ?>
			<div class="SharePulse_widget">
				<h3><?php echo ucfirst( $r ); ?></h3>
				<?php if ( count( $data[ $r ] ) > 0 ) : ?>
					<?php foreach ( $data[ $r ] as $d ) : ?>
						<div class="SharePulse">
							<div class="total"><?php echo $d['total']; ?></div>
							<span class="title"><a
									href="<?php echo get_permalink( $d['id'] ); ?>"><?php echo get_the_title( $d['id'] ); ?></a></span>

							<div class="stats">
								&bull;&nbsp;Twitter:&nbsp;<?php echo $d['twitter']; ?>
								&bull;&nbsp;Facebook:&nbsp;<?php echo $d['facebook']; ?>
								&bull;&nbsp;Linkedin:&nbsp;<?php echo $d['linkedin']; ?>
								&bull;&nbsp;Comments:&nbsp;<?php echo $d['comments']; ?>
							</div>
						</div>
					<?php endforeach; ?>
				<?php else: ?>
					<h4>No Stats</h4>
				<?php endif; ?>
			</div>
			<?php
		endforeach;
	}

	public static function get_stats( $range = 'ALL', $limit = 5, $include_comments = true ) {
		global $wpdb;

		$join   = $where = "";
		$ranges = array( 'DAY', 'WEEK', 'MONTH', 'YEAR' );

		if ( in_array( strtoupper( $range ), $ranges ) ) {
			$join = "
				JOIN {$wpdb->posts} p
				ON pm.post_id = p.ID
			";

			$where = "
				AND post_date BETWEEN DATE_SUB( NOW(), INTERVAL 1 $range ) AND NOW()
                AND p.post_status = 'publish'
			";
		}

		$query = $wpdb->get_results( "
			SELECT DISTINCT pm.post_id, pm.meta_value FROM {$wpdb->postmeta} pm
			$join
			WHERE meta_key = 'SharePulse'
			$where
			ORDER BY meta_value + 0 DESC
			LIMIT $limit;
		" );

		$response = array();

		foreach ( $query as $i => $res ) {
			$response[ $i ]['id']    = $res->post_id;
			$response[ $i ]['title'] = get_the_title( $res->post_id );
			list( $total, $therest ) = explode( '_', $res->meta_value );
			$response[ $i ]['total'] = intval( $total );

			if ( $include_comments ) {
				$response[ $i ]['comments'] = get_comments_number( $res->post_id );
				$response[ $i ]['total'] += $response[ $i ]['comments'];
			}

			$decoded = json_decode( base64_decode( substr( $res->meta_value, strpos( $res->meta_value, '_' ) + 1 ) ) );
			foreach ( $decoded as $key => $stat ) {
				$response[ $i ][ $key ] = $stat;
			}
		}

		usort( $response, array( 'SharePulse', 'stats_cmp' ) );

		return $response;
	}

	private static function stats_cmp( $a, $b ) {
		if ( $a['total'] == $b['total'] ) {
			return 0;
		}

		return ( $a['total'] > $b['total'] ) ? - 1 : 1;
	}

	public function build_stats_done() {
		$options         = $this->get_sp_options();
		$options->status = 'done';
		$this->set_sp_options( $options );

		header( "Content-Type: application/json" );
		echo json_encode( 'done' );
		exit();
	}

	public function build_stats_admin_ajax() {
		$nonce = $_POST['spNonce'];
		if ( ! wp_verify_nonce( $nonce, 'sp-ajax-nonce' ) ) {
			die( 'Busted!' );
		}

		$options         = $this->get_sp_options();
		$options->status = time();

		$this->set_sp_options( $options );

		$id = intval( $_POST['id'] );
		if ( 0 < $id ) {
			$response = $this->get_counts( $id );
			$this->set_counts( $response );
		}

		header( "Content-Type: application/json" );
		echo json_encode( $response );
		exit();
	}

	public function build_stats_ajax() {
		$nonce = $_POST['spNonce'];
		if ( ! wp_verify_nonce( $nonce, 'sp-ajax-nonce' ) ) {
			die( 'Busted!' );
		}
		$id = intval( $_POST['id'] );

		$response = "too soon";
		if ( intval( get_post_meta( $id, 'sp_last_updated', true ) ) < ( time() - 300 ) ) {
			update_post_meta( $id, 'sp_last_updated', time() );
			$response = SharePulse::get_counts( $id );
			SharePulse::set_counts( $response );
		}

		header( "Content-Type: application/json" );
		echo json_encode( $response );
		exit();
	}

	public function rebuild_stats_page() {
		settings_fields( 'SharePulse' );
		do_settings_sections( 'SharePulse' );

		wp_enqueue_style( 'jquery-ui-progressbar-css', '//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css' );

		$options = $this->get_sp_options();

		$args = array(
			'orderby'                => 'comment_count',
			'posts_per_page'         => - 1,
			'cache_results'          => false,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false
		);

		$args = apply_filters( 'sp_get_posts_args', $args );

		$ids = get_posts( $args );

		wp_enqueue_script( 'sharepulse-build-stats', plugin_dir_url( __FILE__ ) . '/js/rebuild.js', array(
			'jquery-ui-progressbar',
			'jquery',
			'underscore'
		) );
		wp_localize_script( 'sharepulse-build-stats', 'sp_Ajax', array(
			'ajaxurl'      => admin_url( 'admin-ajax.php' ),
			'spNonce'      => wp_create_nonce( 'sp-ajax-nonce' ),
			'rebuild_list' => json_encode( wp_list_pluck( $ids, 'ID' ) )
		) );
		wp_enqueue_style( 'sharepulse-build-stats-css', plugin_dir_url( __FILE__ ) . '/css/rebuild.css' );

		$buildText = ( ! isset( $options->status ) || 'done' != $options->status ) ? 'Build' : 'Rebuild'; ?>

		<div class="wrapper">
			<h1><?php echo $buildText; ?> SharePulse Stats</h1>

			<h2 id="statsTitle"><?php echo ( isset( $options->status ) && 'done' != $options->status ) ? 'The last build did not complete successfully.' : ''; ?></h2>

			<p id="buildAlert"<?php echo ( isset( $options->status ) && 'done' != $options->status ) ? ' style="color:orange;font-weight:bold;"' : ''; ?>>
				Please make sure not to leave this page until the process is complete.</p>

			<div id="progressbar"></div>
			<p>
				<button id="rebuild" class="button button-primary"><?php echo $buildText; ?> Now</button>
			</p>
			<ul id="progress"></ul>
		</div>

		<?php
	}

	private static function get_services() {
		// services SharePulse queries
		$services = array(
			'twitter'  => array(
				'url'      => 'http://urls.api.twitter.com/1/urls/count.json?callback=sp&url=%s',
				'cnt_name' => 'count'
			),
			'facebook' => array(
				'url'      => 'https://graph.facebook.com/?callback=sp&id=%s',
				'cnt_name' => 'shares'
			),
			'linkedin' => array(
				'url'      => 'http://www.linkedin.com/countserv/count/share?callback=sp&format=jsonp&url=%s',
				'cnt_name' => 'count'
			)
		);

		return apply_filters( 'sp_add_service', $services );
	}

	public static function set_counts( $stats ) {
		$id    = $stats['id'];
		$total = $stats['total'];
		unset( $stats['id'] );
		unset( $stats['title'] );
		unset( $stats['total'] );
		$hexed = $total . '_' . base64_encode( json_encode( $stats ) );
		add_post_meta( $id, 'SharePulse', $hexed, true ) || update_post_meta( $id, 'SharePulse', $hexed );
	}

	public static function get_counts( $id ) {
		$url = get_permalink( $id );

		$services = self::get_services();
		time_nanosleep( 0, 250000000 );
		// var
		$total = 0;
		$stats = array(
			'id'       => $id,
			'title'    => get_the_title( $id ),
			'twitter'  => 0,
			'facebook' => 0,
			'linkedin' => 0
		);

		// queries services
		foreach ( $services as $name => $service ) {
			$regex  = sprintf( '#"%s":(\d+)#', $service['cnt_name'] );
			$result = wp_remote_get( sprintf( $service['url'], rawurlencode( $url ) ), array( 'timeout' => 10 ) );

			if ( is_wp_error( $result ) ) {
				$error_string = $result->get_error_message();
				error_log( "Please submit the following error to sharepulse_error@jackreichert.com -- $error_string" );
			} else {
				preg_match_all( $regex, $result['body'], $matches );
				$stats[ $name ] = isset( $matches[1][0] ) ? intval( $matches[1][0] ) : 0;
				$total += $stats[ $name ];
			}
		}

		// total
		$stats['total'] = $total;

		return $stats;
	}

	private function get_sp_options() {
		$options = get_option( 'sharepulse' );
		if ( ! $options || is_array( $options ) ) {
			$options = 'eyJzdGF0dXMiOiIifQ==';
		}

		return json_decode( base64_decode( $options ) );
	}

	private function set_sp_options( $options ) {
		update_option( 'sharepulse', base64_encode( json_encode( $options ) ) );
	}

}

class SharePulse_widget extends WP_Widget {
	// The widget construct. Initiating our plugin data.
	function sharePulse_widget() {
		$widgetData = array(
			'classname'   => 'SharePulse_widget',
			'description' => __( 'rank&#39;s, in a sidebar widget, your site&#39;s most popular articles.' )
		);
		$this->WP_Widget( 'sharePulse_widget', __( 'SharePulse' ), $widgetData );
		wp_enqueue_style( 'sharepulse-build-stats-css', plugin_dir_url( __FILE__ ) . '/css/sharepulse.css' );
	}

	// Displays the widget on the screen.
	function widget( $args, $instance ) {
		extract( $args );
		echo $before_widget;
		echo $before_title . $instance['title'] . $after_title;
		$this->SharePulse_display( $instance );
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) { // Updates the settings.
		return $new_instance;
	}

	function form( $instance ) {    // The admin form.
		$defaults = array( 'title' => 'SharePulse', 'date_range' => 'all', 'amount' => '4', 'linklove' => 'yes' );
		$instance = wp_parse_args( $instance, $defaults ); ?>
		<div id="sharePulse-admin-panel">
			<p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>">Widget title:</label>
				<input type="text" class="widefat" name="<?php echo $this->get_field_name( 'title' ); ?>"
				       id="<?php echo $this->get_field_id( 'title' ); ?>" value="<?php echo $instance['title']; ?>"/>
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'date_range' ); ?>">Date range:</label>
				<select name="<?php echo $this->get_field_name( 'date_range' ); ?>"
				        id="<?php echo $this->get_field_id( 'date_range' ); ?>">
					<option value="day" <?php echo( ( $instance['date_range'] == 'day' ) ? 'selected="true"' : '' ); ?>>
						Day
					</option>
					<option
						value="week" <?php echo( ( $instance['date_range'] == 'week' ) ? 'selected="true"' : '' ); ?>>
						Week
					</option>
					<option
						value="month" <?php echo( ( $instance['date_range'] == 'month' ) ? 'selected="true"' : '' ); ?>>
						Month
					</option>
					<option
						value="year" <?php echo( ( $instance['date_range'] == 'year' ) ? 'selected="true"' : '' ); ?>>
						Year
					</option>
					<option value="all" <?php echo( ( $instance['date_range'] == 'all' ) ? 'selected="true"' : '' ); ?>>
						All Time
					</option>
				</select>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'amount' ); ?>">Number of posts to display:</label>
				<input type="text" size="2" name="<?php echo $this->get_field_name( 'amount' ); ?>"
				       id="<?php echo $this->get_field_id( 'amount' ); ?>" value="<?php echo $instance['amount']; ?>"/>
			</p>

			<p style="font-size:11px; text-align:left;">
				<label for="<?php echo $this->get_field_id( 'linklove' ); ?>">Linkback to show your thanks? </label>
				<select name="<?php echo $this->get_field_name( 'linklove' ); ?>"
				        id="<?php echo $this->get_field_id( 'linklove' ); ?>">
					<option value="yes" <?php echo( ( $instance['linklove'] != 'no' ) ? 'selected="true"' : '' ); ?>>
						Yes
					</option>
					<option value="no" <?php echo( ( $instance['linklove'] == 'no' ) ? 'selected="true"' : '' ); ?>>No
					</option>
				</select>
			</p>
			<p style="font-size:9px;"><a href="http://sharepulse.net" target="_blank">SharePulse</a> was developed by <a
					href="http://www.jackreichert.com" target="_blank">Jack Reichert</a> is licensed under <a
					href="http://codex.wordpress.org/GPL" target="_blank">GPLv2</a>.</p>
		</div>
		<?php
	}

	function SharePulse_display( $instance ) {
		$data = SharePulse::get_stats( $instance['date_range'], $instance['amount'], true );
		foreach ( $data as $i => $value ): ?>
			<div class="SharePulse">
				<div class="total"><?php echo $value['total']; ?></div>
				<span class="title"><a
						href="<?php echo get_permalink( $value['id'] ); ?>"><?php echo get_the_title( $value['id'] ); ?></a></span>

				<div class="stats">
					<?php echo ( 0 < $value['comments'] ) ? "&bull;&nbsp;Comments:&nbsp;{$value['comments']}" : ''; ?>
					<?php echo ( 0 < $value['twitter'] ) ? "&bull;&nbsp;Twitter:&nbsp;{$value['twitter']}" : ''; ?>
					<?php echo ( 0 < $value['facebook'] ) ? "&bull;&nbsp;Facebook:&nbsp;{$value['facebook']}" : ''; ?>
					<?php echo ( 0 < $value['linkedin'] ) ? "&bull;&nbsp;LinkedIn:&nbsp;{$value['linkedin']}" : ''; ?>
				</div>
			</div>
		<?php endforeach;
		echo ( $instance['linklove'] == 'yes' ) ? '<h5>Powered by: <a href="http://sharepulse.net" title="SharePulse" target="_blank">SharePulse</a></h5>' : '';
	}

}
<?php
/*
	Plugin Name: Share Pulse
	Plugin URI: http://www.jackreichert.com/plugins/sharepulse/
	Description: SharePulse rank&#39;s, in a sidebar widget, your site&#39;s most popular articles of the week. Stats are tabulated from most commented posts as well as the Twitter and Facebook APIs.
	Author: Jack Reichert
	Version: 2.0.1
	Author URI: http://www.jackreichert.com/
	License: GPLv2

  Copyright 2010  Jack Reichert  (email : contact@jackreichert.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, visit http://codex.wordpress.org/GPL    
    or write to the Free Software Foundation, Inc., 
    51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
    
*/ 
class sharePulse_widget extends WP_Widget {

	function sharePulse_widget() { 	// The widget construct. Initiating our plugin data.
		$widgetData = array( 'classname' => 'sharePulse_widget', 'description' => __( 'rank&#39;s, in a sidebar widget, your site&#39;s most popular articles of the week.' ) );
		$this->WP_Widget('sharePulse_widget', __('Share Pulse'), $widgetData);

	} 

	// Displays the widget on the screen.
	function widget($args, $instance) { 
		extract($args);
		$data  = get_option('sharePulse'); 
		// wont show any widget if there is no data in the selected date range. Notifies use in widget backend.
		if (count($data[$instance['date_range']]) != 0){ 
			echo $before_widget;
			echo $before_title . $instance['title'] . $after_title; 
			$this->SharePulse_display($instance);
			echo $after_widget;
		}
	}
	
	function update($new_instance, $old_instance) { // Updates the settings.
		return $new_instance;
	}
	
	function form($instance) {	// The admin form. 
		$defaults = array( 'title' => 'Share Pulse', 'via' => 'SharePulse', 'awesm_key' => '', 'date_range' => 'all', 'amount' => '4', 'linklove' => 'yes' );
		$data  = get_option('sharePulse'); 
		$instance = wp_parse_args($instance, $defaults); ?>
		<div id="sharePulse-admin-panel">
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>">Widget title:</label>
				<input type="text" class="widefat" name="<?php echo $this->get_field_name('title'); ?>" id="<?php echo $this->get_field_id('title'); ?>" value="<?php echo $instance['title']; ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('date_range'); ?>">Date range:</label>
				<select name="<?php echo $this->get_field_name('date_range'); ?>" id="<?php echo $this->get_field_id('date_range'); ?>">
					<option value="day" <?php echo (($instance['date_range'] == 'day') ? 'selected="true"' : '' ); ?>>Day</option>
					<option value="week" <?php echo (($instance['date_range'] == 'week') ? 'selected="true"' : '' ); ?>>Week</option>
					<option value="month" <?php echo (($instance['date_range'] == 'month') ? 'selected="true"' : '' ); ?>>Month</option>
					<option value="all" <?php echo (($instance['date_range'] == 'all') ? 'selected="true"' : '' ); ?>>All Time</option>
				</select><br/>
				<?php if (count($data[$instance['date_range']]) == 0){ echo 'There is not enough data to display the results for the date range "'.$instance['date_range'].'".'; } ?>
			</p>			
			<p>
				<label for="<?php echo $this->get_field_id('amount'); ?>">Number of posts to display:</label>
				<input type="text" size="2" name="<?php echo $this->get_field_name('amount'); ?>" id="<?php echo $this->get_field_id('amount'); ?>" value="<?php echo $instance['amount']; ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('via'); ?>">Tweeted via: (@myHandle)</label>
				<input type="text" class="widefat" name="<?php echo $this->get_field_name('via'); ?>" id="<?php echo $this->get_field_id('via'); ?>" value="<?php echo $instance['via']; ?>" />
			</p>	
			<p>
				<label for="<?php echo $this->get_field_id('awesm_key'); ?>">Awe.sm API key:</label>
				<input type="text" class="widefat" name="<?php echo $this->get_field_name('awesm_key'); ?>" id="<?php echo $this->get_field_id('awesm_key'); ?>" value="<?php echo $instance['awesm_key']; ?>" />
			</p>	
			<p style="font-size:11px; text-align:left;">
				<label for="<?php echo $this->get_field_id('linklove'); ?>">Linkback to show your thanks? </label>			
				<select name="<?php echo $this->get_field_name('linklove'); ?>" id="<?php echo $this->get_field_id('linklove'); ?>">
					<option value="yes" <?php echo (($instance['linklove'] != 'no') ? 'selected="true"' : '' ); ?>>Yes</option>
					<option value="no" <?php echo (($instance['linklove'] == 'no') ? 'selected="true"' : '' ); ?>>No</option>
				</select>
			</p>			
			<p style="font-size:9px;"><a href="http://www.jackreichert.com/plugins/sharepulse/" target="_blank">SharePulse</a> was developed by <a href="http://www.jackreichert.com" target="_blank">Jack Reichert</a> is licensed under <a href="http://codex.wordpress.org/GPL" target="_blank">GPLv2</a>.<br/><br/>
			Twitter stats powered by: <a href="http://topsy.com" title="Topsy" target="_blank"><img src="http://corp.topsy.com/wp-content/uploads/2010/10/powered_v3_92.png" align="right" alt="Topsy" /></a></p>
		</div>
<?php	} 
	
		function SharePulse_display($instance){
			$data = get_option('sharePulse'); 
			$curLenth = count($data[$instance['date_range']]);
			$numPosts = (($curLenth < $instance['amount']) ? $curLenth : $instance['amount']); // Checks to see if there are enough to display
			for ( $i=0; $i < $numPosts; $i++ ): 
				$url_title = urlencode($data[$instance['date_range']][$i]['title']); 
				$url_permalink = urlencode($data[$instance['date_range']][$i]['url']);
				$api_key = ( $instance['awesm_api'] != '' )?'api_key='.$instance['awesm_api'].'&':'';
				$awesm = 'http://create.awe.sm/url/share?'.$api_key.'version=1&amp;share_type=twitter&amp;create_type=sharelink&amp;target='.$url_permalink.'&amp;destination=http://twitter.com/?status=RT+%40'.$instance['via'].'+'.$url_title.'+AWESM_TARGET'; ?>
				<div class="SharePulse">
					<div class="total"><?php echo $data[$instance['date_range']][$i]['total']; ?></div>
					<span class="title"><a href="<?php echo $data[$instance['date_range']][$i]['url']; ?>"><?php echo $data[$instance['date_range']][$i]['title']; ?></a></span>
					<div class="stats">
						 &bull;&nbsp;<a href="<?php echo $data[$instance['date_range']][$i]['url']; ?>#comments" title="leave a comment">Comments:&nbsp;<?php echo $data[$instance['date_range']][$i]['comments']; ?></a> 
						 &bull;&nbsp;<a href="<?php echo $awesm; ?>" target="_blank" title="tweet this">Twitter:&nbsp;<?php echo $data[$instance['date_range']][$i]['tweets']; ?></a>
						 &bull;&nbsp;<a href="http://www.facebook.com/sharer.php?u=<?php echo $url_permalink; ?>&amp;t=<?php echo $url_title; ?>" target="_blank" title="share on facebook">Facebook:&nbsp;<?php echo $data[$instance['date_range']][$i]['fb']; ?></a>
					</div>
				</div>
	<?php	endfor;
		echo ($instance['linklove']=='yes')?'<h5>Powered by: <a href="http://www.jackreichert.com/plugins/sharepulse/" title="Share Pulse" target="_blank">SharePulse</a></h5>':'<h5 class="SharePulse">Powered by: SharePulse</h5>';
		}
	
}


class sharePulse_getData {
	function sharePulse_getData(){
		$this->url 	= ereg_replace("(https?)://(www.)", "", get_bloginfo('url'));
		$this->date_range = array('day','week','month','all');
		$tweets 	= $this->getTopTweets($this->url);
		$comments 	= $this->getTopComments();
		$combined 	= $this->combineStats($tweets,$comments);
		$combined['everything']	= $this->getMissingFbShares($combined['everything']);
		$combined['everything'] = $this->getTotals($combined['everything']);
		foreach ($combined as $key => $value ){ 
			$combined[$key] = $this->fillInMissing($combined[$key],$combined['everything']);
			$combined[$key]	= $this->sortStats($combined[$key]);
		}
		update_option('sharePulse', $combined);
	}
	
	/** Topsy **/
	function getTopTweets($url){
		
		foreach ($this->date_range as $r){
			$rs = substr($r,0,1);
			$results = $this->topsyAPI($url,$rs);
			$tweets[$r] = $this->extractTwitterTags($results);

		}
		
	return $tweets;	
	}
	function topsyAPI($url,$window){
		$request = 'http://otter.topsy.com/search.json?q=site:'.$url.'&window='.$window; ; 
		try {
		    $result = json_decode(file_get_contents(stripslashes($request)));
		} catch (Exception $e) {
		  //  echo 'Caught exception: ',  $e->getMessage(), "\n";
		  $result = array();
		}
		
	
	return $result;
	}
	function get_twCount_by_url($url) {
		$reqUrl = 'http://otter.topsy.com/stats.json?url='.urlencode( $url );
		try {
		    $topsy = file_get_contents($reqUrl);
		} catch (Exception $e) {
		  //  echo 'Caught exception: ',  $e->getMessage(), "\n";
		  $result = array();
		}		
		$topsy = json_decode($topsy);
		$tw_Num = (int) $topsy->response->all;
		
	return $tw_Num;
	}
	function extractTwitterTags($tweets) {
		foreach( $tweets->response->list as $key=>$story ){ //extracts relevant tags
			$tweetStories[$key]['title'] = $story->title;
			$tweetStories[$key]['comments'] = 0;
			$tweetStories[$key]['tweets'] = $story->hits;
			$tweetStories[$key]['id'] = url_to_postid($story->url);
			$tweetStories[$key]['url'] = ($tweetStories[$key]['id'] != 0) ? get_permalink($tweetStories[$key]['id']) : $story->url;			
			if ($tweetStories[$key]['id'] > 0) { $tweetStories[$key]['title'] = get_the_title($tweetStories[$key]['id']); }
		}	
		if ($tweetStories == NULL){ $tweetStories[]=array('title'=>'empty','url'=>'','comments'=>'','tweets'=>'','id'=>''); }
	return $tweetStories;		
	}	
	
	
	/** Facebook **/
	function get_fbCount_by_url($url) {
		$reqUrl = 'http://api.facebook.com/restserver.php?method=links.getStats&urls='.urlencode( $url );
		try {
		    $facebook_share = simplexml_load_file($reqUrl);
		} catch (Exception $e) {
		  //  echo 'Caught exception: ',  $e->getMessage(), "\n";
		  $result = array();
		}		
		$fb_Num = (int)$facebook_share->link_stat->total_count;
	
	return $fb_Num;
	}	
	function getMissingFbShares($data){
		foreach ($data as $key => $story){
			try{ $data[$key]['fb'] = $this->get_fbCount_by_url($story['url']); }
			catch(Exception $e){ $data[$key]['fb'] = 0; }
		}	
		
	return $data;
	}
	
	
	/** Comments **/
	function getTopComments(){
		foreach ($this->date_range as $r){		
			$results = $this->mostCommented(10,$r);
			$comments[$r] = $this->extractCommentTags($results);
		}
	return $comments;	
	}
	function mostCommented($no_posts = '10', $duration='month') {
		global $wpdb;
		// duration should be DAY WEEK MONTH OR YEAR
		$querystr = "SELECT comment_count, ID, post_title FROM $wpdb->posts wposts, $wpdb->comments wcomments WHERE wposts.ID = wcomments.comment_post_ID AND wposts.post_status='publish' AND wcomments.comment_approved='1' ".(($duration != 'all') ? "AND wcomments.comment_date > DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 ".$duration.")" : "")." GROUP BY wposts.ID ORDER BY comment_count DESC LIMIT 0 ,  10";

		$most_commented = $wpdb->get_results($querystr);

	return $most_commented;
	}
	function getSingleComments($postID) {
		global $wpdb;
		$pop = $wpdb->get_results("SELECT post_title, comment_count FROM {$wpdb->prefix}posts WHERE id=".$postID);

	return intval($pop[0]->comment_count);
	}	
	function extractCommentTags($comments){
		$i=0;
		foreach($comments as $comment){
			$data[$i]['title'] = $comment->post_title;
			$data[$i]['comments'] = intval($comment->comment_count);
			$data[$i]['tweets'] = 0;
			$data[$i]['id'] = $comment->ID;
			$data[$i]['url'] = get_permalink($comment->ID);
			$i++;
		}
		if ($data == NULL){ $data[]=array('title'=>'empty','url'=>'','comments'=>'','tweets'=>'','id'=>''); }
	return $data;
	}
	
	
	/** Tabulate **/
	function combineStats($tweets,$comments){
	
		foreach ($this->date_range as $r){	
	
			foreach($tweets[$r] as $tweetKey => $tweetValue){
				if ($tweetValue['title'] === 'empty'){ unset($tweets[$r][$tweetKey]); }
				foreach($comments[$r] as $commentKey => $commentValue){
					if( strstr($tweetValue['title'],$commentValue['title'])!=false ){
						$comments[$r][$commentKey]['tweets'] = $tweetValue['tweets'];
						unset($tweets[$r][$tweetKey]);
					}
					similar_text($commentValue['title'], $tweetValue['title'],$sim);
					if ($sim > 70){
						$comments[$r][$commentKey]['tweets'] = $tweetValue['tweets'];
						unset($tweets[$r][$tweetKey]);
					}
					if ($commentValue['title'] === 'empty'){ unset($comments[$r][$commentKey]); }
				}
			}
			$combined[$r] = array_merge($comments[$r], $tweets[$r]);
		}
		
			$combined['everything'] = array_merge_recursive($combined['day'],$combined['week'],$combined['month'],$combined['all']);
			foreach ($combined['everything'] as $key => $val){
				foreach ($combined['everything'] as $sKey => $sVal){
					similar_text($val['title'], $sVal['title'],$sim);
					if ($sim > 70  && $key != $sKey){
						unset($combined['everything'][$key]);
					}
				}
			}

		foreach($combined['everything'] as $comKey => $comValue){		
			if (intval($comValue['tweets']) == 0){
				try { $combined['everything'][$comKey]['tweets'] = $this->get_twCount_by_url($comValue['url']); }
				catch(Exception $e){ $combined['everything'][$comKey]['tweets'] = 0; } 
			}
			if (intval($comValue['comments']) == 0){
				$combined['everything'][$comKey]['comments'] = $this->getSingleComments($comValue['id']);			
			}
		} 
	return $combined;
	}
	function fillInMissing($data, $fullList){
			foreach ($data as $k => $d){
				foreach ($fullList as $kEve => $eve ){
					if (intval($d['id']) == intval($eve['id'])){
						$data[$k]['tweets'] = $eve['tweets'];
						$data[$k]['comments'] = $eve['comments'];
						$data[$k]['fb'] = $eve['fb'];
						$data[$k]['total'] = $eve['total'];
					}
				}
			}
	
	return $data;
	}
	
	function getTotals($data){
		foreach($data as $key => $value){
			$data[$key]['total'] = (int)$value['tweets']+(int)$value['fb']+(int)$value['comments'];
		}
		
	return $data;
	}
	function sortStats($data){
		usort($data, array("sharePulse_getData", "sortByTotal"));
		
	return $data;
	}
	function sortByTotal($a,$b){
	    if ($a['total'] == $b['total']) {
	        return 0;
	    }
	    
	return ($a['total'] < $b['total']) ? 1 : -1;
	}
	
} 
class sharePulse_init{
	function sharePulse_init(){ // Creates new instance and populates the database
		$this->sharePulse_update();
	} 
	function sharePulse_update(){ // Creates new instance and populates the database
		$data = new sharePulse_getData();
	} 	
	function newSchedules($schedules){ // Creates new 
		$schedules['threeMin'] = array('interval'=> 180, 'display'=>  __('Once Every 3 Minutes'));
		$schedules['fiveMin'] = array('interval'=> 300, 'display'=>  __('Once Every 5 Minutes'));
		$schedules['tenMin'] = array('interval'=> 600, 'display'=>  __('Once Every 10 Minutes'));  
		$schedules['fifteenMin'] = array('interval'=> 900, 'display'=>  __('Once Every 15 Minutes'));    
		$schedules['thirtyMin'] = array('interval'=> 1800, 'display'=>  __('Once Every 30 Minutes'));      
	  
	return $schedules;
	}

	function addStylesheet() {
        $myStyleUrl = WP_PLUGIN_URL . '/sharepulse/sharepulse.css';
        $myStyleFile = WP_PLUGIN_DIR . '/sharepulse/sharepulse.css';
        if ( file_exists($myStyleFile) ) {
            wp_register_style('sharePulseStyle', $myStyleUrl);
            wp_enqueue_style( 'sharePulseStyle');
        }
    }
	
	function activate(){ // Schedules data updates
		$data = new sharePulse_getData();
		wp_clear_scheduled_hook('updateSharePulse');
		wp_schedule_event(time(),'fifteenMin', 'updateSharePulse');		
	}
	function deactivate(){ // Deletes sharePulse table, unschedules data updates
		delete_option('sharePulse');
		wp_clear_scheduled_hook('updateSharePulse');
	}	
		
}   


	// Register the new schedules 
	add_filter('cron_schedules', array('sharePulse_init', 'newSchedules'));

	// Add the SharePulse action
	add_action('updateSharePulse', array('sharePulse_init', 'sharePulse_update'));

	// Register de/activation
	register_activation_hook( __FILE__, array('sharePulse_init', 'activate'));
	register_deactivation_hook( __FILE__,  array('sharePulse_init', 'deactivate' ));

	// Register the widget
	add_action('widgets_init', create_function('', 'return register_widget("sharePulse_widget");'));
	add_action('wp_print_styles', array('sharePulse_init','addStylesheet')); 
	
?>
<?php
/*
Plugin Name: Share Pulse
Plugin URI: http://www.jackreichert.com/plugins/sharepulse/
Description: SharePulse rank's, in a sidebar widget, your site&#039;s most popular articles of the week. Stats are tabulated from the Twitter, Digg and Facebook APIs. Note: Due to the reliance on external APIs this plugin may take a little longer to activate than you expect.
Version: 1.1.1
Author: Jack Reichert
Author URI: http://www.jackreichert.com/
License: GPLv2

  Copyright 2010  Jack Reichert  (email : contact@jackreichert.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 3, as 
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


//Activate Plugin
ini_set( "display_errors", 0);

add_action("widgets_init", array('Share_Pulse', 'register'));
register_activation_hook( __FILE__, array('Share_Pulse', 'activate'));
register_deactivation_hook( __FILE__, array('Share_Pulse', 'deactivate'));
class Share_Pulse { //creates database for SP if it doesn't exist.
  function activate(){
  	$data = reset_data();
	$data['SharePulse'] = find_pulse($data);
    if ( ! get_option('share_pulse')){
      	add_option('share_pulse' , $data);
    } else {
      	update_option('share_pulse' , $data);
    }
  }
  function deactivate(){ //so it won't leave $#!+ on your server
    delete_option('share_pulse');
  }
  function control(){ //control panel
  $data = get_option('share_pulse');
  ?>
  <p><label>Header text: <input name="share_pulse_heading"
type="text" size="20" value="<?php echo stripcslashes($data['heading']); ?>" /></label></p>
  <p><label>Create thumbnails? <input name="share_pulse_sp_Thumbs"
type="checkbox" value="yes" <?php echo ( $data['sp_Thumbs'] == 'yes' ) ? 'checked="checked"' : ''; ?>" /></label> (wider view)</p>
<p>Theme: 
<select name="share_pulse_theme">
<option value="buttons" <?php echo ($data['theme']=='buttons')?'selected="selected"':''; ?>>Social.me </option>
<option value="bullets" <?php echo ($data['theme']=='bullets')?'selected="selected"':''; ?>>Bullet Holes </option>
</select>
<br /><span style="font-size:8px;">"Bullet Hole" icons designed by <a href="http://www.productivedreams.com" target="_blank">Gopal Raju</a>.<br/>"Social.me" icons designed by <a href="http://jwloh.deviantart.com/art/Social-me-90694011" target="_blank">jwloh</a>.</span>
</p>
  <p><label>Twitter Source (@myHandle)<input name="share_pulse_tw_Source"
type="text" value="<?php echo $data['tw_Source']; ?>" /></label></p>
    <p><label>Awe.sm api key <input name="share_pulse_awesm_api"
type="text" value="<?php echo $data['awesm_api']; ?>" /></label></p>
<p>Display stats from the past 
<select name="share_pulse_date_range">
<option value="day" <?php echo ($data['date_range']=='day')?'selected="selected"':''; ?>>day </option>
<option value="week" <?php echo ($data['date_range']=='week')?'selected="selected"':''; ?>>week </option>
<option value="month" <?php echo ($data['date_range']=='month')?'selected="selected"':''; ?>>month </option>
</select></p>
  <p><label>Number of posts to display <input name="share_pulse_sp_Count"
type="text" size="2" value="<?php echo $data['sp_Count']; ?>" /></label><br />
	<span style="font-size:8px;">Will adjust due to max available via APIs. Will use random posts to fill in amount.</span></p>
  <p style="font-size:11px;"><label>Allow linkback at bottom of widget? <input name="share_pulse_link"
type="checkbox" value="yes" <?php echo ( $data['link'] == 'yes' ) ? 'checked="checked"' : ''; ?>" /></label></p>
  <p style="font-size:9px;"><a href="http://www.jackreichert.com/plugins/sharepulse/" target="_blank">SharePulse</a> was developed by <a href="http://www.jackreichert.com" target="_blank">Jack Reichert</a> is licensed under <a href="http://codex.wordpress.org/GPL" target="_blank">GPLv2</a>.<br/><br/>Saving will reload APIs and may take a little while.</p>
  <?php
   if (isset($_POST['share_pulse_tw_Source'])||isset($_POST['share_pulse_heading'])||isset($_POST['share_pulse_sp_Count'])||isset($_POST['share_pulse_sp_Thumbs'])||isset($_POST['share_pulse_link'])||isset($_POST['share_pulse_date_range'])||isset($_POST['share_pulse_theme'])){
    $data['heading'] = attribute_escape($_POST['share_pulse_heading']);   
    $data['tw_Source'] = attribute_escape($_POST['share_pulse_tw_Source']);
    $data['awesm_api'] = attribute_escape($_POST['share_pulse_awesm_api']);
    $data['sp_Count'] = attribute_escape($_POST['share_pulse_sp_Count']);
	$data['sp_Thumbs'] = ( attribute_escape($_POST['share_pulse_sp_Thumbs']) == 'yes' ) ? 'yes' : 'no';
	$data['link'] = ( attribute_escape($_POST['share_pulse_link']) == 'yes' ) ? 'yes' : 'no';
	$data['date_range'] = attribute_escape($_POST['share_pulse_date_range']); 
	$data['theme'] = attribute_escape($_POST['share_pulse_theme']); 
    $data['sp_Count'] = ( $data['sp_Count'] > 0 ) ? $data['sp_Count'] : 1; /*make sure (25 >= #posts requested > 0) */
    $data['sp_Count'] = ( $data['sp_Count'] <= $data['SharePulse']['max'] ) ? $data['sp_Count'] : ( $data['SharePulse']['max'] == 0 ) ? $data['sp_Count'] : $data['SharePulse']['max'];
	if ($data['sp_Thumbs']=='yes') { $data['SharePulse'] = get_SP_thumbs($data['SharePulse'], $data['sp_Thumbs']); }  
	$data['sp_Time'] = time();
	$data['SharePulse'] = find_pulse($data);  
	$data = array( 'heading' => $data['heading'], 'sp_Thumbs' => $data['sp_Thumbs'] ,'tw_Source' => $data['tw_Source'], 'SharePulse' => $data['SharePulse'], 'sp_Time' => $data['sp_Time'], 'sp_Count' => $data['sp_Count'], 'link'=>$data['link'], 'date_range'=>$data['date_range'], 'theme'=>$data['theme'], 'awesm_api'=>$data['awesm_api'] );
    update_option('share_pulse', $data);
  }
}
  function widget($args){ //the sidebar widget
  	$data = get_option('share_pulse');
    echo $args['before_widget'];
    echo $args['before_title'] . stripcslashes($data['heading']) . $args['after_title'];
    SharePulse();
    echo $args['after_widget'];
  }
  function register(){ //register widget
    register_sidebar_widget('Share Pulse', array('Share_Pulse', 'widget'));
    register_widget_control('Share Pulse', array('Share_Pulse', 'control'));
  }
}

function reset_data() { //resets data in $data, sets structure

	$d = array( 'heading' => "Most Shared Posts", 'sp_Thumbs' => 'yes', 'tw_Source' => 'SharePulse', 'SharePulse' => array(), 'sp_Time' => 0, 'sp_Count' => 5, 'link'=>'yes', 'date_range'=>'week', 'theme'=>'buttons', 'awesm_api'=>'' );

return $d;
}

function get_diggs($date_range) {
	ini_set('user_agent', 'SharePulse/1.0');
	switch ($date_range) {
    case 'day':
    	$window = time()-86400;
        break;
    case 'week':
        $window = time()-604800;
        break;
    case 'month':
        $window = time()-2629743; 
        break;
    default:
    	$window = time()-604800;
        break;
	}	
	$domain = get_bloginfo('url');
	$reqUrl = 'http://services.digg.com/1.0/endpoint?method=story.getAll&type=xml&count=25&domain='.substr($domain,7).'&min_submit_date='.$window;
	$digg = simplexml_load_file($reqUrl);
	$diggs = array();
	$i=0; 
	foreach( $digg->story as $digg_story ){ //extracts relevant tags
		$url = (string)$digg_story['link']; 
		if ( url_to_postid($url) != '' ) {
			$postID = url_to_postid($url);
			$matches=0;
			for ($k=0; $k<$i; $k++) {
				$matches = ( $diggs[$k]['postID'] == $postID ) ? $k : $matches+0;
			}
			if ( $matches == 0 ) { 
				$diggs[$i]['url'] = $url;
				$diggs[$i]['postID'] = $postID;
				$diggs[$i]['dg'] = (int)$digg_story['diggs'];
				$diggs[$i]['tw'] = 0;
				$diggs[$i]['total'] = $diggs[$i]['tw']+$diggs[$i]['dg'];
				$i++;
			}
		} else echo (time()-604800).'-'.$date.' '.$digg_story->title;
	}
	$diggs['max'] = $i;	
return $diggs;
}	

function get_tweets($date_range) {
	//calls tweetmeme api
	$domain = get_bloginfo('url');
	switch ($date_range) {
    case 'day':
    	$window = 'd';
        break;
    case 'week':
        $window = 'w';
        break;
    case 'month':
        $window = 'm'; 
        break;
    default:
    	$window = 'w';
        break;
	}	
	$url = substr($domain,11);
	$reqUrl = 'http://otter.topsy.com/search.json?q=site:'.$url.'&window='.$window; //have window set to accomodate for slow sites
	$topsy = file_get_contents($reqUrl);
	$topsy = json_decode($topsy);
	$i=0; // # of stories colllected
	foreach( $topsy->response->list as $tw_story ){ //extracts relevant tags
		$url = (string) $tw_story->url;
		if ( url_to_postid($url) != '' ) {
			$postID = url_to_postid($url);
			$matches=0;
			for ($k=0; $k<$i; $k++) {
				$matches = ( $tweets[$k]['postID'] == $postID ) ? $k : $matches+0;
			}
			if ( $matches == 0 ) { 
				$tweets[$i]['url'] = $url;
				$tweets[$i]['postID'] = $postID;
				$tweets[$i]['tw'] = (int) $tw_story->trackback_total;
				$tweets[$i]['dg'] = 0;
				$tweets[$i]['total'] = $tweets[$i]['tw']+$tweets[$i]['dg'];
				$i++;
			}
		}
	}
	
	$tweets['max'] = $i;
return $tweets;
}

function combine ( $d_tw, $d_dg ) {
	for ( $l = 0; $l < $d_tw['max']; $l++ ) {
		$d_tw[$l]['matched']='no';
	}
	$i = 0; $m=0; $n=0;
	
	for ( $l = 0; $l < $d_dg['max']; $l++ ) {
		$k = -1;
		$current = (int)$d_dg[$l]['postID'];			

		for ( $j = 0; $j < $d_tw['max']; $j++ ) {
			$master = (int)$d_tw[$j]['postID'];
			if ( $current == $master ) {
				$k = $j; 
			}
		}

		if ( $k != -1 ) {
			$d_tw[$k]['dg'] = $d_dg[$k]['dg'];
			$d_tw[$k]['total'] = $d_tw[$k]['tw']+$d_tw[$k]['dg'];
			$d_tw[$k]['matched'] = 'yes';
			$n++;
		} else {
			$i++;
			$m = $d_tw['max']-1+$i;
			$d_tw[$m] = $d_dg[$l];
			$url = $d_tw[$m]['url'];
			$d_tw[$m]['tw'] = get_twCount_by_url($url);
			$d_tw[$m]['total'] = $d_tw[$m]['tw']+$d_tw[$m]['dg'];
			$n++;			
		}
	}
	
	for ( $l = 0; $l < $d_tw['max']; $l++ ) {
		if ( $d_tw[$l]['matched']=='no' ) {
			$url = $d_tw[$l]['url'];
			$d_tw[$l]['dg'] = get_diggCount_by_url($url);
			$d_tw[$l]['total'] = $d_tw[$l]['tw']+$d_tw[$l]['dg'];
		}
	}
	$d_tw['max'] = $n+$l;
	
return $d_tw;
}

function get_facebook_shares ($d) {
	for ( $i = 0; $i < $d['max']; $i++ ) {
		$url = $d[$i]['url'];
		usleep(330000);
		$d[$i]['fb'] = get_fbCount_by_url($url);
		$d[$i]['total'] = $d[$i]['tw']+$d[$i]['dg']+$d[$i]['fb'];
	}

return $d;
}

function get_twCount_by_url ($url) {
	
	$reqUrl = 'http://otter.topsy.com/stats.json?url='.urlencode( $url );
	$topsy = file_get_contents($reqUrl);
	$topsy = json_decode($topsy);
	$tw_Num = (int) $topsy->response->all;
	
return $tw_Num;
}

function get_fbCount_by_url ($url) {

	$reqUrl = 'http://api.facebook.com/restserver.php?method=links.getStats&urls='.urlencode( $url );
	$facebook_share = simplexml_load_file($reqUrl);
	$fb_Num = (int)$facebook_share->link_stat->total_count;

return $fb_Num;
}


function get_diggCount_by_url ($url) {
	
	ini_set('user_agent', 'SharePulse/1.0');
	
	$domain = get_bloginfo('url');
	$reqUrl = 'http://services.digg.com/1.0/endpoint?appkey='.urlencode( $domain ).'&method=search.stories&query='.urlencode( $url );
	$digg = simplexml_load_file($reqUrl);

	$digg_Num = (int)$digg->story['diggs'];
	
return $digg_Num;
}

function sort_posts ($d) {
	$new = array();
	for ( $l = 0; $l < $d['max']; $l++ ) {
		$k = 0;
		$heighest = 0;
		for ( $j = 0; $j < $d['max']; $j++ ) {
			$current = (int)$d[$j]['total'];
			if ( $current >= $heighest ) {
				$heighest = $current;
				$k = $j;
			}
		}
		if ( $d[$k]['total'] != -2 ){
		$new[$l] = $d[$k];
		$d[$k]['total'] = -2;
		$heighest = 0;}
	}
	$new['max'] = $d['max'];
return $new;
}
	
function get_SP_thumbs ($d,$thumbs) {
	for ( $i = 0; $i < $d['max']; $i++ ) {
		$temp_query = $wp_query; //WP loop
		query_posts("p=".$d[$i]['postID']);
		if (have_posts()) : while (have_posts()) : the_post();
			if ( $thumbs == 'yes' ) :
				$content = get_the_content();
				$output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $content, $matches);
				$domain = get_bloginfo('url');
				$domain_comp = '['.substr($domain,7).']';
				if ( $matches [1][0] != '' && preg_match( $domain_comp , $matches [1][0] )) {
					$d[$i]['img'] = $domain.'/wp-content/plugins/sharepulse/timthumb.php?src='.$matches[1][0].'&w=80&h=80&zc=1&q=100';
				} else {
					$d[$i]['img'] = $domain.'/wp-content/plugins/sharepulse/images/default.jpg';
				}
			endif;
			$d[$i]['title'] = get_the_title();
			$d[$i]['excerpt'] = get_the_excerpt();
			$d[$i]['date'] = get_the_time('U');
		endwhile; endif;
		$wp_query = $temp_query;
	}
return $d;
}
	
function get_rand ($d) {

	$num = $d['sp_Count'] - $d['SharePulse']['max'];
	$i = 0;
	
	$rand_posts = get_posts('numberposts='.$num.'&orderby=rand');
	foreach( $rand_posts as $post ) :

		$rand[$i]['url'] = get_permalink($post->ID);
		$rand[$i]['postID'] = $post->ID;
		$rand[$i]['tw'] = 0;
		$rand[$i]['dg'] = 0;
		$rand[$i]['fb'] = 0;		
		$rand[$i]['total'] = 0;
		
		$i++;
	endforeach;
	$rand['max'] = $i+1;

return $rand;
}
	
function find_pulse ($d) {

	$digg_tally = get_diggs($d['date_range']);
	$tweet_tally = get_tweets($d['date_range']);	
	$combined = combine( $tweet_tally, $digg_tally );
	if ( $digg_tally['max'] + $tweet_tally['max'] < $d['sp_Count'] ) {
		$d['SharePulse'] = $combined;
		$rand =	get_rand ($d);
		$combine = combine($combined,$rand);
		$sorted = sort_posts($combine);
	} else {
		$facebooked = get_facebook_shares($combined);
		$sorted = sort_posts($facebooked);
	}
	$pulse = get_SP_thumbs($sorted, $d['sp_Thumbs']);
	
return $pulse;
}

function SharePulse() {
	if ( ! get_option('share_pulse')){
		$data = reset_data();
		$data['SharePulse'] = find_pulse($data);
		add_option('share_pulse' , $data); 
		echo 'dbfail';
	} else {
		$data = get_option('share_pulse');
		if(time()-180 > $data['sp_Time']){
			$data['sp_Time'] = time();
			$data['SharePulse'] = find_pulse($data);
			update_option('share_pulse' , $data);
		} 
	} ?>
	<div id="SharePulse" style=" width:<?php echo ($data['sp_Thumbs'] == 'yes')?'300px':'200px' ?>;" >	
	<style>
		<?php if ($data['theme']=='bullets') { include('SharePulse-bullets.css'); $add = '-48'; } else { include('SharePulse-buttons.css'); $add = ''; echo '
div.sharestats a:hover div.stat {
  background-position:0 0;
}
div.twitter-share{
	background-image: url("'.get_bloginfo('url').'/wp-content/plugins/sharepulse/images/twitter_32.png");
	text-indent:-9999px;
}
div.fb-share{
	background-image: url("'.get_bloginfo('url').'/wp-content/plugins/sharepulse/images/facebook_32.png");
	text-indent:-9999px;
}
div.digg-share{
	background-image: url("'.get_bloginfo('url').'/wp-content/plugins/sharepulse/images/digg_32.png");
	text-indent:-9999px;
}
div.shadow {
	width:44px;
	height:30px;
	float:left;
	background:url("'.get_bloginfo('url').'/wp-content/plugins/sharepulse/images/shadow.png") 0 25px no-repeat;
}
div.shadow-back {
	width:44px;
	height:30px;
	float:left;
	z-index: -1;
	background:url("'.get_bloginfo('url').'/wp-content/plugins/sharepulse/images/shadow-back.png") 0 20px no-repeat;
}'; } ?>
	</style>
	<?php for ( $i = 0; $i < $data['sp_Count']; $i++ ) { ?>
	<div class="SharePulse">
	<?php
		$domain = get_bloginfo('url');
		$url_title = urlencode($data['SharePulse'][$i]['title']); 
		$url_excerpt = urlencode($data['SharePulse'][$i]['excerpt']); 
		$url_permalink = urlencode( $data['SharePulse'][$i]['url'] );
		$url_source = urlencode(get_bloginfo('name'));
		$api_key = ( $data['awesm_api'] != '' )?'api_key='.$data['awesm_api'].'&':'';
		$awesm = 'http://create.awe.sm/url/share?'.$api_key.'version=1&share_type=twitter&create_type=sharelink&target='.$url_permalink.'&destination=http://twitter.com/home?status=RT+%40'.$data['tw_Source'].'+'.$url_title.'+AWESM_TARGET';
	?>	
			<a href="<?php echo $data['SharePulse'][$i]['url']; ?>" title="<?php echo $data['SharePulse'][$i]['title']; ?>... Tweets: <?php echo $data['SharePulse'][$i]['tw']; ?>, Shares: <?php echo $data['SharePulse'][$i]['fb']; ?>, Diggs: <?php echo $data['SharePulse'][$i]['dg']; ?>, Total: <?php echo $data['SharePulse'][$i]['total']; ?>">
			<?php if ( $data['sp_Thumbs'] == 'yes' ) : ?>
				<?php echo (  $data['SharePulse'][$i]['total'] != 0 ) ? '<div class="total"><h6>'. $data['SharePulse'][$i]['total'] .'</h6></div>' : '' ?>
				<img class="share-thumb" src="<?php echo $data['SharePulse'][$i]['img']; ?>" alt="<?php echo $data['SharePulse'][$i]['title']; ?>" />
				<div class="title" style="margin-left:110px;">
				<?php else: ?>
				<div class="title" style="margin-left:10px;">
			<?php endif; ?>
				<h4><?php echo $data['SharePulse'][$i]['title']; ?></h4></div>
			</a>
		<?php if ($data['theme']=='bullets'): ?>
			<div class="sharestats">
				<div class="tw-share">
					<a class="tw-link" href="<?php echo $awesm; ?>" title="Tweet this" target="_blank" title="Share on Twitter"><img src="<?php echo $domain; ?>/wp-content/plugins/sharepulse/images/twitter<?php echo $add; ?>.png" alt="Tweet This" />
					<div class="tw-count"><?php echo ( $data['SharePulse'][$i]['tw'] != 0 ) ? $data['SharePulse'][$i]['tw'] : ''; ?></div></a>
				</div>
				<div class="fb-share">
					<a class="fb-link" href="http://www.facebook.com/sharer.php?u=<?php echo $url_permalink; ?>&t=<?php echo $url_title; ?>" target="_blank" title="Share on Facebook"><img src="<?php echo $domain; ?>/wp-content/plugins/sharepulse/images/facebook<?php echo $add; ?>.png" alt="Share on Facebook" />
					<div class="fb-count"><?php echo ( $data['SharePulse'][$i]['fb'] != 0 ) ? $data['SharePulse'][$i]['fb'] : ''; ?></div></a>
				</div>
				<div class="dg-share">
					<a class="dg-link" href="http://digg.com/submit?url=<?php echo $url_permalink; ?>&title=<?php echo $url_title; ?>&bodytext=<?php echo $url_excerpt; ?>&media=news" target="_blank" title="digg this"><img src="<?php echo $domain; ?>/wp-content/plugins/sharepulse/images/digg<?php echo $add; ?>.png" alt="Digg This" />
					<div class="dg-count"><?php echo ( $data['SharePulse'][$i]['dg'] != 0 ) ? $data['SharePulse'][$i]['dg'] : ''; ?></div></a>
				</div>
			</div>
			<?php  else: ?>
			<div class="sharestats">
				<div class="twshare">
				<a  class="count" href="<?php echo $awesm; ?>" title="Tweet this" target="_blank" title="Share on Twitter">				
					<div class="shadow-back"><div class="shadow"><div class="twitter-share stat">Share on Twitter</div></div></div>
					<?php echo ( $data['SharePulse'][$i]['tw'] != 0 ) ? '<span class="top">'.$data['SharePulse'][$i]['tw'].'</span>' : ''; ?></a>
				</div>
				<div class="fbshare">
				<a class="count" href="http://www.facebook.com/sharer.php?u=<?php echo $url_permalink; ?>&t=<?php echo $url_title; ?>" target="_blank" title="Share on Facebook">
					<div class="shadow-back"><div class="shadow"><div class="fb-share stat">Share on Facebook</div></div></div>
					<?php echo ( $data['SharePulse'][$i]['fb'] != 0 ) ? '<span class="top">'.$data['SharePulse'][$i]['fb'].'</span>' : ''; ?></a>
				</div>
				<div class="dgshare">
					<a class="count" href="http://digg.com/submit?url=<?php echo $url_permalink; ?>&title=<?php echo $url_title; ?>&bodytext=<?php echo $url_excerpt; ?>&media=news" target="_blank" title="digg this">
					<div class="shadow-back"><div class="shadow"><div class="digg-share stat">Digg This</div></div></div>
					<?php echo ( $data['SharePulse'][$i]['dg'] != 0 ) ? '<span class="top">'.$data['SharePulse'][$i]['dg'].'</span>' : ''; ?></a>
				</div>
			</div>
		<?php endif; ?>
	</div>
	<?php }
	echo ($data['link']=='yes')?'<h5>Powered by: <a href="http://www.jackreichert.com/plugins/sharepulse/" title="Share Pulse" target="_blank">SharePulse</a></h5>':'<h5>Powered by: SharePulse</h5>';
	echo '</div>';
}
?>
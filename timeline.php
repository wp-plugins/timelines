<?php

/**
Plugin Name: Timelines
Plugin URI: 
Description: Publish interactive timelines via shortcode using SIMILE Timeline Widget.
Version: 1.0
Author: Cau Guanabara
Author URI: http://www.caugb.com.br/
*/


function tl_init() {
  load_plugin_textdomain('tl', PLUGINDIR.'/'.dirname(plugin_basename(__FILE__)).'/langs');
	define('TL_PATH', ABSPATH.PLUGINDIR.'/'.dirname(plugin_basename(__FILE__)));
	define('TL_URL', WP_PLUGIN_URL.'/'.dirname(plugin_basename(__FILE__)));
  include_once TL_PATH.'/admin-html.php';
  include_once TL_PATH.'/html.php';

	$locwiz = TL_PATH.'/langs/helper-'.WPLANG.'.js';
	if(file_exists($locwiz)) include_once TL_PATH.'/helper/helper.php';

	if(isset($_POST['tl_action']) && $_POST['tl_action'] == 'export-timeline') {
		$tl = tl_get_timeline($_GET['id']);
		$sarr = http_build_query($tl);
		tl_force_download($sarr, $tl['id'].".tle");
	}

	if(isset($_POST['tl_action']) && $_POST['tl_action'] == 'export-events') {
		$tl = tl_get_timeline($_GET['id']);
		$events = $tl['events'];
		if(count($events) == 0) return false;
		$sarr = http_build_query($events);
		tl_force_download($sarr, $tl['id'].".tev");
	}
}
add_action('plugins_loaded', 'tl_init');

function tl_add_timeline($tl = array()) {
  $def = array("bands" => array(), "decorators" => array(), "events" => array());
  $opt = array_merge($def, $tl);
  $opt['id'] = sanitize_title($opt['name']);
	if(get_option('timeline_'.$opt['id'])) return false;
  $ok = update_option('timeline_'.$opt['id'], $opt);
  $order = get_option('tl_timelines_order');
  if(!is_array($order)) $order = array();
  $order[] = $opt['id'];
  update_option('tl_timelines_order', $order);
  return $ok ? $opt : false;
}

function tl_update_timeline($arr) {
  $tl = tl_get_timeline($arr['id']);
  $events = isset($tl['events']) ? $tl['events'] : array();
  $decorators = isset($tl['decorators']) ? $tl['decorators'] : array();
  $bands = isset($tl['bands']) ? $tl['bands'] : array();
  $arr['bands'] = $bands;
  $arr['events'] = $events;
  $arr['decorators'] = $decorators;
  update_option('timeline_'.$arr['id'], $arr);
  return true;
}

function tl_remove_timeline($name) {
  $id = sanitize_title($name);
  $order = get_option('tl_timelines_order', true);
  if(!is_array($order)) $order = array();
  $norder = array();
  foreach($order as $tid) if($tid != $id) $norder[] = $tid;
  update_option('tl_timelines_order', $norder);
  return delete_option('timeline_'.$id);
}

function tl_import_timeline($tfile) {
	$seri = file_get_contents($tfile['tmp_name']);
	parse_str($seri, $tl);
	if(!is_array($tl) || !isset($tl['id'])) return false;
	return tl_add_timeline($tl);
}

function tl_get_timelines($id = '') {
  global $wpdb;
  $ret = array();
  $res = $wpdb->get_results("SELECT * FROM {$wpdb->options} WHERE option_name ".($id ? "= 'timeline_{$id}'" : "LIKE 'timeline_%'"));
  foreach($res as $tl) $ret[str_replace('timeline_', '', $tl->option_name)] = maybe_unserialize($tl->option_value);
  $order = get_option('tl_timelines_order', true);
  if(is_array($order) && count($ret) > 1) {
    $ret2 = array();
    foreach($order as $tid) if(isset($ret[$tid])) $ret2[$tid] = $ret[$tid];
    return $ret2;
  }
  return $ret;
}

function tl_get_timeline($id) {
  $tl = tl_get_timelines($id);
  if(!is_array($tl)) return false;
  foreach($tl as $t) return $t;
}

function tl_add_band($tid, $bandarr) { 
  $tl = tl_get_timeline($tid);
  if(!isset($tl['bands'])) $tl['bands'] = array();
  $def = array(
    'trackHeight' => '',
    'trackGap' => '',
    'width' => 100,
    'date' => $tl['date'],
    'timeZone' => $tl['timezone'],
    'intervalUnit' => 'MONTH',
    'intervalPixels' => 100,
    'showEventText' => true
  );
  $arr = array_merge($def, $bandarr);
	if(empty($arr['width']) || empty($arr['intervalUnit']) || empty($arr['intervalPixels'])) return false;
	$arr['timeZone'] = (int) $arr['timeZone'];
  $c = count($tl['bands']);
  $tl['bands'][] = $arr;
  update_option('timeline_'.$tid, $tl);
  return $c < count($tl['bands']);
}

function tl_update_band($tid, $bid, $arr) {
  $tl = tl_get_timeline($tid);
  $hzs = isset($tl['bands'][$bid]['hotzones']) ? $tl['bands'][$bid]['hotzones'] : array();
  $bid = (int) $bid;
  if(!isset($tl['bands'][$bid]) || empty($arr['width']) || /*empty($arr['timeZone']) ||*/ empty($arr['intervalUnit']) || empty($arr['intervalPixels'])) return false; 
	$arr['timeZone'] = (int) $arr['timeZone'];
  $tl['bands'][$bid] = $arr;
  $tl['bands'][$bid]['hotzones'] = $hzs;
  update_option('timeline_'.$tid, $tl);
  return true;
}

function tl_remove_band($tid, $bid) {
  $tl = tl_get_timeline($tid);
  if(!isset($tl['bands'][$bid])) return false;
  $ret = array();
  foreach($tl['bands'] as $i => $b) if($i != $bid) $ret[] = $b;
  $tl['bands'] = $ret;
  update_option('timeline_'.$tid, $tl);
  return true;
}

function tl_add_event($tid, $evt) {
  $tl = tl_get_timeline($tid);
  if(!isset($tl['events'])) $tl['events'] = array();
  $def = array('title' => '', 'start' => '', 'end' => '', 'description' => '');
  $arr = array_merge($def, $evt);
	$arr['title'] = tl_htmlentities($arr['title']);
	$arr['description'] = tl_htmlentities($arr['description']);
  if(empty($arr['start']) || empty($arr['title'])) return false;
  $c = count($tl['events']);
  $tl['events'][] = $arr;
  update_option('timeline_'.$tid, $tl);
  return $c < count($tl['events']) && tl_save_events_json($tid);
}

function tl_update_event($tid, $evtid, $arr) {
  $tl = tl_get_timeline($tid);
  $evtid = (int) $evtid;
  if(!isset($tl['events'][$evtid])) return false; 
	$arr['title'] = tl_htmlentities($arr['title']);
	$arr['description'] = tl_htmlentities($arr['description']);
  $tl['events'][$evtid] = $arr;
  update_option('timeline_'.$tid, $tl);
  return tl_save_events_json($tid);
}

function tl_remove_event($tid, $evtid) {
  $tl = tl_get_timeline($tid);
  if(!isset($tl['events'][$evtid])) return false;
  $ret = array();
  foreach($tl['events'] as $i => $e) if($i != $evtid) $ret[] = $e;
  $tl['events'] = $ret;
  update_option('timeline_'.$tid, $tl);
  return tl_save_events_json($tid);
}

function tl_save_events_json($tid) {
  $tl = tl_get_timeline($tid);
  $events = array();
  foreach($tl['events'] as $evt) {
    $maybe = array('image', 'link', 'icon', 'color', 'textColor', 'tapeImage', 'tapeRepeat', 'caption', 'classname');
    $arr = array(
      "start" => date('c', strtotime($evt['start'])),
      "end" => empty($evt['end']) ? '' : date('c', strtotime($evt['end'])),
      "title" => $evt['title'],
      "description" => preg_replace("/[\r\n]+/", "<br />", $evt['description'])
    );
    foreach($maybe as $n) {
      if(isset($evt[$n]) && !empty($evt[$n])) $arr[$n] = $evt[$n];
    }
  $events[] = $arr;
  }
  $fh = fopen(TL_PATH.'/data/'.$tid.'.json', 'w');
	if($fh) {
		$evts = array();
		foreach($events as $evt) {
			$props = array();
			foreach($evt as $en => $ev) $props[] = "{$en}: '".str_replace("'", "&#8217;", $ev)."'";
			$evts[] = "{ ".join(", ", $props)." }";
		}
		$json = "{ dateTimeFormat: 'iso8601', events: [ ".join(", ", $evts)." ] }";
		fwrite($fh, $json);
		fclose($fh);
	}
  return filemtime(TL_PATH.'/data/'.$tid.'.json') > time() - 200;
}

function tl_import_events($tid, $efile) {
	$seri = file_get_contents($efile['tmp_name']);
	parse_str($seri, $evts);
	if(!is_array($evts) || !count($evts)) return false;
  $tl = tl_get_timeline($tid);
	$tl['events'] = array_merge($tl['events'], $evts);
  return update_option('timeline_'.$tid, $tl);
}

function tl_add_hotzone($tid, $bid, $hz) {
  $tl = tl_get_timeline($tid);
  $def = array('startTime' => '', 'endTime' => '', 'unit' => '', 'magnify' => '', 'multiple' => '');
  $arr = array_merge($def, $hz);
  if(!isset($tl['bands'][$bid]) || empty($arr['startTime']) || empty($arr['endTime']) || empty($arr['unit']) || empty($arr['magnify'])) return false;
  if(!isset($tl['bands'][$bid]['hotzones'])) $tl['bands'][$bid]['hotzones'] = array();
  $c = count($tl['bands'][$bid]['hotzones']);
  $tl['bands'][$bid]['hotzones'][] = $arr;
  update_option('timeline_'.$tid, $tl);
  return $c < count($tl['bands'][$bid]['hotzones']);
}

function tl_update_hotzone($tid, $bid, $hzid, $hz) {
  $tl = tl_get_timeline($tid);
  $bid = (int) $bid;
  if(!isset($tl['bands'][$bid]['hotzones'][$hzid])) return false; 
  $tl['bands'][$bid]['hotzones'][$hzid] = $hz;
  update_option('timeline_'.$tid, $tl);
  return true;
}

function tl_remove_hotzone($tid, $bid, $hzid) {
  $tl = tl_get_timeline($tid);
  $bid = (int) $bid;
  if(!isset($tl['bands'][$bid]['hotzones'][$hzid])) return false; 
  $ret = array();
  foreach($tl['bands'][$bid]['hotzones'] as $i => $e) if($i != $hzid) $ret[] = $e;
  $tl['bands'][$bid]['hotzones'] = $ret;
  return update_option('timeline_'.$tid, $tl);
}

function tl_add_decorator($tid, $dec) {
  $tl = tl_get_timeline($tid);
  if(!isset($tl['decorators'])) $tl['decorators'] = array();
  $c = count($tl['decorators']);
  $tl['decorators'][] = $dec;
  update_option('timeline_'.$tid, $tl);
  return $c < count($tl['decorators']);
}

function tl_update_decorator($tid, $did, $dec) {
  $did = (int) $did;
  $tl = tl_get_timeline($tid);
  if(!isset($tl['decorators'])) $tl['decorators'] = array();
  if(!isset($tl['decorators'][$did])) return false; 
  $tl['decorators'][$did] = $dec;
  update_option('timeline_'.$tid, $tl);
  return true;
}

function tl_remove_decorator($tid, $did) {
  $did = (int) $did;
  $tl = tl_get_timeline($tid);
  if(!isset($tl['decorators'][$did])) return false;
  $ret = array();
  foreach($tl['decorators'] as $i => $d) if($i != $did) $ret[] = $d;
  $tl['decorators'] = $ret;
  update_option('timeline_'.$tid, $tl);
  return true;
}

function tl_admin_styles() {
  wp_enqueue_style('farbtastic');
  wp_enqueue_style('custom-ui', TL_URL.'/timepicker/jquery-ui-1.8.9.custom.css');
  ?>
  <style type="text/css">
  .tl_page form { width:95%; }
  .tl_page .formline { margin:0 auto; padding:4px 0; }
  .tl_page .formline label, .tl_page .formline .label { display:inline-block; text-align:right; width:30%; }
  .tl_page .formline input, .tl_page .formline  textarea, .tl_page .formline select, .tl_page .formline .input { width:55%; display:inline-block; }
  .tl_page .formline textarea { height:5em; }
  .tl_page .tarea label { position:relative; top:-3.8em; }
  .tl_page .check label { width:45%; float:none; text-align:left; }
  .tl_page .buttons { margin:10px; text-align:right; }
  .tl_page .buttons input, .tl_page .check input, .tl_page .input input { width:auto !important; }
  .tl_page .input label { width:auto !important; margin-left:3px; }
  .delete:hover { color:#C00 !important; border-color:#C00 !important; }
  .hotzones-list li { background-color:#F0F0F0; border-radius:5px; -moz-border-radius:5px; -webkit-border-radius:5px; border:1px solid #ccc; }
  .hotzones-list { margin:10px 20px 20px 20px; clear:both; }
	input.invisible { background-color:transparent !important; border:0 !important; font-family:"Courier New", Courier, monospace; }
	.hundred { color:#F00; margin:10px; }
	.alignleft { margin-left:4px !important; }
	
	/* css for timepicker */
	.ui-timepicker-div .ui-widget-header{ margin-bottom: 8px; }
	.ui-timepicker-div dl{ text-align: left; }
	.ui-timepicker-div dl dt{ height: 25px; }
	.ui-timepicker-div dl dd{ margin: -25px 10px 10px 85px; }
	.ui-timepicker-div td { font-size: 90%; }
  </style>
  <?php
}
  if(isset($_GET['page']) && $_GET['page'] == 'timelines-admin') add_action('admin_print_styles', 'tl_admin_styles');


function tl_add_js($post) {
  wp_enqueue_script('farbtastic');
  wp_enqueue_script('timeline', TL_URL.'/timeline.js');
  wp_enqueue_script('custom-ui', TL_URL.'/timepicker/jquery-ui-1.8.9.custom.min.js');
  wp_enqueue_script('timepicker', TL_URL.'/timepicker/jquery-ui-timepicker-addon.js');
	if(file_exists(TL_PATH.'/langs/jquery-timepicker-'.WPLANG.'.js')) wp_enqueue_script('timepicker-i18n', TL_URL.'/langs/jquery-timepicker-'.WPLANG.'.js');
}
  if(isset($_GET['page']) && $_GET['page'] == 'timelines-admin') add_action('admin_print_scripts', 'tl_add_js');


function tl_force_download($conts, $name) {
	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
	header("Content-Type: application/force-download");
	header("Content-Type: application/octet-stream");
	header("Content-Disposition: attachment; filename=".$name.";");
	header("Content-Transfer-Encoding: binary");
	header("Content-Length: ".strlen($conts));
	die($conts);
}

function tl_get_publish_id($tid) {
	global $wpdb;
	$posts = $wpdb->get_results("SELECT ID, post_title, post_status FROM {$wpdb->posts} WHERE post_status NOT REGEXP 'inherit|trash|auto' AND post_content REGEXP '\[timeline\s+id=[\'\"]{$tid}[\'\"]'");
  return $posts;
}

function tl_htmlentities($htm) {
	if(!empty($htm)) return htmlentities($htm, ENT_QUOTES | ENT_IGNORE, "UTF-8");
  return $htm;
}
?>
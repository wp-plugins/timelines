<?php

/**
 * Timelines - HTML functions
 */


function tl_timeline_sc($attrs, $content = '') {
  extract(shortcode_atts(array('class' => 'timeline', 'id' => '', 'height' => 0, 'width' => 0), $attrs));
  if(!isset($id) || !($tl = tl_get_timeline($id))) return '';
  $h = empty($height) ? (isset($tl['height']) ? $tl['height'] : 300) : $height;
  $height = 'height:'.(preg_match("/^\d+[a-z]{2}$/", $h) ? $h : $h.'px').';';
  $w = empty($width) ? (isset($tl['width']) ? $tl['width'] : '') : $width;
  $width = empty($w) ? '' : ' width:'.(preg_match("/^\d+[a-z]{2}$/", $w) ? $w : $w.'px').';';
  add_action('wp_footer', create_function('', 'tl_add_timeline_js("'.$id.'");'));
	return '<div id="timeline_'.$id.'" class="timeline-wrap '.$class.'" style="'.$height.$width.'"></div>';
}
add_shortcode('timeline', 'tl_timeline_sc');

function tl_add_timeline_js($id) {
  $tl = tl_get_timeline($id);
  $tl_url = WP_PLUGIN_URL.'/'.basename(dirname(__FILE__));
  ?>
  <script type="text/javascript">
  /* <![CDATA[ */ 
	var Timeline_urlPrefix = "<?php print $tl_url; ?>/simile-timeline/"; 

	function addEvent(elm, evType, fn, useCapture) {
		if(elm.addEventListener) { elm.addEventListener(evType, fn, useCapture); return true; }
		else if(elm.attachEvent) { var r = elm.attachEvent('on' + evType, fn); return r; }
		else { elm['on' + evType] = fn; }
	}
	/* ]]> */
  </script>
  <script type="text/javascript" src="<?php print $tl_url; ?>/simile-timeline/timeline-api.js" defer="defer"></script>
  <script type="text/javascript">
  /* <![CDATA[ */
  function tl_init() { 

    var eventSource = new Timeline.DefaultEventSource(0); 
    <?php 
      $bands = array();
      $extra = array();
      $hzs = array('');
			  if(isset($tl['date']) && strtolower($tl['date']) == 'now') $tl['date'] = date('r');
      foreach($tl['bands'] as $i => $band) {
        if(count($band['hotzones']) > 0) {
          $zones = array();
          foreach($band['hotzones'] as $hz) $zones[] = "{ start: '{$hz['startTime']}', end: '{$hz['endTime']}', magnify: {$hz['magnify']}, unit: Timeline.DateTime.{$hz['unit']}".
                                                       (isset($hz['multiple']) && !empty($hz['multiple']) ? ", multiple: {$hz['multiple']}" : "")." }";
          $iz = count($hzs);
          $hzs[$iz] = $zones;
          $bands[] = "Timeline.createHotZoneBandInfo({ width: '{$band['width']}%', intervalUnit: Timeline.DateTime.{$band['intervalUnit']}, intervalPixels: {$band['intervalPixels']}, ".
                     (isset($band['timeZone']) && !empty($band['timeZone']) ? "timeZone: {$band['timeZone']}, " : "").
                     "showEventText: {$band['showEventText']}, layout: '{$band['layout']}', ".
                     (isset($tl['date']) && !empty($tl['date']) ? "date: '{$tl['date']}', " : "")."eventSource: eventSource, zones: zones{$iz}, theme: theme })";
        } else {
          $bands[] = "Timeline.createBandInfo({ width: '{$band['width']}%', intervalUnit: Timeline.DateTime.{$band['intervalUnit']}, intervalPixels: {$band['intervalPixels']}, ".
                     (isset($band['timeZone']) && !empty($band['timeZone']) ? "timeZone: {$band['timeZone']}, " : "").
                     "showEventText: {$band['showEventText']}, layout: '{$band['layout']}', ".
                     (isset($tl['date']) && !empty($tl['date']) ? "date: '{$tl['date']}', " : "")."eventSource: eventSource, theme: theme })";
        }
        if($i > 0) $extra[] = "bandInfos[{$i}].syncWith = 0;\n    bandInfos[{$i}].highlight = true;\n";
      } 
      
      print "\n    var theme = Timeline.ClassicTheme.create();\n";
      if(isset($tl['bubble_width']) && !empty($tl['bubble_width'])) print "    theme.event.bubble.width = {$tl['bubble_width']};\n";
      if(isset($tl['bubble_height']) && !empty($tl['bubble_height'])) print "    theme.event.bubble.height = {$tl['bubble_height']};\n";
      
      if(count($hzs) > 1) {
        array_shift($hzs);
        foreach($hzs as $iz => $hz) {
          print "\n    var zones".($iz + 1)." = [\n      ".join(",\n      ", $hz)."\n    ];\n";
        }
      }
      
      print "\n    var bandInfos = [\n      ".join(",\n      ", $bands)."\n    ];\n";
      if(!empty($extra)) print "    ".join("\n      ", $extra);
    
      if(isset($tl['decorators']) && count($tl['decorators'])) { ?>
    for(var i = 0; i < bandInfos.length; i++) {
      bandInfos[i].decorators = [<?php 
      $decs = array();
      foreach($tl['decorators'] as $dec) {
        if($dec['type'] == 'track') $decs[] = "new Timeline.SpanHighlightDecorator({ startDate: '{$dec['startTime']}', endDate: '{$dec['endTime']}', ".
                                              (isset($dec['color']) && !empty($dec['color']) ? "color: '{$dec['color']}', " : "").
                                              (isset($dec['opacity']) && !empty($dec['opacity']) ? "opacity: {$dec['opacity']}, " : "").
                                              "startLabel: '{$dec['startLabel']}', endLabel: '{$dec['endLabel']}', theme: theme })";
        elseif($dec['type'] == 'point') $decs[] = "new Timeline.PointHighlightDecorator({ ".(isset($dec['color']) && !empty($dec['color']) ? "color: '{$dec['color']}', " : "").
                                                  (isset($dec['opacity']) && !empty($dec['opacity']) ? "opacity: {$dec['opacity']}, " : "")."date: '{$dec['startTime']}', theme: theme })";
      } 
      print "\n        ".join(",\n        ", $decs)."\n"; 
      ?>
      ];
    } <?php print "\n"; } ?>
    timeline = Timeline.create(document.getElementById('timeline_<?php print $id; ?>'), bandInfos, Timeline<?php print '.'.strtoupper($tl['orientation']); ?>); 
    Timeline.loadJSON("<?php print WP_PLUGIN_URL.'/'.dirname(plugin_basename(__FILE__)).'/data/'.$id.'.json'; ?>", function(data, url){ eventSource.loadJSON(data, url); });
  }
	addEvent(window, 'load', function() { setTimeout(tl_init, 500); });
  /* ]]> */
  </script>
  <style type="text/css">
  .timeline-event-icon, .timeline-event-label, .timeline-event-tape {
		font-family:"Trebuchet MS", Arial, Helvetica, sans-serif;
		font-size:12px;
	}
	div.simileAjax-bubble-container * {
		font-family:"Trebuchet MS", Arial, Helvetica, sans-serif;
		font-size:12px;
	}
	.timeline-event-bubble-title {
		border-color:#CCC;
	}
	.timeline-event-bubble-body {
	}
	.timeline-event-bubble-time {
		margin-top:10px;
	}
	.timeline-highlight-label td {
		font-family:"Trebuchet MS", Arial, Helvetica, sans-serif !important;
		font-size:22px !important;
		font-weight:bold !important;
	}
	.timeline-highlight-label-start td {
	}
	.timeline-highlight-label-end td {
	}
	.timeline-wrap {
		background-image:url(<?php print WP_PLUGIN_URL.'/'.dirname(plugin_basename(__FILE__)); ?>/simile-timeline/images/progress-running.gif);
		background-position:50% 50%;
		background-repeat:no-repeat;
	}
  </style>
  <?php
}

?>
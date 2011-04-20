<?php

/**
 * Timelines - Helper 
 */

function tl_helper_button($id) {
	?>
	<input type="button" id="start_wizard" value="<?php _e('Helper', 'tl'); ?>" class="button-secondary alignleft" onclick="
    if(tl_wizard && tl_wizard.isOpen) tl_wizard.resetHelper(); else { tl_wizard = new tl_helper('<?php print $id; ?>'); tl_wizard.make(); }" />
	<?php
}
add_action('new_timeline_buttons', 'tl_helper_button');

function tl_helper_style() {
  ?>
  <style type="text/css">
  .tl_page .helper { background-color:#F9F9F9; padding:0px 20px; }
  .tl_page .helper h3, .tl_page .helper h4 { margin:10px 0; }
  .tl_page .helper-content { background-color:#FCFCFC; border:1px solid #CCC; padding:7px 15px; border-radius:4px; -moz-border-radius:4px; -webkit-border-radius:4px; }
  .tl_page .alert { background-color:#F00; color:#FFF; }
  .tl_page .required { background-color:#FEF4F1; }
  .tl_page .required.filled { background-color:#E9FEF8; }
  .tl_page .helper-status { margin:15px 5px; background-color:#DBFBE8; border:1px solid #CCC; padding:8px 16px; border-radius:4px; -moz-border-radius:4px; -webkit-border-radius:4px; }
  .tl_page .missing { background-color:#FDE6DF; }
  </style>
  <?php
}
  if(isset($_GET['page']) && $_GET['page'] == 'timelines-admin') add_action('admin_print_styles', 'tl_helper_style');

function tl_helper_js() {
	if(!file_exists( TL_PATH.'/langs/helper-'.WPLANG.'.js')) return;
	wp_enqueue_script('helper-l10n', TL_URL.'/langs/helper-'.WPLANG.'.js');
	wp_enqueue_script('helper', TL_URL.'/helper/helper.js');
}
add_action('admin_print_scripts', 'tl_helper_js');

?>
<?php

/**
 * Timelines - Admin HTML functions
 */


function tl_add_admin_page() {
  add_submenu_page('upload.php', __("Edit Timelines", 'tl'), __("Timelines", 'tl'), 'edit_posts', 'timelines-admin', 'tl_admin_page');
}
add_action('admin_menu', 'tl_add_admin_page');

function tl_admin_page() {
  $msg = '';
    
  if(isset($_POST['tl_action'])) {
    switch($_POST['tl_action']) {
      case 'add-timeline':
        if(empty($_POST['tl_timeline']['name'])) $ret = false; 
        else $ret = tl_add_timeline($_POST['tl_timeline']);
        if($ret) $msg = __('Timeline successfully created', 'tl');
        else $msg = __('The timeline could not be created', 'tl');
        break;
      case 'delete-timeline':
        $msg = tl_remove_timeline($_POST['tl_timeline_id']) ? __('Timeline successfully removed', 'tl') : __('The timeline could not be removed', 'tl');
        break;
      case 'import-timeline':
			  $fok = (is_array($_FILES['tl_import_file']) && $_FILES['tl_import_file']['error'] == 0);
        $msg = ($fok && tl_import_timeline($_FILES['tl_import_file'])) ? __('Timeline successfully imported', 'tl') : __('The timeline could not be imported', 'tl');
        break;
    }
  }
    
  if(isset($_GET['tl_action'])) {
    if($_GET['tl_action'] == 'edit' || strstr($_GET['tl_action'], 'band')) return tl_edit_timeline();
    elseif($_GET['tl_action'] == 'edit-events') return tl_edit_timeline_events();
  }
?>
  <div class="wrap tl_page">
    <div class="icon32" id="icon-tools">&nbsp;</div>
    <?php 
		$timelines = tl_get_timelines();
		?>
		<h2><?php _e('Timelines', 'tl'); ?></h2>
		<?php if(!empty($msg)) print '<div class="updated"><p>'.$msg.'</p></div>'; ?>
		<form method="post" action="?page=<?php print $_GET['page']; ?>" id="tl_form">
			<div id="col-container">
				<div id="col-right">
					<div class="col-wrap">
						<h3><?php _e('Current Timelines', 'tl'); ?></h3>
						<table cellspacing="0" class="widefat fixed">
							<thead>
								<tr class="thead">
									<th class="manage-column column-name" scope="col"><?php _e('Name', 'tl'); ?></th>
									<th class="manage-column column-date" scope="col"><?php _e('Date', 'tl'); ?></th>
									<th class="manage-column column-bands" scope="col"><?php _e('Bands', 'tl'); ?></th>
									<th class="manage-column column-events" scope="col"><?php _e('Events', 'tl'); ?></th>
								</tr>
							</thead>
							
							<tfoot>
								<tr class="thead">
									<th class="manage-column column-name" scope="col"><?php _e('Name', 'tl'); ?></th>
									<th class="manage-column column-date" scope="col"><?php _e('Date', 'tl'); ?></th>
									<th class="manage-column column-bands" scope="col"><?php _e('Bands', 'tl'); ?></th>
									<th class="manage-column column-events" scope="col"><?php _e('Events', 'tl'); ?></th>
								</tr>
							</tfoot>
							
							<tbody>
							<?php
							$cls = ' class="alternate"';
							if(count($timelines)) {
								foreach($timelines as $tl) {
									?>
									<tr<?php print ($cls = $cls == '' ? ' class="alternative"' : ''); ?>>
										<td class="name"><strong><a href="?page=<?php print $_GET['page']; ?>&tl_action=edit&id=<?php print $tl['id']; ?>"><?php print $tl['name']; ?></a></strong><br />
											<div class="row-actions">
												<a href="?page=<?php print $_GET['page']; ?>&tl_action=edit&id=<?php print $tl['id']; ?>"><?php _e('Edit', 'tl'); ?></a> |
												<a href="?page=timelines-admin&tl_action=edit-events&id=<?php print $tl['id']; ?>"><?php _e('Manage Events', 'tl'); ?></a> | 
												<a href="javascript://" onclick="if(!confirm('<?php _e('Delete this timeline?', 'tl'); ?>')) return false; removeTimeline('<?php print $tl['id']; ?>');"><?php _e('Delete', 'tl'); ?></a>
											</div>
										</td>
										<td class="date"><?php print $tl['date']; ?></td>
										<td class="bands"><?php print isset($tl['bands']) ? count($tl['bands']) : '0'; ?></td>
										<td class="events"><?php print isset($tl['events']) ? count($tl['events']) : '0'; ?></td>
									</tr>
									<?php
								}
							} else {
								?>
								<td colspan="4"><?php _e('There are no defined timelines', 'tl'); ?></td>
								<?php
							}
							?>
							</tbody>
						</table>
					</div>
				</div>
				<div id="col-left">
					<div class="col-wrap">
						<div id="newtimeline">
							<h3><?php _e('Add New Timeline', 'tl'); ?></h3>

							<?php tl_edit_tl_form(); ?>
							
							<div class="formline buttons">
								<?php do_action('new_timeline_buttons', 'timeline'); ?>
								<input type="button" class="button-secondary" id="importbtn" value="<?php _e('Import Timelines', 'tl'); ?>" onclick="showImportTimeline();" />
								<input type="submit" class="button-primary" value="<?php _e('Add Timeline', 'tl'); ?>" />
								<input type="hidden" name="tl_action" id="tl_action" value="add-timeline" />
								<input type="hidden" name="tl_timeline_id" id="tl_timeline_id" />
							</div>
						</div>
						<div class="formline-outer" id="importform" style="display:none">
							<h3><?php _e('Import Timeline', 'tl'); ?></h3>
							<p><?php _e('If you have a Timeline export file (.TLE), you can import the entire timeline to this system. If a timeline with the same name exists, the proccess will fail.', 'tl'); ?></p>
							<div class="formline">
								<label for="tl_import_file"><?php _e('Select File', 'tl'); ?></label>
								<input type="file" name="tl_import_file" id="tl_import_file" />
							</div>
							<div class="formline buttons">
								<input type="button" id="closeimport" class="button-secondary alignleft" value="<?php _e('Cancel', 'tl'); ?>" onclick="showImportTimeline(true);" />
								<input type="button" class="button-primary" value="<?php _e('Upload File', 'tl'); ?>" onclick="
									if(!/\.tle$/.test(this.form.tl_import_file.value)) { alert('<?php _e('The selected file must have &quot;.TLE&quot; extension.', 'tl'); ?>'); this.form.tl_import_file.focus(); } 
									this.form.encoding = 'multipart/form-data'; 
									importTimeline()" />
							</div>
						</div>
					</div>
				</div>
			</div>
		</form>
  </div>
<?php
}

function tl_edit_timeline() {
  if(isset($_REQUEST['tl_action'])) {
    switch($_REQUEST['tl_action']) {
      case 'edited-timeline': 
        if(empty($_POST['tl_timeline']['id'])) $ret = false; 
        else $ret = tl_update_timeline($_POST['tl_timeline']);
        if($ret) $msg = __('Timeline successfully edited', 'tl');
        else $msg = __('The timeline could not be modified', 'tl');
        break;
      case 'add-band':
        $msg = tl_add_band($_GET['id'], $_POST['tl_band']) ? __('New band successfully added', 'tl') : __('The band could not be added', 'tl');
        break;
      case 'remove-band':
        $msg = tl_remove_band($_GET['id'], $_POST['tl_band_pos']) ? __('Band successfully removed', 'tl') : __('The band could not be removed', 'tl');
        break;
      case 'update-band':
        $p = (string) (int) $_POST['tl_band_pos'];
        $msg = tl_update_band($_GET['id'], $p, $_POST['tl_'.$p.'_band']) ? __('The band was successfully modified', 'tl') : __('The band could not be edited', 'tl');
        break;
      case 'add-hotzone':
        $msg = tl_add_hotzone($_GET['id'], $_POST['tl_band_pos'], $_POST['tl_hz']) ? __('The hot zone was successfully added', 'tl') : __('The hot zone could not be added', 'tl');
        break;
      case 'edit-hotzone':
        $msg = tl_update_hotzone($_GET['id'], $_POST['tl_band_pos'], $_POST['tl_hz_to_edit'], $_POST['tl_'.$_POST['tl_band_pos'].'_'.$_POST['tl_hz_to_edit'].'_hz']) 
                 ? __('The hot zone was successfully modified', 'tl') : __('The hot zone could not be edited', 'tl');
        break;
      case 'remove-hotzone':
        $msg = tl_remove_hotzone($_GET['id'], $_POST['tl_band_pos'], $_POST['tl_hz_to_edit']) ? __('The hot zone was successfully removed', 'tl') : __('The hot zone could not be removed', 'tl');
        break;
      case 'add-decorator':
        $msg = tl_add_decorator($_GET['id'], $_POST['tl_dec']) ? __('The decorator was successfully created', 'tl') : __('The decorator could not be created', 'tl');
        break;
      case 'update-decorator': 
        $msg = tl_update_decorator($_GET['id'], $_POST['tl_decorator_pos'], $_POST['tl_dec_'.$_POST['tl_decorator_pos']]) ? __('The decorator was successfully modified', 'tl') : __('The decorator could not be edited', 'tl');
        break;
      case 'remove-decorator':
        $msg = tl_remove_decorator($_GET['id'], $_POST['tl_decorator_pos']) ? __('The decorator was successfully removed', 'tl') : __('The decorator could not be removed', 'tl');
        break;
    }
  }
  $tl = tl_get_timeline($_GET['id']);
  $units = array(
	  'MILLISECOND' => __('Milliseconds', 'tl'),
    'SECOND'      => __('Seconds', 'tl'),
    'MINUTE'      => __('Minutes', 'tl'),
    'HOUR'        => __('Hours', 'tl'),
    'DAY'         => __('Days', 'tl'),
    'WEEK'        => __('Weeks', 'tl'),
    'MONTH'       => __('Months', 'tl'),
    'YEAR'        => __('Years', 'tl'),
    'DECADE'      => __('Decades', 'tl'),
    'CENTURY'     => __('Centuries', 'tl'),
    'MILLENNIUM'  => __('Millenniums', 'tl')
  );
?>
  <div class="wrap tl_page">
    <div class="icon32" id="icon-tools">&nbsp;</div>
    <h2><?php printf(__('Edit Timeline &quot;%s&quot;', 'tl'), $tl['name']); ?></h2>
    <?php if(!empty($msg)) print '<div class="updated"><p>'.$msg.'</p></div>'; ?>
    <form method="post" action="?page=<?php print $_GET['page']; ?>&tl_action=edit&id=<?php print $_GET['id']; ?>" id="tl_form">
      <div id="col-container">
        <div id="col-right">
          <div class="col-wrap">
            <h3><?php _e('Current Bands', 'tl'); ?></h3>
            <table cellspacing="0" class="widefat fixed">
              <thead>
                <tr class="thead">
                  <th class="manage-column column-name" scope="col"><?php _e('Width', 'tl'); ?></th>
                  <th class="manage-column column-unit" scope="col"><?php _e('Unit', 'tl'); ?></th>
                  <th class="manage-column column-hotzones" scope="col"><?php _e('Hot Zones', 'tl'); ?></th>
                </tr>
              </thead>
              
              <tfoot>
                <tr class="thead">
                  <th class="manage-column column-name" scope="col"><?php _e('Width', 'tl'); ?></th>
                  <th class="manage-column column-unit" scope="col"><?php _e('Unit', 'tl'); ?></th>
                  <th class="manage-column column-hotzones" scope="col"><?php _e('Hot Zones', 'tl'); ?></th>
                </tr>
              </tfoot>
              
              <tbody>
              <?php
              $cls = ' alternate';
							$perc = 0;
              if(isset($tl['bands']) && count($tl['bands'])) {
                foreach($tl['bands'] as $i => $band) {
									$perc += (int) $band['width'];
                  ?>
                  <tr id="band_<?php print $i; ?>" class="visible-row<?php print ($cls = $cls == '' ? ' alternate' : ''); ?>">
                    <td class="width"><?php print $band['width']; ?>%<br />
                      <div class="row-actions">
                        <a href="javascript://" onclick="inlineEditBand(<?php print $i; ?>);"><?php _e('Edit', 'tl'); ?></a> | 
                        <a href="javascript://" class="delete" onclick="if(confirm('<?php _e('Delete this band?', 'tl'); ?>')) removeBand(<?php print $i; ?>);"><?php _e('Delete', 'tl'); ?></a>
                      </div>
                    </td>
                    <td class="unit"><?php print $units[$band['intervalUnit']]; ?></td>
                    <td class="hotzones"><?php print isset($band['hotzones']) ? count($band['hotzones']) : '0'; ?></td>
                  </tr>
                  <tr class="inline-edit-row" id="band_<?php print $i; ?>_edit" style="display:none">
                    <td class="edit-band" colspan="3">
                      <fieldset id="band_<?php print $i; ?>_fs">
                        <h3><?php printf(__('Editing Band #%d', 'tl'), $i); ?></h3>
                        <?php tl_edit_band_form($_GET['id'], $i, $band); ?>
                        <div class="buttons inline-edit-save">
                          <a onclick="inlineEditBand(<?php print $i; ?>, true);" href="javascript://" class="button-secondary cancel alignleft"><?php _e('Close', 'tl'); ?></a>
                          <a onclick="inlineUpdateBand(<?php print $i; ?>);" href="javascript://" class="button-primary save alignright"><?php _e('Update Band', 'tl'); ?></a>
                          <a onclick="showAddHotZone(<?php print $i; ?>);" href="javascript://" class="button-secondary save alignright" style="margin-right:10px;"><?php _e('Add Hot Zone', 'tl'); ?></a>
                          <div style="clear:both"></div>
                        </div>
                      </fieldset>
                    </td>
                  </tr>
                  <?php
                }
              } else {
                ?>
                <td colspan="3"><?php _e('There are no defined bands', 'tl'); ?></td>
                <?php
              }
              ?>
              </tbody>
            </table>
            
            <div class="hundred"><?php if($perc > 0 && $perc != 100) printf(__('Band widths should sum exactly 100%% (current total is %d%%)', 'tl'), $perc); ?></div>
            
            <input type="hidden" name="tl_hz_to_edit" id="tl_hz_to_edit" />
            <input type="hidden" name="tl_band_pos" id="tl_band_pos" />
            
            <br />

            <h3><?php _e('Current Decorators', 'tl'); ?></h3>
            <table cellspacing="0" class="widefat fixed">
              <thead>
                <tr class="thead">
                  <th class="manage-column column-start" scope="col"><?php _e('Start', 'tl'); ?></th>
                  <th class="manage-column column-dectype" scope="col"><?php _e('Type', 'tl'); ?></th>
                  <th class="manage-column column-unit" scope="col"><?php _e('Unit', 'tl'); ?></th>
                </tr>
              </thead>
              
              <tfoot>
                <tr class="thead">
                  <th class="manage-column column-start" scope="col"><?php _e('Start', 'tl'); ?></th>
                  <th class="manage-column column-dectype" scope="col"><?php _e('Type', 'tl'); ?></th>
                  <th class="manage-column column-unit" scope="col"><?php _e('Unit', 'tl'); ?></th>
                </tr>
              </tfoot>
              
              <tbody>
              <?php
              $cls = ' alternate';
              if(isset($tl['decorators']) && count($tl['decorators'])) {
                foreach($tl['decorators'] as $i => $dec) {
                  ?>
                  <tr id="dec_<?php print $i; ?>" class="visible-row<?php print ($cls = $cls == '' ? ' alternate' : ''); ?>">
                    <td class="start"><?php print $dec['startTime']; ?><br />
                      <div class="row-actions">
                        <a href="javascript://" onclick="inlineEditDecorator(<?php print $i; ?>);"><?php _e('Edit', 'tl'); ?></a> | 
                        <a href="javascript://" class="delete" onclick="if(confirm('<?php _e('Delete this decorator?', 'tl'); ?>')) inlineRemoveDecorator(<?php print $i; ?>);"><?php _e('Remove', 'tl'); ?></a>
                      </div>
                    </td>
                    <td class="dectype"><?php print isset($dec['endTime']) && !empty($dec['endTime']) ? __('Track', 'tl') : __('Point', 'tl'); ?></td>
                    <td class="unit"><?php print $units[$dec['unit']]; ?></td>
                  </tr>
                  <tr class="inline-edit-row" id="dec_<?php print $i; ?>_edit" style="display:none">
                    <td class="edit-dec" colspan="3">
                      <fieldset id="dec_<?php print $i; ?>_fs">
                        <h3><?php printf(__('Editing Decorator #%d', 'tl'), $i); ?></h3>
                        <?php tl_edit_dec_form($_GET['id'], $i, $dec); ?>
                        <div class="buttons inline-edit-save">
                          <a onclick="inlineEditDecorator(<?php print $i; ?>, true);" href="javascript://" class="button-secondary cancel alignleft"><?php _e('Close', 'tl'); ?></a>
                          <a onclick="inlineUpdateDecorator(<?php print $i; ?>);" href="javascript://" class="button-primary save alignright"><?php _e('Update Decorator', 'tl'); ?></a>
                          <div style="clear:both"></div>
                        </div>
                      </fieldset>
                    </td>
                  </tr>
                  <?php
                }
              } else {
                ?>
                <td colspan="3"><?php _e('There are no defined decorators', 'tl'); ?></td>
                <?php
              }
              ?>
              </tbody>
            </table>

            <div class="buttons">
              <input type="button" class="button-secondary" value="<?php _e('Export Timeline', 'tl'); ?>" onclick="exportTimeline('<?php print $_GET['id']; ?>');" />
            </div>

          </div>
        </div>

        <input type="hidden" name="tl_decorator_pos" id="tl_decorator_pos" />
        <div id="col-left">
          <div class="col-wrap">
              <div class="formline-outer" id="tinfo">
                <h3><?php _e('Basic information', 'tl'); ?></h3>
                <div class="formline">
                  <label for="tl_code"><?php _e('Publish Code', 'tl'); ?></label>
                  <input type="text" readonly="readonly" class="invisible" id="tl_code" onfocus="this.select()" onclick="this.select()" value="<?php print "[timeline id='{$tl['id']}']"; ?>" />
                </div>
                <?php tl_edit_tl_form($tl); ?>
                <div class="formline">
                  <div class="label"><?php _e('Bands', 'tl'); ?></div>
                  <div class="input">
                    <strong><?php print (string) count($tl['bands']); ?></strong> &nbsp; <a href="javascript://" onclick="showAddBand();"><?php _e('Add Band', 'tl'); ?></a>
                  </div>
                </div>
                <div class="formline">
                  <div class="label"><?php _e('Decorators', 'tl'); ?></div>
                  <div class="input">
                    <strong><?php print count($tl['decorators']); ?></strong> &nbsp; <a href="javascript://" onclick="showAddDecorator();"><?php _e('Add Decorator', 'tl'); ?></a>
                  </div>
                </div>
                <div class="formline">
                  <div class="label"><?php _e('Events', 'tl'); ?></div>
                  <div class="input">
                    <strong><?php print count($tl['events']); ?></strong> &nbsp; <a href="?page=timelines-admin&tl_action=edit-events&id=<?php print $_GET['id']; ?>"><?php _e('Manage Events', 'tl'); ?></a>
                  </div>
                </div>
                <div class="formline">
                  <div class="label"><?php _e('Related posts', 'tl'); ?></div>
                  <div class="input">
										<?php
                      $tposts = tl_get_publish_id($tl['id']);
											if($tposts) {
												$pst = array(); 
												?>
                          <strong><?php print count($tposts); ?></strong> &nbsp;
												<?php 
                          foreach($tposts as $p): 
                            if(!$p) continue; 
                            if(!$p->post_title) $p->post_title =__('Untitled post', 'tl') ; 
                            $pst[] = '<a href="post.php?post='.$p->ID.'&action=edit" title="'.sprintf(__('Edit post: %s (%s)', 'tl'), $p->post_title, $p->post_status).'">'.sprintf(__('#%d', 'tl'), $p->ID).'</a>';
                          endforeach; 
													print join(', ', $pst);
											} else {
												?>
                          <strong>0</strong> 
												<?php
											}
                    ?>
                  </div>
                </div>
                <div class="formline buttons">
                  <input type="button" class="button-secondary alignleft" value="<?php _e('&laquo; Timelines', 'tl'); ?>" onclick="location.href='?page=timelines-admin';" />
                  <input type="submit" class="button-primary" value="<?php _e('Update Timeline', 'tl'); ?>" onclick="this.form.tl_action.value = 'edited-timeline';" />
                  <input type="hidden" name="tl_action" id="tl_action" value="edited-timeline" />
                </div>
              </div>
              <div class="formline-outer" id="addband" style="display:none">
                <h3><?php _e('Add Band', 'tl'); ?></h3>
                <?php tl_edit_band_form($_GET['id']); ?>
                <div class="formline buttons">
                  <input type="button" id="closeaddband" class="button-secondary alignleft" value="<?php _e('Cancel', 'tl'); ?>" onclick="showAddBand(true);" />
							    <?php do_action('new_timeline_buttons', 'band'); ?>
                  <input type="button" class="button-primary" value="<?php _e('Add Band', 'tl'); ?>" onclick="addBand()" />
                </div>
              </div>
              <div class="formline-outer" id="adddec" style="display:none">
                <h3><?php _e('Add Decorator', 'tl'); ?></h3>
                <?php tl_edit_dec_form(/*$_GET['id']*/); ?>
                <div class="formline buttons">
                  <input type="button" id="closeadddec" class="button-secondary alignleft" value="<?php _e('Cancel', 'tl'); ?>" onclick="showAddDecorator(true);" />
							    <?php do_action('new_timeline_buttons', 'decorator'); ?>
                  <input type="button" class="button-primary" value="<?php _e('Add Decorator', 'tl'); ?>" onclick="inlineAddDecorator()" />
                </div>
              </div>
          </div>
        </div>
      </div>
      <div id="edit_hz" style="display:none">
        <fieldset id="edit_hz_fs">
          <h3><?php _e('Add Hot Zone', 'tl'); ?></h3>
          <?php tl_edit_hz_form(); ?>
          <p class="buttons inline-edit-save">
            <a onclick="showAddHotZone(<?php print $i; ?>, true);" href="javascript://" class="button-secondary cancel alignleft"><?php _e('Cancel', 'tl'); ?></a>
				    <?php do_action('new_timeline_buttons', 'hotzone'); ?>
            <a onclick="inlineAddHZ(<?php print $i; ?>);" href="javascript://" class="button-primary save alignright"><?php _e('Add', 'tl'); ?></a>
            <br style="clear:both" /> 
          </p>
        </fieldset>
      </div>
    </form>
  </div>
  <div id="colorpicker" style="display:none"></div>
<?php
}

function tl_edit_timeline_events() {
  $msg = '';
  if(isset($_POST['tl_action'])) {
    switch($_POST['tl_action']) {
      case 'add-event':
        $msg = tl_add_event($_GET['id'], $_POST['tl_event']) ? __('The event was successfully added', 'tl') : __('The event could not be added', 'tl');
        break;
      case 'remove-event':
        $msg = tl_remove_event($_GET['id'], $_POST['tl_event_pos']) ? __('The event was successfully removed', 'tl') : __('The event could not be removed', 'tl');
        break;
      case 'update-event':
        $msg = tl_update_event($_GET['id'], $_POST['tl_event_pos'], $_POST['tl_event_'.$_POST['tl_event_pos']]) ? __('The event was successfully modified', 'tl') : __('The event could not be edited', 'tl');
        break;
      case 'import-events':
			  $fok = (is_array($_FILES['tl_import_events']) && $_FILES['tl_import_events']['error'] == 0);
        $msg = ($fok && tl_import_events($_GET['id'], $_FILES['tl_import_events'])) ? __('Events successfully imported', 'tl') : __('The sent events could not be imported', 'tl');
        break;
    }
  }
  $tl = tl_get_timeline($_GET['id']);
?>
  <div class="wrap tl_page">
    <?php if(!empty($msg)) print '<div class="updated"><p>'.$msg.'</p></div>'; ?>
    <div class="icon32" id="icon-tools">&nbsp;</div>
    <h2><?php printf(__('Timeline events for &quot;%s&quot;', 'tl'), $tl['name']); ?></h2>
    <form method="post" action="?page=<?php print $_GET['page']; ?>&tl_action=edit-events&id=<?php print $_GET['id']; ?>" id="tl_form">
      <div id="col-container">
        <div id="col-right">
          <div class="col-wrap">
            <h3><?php _e('Current Events', 'tl'); ?></h3>
            <table cellspacing="0" class="widefat">
              <thead>
                <tr class="thead">
                  <th class="manage-column column-title" scope="col"><?php _e('Title', 'tl'); ?></th>
                  <th class="manage-column column-start" scope="col"><?php _e('Start', 'tl'); ?></th>
                  <th class="manage-column column-end" scope="col"><?php _e('End', 'tl'); ?></th>
                </tr>
              </thead>
              
              <tfoot>
                <tr class="thead">
                  <th class="manage-column column-title" scope="col"><?php _e('Title', 'tl'); ?></th>
                  <th class="manage-column column-start" scope="col"><?php _e('Start', 'tl'); ?></th>
                  <th class="manage-column column-end" scope="col"><?php _e('End', 'tl'); ?></th>
                </tr>
              </tfoot>
              
              <tbody>
              <?php
              if(isset($tl['events']) && count($tl['events'])) {
								$cls = ' alternate';
                foreach($tl['events'] as $i => $event) {
									$cls = $cls == '' ? ' alternate' : '';
                  ?>
                  <tr id="event_<?php print $i; ?>" class="<?php print $cls; ?>">
                    <td class="title"><strong><?php print $event['title']; ?></strong><br />
                      <div class="row-actions">
                        <a href="javascript://" onclick="inlineEditEvent(<?php print $i; ?>);"><?php _e('Edit', 'tl'); ?></a> | 
                        <a href="javascript://" class="delete" onclick="if(confirm('<?php _e('Delete this event?', 'tl'); ?>')) removeEvent(<?php print $i; ?>);"><?php _e('Delete', 'tl'); ?></a>
                      </div>
                    </td>
                    <td class="start"><?php print $event['start']; ?></td>
                    <td class="end"><?php print $event['end']; ?></td>
                  </tr>
                  <tr id="event_<?php print $i; ?>_edit" style="display:none" class="inline-edit-row<?php print $cls; ?>">
                    <td colspan="3">
                      <fieldset>
                        <?php tl_edit_event_form($i, $event); ?>
                      </fieldset>
                      <div class="buttons inline-edit-save">
                        <a onclick="inlineEditEvent(<?php print $i; ?>, true);" href="javascript://" class="button-secondary cancel alignleft"><?php _e('Close', 'tl'); ?></a>
                        <a onclick="inlineUpdateEvent(<?php print $i; ?>);" href="javascript://" class="button-primary save alignright"><?php _e('Update Event', 'tl'); ?></a>
                        <div style="clear:both"></div>
                      </div>
                    </td>
                  </tr>
                  <?php
                }
              } else {
                ?>
                <tr>
                  <td colspan="3"><?php _e('There are no defined events', 'tl'); ?></td>
                </tr>
                <?php
              }
              ?>
              </tbody>
            </table>
            
            <div class="buttons">
              <div id="importevents" style="display:none">
                <a onclick="showImportEvents(true);" href="javascript://" class="button-secondary alignleft"><?php _e('Close', 'tl'); ?></a>
                <label for="tl_import_events"><?php _e('Select File', 'tl'); ?></label>
                <input type="file" name="tl_import_events" id="tl_import_events" />
                <a onclick="var e = jQuery('#tl_import_events');
                    if(!/\.tev$/.test(e.val())) { alert('<?php _e('The selected file must have &quot;.TEV&quot; extension.', 'tl'); ?>'); e.focus(); return; } 
                    jQuery('#tl_form').attr('encoding', 'multipart/form-data'); importEvents();" href="javascript://" class="button-primary"><?php _e('Import', 'tl'); ?></a>
              </div>
              <div id="importexport">
              <a onclick="exportEvents();" href="javascript://" class="button-secondary"><?php _e('Export Events', 'tl'); ?></a> 
              <a onclick="showImportEvents();" href="javascript://" class="button-secondary"><?php _e('Import Events', 'tl'); ?></a>
              </div>
            </div>
            <input type="hidden" name="tl_event_pos" id="tl_event_pos" />
          </div>
        </div>
        <div id="col-left">
          <div class="col-wrap">
            <div class="formline-outer" id="addevent">
              <h3><?php _e('Add Event', 'tl'); ?></h3>
              <?php tl_edit_event_form(); ?>
              <div class="formline buttons">
                <input type="hidden" name="tl_action" id="tl_action" value="add-event" />
                <input type="button" class="button-secondary alignleft" value="<?php _e('&laquo; Timelines', 'tl'); ?>" onclick="location.href='?page=timelines-admin';" />
                <input type="button" class="button-secondary alignleft" value="<?php _e('&laquo; Edit Timeline', 'tl'); ?>" onclick="location.href='?page=timelines-admin&tl_action=edit&id=<?php print $_GET['id']; ?>';" />
                <?php do_action('new_timeline_buttons', 'event'); ?>
                <input type="button" class="button-primary" value="<?php _e('Add Event', 'tl'); ?>" onclick="addEvent()" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
  <div id="colorpicker" style="display:none"></div>
<?php
}

function tl_edit_tl_form($tl = NULL) {
  ?>
	  <?php if(isset($tl['id'])) { ?>
    <input type="hidden" name="tl_timeline[id]" id="tl_id" value="<?php print $tl['id']; ?>" />
	  <?php } ?>
    <div class="formline">
      <label for="tl_name"><?php _e('Name', 'tl'); ?></label>
      <input type="text" name="tl_timeline[name]" id="tl_name"<?php if(isset($tl['name'])) print " value=\"{$tl['name']}\" readonly=\"readonly\""; ?> />
    </div>
    <div class="formline">
      <label for="tl_timezone"><?php _e('Time zone', 'tl'); ?></label>
      <input type="text" name="tl_timeline[timezone]" id="tl_timezone"<?php if(isset($tl['timezone'])) print " value=\"{$tl['timezone']}\""; ?> />
    </div>
    <div class="formline">
      <label for="tl_orientation"><?php _e('Orientation', 'tl'); ?></label>
      <select name="tl_timeline[orientation]" id="tl_orientation">
        <option value="horizontal"<?php if(!$tl || (isset($tl['orientation']) && $tl['orientation'] == 'horizontal')) print ' selected="selected"'; ?>><?php _e('Horizontal', 'tl'); ?></option>
        <option value="vertical"<?php if(isset($tl['orientation']) && $tl['orientation'] == 'vertical') print ' selected="selected"'; ?>><?php _e('Vertical', 'tl'); ?></option>
      </select>
    </div>
    <div class="formline">
      <label for="tl_date"><?php _e('Date on center', 'tl'); ?></label>
      <?php tl_date_field('tl_date', 'tl_timeline[date]', isset($tl['date']) ? $tl['date'] : NULL); ?>
    </div>
    <div class="formline">
      <label for="tl_width"><?php _e('Widget Width', 'tl'); ?></label>
      <input type="text" name="tl_timeline[width]" id="tl_width"<?php if(isset($tl['width'])) print " value=\"{$tl['width']}\""; ?> />
    </div>
    <div class="formline">
      <label for="tl_height"><?php _e('Widget Height', 'tl'); ?></label>
      <input type="text" name="tl_timeline[height]" id="tl_height"<?php if(isset($tl['height'])) print " value=\"{$tl['height']}\""; ?> />
    </div>
    <div class="formline">
      <label for="tl_bubble_width"><?php _e('Bubble Width', 'tl'); ?></label>
      <input type="text" name="tl_timeline[bubble_width]" id="tl_bubble_width"<?php if(isset($tl['bubble_width'])) print " value=\"{$tl['bubble_width']}\""; ?> />
    </div>
    <div class="formline">
      <label for="tl_bubble_height"><?php _e('Bubble Height', 'tl'); ?></label>
      <input type="text" name="tl_timeline[bubble_height]" id="tl_bubble_height"<?php if(isset($tl['bubble_height'])) print " value=\"{$tl['bubble_height']}\""; ?> />
    </div>
	<?php
}

function tl_edit_event_form($eid = NULL, $event = NULL) {
  if($eid !== NULL) $eid = (string) (int) $eid;
  ?>
    <div class="formline">
      <label for="tl_event<?php if($eid !== NULL) print '_'.$eid; ?>_title"><?php _e('Title', 'tl'); ?></label>
      <input type="text" id="tl_event<?php if($eid !== NULL) print '_'.$eid; ?>_title" name="tl_event<?php if($eid !== NULL) print '_'.$eid; ?>[title]" value="<?php if(isset($event['title'])) print $event['title']; ?>" />
    </div>
    <div class="formline">
      <label for="tl_event<?php if($eid !== NULL) print '_'.$eid; ?>_start"><?php _e('Start Date', 'tl'); ?></label>
      <?php tl_date_field('tl_event'.($eid !== NULL ? '_'.$eid : '').'_start', 'tl_event'.($eid !== NULL ? '_'.$eid : '').'[start]', isset($event['start']) ? $event['start'] : NULL); ?>
    </div>
    <div class="formline">
      <label for="tl_event<?php if($eid !== NULL) print '_'.$eid; ?>_end"><?php _e('End Date', 'tl'); ?></label>
      <?php tl_date_field('tl_event'.($eid !== NULL ? '_'.$eid : '').'_end', 'tl_event'.($eid !== NULL ? '_'.$eid : '').'[end]', isset($event['end']) ? $event['end'] : NULL); ?>
    </div>
    <div class="formline">
      <label for="tl_event<?php if($eid !== NULL) print '_'.$eid; ?>_caption"><?php _e('Caption', 'tl'); ?></label>
      <input type="text" id="tl_event<?php if($eid !== NULL) print '_'.$eid; ?>_caption" name="tl_event<?php if($eid !== NULL) print '_'.$eid; ?>[caption]" value="<?php if(isset($event['caption'])) print $event['caption']; ?>" />
    </div>
    <div class="formline">
      <label for="tl_event<?php if($eid !== NULL) print '_'.$eid; ?>_link"><?php _e('Link URL', 'tl'); ?></label>
      <input type="text" id="tl_event<?php if($eid !== NULL) print '_'.$eid; ?>_link" name="tl_event<?php if($eid !== NULL) print '_'.$eid; ?>[link]" value="<?php if(isset($event['link'])) print $event['link']; ?>" />
    </div>
    <div class="formline">
      <label for="tl_event<?php if($eid !== NULL) print '_'.$eid; ?>_color"><?php _e('Bar Color', 'tl'); ?></label>
      <input type="text" id="tl_event<?php if($eid !== NULL) print '_'.$eid; ?>_color" name="tl_event<?php if($eid !== NULL) print '_'.$eid; ?>[color]" value="<?php if(isset($event['color'])) print $event['color']; ?>" class="color-field" />
    </div>
    <div class="formline">
      <label for="tl_event<?php if($eid !== NULL) print '_'.$eid; ?>_textColor"><?php _e('Text Color', 'tl'); ?></label>
      <input type="text" id="tl_event<?php if($eid !== NULL) print '_'.$eid; ?>_textColor" name="tl_event<?php if($eid !== NULL) print '_'.$eid; ?>[textColor]" value="<?php if(isset($event['textColor'])) print $event['textColor']; ?>" class="color-field" />
    </div>
    <div class="formline">
      <label for="tl_event<?php if($eid !== NULL) print '_'.$eid; ?>_icon"><?php _e('Icon', 'tl'); ?></label>
      <input type="text" id="tl_event<?php if($eid !== NULL) print '_'.$eid; ?>_icon" name="tl_event<?php if($eid !== NULL) print '_'.$eid; ?>[icon]" value="<?php if(isset($event['icon'])) print $event['icon']; ?>" />
    </div>
    <div class="formline">
      <label for="tl_event<?php if($eid !== NULL) print '_'.$eid; ?>_image"><?php _e('Image', 'tl'); ?></label>
      <input type="text" id="tl_event<?php if($eid !== NULL) print '_'.$eid; ?>_image" name="tl_event<?php if($eid !== NULL) print '_'.$eid; ?>[image]" value="<?php if(isset($event['image'])) print $event['image']; ?>" />
    </div>
    <div class="formline tarea">
      <label for="tl_event<?php if($eid !== NULL) print '_'.$eid; ?>_description"><?php _e('Event Text', 'tl'); ?></label>
      <textarea id="tl_event<?php if($eid !== NULL) print '_'.$eid; ?>_description" name="tl_event<?php if($eid !== NULL) print '_'.$eid; ?>[description]"><?php if(isset($event['description'])) print $event['description']; ?></textarea>
    </div>
  <?php
}

function tl_edit_dec_form($bid = 0, $did = 0, $dec = NULL) {
  $tl = tl_get_timeline($bid);
  $bands = count($tl['bands']);
  ?>
  <div class="decorator-<?php print $dec !== NULL ? $did : 'add'; ?>">
    <div class="formline track point">
      <label for="tl_dec<?php if($dec !== NULL) print '_'.$did; ?>_type"><?php _e('Type', 'tl'); ?></label>
      <select name="tl_dec<?php if($dec !== NULL) print '_'.$did; ?>[type]" id="tl_dec<?php if($dec !== NULL) print '_'.$did; ?>_type" onchange="changeAddDecType(this.value, <?php print $dec !== NULL ? (string) $did : 'false'; ?>)">
        <option value="track"<?php if(!$dec || ($dec !== NULL && $dec['type'] == 'track')) print ' selected="selected"'; ?>><?php _e('Track', 'tl'); ?></option>
        <option value="point"<?php if($dec !== NULL && $dec['type'] == 'point') print ' selected="selected"'; ?>><?php _e('Point', 'tl'); ?></option>
      </select>
    </div>
    <div class="formline track point">
      <label for="tl_dec<?php if($dec !== NULL) print '_'.$did; ?>_unit"><?php _e('Unit', 'tl'); ?></label>
      <select name="tl_dec<?php if($dec !== NULL) print '_'.$did; ?>[unit]" id="tl_dec<?php if($dec !== NULL) print '_'.$did; ?>_unit">
        <option value="MILLISECOND"<?php if($dec !== NULL && $dec['unit'] == 'MILLISECOND') print ' selected="selected"'; ?>><?php _e('Milliseconds', 'tl'); ?></option>
        <option value="SECOND"<?php if($dec !== NULL && $dec['unit'] == 'SECOND') print ' selected="selected"'; ?>><?php _e('Seconds', 'tl'); ?></option>
        <option value="MINUTE"<?php if($dec !== NULL && $dec['unit'] == 'MINUTE') print ' selected="selected"'; ?>><?php _e('Minutes', 'tl'); ?></option>
        <option value="HOUR"<?php if($dec !== NULL && $dec['unit'] == 'HOUR') print ' selected="selected"'; ?>><?php _e('Hours', 'tl'); ?></option>
        <option value="DAY"<?php if($dec !== NULL && $dec['unit'] == 'DAY') print ' selected="selected"'; ?>><?php _e('Days', 'tl'); ?></option>
        <option value="WEEK"<?php if($dec !== NULL && $dec['unit'] == 'WEEK') print ' selected="selected"'; ?>><?php _e('Weeks', 'tl'); ?></option>
        <option value="MONTH"<?php if($dec !== NULL && $dec['unit'] == 'MONTH') print ' selected="selected"'; ?>><?php _e('Months', 'tl'); ?></option>
        <option value="YEAR"<?php if($dec !== NULL && $dec['unit'] == 'YEAR') print ' selected="selected"'; ?>><?php _e('Years', 'tl'); ?></option>
        <option value="DECADE"<?php if($dec !== NULL && $dec['unit'] == 'DECADE') print ' selected="selected"'; ?>><?php _e('Decades', 'tl'); ?></option>
        <option value="CENTURY"<?php if($dec !== NULL && $dec['unit'] == 'CENTURY') print ' selected="selected"'; ?>><?php _e('Centuries', 'tl'); ?></option>
        <option value="MILLENNIUM"<?php if($dec !== NULL && $dec['unit'] == 'MILLENNIUM') print ' selected="selected"'; ?>><?php _e('Millenniums', 'tl'); ?></option>
      </select>
    </div>
    <div class="formline track point">
      <label for="tl_dec<?php if($dec !== NULL) print '_'.$did; ?>_startTime"><?php _e('Start Date', 'tl'); ?></label>
      <?php 
        $start = isset($dec['startTime']) ? $dec['startTime'] : (isset($dec['date']) ? $dec['date'] : NULL);
        tl_date_field('tl_dec'.($dec !== NULL ? '_'.$did : '').'_startTime', 'tl_dec'.($dec !== NULL ? '_'.$did : '').'[startTime]', $start); 
      ?>
    </div>
    <div class="formline track">
      <label for="tl_dec<?php if($dec !== NULL) print '_'.$did; ?>_endTime"><?php _e('End Date', 'tl'); ?></label>
      <?php tl_date_field('tl_dec'.($dec !== NULL ? '_'.$did : '').'_endTime', 'tl_dec'.($dec !== NULL ? '_'.$did : '').'[endTime]', $dec !== NULL ? $dec['endTime'] : NULL); ?>
    </div>
    <div class="formline track">
      <label for="tl_dec<?php if($dec !== NULL) print '_'.$did; ?>_startLabel"><?php _e('Start Label', 'tl'); ?></label>
      <input type="text" name="tl_dec<?php if($dec !== NULL) print '_'.$did; ?>[startLabel]" id="tl_dec<?php if($dec !== NULL) print '_'.$did; ?>_startLabel"<?php if($dec !== NULL) print ' value="'.$dec['startLabel'].'"'; ?> />
    </div>
    <div class="formline track">
      <label for="tl_dec<?php if($dec !== NULL) print '_'.$did; ?>_endLabel"><?php _e('End Label', 'tl'); ?></label>
      <input type="text" name="tl_dec<?php if($dec !== NULL) print '_'.$did; ?>[endLabel]" id="tl_dec<?php if($dec !== NULL) print '_'.$did; ?>_endLabel"<?php if($dec !== NULL) print ' value="'.$dec['endLabel'].'"'; ?> />
    </div>
    <div class="formline point" style="display:none">
      <label for="tl_dec<?php if($dec !== NULL) print '_'.$did; ?>_width"><?php _e('Width', 'tl'); ?></label>
      <input type="text" name="tl_dec<?php if($dec !== NULL) print '_'.$did; ?>[width]" id="tl_dec<?php if($dec !== NULL) print '_'.$did; ?>_width"<?php if($dec !== NULL) print ' value="'.$dec['width'].'"'; ?> />
    </div>
    <div class="formline track point">
      <label for="tl_dec<?php if($dec !== NULL) print '_'.$did; ?>_color"><?php _e('Color', 'tl'); ?></label>
      <input type="text" id="tl_dec<?php if($dec !== NULL) print '_'.$did; ?>_color" name="tl_dec<?php if($dec !== NULL) print '_'.$did; ?>[color]"<?php if($dec !== NULL) print ' value="'.$dec['color'].'"'; ?> class="color-field" />
    </div>
    <div class="formline track point">
      <label for="tl_dec<?php if($dec !== NULL) print '_'.$did; ?>_cssClass"><?php _e('CSS Class', 'tl'); ?></label>
      <input type="text" name="tl_dec<?php if($dec !== NULL) print '_'.$did; ?>[cssClass]" id="tl_dec<?php if($dec !== NULL) print '_'.$did; ?>_cssClass"<?php if($dec !== NULL) print ' value="'.$dec['cssClass'].'"'; ?> />
    </div>
    <div class="formline track point">
      <label for="tl_dec<?php if($dec !== NULL) print '_'.$did; ?>_opacity"><?php _e('Opacity', 'tl'); ?></label>
      <input type="text" name="tl_dec<?php if($dec !== NULL) print '_'.$did; ?>[opacity]" id="tl_dec<?php if($dec !== NULL) print '_'.$did; ?>_opacity"<?php if($dec !== NULL) print ' value="'.$dec['opacity'].'"'; ?> />
    </div>
    <div class="formline track">
      <label for="tl_dec<?php if($dec !== NULL) print '_'.$did; ?>_inFront"><?php _e('Show on Top', 'tl'); ?></label>
      <select name="tl_dec<?php if($dec !== NULL) print '_'.$did; ?>[inFront]" id="tl_dec<?php if($dec !== NULL) print '_'.$did; ?>_inFront"<?php if($dec !== NULL) print ' value="'.$dec['inFront'].'"'; ?>>
        <option value="1"<?php if($dec !== NULL && $dec['inFront'] == 1) print ' selected="selected"'; ?>><?php _e('yes', 'tl'); ?></option>
        <option value="0"<?php if($dec === NULL || !$dec['inFront']) print ' selected="selected"'; ?>><?php _e('no', 'tl'); ?></option>
      </select>
    </div>
  </div>
  <script type="text/javascript" defer="defer">
  <?php if($dec !== NULL) print 'changeAddDecType("'.$dec['type'].'");'; else { ?>
  jQuery(document).ready(function() { setTimeout(function() { jQuery('#tl_dec<?php if($dec !== NULL) print '_'.$did; ?>_type').attr('selectedIndex', 0); changeAddDecType('track'); }, 400); } );
  <?php } ?>
  </script>
  <?php
}

function tl_edit_hz_form($bid = 0, $hzid = 0, $hz = NULL) {
  ?>
    <div class="formline">
      <label for="tl_<?php if($hz !== NULL) print $bid.'_'.$hzid.'_'; ?>hz_startTime"><?php _e('Start', 'tl'); ?></label>
      <?php tl_date_field('tl_'.($hz !== NULL ? $bid.'_'.$hzid.'_' : '').'hz_startTime', 'tl_'.($hz !== NULL ? $bid.'_'.$hzid.'_' : '').'hz[startTime]', $hz !== NULL ? $hz['startTime'] : NULL); ?>
    </div>
    <div class="formline">
      <label for="tl_<?php if($hz !== NULL) print $bid.'_'.$hzid.'_'; ?>hz_endTime"><?php _e('End', 'tl'); ?></label>
      <?php tl_date_field('tl_'.($hz !== NULL ? $bid.'_'.$hzid.'_' : '').'hz_endTime', 'tl_'.($hz !== NULL ? $bid.'_'.$hzid.'_' : '').'hz[endTime]', $hz !== NULL ? $hz['endTime'] : NULL); ?>
    </div>
    <div class="formline">
      <label for="tl_<?php if($hz !== NULL) print $bid.'_'.$hzid.'_'; ?>hz_magnify"><?php _e('Magnify', 'tl'); ?></label>
      <input type="text" name="tl_<?php if($hz !== NULL) print $bid.'_'.$hzid.'_'; ?>hz[magnify]" id="tl_<?php if($hz !== NULL) print $bid.'_'.$hzid.'_'; ?>hz_magnify"<?php if($hz !== NULL) print ' value="'.$hz['magnify'].'"'; ?> />
    </div>
    <div class="formline">
      <label for="tl_<?php if($hz !== NULL) print $bid.'_'.$hzid.'_'; ?>hz_unit"><?php _e('Unit', 'tl'); ?></label>
      <select name="tl_<?php if($hz !== NULL) print $bid.'_'.$hzid.'_'; ?>hz[unit]" id="tl_<?php if($hz !== NULL) print $bid.'_'.$hzid.'_'; ?>hz_unit">
        <option value="MILLISECOND"<?php if($hz !== NULL && $hz['unit'] == 'MILLISECOND') print ' selected="selected"'; ?>><?php _e('Milliseconds', 'tl'); ?></option>
        <option value="SECOND"<?php if($hz !== NULL && $hz['unit'] == 'SECOND') print ' selected="selected"'; ?>><?php _e('Seconds', 'tl'); ?></option>
        <option value="MINUTE"<?php if($hz !== NULL && $hz['unit'] == 'MINUTE') print ' selected="selected"'; ?>><?php _e('Minutes', 'tl'); ?></option>
        <option value="HOUR"<?php if($hz !== NULL && $hz['unit'] == 'HOUR') print ' selected="selected"'; ?>><?php _e('Hours', 'tl'); ?></option>
        <option value="DAY"<?php if($hz !== NULL && $hz['unit'] == 'DAY') print ' selected="selected"'; ?>><?php _e('Days', 'tl'); ?></option>
        <option value="WEEK"<?php if($hz !== NULL && $hz['unit'] == 'WEEK') print ' selected="selected"'; ?>><?php _e('Weeks', 'tl'); ?></option>
        <option value="MONTH"<?php if($hz !== NULL && $hz['unit'] == 'MONTH') print ' selected="selected"'; ?>><?php _e('Months', 'tl'); ?></option>
        <option value="YEAR"<?php if($hz !== NULL && $hz['unit'] == 'YEAR') print ' selected="selected"'; ?>><?php _e('Years', 'tl'); ?></option>
        <option value="DECADE"<?php if($hz !== NULL && $hz['unit'] == 'DECADE') print ' selected="selected"'; ?>><?php _e('Decades', 'tl'); ?></option>
        <option value="CENTURY"<?php if($hz !== NULL && $hz['unit'] == 'CENTURY') print ' selected="selected"'; ?>><?php _e('Centuries', 'tl'); ?></option>
        <option value="MILLENNIUM"<?php if($hz !== NULL && $hz['unit'] == 'MILLENNIUM') print ' selected="selected"'; ?>><?php _e('Millenniums', 'tl'); ?></option>
      </select>
    </div>
    <div class="formline">
      <label for="tl_<?php if($hz !== NULL) print $bid.'_'.$hzid.'_'; ?>hz_multiple"><?php _e('Multiple', 'tl'); ?></label>
      <input type="text" name="tl_<?php if($hz !== NULL) print $bid.'_'.$hzid.'_'; ?>hz[multiple]" id="tl_<?php if($hz !== NULL) print $bid.'_'.$hzid.'_'; ?>hz_multiple"<?php if($hz !== NULL) print ' value="'.$hz['multiple'].'"'; ?> />
    </div>
  <?php
}

function tl_edit_band_form($tid = 0, $bid = 0, $band = NULL) {
  $tl = $tid ? tl_get_timeline($tid) : NULL;
  //print $tid;
  //print_r($tl);
  ?>
    <div class="formline">
      <label for="tl_<?php if($band !== NULL) print $bid.'_'; ?>band_width"><?php _e('Width', 'tl'); ?></label>
      <select name="tl_<?php if($band !== NULL) print $bid.'_'; ?>band[width]" id="tl_<?php if($band !== NULL) print $bid.'_'; ?>band_width">
      <?php for($i = 100; $i > 0; $i -= 5) { ?>
        <option value="<?php print $i; ?>"<?php if($tid === false || $band['width'] == $i) print ' selected="selected"'; ?>><?php print $i; ?>%</option>
      <?php } ?>
      </select>
    </div>

    <div class="formline">
      <label for="tl_<?php if($band !== NULL) print $bid.'_'; ?>band_intervalUnit"><?php _e('Interval unit', 'tl'); ?></label>
      <select name="tl_<?php if($band !== NULL) print $bid.'_'; ?>band[intervalUnit]" id="tl_<?php if($band !== NULL) print $bid.'_'; ?>band_intervalUnit">
        <option value="MILLISECOND"<?php if($band !== NULL && $band['intervalUnit'] == 'MILLISECOND') print ' selected="selected"'; ?>><?php _e('Milliseconds', 'tl'); ?></option>
        <option value="SECOND"<?php if($band !== NULL && $band['intervalUnit'] == 'SECOND') print ' selected="selected"'; ?>><?php _e('Seconds', 'tl'); ?></option>
        <option value="MINUTE"<?php if($band !== NULL && $band['intervalUnit'] == 'MINUTE') print ' selected="selected"'; ?>><?php _e('Minutes', 'tl'); ?></option>
        <option value="HOUR"<?php if($band !== NULL && $band['intervalUnit'] == 'HOUR') print ' selected="selected"'; ?>><?php _e('Hours', 'tl'); ?></option>
        <option value="DAY"<?php if($band !== NULL && $band['intervalUnit'] == 'DAY') print ' selected="selected"'; ?>><?php _e('Days', 'tl'); ?></option>
        <option value="WEEK"<?php if($band !== NULL && $band['intervalUnit'] == 'WEEK') print ' selected="selected"'; ?>><?php _e('Weeks', 'tl'); ?></option>
        <option value="MONTH"<?php if($band !== NULL && $band['intervalUnit'] == 'MONTH') print ' selected="selected"'; ?>><?php _e('Months', 'tl'); ?></option>
        <option value="YEAR"<?php if($band !== NULL && $band['intervalUnit'] == 'YEAR') print ' selected="selected"'; ?>><?php _e('Years', 'tl'); ?></option>
        <option value="DECADE"<?php if($band !== NULL && $band['intervalUnit'] == 'DECADE') print ' selected="selected"'; ?>><?php _e('Decades', 'tl'); ?></option>
        <option value="CENTURY"<?php if($band !== NULL && $band['intervalUnit'] == 'CENTURY') print ' selected="selected"'; ?>><?php _e('Centuries', 'tl'); ?></option>
        <option value="MILLENNIUM"<?php if($band !== NULL && $band['intervalUnit'] == 'MILLENNIUM') print ' selected="selected"'; ?>><?php _e('Millenniums', 'tl'); ?></option>
      </select>
    </div>

    <div class="formline">
      <label for="tl_<?php if($band !== NULL) print $bid.'_'; ?>band_timeZone"><?php _e('Time zone', 'tl'); ?></label>
      <input type="text" name="tl_<?php if($band !== NULL) print $bid.'_'; ?>band[timeZone]" id="tl_<?php if($band !== NULL) print $bid.'_'; ?>band_timeZone"<?php print ' value="'.(isset($band['timeZone']) ? $band['timeZone'] : ($tl ? $tl['timezone'] : '')).'"'; ?> />
    </div>
    <div class="formline">
      <label for="tl_<?php if($band !== NULL) print $bid.'_'; ?>band_intervalPixels"><?php _e('Interval size', 'tl'); ?></label>
      <input type="text" name="tl_<?php if($band !== NULL) print $bid.'_'; ?>band[intervalPixels]" id="tl_<?php if($band !== NULL) print $bid.'_'; ?>band_intervalPixels"<?php if($band !== NULL) print ' value="'.$band['intervalPixels'].'"'; ?> />
    </div>
    <div class="formline">
      <label for="tl_<?php if($band !== NULL) print $bid.'_'; ?>band_trackHeight"><?php _e('Track height', 'tl'); ?></label>
      <input type="text" name="tl_<?php if($band !== NULL) print $bid.'_'; ?>band[trackHeight]" id="tl_<?php if($band !== NULL) print $bid.'_'; ?>band_trackHeight"<?php if($band !== NULL) print ' value="'.$band['trackHeight'].'"'; ?> />
    </div>
    <div class="formline">
      <label for="tl_<?php if($band !== NULL) print $bid.'_'; ?>band_trackGap"><?php _e('Track gap', 'tl'); ?></label>
      <input type="text" name="tl_<?php if($band !== NULL) print $bid.'_'; ?>band[trackGap]" id="tl_<?php if($band !== NULL) print $bid.'_'; ?>band_trackGap"<?php if($band !== NULL) print ' value="'.$band['trackGap'].'"'; ?> />
    </div>
    <div class="formline">
      <label for="tl_<?php if($band !== NULL) print $bid.'_'; ?>band_showEventText"><?php _e('Show text in events', 'tl'); ?></label>
      <select name="tl_<?php if($band !== NULL) print $bid.'_'; ?>band[showEventText]" id="tl_<?php if($band !== NULL) print $bid.'_'; ?>band_showEventText">
        <option value="1"<?php if($tid === false || $band['showEventText'] == '1') print ' selected="selected"'; ?>><?php _e('yes', 'tl'); ?></option>
        <option value="0"<?php if($band !== NULL && $band['showEventText'] == '0') print ' selected="selected"'; ?>><?php _e('no', 'tl'); ?></option>
      </select>
    </div>
    <div class="formline">
      <label for="tl_<?php if($band !== NULL) print $bid.'_'; ?>band_layout"><?php _e('Layout', 'tl'); ?></label>
      <select name="tl_<?php if($band !== NULL) print $bid.'_'; ?>band[layout]" id="tl_<?php if($band !== NULL) print $bid.'_'; ?>band_layout">
        <option value="original"<?php if($tid === false || $band['layout'] == 'original') print ' selected="selected"'; ?>><?php _e('Original', 'tl'); ?></option>
        <option value="overview"<?php if($band !== NULL && $band['layout'] == 'overview') print ' selected="selected"'; ?>><?php _e('Overview', 'tl'); ?></option>
        <option value="detailed"<?php if($band !== NULL && $band['layout'] == 'detailed') print ' selected="selected"'; ?>><?php _e('Detailed', 'tl'); ?></option>
      </select>
    </div>

    <?php if($band !== NULL) { ?>
    <div class="formline">
      <div class="label"><?php _e('Hot Zones', 'tl'); ?></div>
      <div class="input">
        <strong><?php 
        if(isset($band['hotzones']) && count($band['hotzones'])) print count($band['hotzones']);
        else _e('No hot zones defined', 'tl');
        ?></strong> 
        <?php if(isset($band['hotzones']) && count($band['hotzones'])) { ?>
       &nbsp; <a onclick="showHZList(<?php print $i; ?>);" href="javascript://"><?php _e('Manage Hot Zones', 'tl'); ?></a>
        <?php } ?>
      </div>
    </div>
    <ul id="band_<?php print $bid; ?>_hotzones_list" style="display:none" class="hotzones-list">
      <?php 
      if(isset($band['hotzones']) && count($band['hotzones'])) {
        foreach($band['hotzones'] as $i => $hz) {
          ?>
          <li>
            <div class="formline">
              <label>&nbsp;</label>
              <div class="input">
                <strong><?php printf(__('Hot Zone #%d', 'tl'), $i); ?></strong> &nbsp;
                <a href="javascript://" onclick="showEditHZ(<?php print $bid; ?>, <?php print $i; ?>)"><?php _e('edit', 'tl'); ?></a> |
                <a onclick="if(confirm('<?php _e('Delete this hot zone?', 'tl'); ?>')) inlineRemoveHZ(<?php print $bid; ?>, <?php print $i; ?>);" href="javascript://" class="delete"><?php _e('remove', 'tl'); ?></a>
              </div>
            </div>
            <fieldset id="tl_band_<?php print $bid; ?>_hz_<?php print $i; ?>" style="display:none" class="hz-edit">
              <?php tl_edit_hz_form($bid, $i, $hz); ?>
              <div class="buttons inline-edit-save">
                <a onclick="showEditHZ(<?php print $bid; ?>, <?php print $i; ?>, true);" href="javascript://" class="button-secondary cancel alignleft"><?php _e('Close', 'tl'); ?></a>
                <a onclick="inlineUpdateHZ(<?php print $bid; ?>, <?php print $i; ?>);" href="javascript://" class="button-secondary save alignright"><?php _e('Update Hot Zone', 'tl'); ?></a>
                <div style="clear:both"></div>
              </div>
            </fieldset>
            <div style="clear:both"></div>
          </li>
          <?php
        }
      }
      ?>
    </ul>
    <?php } ?>
  <?php
}

function tl_date_field($id, $name = '', $val = '') {
  ?>
  <input type="text" name="<?php print empty($name) ? $id : $name; ?>" id="<?php print $id; ?>"<?php if(!empty($val)) print ' value="'.$val.'"'; ?> />
  <script type="text/javascript">
  /*<![CDATA[*/ addLoadEvent(function() { jQuery('#<?php print $id; ?>').attr('autocomplete', 'off').datetimepicker(); }); /*]]>*/
  </script>
  <?php
}

function tl_add_fav_link($actions) {
	$actions["upload.php?page=timelines-admin"] = array(__('Timelines', 'tl'), "manage_options");
  return $actions;
}
add_filter('favorite_actions', 'tl_add_fav_link');

?>
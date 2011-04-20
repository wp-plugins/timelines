
/**
 * Timelines
 */


  jQuery(document).ready(function() {
    if(jQuery('#colorpicker').length == 0) return;
    jQuery.farbtastic('#colorpicker');
    jQuery('.color-field').attr('autocomplete', 'off')
		  .focus(function() { jQuery(document).unbind('click'); showColors(this); })
			.blur(function() { if(this.value == '') this.style.backgroundColor = ''; });
  });

function showColors(field) {
  var fb = jQuery.farbtastic('#colorpicker').linkTo(function(c) { field.style.color = fb.hsl[2] > 0.5 ? '#000' : '#fff'; field.value = field.style.backgroundColor = c; });
  var e = jQuery(field), h = e.height(), pos = e.position(), top = pos.top + h + 5, left = pos.left;
  jQuery('#colorpicker').css({'position': 'absolute', 'top': top+'px', 'left': left+'px'}).show();
  setTimeout(function() { jQuery(document).click(function(e) { if(jQuery(e.target).is('#colorpicker, #colorpicker *')) return; jQuery('#colorpicker').hide(); jQuery(document).unbind('click'); }); }, 200);
}

function removeTimeline(tid) {
	submitForm('delete-timeline', {'tl_timeline_id': tid});
}

function showImportTimeline(clos) {
  jQuery('#importform')[clos ? 'slideUp' : 'slideDown']();
  jQuery('#newtimeline')[clos ? 'slideDown' : 'slideUp']();
}

function importTimeline() {
	submitForm('import-timeline');
}

function exportTimeline(tid) {
	submitForm('export-timeline', {'tl_timeline_id': tid});
}

function showAddBand(clos) {
  jQuery('#addband')[clos ? 'slideUp' : 'slideDown']();
  jQuery('#tinfo')[clos ? 'slideDown' : 'slideUp']();
}

function addBand() {
	submitForm('add-band');
}

function removeBand(n) {
	submitForm('remove-band', {'tl_band_pos': n});
}

function inlineEditBand(n, clos) {
  inlineEditCloseAll();
	jQuery('#band_'+String(n))[clos ? 'show' : 'hide']();
	jQuery('#band_'+String(n)+'_edit')[clos ? 'hide' : 'show']();
}

function inlineEditCloseAll() {
  if(jQuery('#edit_hz #edit_hz_fs').length == 0) {
    jQuery('#edit_hz').append(jQuery('#edit_hz_fs'));
    jQuery('.inline-edit-row fieldset').show();
  }
  jQuery('.visible-row').show();
  jQuery('.inline-edit-row').hide();
}

function inlineUpdateBand(n) {
	submitForm('update-band', {'tl_band_pos': n});
}

function addEvent() {
	submitForm('add-event');
}

function removeEvent(n) {
	submitForm('remove-event', {'tl_event_pos': n});
}

function saveEventsFile(n) {
	submitForm('save-events-file');
}

function inlineEditEvent(n, clos) {
	jQuery('#event_'+String(n))[clos ? 'show' : 'hide']();
	jQuery('#event_'+String(n)+'_edit')[clos ? 'hide' : 'show']();
}

function inlineUpdateEvent(n) {
	submitForm('update-event', {'tl_event_pos': n});
}

function showImportEvents(clos) {
  jQuery('#importevents')[clos ? 'slideUp' : 'slideDown']();
  jQuery('#importexport')[clos ? 'slideDown' : 'slideUp']();
}

function importEvents(clos) {
	submitForm('import-events');
}

function exportEvents(tid) {
	submitForm('export-events', {'tl_timeline_id': tid});
}

function showAddHotZone(n, clos) {
  if(clos) {
    jQuery('#edit_hz').append(jQuery('#edit_hz_fs'));
    jQuery('#band_'+String(n)+'_fs').show();
  } else {
    jQuery('#band_'+String(n)+'_fs').hide();
    jQuery('#band_'+String(n)+'_edit .edit-band').append(jQuery('#edit_hz_fs'));
    var as = jQuery('#edit_hz_fs p.submit a');
    as.eq(0).click(function() { showAddHotZone(n || 0, true); });
    as.eq(1).click(function() { inlineAddHZ(n); });
    jQuery('#tl_band_pos').val(String(n));
  }
}

function inlineAddHZ(n) {
	submitForm('add-hotzone');
}

function showEditHZ(b, n, clos) {
  jQuery('.hz-edit').hide();
  if(!clos) jQuery('#tl_band_'+b+'_hz_'+n).show();
}

function showHZList(b) {
  jQuery('#band_'+b+'_hotzones_list').toggle();
}

function inlineUpdateHZ(b, n) {
	submitForm('edit-hotzone', {'tl_band_pos': b, 'tl_hz_to_edit': n});
}

function showAddDecorator(clos) {
	jQuery('#adddec')[clos ? 'slideUp' : 'slideDown']();
	jQuery('#tinfo')[clos ? 'slideDown' : 'slideUp']();
}

function inlineAddDecorator() {
	submitForm('add-decorator');
}

function changeAddDecType(typ, ind) {
  var toshow = typ == 'track' ? 'track' : 'point', tohide = typ == 'track' ? 'point' : 'track', selcomp = ind !== false ? '.decorator-'+ind+' ' : '.decorator-add ';
  jQuery(selcomp+'.'+tohide).hide();
  jQuery(selcomp+'.'+toshow+', '+selcomp+'.track.point').show();
}

function inlineEditDecorator(n, clos) {
	jQuery('#dec_'+String(n))[clos ? 'show' : 'hide']();
	jQuery('#dec_'+String(n)+'_edit')[clos ? 'hide' : 'show']();
}

function inlineUpdateDecorator(n) {
	submitForm('update-decorator', {'tl_decorator_pos': n});
}

function inlineRemoveDecorator(n) {
	submitForm('remove-decorator', {'tl_decorator_pos': n});
}

function inlineRemoveHZ(b, n) {
	submitForm('remove-hotzone', {'tl_band_pos': b,  'tl_hz_to_edit': n});
}

function submitForm(act, fields) {
  jQuery('#tl_action').val(act);
	if(typeof fields != 'object') fields = {};
	for(var i in fields) jQuery('#'+i).val(String(fields[i]));
  jQuery('#tl_form').submit();
}

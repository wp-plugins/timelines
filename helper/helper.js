	function tl_helper(topic) {
		
		this.isOpen = false;
		this.isReady = false;
		this.current = topic;
		this.topics = tl_helper_topics;

		this.make = function() {
			var e = jQuery('#helper_div'), topic = this.topics[this.current];
			this.resetHelper();
			if(topic.bindCloseSelector !== undefined) jQuery(topic.bindCloseSelector).click(function() { tl_wizard.resetHelper(); });
			if(e.length == 0) {
				e = jQuery('<div id="helper_div" class="helper" style="display:none" />');
				jQuery('#tl_form').prepend(e).submit(function() { if(!tl_wizard.isReady) return false; return true; });
			}
			e.html('');
			var title = jQuery('<h3>'+topic.title+'</h3>'), subtitle = jQuery('<div class="subtit">'+topic.subtitle+'</div>'), content = jQuery('<p>'+topic.content+'</p>');
			e.append(title).append(subtitle).append(content).append('<div class="helper-content" />').append('<p class="helper-status" style="display:none" />')
			  .slideDown(300, function() { tl_wizard.show(tl_wizard.topics[tl_wizard.current].fields[0].id); });
			jQuery(topic.parentSelector+' input, '+topic.parentSelector+' select, '+topic.parentSelector+' textarea')
			  .focus(function() { if(this.name) tl_wizard.show(this.id); })
				.blur(function() { 
				  jQuery('#helper_div .helper-content h4').html(tl_helper_l10n.focus_on_field);
					jQuery('#helper_div .helper-content p').html('&nbsp;'); 
				  tl_wizard.setClasses(); 
				}).each(function() { var f = tl_wizard.getField(this.id); if(f && f.required) jQuery(this).addClass('required'); });
			this.isOpen = true;
			setTimeout(function() { tl_wizard.setClasses(); jQuery("#helper_div .helper-status").slideDown('fast'); }, 800); 
			jQuery('#'+topic.fields[0].id).focus();
		}
		
		this.setClasses = function() {
			jQuery(this.topics[this.current].parentSelector+' input, '+this.topics[this.current].parentSelector+' select, '+this.topics[this.current].parentSelector+' textarea').each(function() { 
			  var f = tl_wizard.getField(this.id), re;
			  if(f !== null && f.re !== undefined) re = f.re; 
				else re = /[a-z0-9]+/i;
			  var e = jQuery(this);
			  if(re.test(this.value)) e.addClass('filled'); 
				else { e.removeClass('filled'); e.removeClass('field-ready'); }
				if(e.hasClass('required') && e.hasClass('filled')) e.addClass('field-ready'); 
			});
			var ok = this.isGood();
			if(this.topics[this.current].readyCallback !== undefined) ok = this.topics[this.current].readyCallback();
			this.status(this.isReady = ok);
		}
		
		this.getField = function(id) {
			var fields = this.topics[this.current].fields;
		  for(var i = 0; i < fields.length; i++) {
			  if(fields[i].id == id) return fields[i];
			}
			return null;
		}
		
		this.status = function(ok) {
			var msg = ok ? tl_helper_l10n.form_filled_ok : tl_helper_l10n.form_filled_fail;
			jQuery('#helper_div .helper-status').html(msg)[ok ? 'removeClass'  : 'addClass']('missing');
		}
		
		this.show = function(id) {
			var e = jQuery('#helper_div .helper-content'), obj = this.getField(id);
			if(obj) e.html('').append('<h4>'+obj.name+'</h4>').append('<p>'+obj.description+'</p>');
		}
		
		this.isGood = function() {
			return (jQuery('.required').length == jQuery('.field-ready').length);
		}
		
		this.resetHelper = function() {
			jQuery(this.topics[this.current].parentSelector+' input, '+this.topics[this.current].parentSelector+' select, '+this.topics[this.current].parentSelector+' textarea').each(function() { 
			  jQuery(this).removeClass('required').removeClass('filled').removeClass('field-ready');
			});
			this.isOpen = false;
			jQuery('#helper_div').slideUp(300, function() { jQuery('#helper_div').remove(); });
		}
	}
	var tl_wizard = null;

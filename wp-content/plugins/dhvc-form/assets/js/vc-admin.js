(function ($) {
	if(_.isUndefined(window.vc)) window.vc = {};
	if(_.isUndefined(vc.edit_form_callbacks)) vc.edit_form_callbacks = [];

	
	
	var dhvc_controls = ['dhvc_form_checkbox','dhvc_form_color','dhvc_form_datetime','dhvc_form_file','dhvc_form_hidden','dhvc_form_password','dhvc_form_radio','dhvc_form_captcha','dhvc_form_recaptcha','dhvc_form_select','dhvc_form_multiple_select','dhvc_form_text','dhvc_form_email','dhvc_form_textarea','dhvc_form_rate','dhvc_form_slider','dhvc_form_label','dhvc_form_submit_button'];
	
	var dhvc_form_admin = function(){
		
		$('#dhvc_form_save,#submitdiv #publish').on('click',function(e){
			e.stopPropagation();
			e.preventDefault();
			var deferred,
				dhvc_form_control_object = [];
			deferred = $.Deferred(function() {
				console.log('s');
				_.each(vc.shortcodes.models,function(model){
					var shortcode = model.get('shortcode');
					if($.inArray(shortcode,dhvc_controls) > -1){
						var control = model.get('params');
							control['tag'] = shortcode;
						dhvc_form_control_object.push(control);
					}
				});
				$('#form_control').val(JSON.stringify(dhvc_form_control_object));
			});
			deferred.promise().always($('form#post').submit());
		});
	}
	dhvc_form_admin();
	var dhvc_form_frontend = function(){
		$( document ).ajaxSend(function( event, request, settings ) {
			if(settings.url === 'post.php'){
				var dhvc_form_control_object = [];
				_.each(vc.shortcodes.models,function(model){
					var shortcode = model.get('shortcode');
					if($.inArray(shortcode,dhvc_controls) > -1){
						var control = model.get('params');
							control['tag'] = shortcode;
						dhvc_form_control_object.push(control);
					}
				});
				var a = { form_control: JSON.stringify(dhvc_form_control_object)};
				
				settings.data += '&' + $.param(a);
			}
			
		});
	}
	
	vc.edit_form_callbacks.push(function() {
		var model = this.$el;
		var conditional_list = model.find('.dhvc-form-conditional-list');
		if(conditional_list.length){
			var conditionals = [];
			conditional_list.find('table tbody tr').each(function(){
				var $this = $(this);
				var conditional = {};
				if($this.find('#conditional-element').val() != ''){
					conditional['type'] = $this.find('#conditional-type').val();
					conditional['value'] = $this.find('#conditional-value').val();
					conditional['action'] = $this.find('#conditional-action').val();
					conditional['element'] = $this.find('#conditional-element').val();
					conditionals.push(conditional);
				}
			});
			if(_.isEmpty(conditionals)){
				this.params.conditional='';
			}else{
				conditionals_json = JSON.stringify(conditionals);
				this.params.conditional = base64_encode(conditionals_json);
			}
		}
		
		var rate_option_list = model.find('.dhvc-form-rate-option-list');
		if(rate_option_list.length){
			var rate_options = [];
			rate_option_list.find('table tbody tr').each(function(){
				var $this = $(this);
				var rate_option = {};
				rate_option['label'] = $this.find('#rate-label').val();
				rate_option['value'] = $this.find('#rate-value').val();
				rate_options.push(rate_option);
			});
			if(_.isEmpty(rate_options)){
				this.params.rate_option='';
			}else{
				rate_options_json = JSON.stringify(rate_options);
				this.params.rate_option = base64_encode(rate_options_json);
			}
		}
		
		var option_list = model.find('.dhvc-form-option-list');
		if(option_list.length){
			var options = [];
			option_list.find('table tbody tr').each(function(){
				var $this = $(this);
				var option = {};
				option['is_default'] = 0;
				if($this.find('#is_default').is(':checked')){
					option['is_default'] = 1;
				}
				option['label'] = $this.find('#label').val();
				option['value'] = $this.find('#value').val();
				options.push(option);
			});
			if(_.isEmpty(options)){
				
				this.params.options='';
			}else{
				options_json = JSON.stringify(options);
				this.params.options = base64_encode(options_json);
			}
		}
	});
})(window.jQuery);
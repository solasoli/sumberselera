
!function( $ ) {
	"use strict"; // jshint ;_;
	
	$(document).ready(function () {
		
		$('[data-auto-open].dhvc-form-popup').each(function(){
			var $this = $(this),
				id = $this.attr('id'),
				open_delay = $this.data('open-delay'),
				auto_close = $this.data('auto-close'),
				close_delay = $this.data('close-delay'),
				one_time = $this.data('one-time'),
				open_timeout,
				close_timeout;
			clearTimeout(open_timeout);
			clearTimeout(close_timeout);
			open_timeout = setTimeout(function(){
				clearTimeout(close_timeout);	
				
				if(one_time){
					if(!$.cookie(id)){
						$('.dhvc-form-pop-overlay').show();
						$('body').addClass('dhvc-form-opening');
						$this.show();
						$.cookie(id,1,{ expires: 360 * 10 , path: "/" });
					}
				}else{
					$.cookie(id,0,{ expires: -1});
					$('.dhvc-form-pop-overlay').show();
					$this.show();
				}
			},open_delay);
			
			if(auto_close){
				close_timeout = setTimeout(function(){
					clearTimeout(open_timeout);
					$('.dhvc-form-pop-overlay').hide();
					$('body').addClass('dhvc-form-opening');
					$this.hide();
					
				},close_delay);
			}
			
		});
		
		$(document).on('click','[data-toggle="dhvcformpopup"],[rel="dhvcformpopup"]',function(e){
			e.stopPropagation();
			e.preventDefault();
			var href;
			var $this = $(this);
			var $target = $($this.attr('data-target') || (href = $this.attr('href')) && href.replace(/.*(?=#[^\s]+$)/, '')); // strip for ie7
			if ($this.is('a')) e.preventDefault();
			$('.dhvc-form-pop-overlay').show();
			$('body').addClass('dhvc-form-opening');
			$target.show();
			$target.off('click').on('click',function(e){
				 if (e.target !== e.currentTarget) return
				$('.dhvc-form-pop-overlay').hide();
				$('body').removeClass('dhvc-form-opening');
				$target.hide();
				
			});
		});
		
		$(document).on('click','.dhvc-form-popup-close',function(e){
			$('.dhvc-form-pop-overlay').hide();
			$('body').removeClass('dhvc-form-opening');
			$(this).closest('.dhvc-form-popup').hide();
		});
		
		
		$('.dhvc-form-slider-control').each(function(){
			var $this = $(this);
			$this.slider({
				 min: $this.data('min'),
			     max: $this.data('max'),
			     step: $this.data('step'),
			     range: ($this.data('type') == 'range' ? true : 'min'),
			     slide: function(event, ui){
			    	 if($this.data('type') == 'range'){
			    		 $this.closest('.dhvc-form-group').find('.dhvc-form-slider-value-from').text(ui.values[0]);
			    		 $this.closest('.dhvc-form-group').find('.dhvc-form-slider-value-to').text(ui.values[1]);
			    		 $this.closest('.dhvc-form-group').find('input[type="hidden"]').val(ui.values[0] + '-' + ui.values[1]).trigger('change');
			    	 }else{
			    		 $this.closest('.dhvc-form-group').find('.dhvc-form-slider-value').text(ui.value);
			    		 $this.closest('.dhvc-form-group').find('input[type="hidden"]').val(ui.value).trigger('change');
			    	 }
			     }
			});
			if($this.data('type') == 'range'){
				$this.slider('values',[0,$this.data('minmax')]);
			}else{
				$this.slider('value',$this.data('value'));
			}
		});
		
		var basename = function(path, suffix) {
		  //  discuss at: http://phpjs.org/functions/basename/
		  // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
		  // improved by: Ash Searle (http://hexmen.com/blog/)
		  // improved by: Lincoln Ramsay
		  // improved by: djmix
		  // improved by: Dmitry Gorelenkov
		  //   example 1: basename('/www/site/home.htm', '.htm');
		  //   returns 1: 'home'
		  //   example 2: basename('ecra.php?p=1');
		  //   returns 2: 'ecra.php?p=1'
		  //   example 3: basename('/some/path/');
		  //   returns 3: 'path'
		  //   example 4: basename('/some/path_ext.ext/','.ext');
		  //   returns 4: 'path_ext'

		  var b = path;
		  var lastChar = b.charAt(b.length - 1);

		  if (lastChar === '/' || lastChar === '\\') {
		    b = b.slice(0, -1);
		  }

		  b = b.replace(/^.*[\/\\]/g, '');

		  if (typeof suffix === 'string' && b.substr(b.length - suffix.length) == suffix) {
		    b = b.substr(0, b.length - suffix.length);
		  }

		  return b;
		}

		
		var operators = {
		    '>': function(a, b) { return a > b },
		    '=': function(a, b) { return a == b },
		    '<': function(a, b) { return a < b }
		};
		var conditional_hook = function(e){
			var $this = $(e.currentTarget),
				form = $this.closest('form'),
				container_class = dhvcformL10n.container_class,
				master_container = $this.closest(container_class),
				master_value,
				is_empty,
				conditional_data = $this.data('conditional'),
				conditional_data2=[],
				conditional_current=null;
			
			master_value = $this.is(':checkbox') ? $.map(form.find('[data-conditional-name=' + $this.data('conditional-name') + '].dhvc-form-value:checked'),
	                function (element) {
						return $(element).val();
	            	})
	            : ($this.is(':radio') ? form.find('[data-conditional-name=' + $this.data('conditional-name') + '].dhvc-form-value:checked').val() : $this.val() );
	       is_empty = $this.is(':checkbox') ? !form.find('[data-conditional-name=' + $this.data('conditional-name') + '].dhvc-form-value:checked').length
                 : ( $this.is(':radio') ? !form.find('[data-conditional-name=' + $this.data('conditional-name') + '].dhvc-form-value:checked').val() : !master_value.length )  ;
	       
	       
	        if(is_empty){
	        	$.each(conditional_data,function(i,conditional){
	        		var elements = conditional.element.split(',');
	        		$.each(elements,function(index,element){
						var $this = form.find('.dhvc-form-control-'+element);
						$this.closest(container_class).addClass('dhvc-form-hidden');
					});
	        	});
	        	$.each(conditional_data,function(i,conditional){
					var elements = conditional.element.split(',');
		        	if(conditional.type == 'is_empty'){
		        		if(conditional.action == 'hide'){
							$.each(elements,function(index,element){
								var $this = form.find('.dhvc-form-control-'+element);
								$this.closest(container_class).addClass('dhvc-form-hidden');
								$this.trigger('change');
							});
						}else{
							$.each(elements,function(index,element){
								var $this = form.find('.dhvc-form-control-'+element);
								$this.closest(container_class).removeClass('dhvc-form-hidden');
								$this.trigger('change');
							});
						}
		        	}
	        	});
	        }else{
	        	if ($.isNumeric(master_value))
		        {
		        	master_value = parseInt(master_value);
		        }
	        	$.each(conditional_data,function(i,conditional){
	        		if(conditional.value == master_value){
	        			conditional_current = conditional;
	        		}else{
	        			conditional_data2.push(conditional);
	        		}
	        	});
	        	if(conditional_current != null){
		        	conditional_data2.push(conditional_current)
		        	conditional_data = conditional_data2;
	        	}
				$.each(conditional_data,function(i,conditional){
					var elements = conditional.element.split(',');
					
					if(master_container.hasClass('dhvc-form-hidden')) {
						$.each(elements,function(index,element){
							var $this = form.find('.dhvc-form-control-'+element);
							$this.closest(container_class).addClass('dhvc-form-hidden');
						});
					}else{
						if(conditional.type == 'not_empty'){
							if(conditional.action == 'hide'){
								$.each(elements,function(index,element){
									var $this = form.find('.dhvc-form-control-'+element);
									$this.closest(container_class).addClass('dhvc-form-hidden');
									$this.trigger('change');
								});
							}else{
								$.each(elements,function(index,element){
									var $this = form.find('.dhvc-form-control-'+element);
									$this.closest(container_class).removeClass('dhvc-form-hidden');
									$this.trigger('change');
								});
							}
						}else if(conditional.type == 'is_empty'){
							
							if(conditional.action == 'hide'){
								$.each(elements,function(index,element){
									var $this = form.find('.dhvc-form-control-'+element);
									$this.closest(container_class).removeClass('dhvc-form-hidden');
									$this.trigger('change');
								});
							}else{
								$.each(elements,function(index,element){
									var $this = form.find('.dhvc-form-control-'+element);
									$this.closest(container_class).addClass('dhvc-form-hidden');
									$this.trigger('change');
								});
							}
						}else{
							if($.isArray(master_value)){
								if($.inArray(conditional.value,master_value) > -1){
									if(conditional.action == 'hide'){
										$.each(elements,function(index,element){
											var $this = form.find('.dhvc-form-control-'+element);
											$this.closest(container_class).addClass('dhvc-form-hidden');
											$this.trigger('change');
										});
									}else{
										$.each(elements,function(index,element){
											var $this = form.find('.dhvc-form-control-'+element);
											$this.closest(container_class).removeClass('dhvc-form-hidden');
											$this.trigger('change');
										});
									}
								}else{
									if(conditional.action == 'hide'){
										$.each(elements,function(index,element){
											var $this = form.find('.dhvc-form-control-'+element);
											$this.closest(container_class).removeClass('dhvc-form-hidden');
											$this.trigger('change');
										});
									}else{
										$.each(elements,function(index,element){
											var $this = form.find('.dhvc-form-control-'+element);
											$this.closest(container_class).addClass('dhvc-form-hidden');
											$this.trigger('change');
										});
									}
								}
							}else{
								
						        if ($.isNumeric(master_value))
						        {
						        	master_value = parseInt(master_value);
						        }
						        if ($.isNumeric(conditional.value) &&  conditional.value !='0')
						        {
						        	conditional.value = parseInt(conditional.value);
						        }
								if(conditional.type != 'not_empty' && conditional.type != 'is_empty' && operators[conditional.type](master_value,conditional.value)){
									
									if(conditional.action == 'hide'){
										$.each(elements,function(index,element){
											var $this = form.find('.dhvc-form-control-'+element);
											$this.closest(container_class).addClass('dhvc-form-hidden');
											$this.trigger('change');
										});
									}else{
										$.each(elements,function(index,element){
											var $this = form.find('.dhvc-form-control-'+element);
											$this.closest(container_class).removeClass('dhvc-form-hidden');
											$this.trigger('change');
										});
									}
								}else{
									if(conditional.action == 'hide'){
										$.each(elements,function(index,element){
											var $this = form.find('.dhvc-form-control-'+element);
											$this.closest(container_class).removeClass('dhvc-form-hidden');
											$this.trigger('change');
										});
									}else{
										$.each(elements,function(index,element){
											var $this = form.find('.dhvc-form-control-'+element);
											$this.closest(container_class).addClass('dhvc-form-hidden');
											$this.trigger('change');
										});
									}
								}
							}
						}
					}
					
				});
	        }
		}
		var conditional_init = function(){
			$('form.dhvcform').each(function(){
				var form = $(this),
					master_box = form.find('.dhvc-form-conditional');
				
				
				$.each(master_box,function(){
					var masters = $(this).find('[data-conditional].dhvc-form-value');
					$(masters).bind('keyup change',conditional_hook);
					$.each(masters,function(){
						conditional_hook({currentTarget: $(this) });
					});
				});
			});
		};
		conditional_init();
		if($('.dhvc-form-datepicker').length || $('.dhvc-form-timepicker').length){
			$.datetimepicker.setLocale(dhvcformL10n.datetimepicker_lang);
		}
		
		
		var form_submit_loading = function(form,loaded){
			loaded = loaded || false;
			var submit = $(form).find('.dhvc-form-submit');
			var dhvc_button_label = $(form).find('.dhvc-form-submit-label');
			var dhvc_ajax_spinner = $(form).find('.dhvc-form-submit-spinner');
			if(loaded){
				submit.removeAttr('disabled');
	        	dhvc_button_label.removeClass('dhvc-form-submit-label-hidden');
	        	dhvc_ajax_spinner.hide();
			}else{
				submit.attr('disabled','disabled');
	        	dhvc_button_label.addClass('dhvc-form-submit-label-hidden');
	        	dhvc_ajax_spinner.show();
			}
		}
		
		if($('.dhvc-form-datepicker').length){
			$('.dhvc-form-datepicker').each(function(){
				var _this = $(this);
				_this.datetimepicker({
					format: dhvcformL10n.date_format,
					timepicker:false,
					scrollMonth:false,
					dayOfWeekStart: parseInt(dhvcformL10n.dayofweekstart),
					scrollTime:false,
					minDate: _this.data('min-date'),
					maxDate: _this.data('max-date'),
					yearStart: _this.data('year-start'),
					yearEnd: _this.data('year-end'),
					scrollInput:false
				});
			});
			
		}
		
		if($('.dhvc-form-timepicker').length){
			$('.dhvc-form-timepicker').each(function(){
				var _this = $(this);
				_this.datetimepicker({
					format: dhvcformL10n.time_format,
					datepicker:false,
					scrollMonth:false,
					scrollTime:true,
					scrollInput:false,
					dayOfWeekStart: parseInt(dhvcformL10n.dayofweekstart),
					minTime: _this.data('min-time'),
					maxTime: _this.data('max-time'),
					minDate: _this.data('min-date'),
					maxDate: _this.data('max-date'),
					yearStart: _this.data('year-start'),
					yearEnd: _this.data('year-end'),
					step: parseInt(dhvcformL10n.time_picker_step)
				});
			});
		}
		
		if($('.dhvc-form-datetimepicker').length){
			$('.dhvc-form-datetimepicker').each(function(){
				var _this = $(this);
				_this.datetimepicker({
					format: dhvcformL10n.date_format +' '+dhvcformL10n.time_format,
					datepicker:true,
					scrollMonth:false,
					scrollTime:true,
					scrollInput:false,
					minTime: _this.data('min-time'),
					maxTime: _this.data('max-time'),
					step: parseInt(dhvcformL10n.time_picker_step)
				});
			});
		}
		
		$.extend($.validator.messages, {
			required: dhvcformL10n.validate_messages.required,
			remote: dhvcformL10n.validate_messages.remote,
			email: dhvcformL10n.validate_messages.email,
			url: dhvcformL10n.validate_messages.url,
			date: dhvcformL10n.validate_messages.date,
			dateISO: dhvcformL10n.validate_messages.dateISO,
			number: dhvcformL10n.validate_messages.number,
			digits: dhvcformL10n.validate_messages.digits,
			creditcard: dhvcformL10n.validate_messages.creditcard,
			equalTo: dhvcformL10n.validate_messages.equalTo,
			maxlength: $.validator.format(dhvcformL10n.validate_messages.maxlength),
			minlength: $.validator.format(dhvcformL10n.validate_messages.minlength),
			rangelength: $.validator.format(dhvcformL10n.validate_messages.rangelength),
			range: $.validator.format(dhvcformL10n.validate_messages.range),
			max: $.validator.format(dhvcformL10n.validate_messages.max),
			min: $.validator.format(dhvcformL10n.validate_messages.min)
		});
		
		$.validator.addMethod("alpha", function(value, element, param) {
			return this.optional(element) || /^[a-zA-Z]+$/.test(value);
		},dhvcformL10n.validate_messages.alpha);
		
		$.validator.addMethod("number2", function(value, element, param) {
			var re = /^(?=.*[0-9])[- +()0-9]+$/gm; 
			return this.optional(element) || re.test(value);
		},dhvcformL10n.validate_messages.number2);
		
		$.validator.addMethod("email2", function(value, element, param) {
			return this.optional(element) || /^([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*@([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*\.(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]){2,})$/i.test(value);
		},dhvcformL10n.validate_messages.email);
		
		$.validator.addMethod("alphanum", function(value, element, param) {
			return this.optional(element) || /^[a-zA-Z0-9]+$/.test(value);
		},dhvcformL10n.validate_messages.alphanum);
		
		$.validator.addMethod("url", function(value, element, param) {
			value = (value || '').replace(/^\s+/, '').replace(/\s+$/, '');
             return this.optional(element) || /^(http|https|ftp):\/\/(([A-Z0-9]([A-Z0-9_-]*[A-Z0-9]|))(\.[A-Z0-9]([A-Z0-9_-]*[A-Z0-9]|))*)(:(\d+))?(\/[A-Z0-9~](([A-Z0-9_~-]|\.)*[A-Z0-9~]|))*\/?(.*)?$/i.test(value);
             
		},dhvcformL10n.validate_messages.url);
		$.validator.addMethod("zip", function(value, element, param) {
			return this.optional(element) || /(^\d{5}$)|(^\d{5}-\d{4}$)/.test(value);	
		},dhvcformL10n.validate_messages.zip);
		
		$.validator.addMethod("fax", function(value, element, param) {
			return this.optional(element) || /^(\()?\d{3}(\))?(-|\s)?\d{3}(-|\s)\d{4}$/.test(value);
		},dhvcformL10n.validate_messages.fax);
		
		$.validator.addMethod("cpassword", function(value, element, param) {
			var pass = $(element).data('validate-cpassword');
			return this.optional(element) || value === $(element).closest('form').find('#dhvc_form_control_'+pass).val();
		},dhvcformL10n.validate_messages.cpassword);
		
		$.validator.addMethod("extension", function(value, element, param) {
			param = typeof param === "string" ? param.replace(/,/g, "|") : "png|jpe?g|gif";
			return this.optional(element) || value.match(new RegExp(".(" + param + ")$", "i"));
		}, dhvcformL10n.validate_messages.extension);
		
		$.validator.addMethod("recaptcha",function(value, element, param) {
			var isCaptchaValid = true;
			if(dhvcformL10n.recaptcha_ajax_verify==='yes' ){
				isCaptchaValid = false
				$.ajax({
				    url: dhvcformL10n.ajax_url,
				    type: "POST",
				    async: false,
				    data:{
				      action: 'dhvc_form_recaptcha',
				      _ajax_nonce: dhvcformL10n._ajax_nonce,
				      recaptcha_challenge_field: Recaptcha.get_challenge(),
				      recaptcha_response_field: Recaptcha.get_response()
				    },
				    beforeSend: function(){
				    	form_submit_loading($(element).closest('form'),false);
			        },
			        success:function(resp){
			        	form_submit_loading($(element).closest('true'),false);
				    	if(resp > 0){
				    		isCaptchaValid = true;
				    	}else{
				    		Recaptcha.reload();
				    	}
				    }
				});
			}
			return isCaptchaValid;
		},dhvcformL10n.validate_messages.recaptcha);
		
		$.validator.addMethod("dhvcformcaptcha",function(value, element, param) {
			var isCaptchaValid = false;
			$.ajax({
			    url: dhvcformL10n.ajax_url,
			    type: "POST",
			    async: false,
			    data:{
			      action: 'dhvc_form_captcha',
			      _ajax_nonce: dhvcformL10n._ajax_nonce,
			      answer: $(element).val(),
			    },
			    beforeSend: function(){
			    	form_submit_loading($(element).closest('form'),false);
		        },
			    success:function(resp){
			    	form_submit_loading($(element).closest('form'),true);
			    	if(resp > 0){
			    		isCaptchaValid = true;
			    	}else{
			    		$(element).parent().find('img').get(0).src = dhvcformL10n.plugin_url + '/captcha.php?t='+Math.random();
			    	}
			    }
			});
			return isCaptchaValid;
		},dhvcformL10n.validate_messages.captcha);
		
		$.validator.addClassRules({
			'dhvc-form-required-entry':{
				required : true
			},
			'dhvc-form-validate-email':{
				email2: true
			},
			'dhvc-form-validate-date':{
				date: true
			},
			'dhvc-form-validate-number':{
				number: true
			},
			'dhvc-form-validate-number2':{
				number2: true
			},
			'dhvc-form-validate-digits':{
				digits: true
			},
			'dhvc-form-validate-alpha':{
				alpha: true
			},
			'dhvc-form-validate-alphanum':{
				alphanum: true
			},
			'dhvc-form-validate-url':{
				url: true
			},
			'dhvc-form-validate-zip':{
				zip: true
			},
			'dhvc-form-validate-fax':{
				fax: true
			},
			'dhvc-form-validate-password':{
				required: true,
                minlength: 6
			},
			'dhvc-form-validate-cpassword':{
				required: true,
                minlength: 6,
                cpassword: true
			},
			'dhvc-form-validate-captcha':{
				required: true,
                dhvcformcaptcha: true
			},
			'dhvc-form-validate-extension':{
				extension:dhvcformL10n.allowed_file_extension
			}
		});
		
		$("form.dhvcform").each(function(){
			$(this).find('.dhvc-form-file').find('input[type=file]').on('change',function(){
				var _val = $(this).val();
				$(this).closest('label').find('.dhvc-form-control').prop('value',basename(_val));
			});
			$(this).find('.dhvc-form-file').each(function(){
				$(this).find('input[type="text"]').css({'padding-right':$(this).find('.dhvc-form-file-button').outerWidth(true) + 'px'});
				$(this).find('input[type="text"]').on('click',function(){
					$(this).closest('label').trigger('click');
				});
			});
			$(this).find('.dhvc-form-rate .dhvc-form-rate-star').tooltip({ html: true,container:$('body')});
			$(this).validate({
				focusInvalid: false,
				onkeyup: false,
				onfocusout: false,
				onclick: false,
				errorClass: "dhvc-form-error",
				validClass: "dhvc-form-valid",
				errorElement: "span",
				errorPlacement: function(error, element) {
					if ( element.is( ':radio' ) || element.is( ':checkbox' ) )
						error.appendTo( element.parent().parent() );
					else if($(element).attr('id')=='recaptcha_response_field')
						error.appendTo($(element).closest('.dhvc-form-group-recaptcha') );
					else
						error.appendTo( element.parent().parent());
				},
				rules:{
					recaptcha_response_field:{
						required: true,
						recaptcha: true
					}
				},
				invalidHandler: function(e, validator) {
					 if (!validator.numberOfInvalids())
				            return;
				      window.setTimeout(function() {
				        $("html,body").animate({
				          scrollTop: $(validator.errorList[0].element).offset().top + parseInt(dhvcformL10n.validate_error_sroll_offset)
				        });
				      }, 0);
				},
				submitHandler: function(form){
					var user_ajax = $(form).data('use-ajax');
					var msg_container = $(form).closest('.dhvc-form-container').find('.dhvc-form-message');
					var recaptcha2_valid = true;
					var submit = $(form).find('.dhvc-form-submit');
//					var dhvc_button_label = $(form).find('.dhvc-form-submit-label');
//					var dhvc_ajax_spinner = $(form).find('.dhvc-form-submit-spinner');
					if(dhvcformL10n.recaptcha_ajax_verify==='yes' && $(form).find('.dhvc-form-recaptcha2').length){
						$.ajax({
						    url: dhvcformL10n.ajax_url,
						    type: "POST",
						    async: false,
						    data:{
						      action : 'dhvc_form_recaptcha2',
						      _ajax_nonce: dhvcformL10n._ajax_nonce,
						      recaptcha2_response: grecaptcha.getResponse($(form).find('.dhvc-form-recaptcha2:first').data('grecaptcha'))
						    },
						    beforeSend: function(){
						    	form_submit_loading($(form),false);
					        	msg_container.empty().fadeOut();
					        },
						    success:function(resp){
						    	form_submit_loading($(form),true);
						    	if(resp.success == false){
						    		recaptcha2_valid = false;
						    		//grecaptcha.reset($(form).find('.dhvc-form-group-recaptcha:first').data('grecaptcha'))
						    		$(resp.message).appendTo($(form).find('.dhvc-form-recaptcha2') );
						    	}
						    	
						    }
						});
					}
					if(user_ajax && recaptcha2_valid){
						 $.ajax({
					        url: dhvcformL10n.ajax_url,
					        type: "POST",
					        data: $(form).serialize(),
					        dataType: 'json',
					        beforeSend: function(){
					        	form_submit_loading($(form),false);
					        	msg_container.empty().removeClass('dhvc-form-show').fadeOut();
					        	
					        	// Trigger event so themes can refresh other areas
								$( document.body ).trigger( 'dhvc_form_before_submit', [ $(form) , $(submit)] );
					        },
					        success: function(resp) {
					        	form_submit_loading($(form),true);
					        	
					        	// Trigger event so themes can refresh other areas
								$( document.body ).trigger( 'dhvc_form_after_submit', [ $(form) , $(submit), resp] );
								
					           if(resp.success){
					        	   if(resp.scripts_on_sent_ok){
					        		   $.each(resp.scripts_on_sent_ok, function(i, n) { eval(n) });
					        	   }
					        	   if(resp.on_success == 'message'){
					        		   msg_container.html(resp.message).addClass('dhvc-form-show').fadeIn();
									   if($(form).data('ajax_reset_submit')=='1'){
						        		   $(form).resetForm();
						        		   $('input[type="text"], textarea', $(form)).blur();
									   }
					        		   $(form).find('.dhvc-form-captcha').each(function(){
										   $(this).find('img').get(0).src = dhvcformL10n.plugin_url + '/captcha.php?t='+Math.random();
									   });
					        		   if(!$(form).data('popup') && $(form).data('scroll_to_msg')){
						        		   $.smoothScroll({
												scrollTarget: msg_container,
												offset: -100,
												speed: 500
										  });
					        		   }
					        		   
					        	   }else{
					        		   window.location = resp.redirect_url;
					        	   }
					           }else{
					           	   msg_container.html('<span class="error">' + resp.message + '</span>').addClass('dhvc-form-show').fadeIn();
					           	   if($(form).data('ajax_reset_submit')=='1'){
					        		   $(form).resetForm();
					        		   $('input[type="text"], textarea', $(form)).blur();
								   }
								   $(form).find('.dhvc-form-captcha').each(function(){
									   $(this).find('img').get(0).src = dhvcformL10n.plugin_url + '/captcha.php?t='+Math.random();
								   });
								   if($(form).find('.dhvc-form-recaptcha2').length){
									   grecaptcha.reset($(form).find('.dhvc-form-group-recaptcha:first').data('grecaptcha'));
								   }
				        		   if(!$(form).data('popup') && $(form).data('scroll_to_msg')){
					        		   $.smoothScroll({
											scrollTarget: msg_container,
											offset: -100,
											speed: 500
									  });
				        		   }
					           }
					        }            
				         });
						return false;
					}
					if(recaptcha2_valid){
						form.submit();
					}
					return false;
				}
			}); 
		});
	});
	
}(window.jQuery);
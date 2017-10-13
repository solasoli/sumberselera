<?php
$post_id = 0;
if (isset ( $_GET ['post'] ))
	$post_id = ( int ) $_GET ['post'];
elseif (isset ( $_POST ['post_ID'] ))
	$post_id = ( int ) $_POST ['post_ID'];
elseif (isset ( $_POST ['post_id'] ))
	$post_id = ( int ) $_POST ['post_id'];

if ((isset ( $_GET ['post_type'] ) && $_GET ['post_type'] === 'dhvcform') || (get_post_type ( $post_id ) === 'dhvcform') || (dhvc_is_editor () && ((isset ( $_GET ['post_type'] ) && $_GET ['post_type'] === 'dhvcform') || (get_post_type ( $post_id ) === 'dhvcform'))) || (dhvc_is_inline () && ((isset ( $_GET ['post_type'] ) && $_GET ['post_type'] === 'dhvcform') || (get_post_type ( $post_id ) === 'dhvcform'))) || (dhvc_is_editable () && ((isset ( $_GET ['post_type'] ) && $_GET ['post_type'] === 'dhvcform') || (get_post_type ( $post_id ) === 'dhvcform')))) :
	if (function_exists ( 'vc_disable_frontend' )) :
		vc_disable_frontend ();
	 else :
		if (class_exists ( 'NewVisualComposer' ))
			NewVisualComposer::disableInline ();
	endif;
else:
	$args = array(
		'post_type'=>'dhvcform',
		'posts_per_page'=> -1,
		'post_status'=>'publish',
		'meta_query' => array(
			array(
				'key'     => '_form_popup',
				'compare' => 'NOT EXISTS',
			),
		),
	);
	$forms = get_posts($args);
	$forms_options = array();
	$forms_options['-- Select Form --']='';
	foreach ($forms as $form){
		if(empty($form->post_title))
			$form->post_title = 'No Title';
		$forms_options[$form->post_title] = $form->ID;
	}
	vc_map ( array (
		"name" => __ ( "DHVC Form", 'dhvc-form' ),
		"base" => "dhvc_form",
		"category" => __ ( "DHVC Form", 'dhvc-form' ),
		"params" => array (
			array (
				"type" => "dropdown",
				'admin_label'=>true,
				"heading" => __ ( "Form Name", 'dhvc-form' ),
				"param_name" => "id",
				"value" => $forms_options
			),
		)
	) );
endif;

if ((isset($_GET['page']) && ($_GET['page'] === 'vc-roles' || $_GET['page'] === 'vc_settings' || $_GET['page'] ==='wpb_vc_settings' || $_GET['page'] === 'vc-general')) || (isset ( $_GET ['post_type'] ) && $_GET ['post_type'] === 'dhvcform') || (get_post_type ( $post_id ) === 'dhvcform') || (dhvc_is_editor () && ((isset ( $_GET ['post_type'] ) && $_GET ['post_type'] === 'dhvcform') || (get_post_type ( $post_id ) === 'dhvcform'))) || ! is_admin () || (dhvc_is_inline () && ((isset ( $_GET ['post_type'] ) && $_GET ['post_type'] === 'dhvcform') || (get_post_type ( $post_id ) === 'dhvcform'))) || (dhvc_is_editable () && ((isset ( $_GET ['post_type'] ) && $_GET ['post_type'] === 'dhvcform') || (get_post_type ( $post_id ) === 'dhvcform')))) :
	
	vc_map ( array (
			"name" => __ ( "Form Text", 'dhvc-form' ),
			"base" => "dhvc_form_text",
			"category" => __ ( "Form Control", 'dhvc-form' ),
			"icon" => "icon-dhvc-form-text",
			"params" => array (
					array (
							"type" => "textfield",
							"heading" => __ ( "Label", 'dhvc-form' ),
							"param_name" => "control_label",
							'admin_label' => true 
					),
					array (
							"type" => "dhvc_form_name",
							"heading" => __ ( "Name", 'dhvc-form' ),
							"param_name" => "control_name",
							'admin_label' => true,
							"description" => __ ( 'Field name is required.  Please enter single word, no spaces, no start with number. Underscores(_) allowed', 'dhvc-form' ) 
					),
					array (
							"type" => "textfield",
							"heading" => __ ( "Default value", 'dhvc-form' ),
							"param_name" => "default_value" 
					),
					array (
							"type" => "textfield",
							"heading" => __ ( "Maximum length characters", 'dhvc-form' ),
							"param_name" => "maxlength" 
					),
					array (
							"type" => "textfield",
							"heading" => __ ( "Placeholder text", 'dhvc-form' ),
							"param_name" => "placeholder" 
					),
					array (
							"type" => "dropdown",
							"heading" => __ ( "Icon", 'dhvc-form' ),
							"param_name" => "icon",
							"param_holder_class" => 'dhvc-form-font-awesome',
							"value" => dhvc_form_font_awesome (),
							'description' => __ ( 'Select icon add-on for this control.', 'dhvc-form' ) 
					),
					array (
							"type" => "textarea",
							"heading" => __ ( "Help text", 'dhvc-form' ),
							"param_name" => "help_text",
							'description' => __ ( 'This is the help text for this form control.', 'dhvc-form' ) 
					),
					array (
							"type" => "checkbox",
							"heading" => __ ( "Required ? ", 'dhvc-form' ),
							"param_name" => "required",
							"value" => array (
									__ ( 'Yes, please', 'dhvc-form' ) => '1' 
							) 
					),
					array (
							"type" => "dropdown",
							"heading" => __ ( "Read only ? ", 'dhvc-form' ),
							"param_name" => "readonly",
							"value" => array (
									__ ( 'No', 'dhvc-form' ) => 'no',
									__ ( 'Yes', 'dhvc-form' ) => 'yes' 
							) 
					),
					array (
							"type" => "dhvc_form_validator",
							"heading" => __ ( "Add validator", 'dhvc-form' ),
							"param_name" => "validator",
							"dependency" => array (
									'element' => "readonly",
									'value' => array (
											'no' 
									) 
							) 
					),
					array (
							"type" => "textfield",
							"heading" => __ ( "Attributes", 'dhvc-form' ),
							"param_name" => "attributes",
							'description' => __ ( 'Add attribute for this form control,eg: <em>onclick="" onchange="" </em> or \'<em>data-*</em>\'  attributes HTML5, not in attributes: <span style="color:#ff0000">type, value, name, required, placeholder, maxlength, id</span>', 'dhvc-form' ) 
					),
					
					array (
							'type' => 'textfield',
							'heading' => __ ( 'Extra class name', 'dhvc-form' ),
							'param_name' => 'el_class',
							'description' => __ ( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'dhvc-form' ) 
					) 
			) 
	) );
	vc_map ( array (
			"name" => __ ( "Form Email", 'dhvc-form' ),
			"base" => "dhvc_form_email",
			"category" => __ ( "Form Control", 'dhvc-form' ),
			"icon" => "icon-dhvc-form-email",
			"params" => array (
					array (
							"type" => "textfield",
							"heading" => __ ( "Label", 'dhvc-form' ),
							"param_name" => "control_label",
							'admin_label' => true 
					),
					array (
							"type" => "dhvc_form_name",
							"heading" => __ ( "Name", 'dhvc-form' ),
							"param_name" => "control_name",
							'admin_label' => true,
							"description" => __ ( 'Field name is required.  Please enter single word, no spaces, no start with number. Underscores(_) allowed', 'dhvc-form' ) 
					),
					array (
							"type" => "textfield",
							"heading" => __ ( "Default value", 'dhvc-form' ),
							"param_name" => "default_value" 
					),
					array (
							"type" => "textfield",
							"heading" => __ ( "Maximum length characters", 'dhvc-form' ),
							"param_name" => "maxlength" 
					),
					array (
							"type" => "textfield",
							"heading" => __ ( "Placeholder text", 'dhvc-form' ),
							"param_name" => "placeholder" 
					),
					array (
							"type" => "dropdown",
							"heading" => __ ( "Icon", 'dhvc-form' ),
							"param_name" => "icon",
							"param_holder_class" => 'dhvc-form-font-awesome',
							"value" => dhvc_form_font_awesome (),
							'description' => __ ( 'Select icon add-on for this control.', 'dhvc-form' ) 
					),
					array (
							"type" => "textarea",
							"heading" => __ ( "Help text", 'dhvc-form' ),
							"param_name" => "help_text",
							'description' => __ ( 'This is the help text for this form control.', 'dhvc-form' ) 
					),
					array (
							"type" => "checkbox",
							"heading" => __ ( "Required ? ", 'dhvc-form' ),
							"param_name" => "required",
							"value" => array (
									__ ( 'Yes, please', 'dhvc-form' ) => '1' 
							) 
					),
					array (
							"type" => "dropdown",
							"heading" => __ ( "Read only ? ", 'dhvc-form' ),
							"param_name" => "readonly",
							"value" => array (
									__ ( 'No', 'dhvc-form' ) => 'no',
									__ ( 'Yes', 'dhvc-form' ) => 'yes' 
							) 
					),
					array (
							"type" => "textfield",
							"heading" => __ ( "Attributes", 'dhvc-form' ),
							"param_name" => "attributes",
							'description' => __ ( 'Add attribute for this form control,eg: <em>onclick="" onchange="" </em> or \'<em>data-*</em>\'  attributes HTML5, not in attributes: <span style="color:#ff0000">type, value, name, required, placeholder, maxlength, id</span>', 'dhvc-form' ) 
					),
					
					array (
							'type' => 'textfield',
							'heading' => __ ( 'Extra class name', 'dhvc-form' ),
							'param_name' => 'el_class',
							'description' => __ ( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'dhvc-form' ) 
					) 
			) 
	) );
	vc_map ( array (
			"name" => __ ( "Form Label", 'dhvc-form' ),
			"base" => "dhvc_form_label",
			"category" => __ ( "Form Control", 'dhvc-form' ),
			"icon" => "icon-dhvc-form-label",
			"params" => array (
					array (
							"type" => "dhvc_form_name",
							"heading" => __ ( "Name", 'dhvc-form' ),
							"param_name" => "control_name",
							'admin_label' => true,
							"description" => __ ( 'Field name is required.  Please enter single word, no spaces, no start with number. Underscores(_) allowed', 'dhvc-form' ) 
					),
					array (
							'type' => 'textarea_html',
							'holder' => 'div',
							'heading' => __ ( 'Text', 'dhvc-form' ),
							'param_name' => 'content',
							'value' => __ ( '<p>I am text block. Click edit button to change this text. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.</p>', 'dhvc-form' ) 
					),
					array (
							'type' => 'textfield',
							'heading' => __ ( 'Extra class name', 'dhvc-form' ),
							'param_name' => 'el_class',
							'description' => __ ( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'dhvc-form' ) 
					) 
			) 
	) );
	vc_map ( array (
			"name" => __ ( "Form Slider", 'dhvc-form' ),
			"base" => "dhvc_form_slider",
			"category" => __ ( "Form Control", 'dhvc-form' ),
			"icon" => "icon-dhvc-form-slider",
			"params" => array (
					array (
							"type" => "dropdown",
							"heading" => __ ( "Type", 'dhvc-form' ),
							"param_name" => "type",
							"value" => array (
									__ ( 'Slider', 'dhvc-form' ) => 'slider',
									__ ( 'Range', 'dhvc-form' ) => 'range' 
							),
							'admin_label' => true 
					),
					array (
							"type" => "textfield",
							"heading" => __ ( "Label", 'dhvc-form' ),
							"param_name" => "control_label",
							'admin_label' => true 
					),
					array (
							"type" => "dhvc_form_name",
							"heading" => __ ( "Name", 'dhvc-form' ),
							"param_name" => "control_name",
							'admin_label' => true,
							"description" => __ ( 'Field name is required.  Please enter single word, no spaces, no start with number. Underscores(_) allowed', 'dhvc-form' ) 
					),
					array (
							"type" => "textfield",
							"heading" => __ ( "Minimum Value", 'dhvc-form' ),
							"param_name" => "minimum_value",
							"value" => 0 
					),
					array (
							"type" => "textfield",
							"heading" => __ ( "Maximum Value", 'dhvc-form' ),
							"param_name" => "maximum_value",
							"value" => 100 
					),
					array (
							"type" => "textfield",
							"heading" => __ ( "Step", 'dhvc-form' ),
							"param_name" => "step",
							"value" => 5 
					),
					array (
							"type" => "textfield",
							"heading" => __ ( "Default value", 'dhvc-form' ),
							"param_name" => "default_value" 
					),
					array (
							"type" => "textarea",
							"heading" => __ ( "Help text", 'dhvc-form' ),
							"param_name" => "help_text",
							'description' => __ ( 'This is the help text for this form control.', 'dhvc-form' ) 
					),
					array (
							"type" => "dhvc_form_conditional",
							"heading" => __ ( "Conditional Logic", 'dhvc-form' ),
							"param_name" => "conditional",
							"dependency" => array (
									'element' => "type",
									'value' => array (
											'slider' 
									) 
							),
							'description' => __ ( 'Create rules to show or hide this field depending on the values of other fields ', 'dhvc-form' ) 
					),
					array (
							'type' => 'textfield',
							'heading' => __ ( 'Extra class name', 'dhvc-form' ),
							'param_name' => 'el_class',
							'description' => __ ( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'dhvc-form' ) 
					) 
			) 
	) );
	vc_map ( array (
			"name" => __ ( "Form Rate", 'dhvc-form' ),
			"base" => "dhvc_form_rate",
			"category" => __ ( "Form Control", 'dhvc-form' ),
			"icon" => "icon-dhvc-form-rate",
			"params" => array (
					array (
							"type" => "textfield",
							"heading" => __ ( "Label", 'dhvc-form' ),
							"param_name" => "control_label",
							'admin_label' => true 
					),
					array (
							"type" => "dhvc_form_name",
							"heading" => __ ( "Name", 'dhvc-form' ),
							"param_name" => "control_name",
							'admin_label' => true,
							"description" => __ ( 'Field name is required.  Please enter single word, no spaces, no start with number. Underscores(_) allowed', 'dhvc-form' ) 
					),
					array (
							"type" => "dhvc_form_rate_option",
							"heading" => __ ( "Options", 'dhvc-form' ),
							"param_name" => "rate_option" 
					),
					array (
							"type" => "textarea",
							"heading" => __ ( "Help text", 'dhvc-form' ),
							"param_name" => "help_text",
							'description' => __ ( 'This is the help text for this form control.', 'dhvc-form' ) 
					),
					array (
							"type" => "dhvc_form_conditional",
							"heading" => __ ( "Conditional Logic", 'dhvc-form' ),
							"param_name" => "conditional",
							'description' => __ ( 'Create rules to show or hide this field depending on the values of other fields ', 'dhvc-form' ) 
					),
					array (
							'type' => 'textfield',
							'heading' => __ ( 'Extra class name', 'dhvc-form' ),
							'param_name' => 'el_class',
							'description' => __ ( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'dhvc-form' ) 
					) 
			) 
	) );
	vc_map ( array (
			"name" => __ ( "Form Hidden", 'dhvc-form' ),
			"base" => "dhvc_form_hidden",
			"category" => __ ( "Form Control", 'dhvc-form' ),
			"icon" => "icon-dhvc-form-hidden",
			"params" => array (
					array (
							"type" => "dhvc_form_name",
							"heading" => __ ( "Name", 'dhvc-form' ),
							"param_name" => "control_name",
							'admin_label' => true,
							"description" => __ ( 'Field name is required.  Please enter single word, no spaces, no start with number. Underscores(_) allowed', 'dhvc-form' ) 
					),
					array (
							"type" => "textfield",
							"heading" => __ ( "Default value", 'dhvc-form' ),
							"param_name" => "default_value" 
					) 
			) 
	) );
	vc_map ( array (
			"name" => __ ( "Form Captcha", 'dhvc-form' ),
			"base" => "dhvc_form_captcha",
			"category" => __ ( "Form Control", 'dhvc-form' ),
			"icon" => "icon-dhvc-form-captcha",
			"params" => array (
					array (
							"type" => "textfield",
							"heading" => __ ( "Label", 'dhvc-form' ),
							"param_name" => "control_label",
							'admin_label' => true 
					),
					array (
							"type" => "dhvc_form_name",
							"heading" => __ ( "Name", 'dhvc-form' ),
							"param_name" => "control_name",
							'admin_label' => true,
							"description" => __ ( 'Field name is required.  Please enter single word, no spaces, no start with number. Underscores(_) allowed', 'dhvc-form' ) 
					),
					array (
							"type" => "textfield",
							"heading" => __ ( "Placeholder text", 'dhvc-form' ),
							"param_name" => "placeholder" 
					),
					array (
							"type" => "textarea",
							"heading" => __ ( "Help text", 'dhvc-form' ),
							"param_name" => "help_text",
							'description' => __ ( 'This is the help text for this form control.', 'dhvc-form' ) 
					),
					array (
							'type' => 'textfield',
							'heading' => __ ( 'Extra class name', 'dhvc-form' ),
							'param_name' => 'el_class',
							'description' => __ ( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'dhvc-form' ) 
					) 
			) 
	) );
	vc_map ( array (
			"name" => __ ( "Form reCaptcha", 'dhvc-form' ),
			"base" => "dhvc_form_recaptcha",
			"category" => __ ( "Form Control", 'dhvc-form' ),
			"icon" => "icon-dhvc-form-recaptcha",
			"params" => array (
					array (
						"type" => "dropdown",
						"heading" => __ ( "reCaptcha Version", 'dhvc-form' ),
						"param_name" => "captcha_type",
						'std'=>'2',
						"value" => array (
							__ ( 'Version 1', 'dhvc-form' ) => '1',
							__ ( 'Version 2', 'dhvc-form' ) => '2',
						),
						'description' => __ ( 'Select reCaptcha version you want use.', 'dhvc-form' )
					),
					array (
							"type" => "dropdown",
							"heading" => __ ( "Theme", 'dhvc-form' ),
							"param_name" => "theme",
							"value" => array (
									__ ( 'Red', 'dhvc-form' ) => 'red',
									__ ( 'Clean', 'dhvc-form' ) => 'clean',
									__ ( 'White', 'dhvc-form' ) => 'white',
									__ ( 'BlackGlass', 'dhvc-form' ) => 'blackglass' 
							),
							"dependency" => array (
								'element' => "captcha_type",
								'value' => array (
									'1'
								)
							),
							'description' => __ ( 'Defines which theme to use for reCAPTCHA.', 'dhvc-form' ) 
					),
					array (
							"type" => "dropdown",
							"heading" => __ ( "Language", 'dhvc-form' ),
							"param_name" => "language",
							"dependency" => array (
								'element' => "captcha_type",
								'value' => array (
									'1'
								)
							),
							"value" => dhvc_form_get_recaptcha_lang (),
							'description' => __ ( 'Select the language you would like to use for the reCAPTCHA display from the available options.', 'dhvc-form' ) 
					),
					array (
							"type" => "textfield",
							"heading" => __ ( "Label", 'dhvc-form' ),
							"param_name" => "control_label",
							'admin_label' => true 
					),
					array (
							"type" => "dhvc_form_name",
							"heading" => __ ( "Name", 'dhvc-form' ),
							"param_name" => "control_name",
							'admin_label' => true,
							"description" => __ ( 'Field name is required.  Please enter single word, no spaces, no start with number. Underscores(_) allowed', 'dhvc-form' ) 
					),
					array (
							"type" => "textarea",
							"heading" => __ ( "Help text", 'dhvc-form' ),
							"param_name" => "help_text",
							'description' => __ ( 'This is the help text for this form control.', 'dhvc-form' ) 
					),
					array (
							'type' => 'textfield',
							'heading' => __ ( 'Extra class name', 'dhvc-form' ),
							'param_name' => 'el_class',
							'description' => __ ( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'dhvc-form' ) 
					) 
			) 
	) );
	vc_map ( array (
			"name" => __ ( "Form DateTime", 'dhvc-form' ),
			"base" => "dhvc_form_datetime",
			"category" => __ ( "Form Control", 'dhvc-form' ),
			"icon" => "icon-dhvc-form-datetime",
			"params" => array (
					array (
							"type" => "dropdown",
							"heading" => __ ( "Type", 'dhvc-form' ),
							"param_name" => "type",
							'admin_label' => true,
							"value" => array (
									__ ( 'Date', 'dhvc-form' ) => 'date',
									__ ( 'Time', 'dhvc-form' ) => 'time' ,
									__ ( 'Date & Time', 'dhvc-form' ) => 'datetime',
							) 
					),
					array (
							"type" => "textfield",
							"heading" => __ ( "Label", 'dhvc-form' ),
							"param_name" => "control_label",
							'admin_label' => true 
					),
					array (
							"type" => "dhvc_form_name",
							"heading" => __ ( "Name", 'dhvc-form' ),
							"param_name" => "control_name",
							'admin_label' => true,
							"description" => __ ( 'Field name is required.  Please enter single word, no spaces, no start with number. Underscores(_) allowed', 'dhvc-form' ) 
					),
					array (
							"type" => "textfield",
							"heading" => __ ( "Maximum length characters", 'dhvc-form' ),
							"param_name" => "maxlength" 
					),
					array (
							"type" => "textfield",
							"heading" => __ ( "Placeholder text", 'dhvc-form' ),
							"param_name" => "placeholder" 
					),
					array (
							"type" => "textarea",
							"heading" => __ ( "Help text", 'dhvc-form' ),
							"param_name" => "help_text",
							'description' => __ ( 'This is the help text for this form control.', 'dhvc-form' ) 
					),
					array (
							"type" => "checkbox",
							"heading" => __ ( "Required ? ", 'dhvc-form' ),
							"param_name" => "required",
							"value" => array (
									__ ( 'Yes, please', 'dhvc-form' ) => '1' 
							) 
					),
					array (
							"type" => "checkbox",
							"heading" => __ ( "Read only ? ", 'dhvc-form' ),
							"param_name" => "readonly",
							"value" => array (
									__ ( 'Yes, please', 'dhvc-form' ) => '1' 
							) 
					),
					array (
							"type" => "textfield",
							"heading" => __ ( "Attributes", 'dhvc-form' ),
							"param_name" => "attributes",
							'description' => __ ( 'Add attribute for this form control,eg: <em>onclick="" onchange="" </em> or \'<em>data-*</em>\'  attributes HTML5, not in attributes: <span style="color:#ff0000">type, value, name, required, placeholder, maxlength, id</span>', 'dhvc-form' ) 
					),
					array (
							'type' => 'textfield',
							'heading' => __ ( 'Extra class name', 'dhvc-form' ),
							'param_name' => 'el_class',
							'description' => __ ( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'dhvc-form' ) 
					) 
			) 
	) );
	vc_map ( array (
			"name" => __ ( "Form Color", 'dhvc-form' ),
			"base" => "dhvc_form_color",
			"category" => __ ( "Form Control", 'dhvc-form' ),
			"icon" => "icon-dhvc-form-color",
			"params" => array (
					array (
							"type" => "textfield",
							"heading" => __ ( "Label", 'dhvc-form' ),
							"param_name" => "control_label",
							'admin_label' => true 
					),
					array (
							"type" => "dhvc_form_name",
							"heading" => __ ( "Name", 'dhvc-form' ),
							"param_name" => "control_name",
							'admin_label' => true,
							"description" => __ ( 'Field name is required.  Please enter single word, no spaces, no start with number. Underscores(_) allowed', 'dhvc-form' ) 
					),
					array (
							"type" => "colorpicker",
							"heading" => __ ( "Default value", 'dhvc-form' ),
							"param_name" => "default_value" 
					),
					array (
							"type" => "textfield",
							"heading" => __ ( "Placeholder text", 'dhvc-form' ),
							"param_name" => "placeholder" 
					),
					array (
							"type" => "textarea",
							"heading" => __ ( "Help text", 'dhvc-form' ),
							"param_name" => "help_text",
							'description' => __ ( 'This is the help text for this form control.', 'dhvc-form' ) 
					),
					array (
							"type" => "checkbox",
							"heading" => __ ( "Required ? ", 'dhvc-form' ),
							"param_name" => "required",
							"value" => array (
									__ ( 'Yes, please', 'dhvc-form' ) => '1' 
							) 
					),
					array (
							"type" => "checkbox",
							"heading" => __ ( "Read only ? ", 'dhvc-form' ),
							"param_name" => "readonly",
							"value" => array (
									__ ( 'Yes, please', 'dhvc-form' ) => '1' 
							) 
					),
					array (
							"type" => "textfield",
							"heading" => __ ( "Attributes", 'dhvc-form' ),
							"param_name" => "attributes",
							'description' => __ ( 'Add attribute for this form control,eg: <em>onclick="" onchange="" </em> or \'<em>data-*</em>\'  attributes HTML5, not in attributes: <span style="color:#ff0000">type, value, name, required, placeholder, maxlength, id</span>', 'dhvc-form' ) 
					),
					array (
							'type' => 'textfield',
							'heading' => __ ( 'Extra class name', 'dhvc-form' ),
							'param_name' => 'el_class',
							'description' => __ ( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'dhvc-form' ) 
					) 
			) 
	) );
	vc_map ( array (
			"name" => __ ( "Form Password", 'dhvc-form' ),
			"base" => "dhvc_form_password",
			"category" => __ ( "Form Control", 'dhvc-form' ),
			"icon" => "icon-dhvc-form-password",
			"params" => array (
					
					array (
							"type" => "textfield",
							"heading" => __ ( "Label", 'dhvc-form' ),
							"param_name" => "control_label",
							'admin_label' => true 
					),
					array (
							"type" => "dhvc_form_name",
							"heading" => __ ( "Name", 'dhvc-form' ),
							"param_name" => "control_name",
							'admin_label' => true,
							"description" => __ ( 'Field name is required.  Please enter single word, no spaces, no start with number. Underscores(_) allowed', 'dhvc-form' ) 
					),
					array (
							"type" => "checkbox",
							"heading" => __ ( "Is confirmation ? ", 'dhvc-form' ),
							"param_name" => "confirmation",
							"value" => array (
									__ ( 'Yes, please', 'dhvc-form' ) => '1' 
							) 
					),
					array (
							"type" => "textfield",
							"heading" => __ ( "Password field ", 'dhvc-form' ),
							"param_name" => "password_field",
							"dependency" => array (
									'element' => "confirmation",
									'not_empty' => true 
							),
							'description' => __ ( 'enter passwords field name to validate match', 'dhvc-form' ) 
					),
					array (
							"type" => "textfield",
							"heading" => __ ( "Placeholder text", 'dhvc-form' ),
							"param_name" => "placeholder" 
					),
					array (
							"type" => "dropdown",
							"heading" => __ ( "Icon", 'dhvc-form' ),
							"param_name" => "icon",
							"param_holder_class" => 'dhvc-form-font-awesome',
							"value" => dhvc_form_font_awesome (),
							'description' => __ ( 'Select icon add-on for this control.', 'dhvc-form' ) 
					),
					array (
							"type" => "textarea",
							"heading" => __ ( "Help text", 'dhvc-form' ),
							"param_name" => "help_text",
							'description' => __ ( 'This is the help text for this form control.', 'dhvc-form' ) 
					),
					array (
							"type" => "checkbox",
							"heading" => __ ( "Required ? ", 'dhvc-form' ),
							"param_name" => "required",
							"value" => array (
									__ ( 'Yes, please', 'dhvc-form' ) => '1' 
							) 
					),
					array (
							"type" => "checkbox",
							"heading" => __ ( "Read only ? ", 'dhvc-form' ),
							"param_name" => "readonly",
							"value" => array (
									__ ( 'Yes, please', 'dhvc-form' ) => '1' 
							) 
					),
					array (
							"type" => "checkbox",
							"heading" => __ ( "Password validator ? ", 'dhvc-form' ),
							"param_name" => "validator",
							"value" => array (
									__ ( 'Yes, please', 'dhvc-form' ) => '1' 
							) 
					),
					array (
							"type" => "textfield",
							"heading" => __ ( "Attributes", 'dhvc-form' ),
							"param_name" => "attributes",
							'description' => __ ( 'Add attribute for this form control,eg: <em>onclick="" onchange="" </em> or \'<em>data-*</em>\'  attributes HTML5, not in attributes: <span style="color:#ff0000">type, value, name, required, placeholder, maxlength, id</span>', 'dhvc-form' ) 
					),
					array (
							'type' => 'textfield',
							'heading' => __ ( 'Extra class name', 'dhvc-form' ),
							'param_name' => 'el_class',
							'description' => __ ( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'dhvc-form' ) 
					) 
			) 
	) );
	
	vc_map ( array (
			"name" => __ ( "Form Radio", 'dhvc-form' ),
			"base" => "dhvc_form_radio",
			"category" => __ ( "Form Control", 'dhvc-form' ),
			"icon" => "icon-dhvc-form-radio",
			"params" => array (
					array (
							"type" => "textfield",
							"heading" => __ ( "Label", 'dhvc-form' ),
							"param_name" => "control_label",
							'admin_label' => true 
					),
					array (
							"type" => "dhvc_form_name",
							"heading" => __ ( "Name", 'dhvc-form' ),
							"param_name" => "control_name",
							'admin_label' => true,
							"description" => __ ( 'Field name is required.  Please enter single word, no spaces, no start with number. Underscores(_) allowed', 'dhvc-form' ) 
					),
					array (
							"type" => "dhvc_form_option",
							"heading" => __ ( "Options", 'dhvc-form' ),
							"param_name" => "options" 
					),
					array (
							"type" => "textarea",
							"heading" => __ ( "Help text", 'dhvc-form' ),
							"param_name" => "help_text",
							'description' => __ ( 'This is the help text for this form control.', 'dhvc-form' ) 
					),
					array (
							"type" => "checkbox",
							"heading" => __ ( "Required ? ", 'dhvc-form' ),
							"param_name" => "required",
							"value" => array (
									__ ( 'Yes, please', 'dhvc-form' ) => '1' 
							) 
					),
					array (
							"type" => "checkbox",
							"heading" => __ ( "Disabled ? ", 'dhvc-form' ),
							"param_name" => "disabled",
							"value" => array (
									__ ( 'Yes, please', 'dhvc-form' ) => '1' 
							) 
					),
					array (
							"type" => "dhvc_form_conditional",
							"heading" => __ ( "Conditional Logic", 'dhvc-form' ),
							"param_name" => "conditional",
							'description' => __ ( 'Create rules to show or hide this field depending on the values of other fields ', 'dhvc-form' ) 
					),
					array (
							'type' => 'textfield',
							'heading' => __ ( 'Extra class name', 'dhvc-form' ),
							'param_name' => 'el_class',
							'description' => __ ( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'dhvc-form' ) 
					) 
			) 
	) );
	
	vc_map ( array (
			"name" => __ ( "Form File", 'dhvc-form' ),
			"base" => "dhvc_form_file",
			"category" => __ ( "Form Control", 'dhvc-form' ),
			"icon" => "icon-dhvc-form-file",
			"params" => array (
					array (
							"type" => "textfield",
							"heading" => __ ( "Label", 'dhvc-form' ),
							"param_name" => "control_label",
							'admin_label' => true 
					),
					array (
							"type" => "dhvc_form_name",
							"heading" => __ ( "Name", 'dhvc-form' ),
							"param_name" => "control_name",
							'admin_label' => true,
							"description" => __ ( 'Field name is required.  Please enter single word, no spaces, no start with number. Underscores(_) allowed', 'dhvc-form' ) 
					),
					array (
							"type" => "textarea",
							"heading" => __ ( "Help text", 'dhvc-form' ),
							"param_name" => "help_text",
							'description' => __ ( 'This is the help text for this form control.', 'dhvc-form' ) 
					),
					array (
							"type" => "checkbox",
							"heading" => __ ( "Required ? ", 'dhvc-form' ),
							"param_name" => "required",
							"value" => array (
									__ ( 'Yes, please', 'dhvc-form' ) => '1' 
							) 
					),
					array (
							"type" => "textfield",
							"heading" => __ ( "Attributes", 'dhvc-form' ),
							"param_name" => "attributes",
							'description' => __ ( 'Add attribute for this form control,eg: <em>onclick="" onchange="" </em> or \'<em>data-*</em>\'  attributes HTML5, not in attributes: <span style="color:#ff0000">type, value, name, required, placeholder, maxlength, id</span>', 'dhvc-form' ) 
					),
					array (
							'type' => 'textfield',
							'heading' => __ ( 'Extra class name', 'dhvc-form' ),
							'param_name' => 'el_class',
							'description' => __ ( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'dhvc-form' ) 
					) 
			) 
	) );
	vc_map ( array (
			"name" => __ ( "Form Checkboxes", 'dhvc-form' ),
			"base" => "dhvc_form_checkbox",
			"category" => __ ( "Form Control", 'dhvc-form' ),
			"icon" => "icon-dhvc-form-checkbox",
			"params" => array (
					array (
							"type" => "textfield",
							"heading" => __ ( "Label", 'dhvc-form' ),
							"param_name" => "control_label",
							'admin_label' => true 
					),
					array (
							"type" => "dhvc_form_name",
							"heading" => __ ( "Name", 'dhvc-form' ),
							"param_name" => "control_name",
							'admin_label' => true,
							"description" => __ ( 'Field name is required.  Please enter single word, no spaces, no start with number. Underscores(_) allowed', 'dhvc-form' ) 
					),
					array (
							"type" => "dhvc_form_option",
							"heading" => __ ( "Options", 'dhvc-form' ),
							"param_name" => "options",
							'option_checkbox' => true 
					),
					array (
							"type" => "textarea",
							"heading" => __ ( "Help text", 'dhvc-form' ),
							"param_name" => "help_text",
							'description' => __ ( 'This is the help text for this form control.', 'dhvc-form' ) 
					),
					array (
							"type" => "checkbox",
							"heading" => __ ( "Required ? ", 'dhvc-form' ),
							"param_name" => "required",
							"value" => array (
									__ ( 'Yes, please', 'dhvc-form' ) => '1' 
							) 
					),
					array (
							"type" => "checkbox",
							"heading" => __ ( "Disabled ? ", 'dhvc-form' ),
							"param_name" => "disabled",
							"value" => array (
									__ ( 'Yes, please', 'dhvc-form' ) => '1' 
							) 
					),
					array (
							"type" => "dhvc_form_conditional",
							"heading" => __ ( "Conditional Logic", 'dhvc-form' ),
							"param_name" => "conditional",
							'description' => __ ( 'Create rules to show or hide this field depending on the values of other fields ', 'dhvc-form' ) 
					),
					array (
							'type' => 'textfield',
							'heading' => __ ( 'Extra class name', 'dhvc-form' ),
							'param_name' => 'el_class',
							'description' => __ ( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'dhvc-form' ) 
					) 
			) 
	) );
	vc_map ( array (
			"name" => __ ( "Form Select", 'dhvc-form' ),
			"base" => "dhvc_form_select",
			"category" => __ ( "Form Control", 'dhvc-form' ),
			"icon" => "icon-dhvc-form-select",
			"params" => array (
					array (
							"type" => "textfield",
							"heading" => __ ( "Label", 'dhvc-form' ),
							"param_name" => "control_label",
							'admin_label' => true 
					),
					array (
							"type" => "dhvc_form_name",
							"heading" => __ ( "Name", 'dhvc-form' ),
							"param_name" => "control_name",
							'admin_label' => true,
							"description" => __ ( 'Field name is required.  Please enter single word, no spaces, no start with number. Underscores(_) allowed', 'dhvc-form' ) 
					),
					array (
							"type" => "dhvc_form_option",
							"heading" => __ ( "Options", 'dhvc-form' ),
							"param_name" => "options" 
					),
					array (
							"type" => "textarea",
							"heading" => __ ( "Help text", 'dhvc-form' ),
							"param_name" => "help_text",
							'description' => __ ( 'This is the help text for this form control.', 'dhvc-form' ) 
					),
					array (
							"type" => "checkbox",
							"heading" => __ ( "Required ? ", 'dhvc-form' ),
							"param_name" => "required",
							"value" => array (
									__ ( 'Yes, please', 'dhvc-form' ) => '1' 
							) 
					),
					array (
							"type" => "checkbox",
							"heading" => __ ( "Disabled ? ", 'dhvc-form' ),
							"param_name" => "disabled",
							"value" => array (
									__ ( 'Yes, please', 'dhvc-form' ) => '1' 
							) 
					),
					array (
							"type" => "textfield",
							"heading" => __ ( "Attributes", 'dhvc-form' ),
							"param_name" => "attributes",
							'description' => __ ( 'Add attribute for this form control,eg: <em>onclick="" onchange="" </em> or \'<em>data-*</em>\'  attributes HTML5, not in attributes: <span style="color:#ff0000">type, value, name, required, placeholder, maxlength, id</span>', 'dhvc-form' ) 
					),
					array (
							"type" => "dhvc_form_conditional",
							"heading" => __ ( "Conditional Logic", 'dhvc-form' ),
							"param_name" => "conditional",
							'description' => __ ( 'Create rules to show or hide this field depending on the values of other fields ', 'dhvc-form' ) 
					),
					array (
							'type' => 'textfield',
							'heading' => __ ( 'Extra class name', 'dhvc-form' ),
							'param_name' => 'el_class',
							'description' => __ ( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'dhvc-form' ) 
					) 
			) 
	) );
	vc_map ( array (
			"name" => __ ( "Form Multiple Select", 'dhvc-form' ),
			"base" => "dhvc_form_multiple_select",
			"category" => __ ( "Form Control", 'dhvc-form' ),
			"icon" => "icon-dhvc-form-select",
			"params" => array (
					array (
							"type" => "textfield",
							"heading" => __ ( "Label", 'dhvc-form' ),
							"param_name" => "control_label",
							'admin_label' => true 
					),
					array (
							"type" => "dhvc_form_name",
							"heading" => __ ( "Name", 'dhvc-form' ),
							"param_name" => "control_name",
							'admin_label' => true,
							"description" => __ ( 'Field name is required.  Please enter single word, no spaces, no start with number. Underscores(_) allowed', 'dhvc-form' ) 
					),
					array (
							"type" => "dhvc_form_option",
							"heading" => __ ( "Options", 'dhvc-form' ),
							"param_name" => "options",
							'option_checkbox' => true 
					),
					array (
							"type" => "textarea",
							"heading" => __ ( "Help text", 'dhvc-form' ),
							"param_name" => "help_text",
							'description' => __ ( 'This is the help text for this form control.', 'dhvc-form' ) 
					),
					array (
							"type" => "checkbox",
							"heading" => __ ( "Required ? ", 'dhvc-form' ),
							"param_name" => "required",
							"value" => array (
									__ ( 'Yes, please', 'dhvc-form' ) => '1' 
							) 
					),
					array (
							"type" => "checkbox",
							"heading" => __ ( "Disabled ? ", 'dhvc-form' ),
							"param_name" => "disabled",
							"value" => array (
									__ ( 'Yes, please', 'dhvc-form' ) => '1' 
							) 
					),
					array (
							"type" => "textfield",
							"heading" => __ ( "Attributes", 'dhvc-form' ),
							"param_name" => "attributes",
							'description' => __ ( 'Add attribute for this form control,eg: <em>onclick="" onchange="" </em> or \'<em>data-*</em>\'  attributes HTML5, not in attributes: <span style="color:#ff0000">type, value, name, required, placeholder, maxlength, id</span>', 'dhvc-form' ) 
					),
					array (
							'type' => 'textfield',
							'heading' => __ ( 'Extra class name', 'dhvc-form' ),
							'param_name' => 'el_class',
							'description' => __ ( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'dhvc-form' ) 
					) 
			) 
	) );
	vc_map ( array (
			"name" => __ ( "Form Textarea", 'dhvc-form' ),
			"base" => "dhvc_form_textarea",
			"category" => __ ( "Form Control", 'dhvc-form' ),
			"icon" => "icon-dhvc-form-textarea",
			"params" => array (
					array (
							"type" => "textfield",
							"heading" => __ ( "Label", 'dhvc-form' ),
							"param_name" => "control_label",
							'admin_label' => true 
					),
					array (
							"type" => "dhvc_form_name",
							"heading" => __ ( "Name", 'dhvc-form' ),
							"param_name" => "control_name",
							'admin_label' => true,
							"description" => __ ( 'Field name is required.  Please enter single word, no spaces, no start with number. Underscores(_) allowed', 'dhvc-form' ) 
					),
					array (
							"type" => "textarea",
							"heading" => __ ( "Default value", 'dhvc-form' ),
							"param_name" => "default_value" 
					),
					array (
							"type" => "textfield",
							"heading" => __ ( "Placeholder text", 'dhvc-form' ),
							"param_name" => "placeholder" 
					),
					array (
							"type" => "textarea",
							"heading" => __ ( "Help text", 'dhvc-form' ),
							"param_name" => "help_text",
							'description' => __ ( 'This is the help text for this form control.', 'dhvc-form' ) 
					),
					array (
							"type" => "checkbox",
							"heading" => __ ( "Required ? ", 'dhvc-form' ),
							"param_name" => "required",
							"value" => array (
									__ ( 'Yes, please', 'dhvc-form' ) => '1' 
							) 
					),
					array (
							"type" => "checkbox",
							"heading" => __ ( "Read only ? ", 'dhvc-form' ),
							"param_name" => "readonly",
							"value" => array (
									__ ( 'Yes, please', 'dhvc-form' ) => '1' 
							) 
					),
					array (
							"type" => "textfield",
							"heading" => __ ( "Attributes", 'dhvc-form' ),
							"param_name" => "attributes",
							'description' => __ ( 'Add attribute for this form control,eg: <em>onclick="" onchange="" </em> or \'<em>data-*</em>\'  attributes HTML5, not in attributes: <span style="color:#ff0000">type, value, name, required, placeholder, maxlength, id</span>', 'dhvc-form' ) 
					),
					array (
							'type' => 'textfield',
							'heading' => __ ( 'Extra class name', 'dhvc-form' ),
							'param_name' => 'el_class',
							'description' => __ ( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'dhvc-form' ) 
					) 
			) 
	) );
	
	vc_map ( array (
			"name" => __ ( "Form Button Submit", 'dhvc-form' ),
			"base" => "dhvc_form_submit_button",
			"category" => __ ( "Form Control", 'dhvc-form' ),
			"icon" => "icon-dhvc-form-submit-button",
			"params" => array (
					array (
							"type" => "textfield",
							"heading" => __ ( "Label", 'dhvc-form' ),
							"param_name" => "label",
							'value'=>__('Submit','dhvc-form'),
							'admin_label' => true,
							"description" => __ ( 'Field name is required.  Please enter single word, no spaces, no start with number. Underscores(_) allowed', 'dhvc-form' ) 
					),
					array (
							'type' => 'textfield',
							'heading' => __ ( 'Extra class name', 'dhvc-form' ),
							'param_name' => 'el_class',
							'description' => __ ( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'dhvc-form' ) 
					) 
			) 
	) );


endif;
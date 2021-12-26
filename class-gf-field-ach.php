<?php

if ( ! class_exists( 'GFForms' ) ) {
	die();
}

class GF_Field_ACH extends GF_Field {

	public $type = 'ach';

    public function __construct( $data = array() ) {
        parent::__construct( $data );
        $this->_has_settings_renderer  = version_compare( GFCommon::$version, '2.5-beta', '>=' );;
    }

	public function get_form_editor_field_title() {
		return esc_attr__( 'ACH', 'gf-ach-field' );
	}

    /**
     * Returns the field's form editor description.
     *
     * @since 1.0.2
     *
     * @return string
     */
    public function get_form_editor_field_description() {
        return esc_attr__( 'Allows users to enter bank account information.', 'gf-ach-field' );
    }

    /**
     * Returns the field's form editor icon.
     *
     * This could be an icon url or a gform-icon class.
     *
     * @since 2.5
     *
     * @return string
     */
    public function get_form_editor_field_icon() {
        return GF_ACH_PLUGIN_URL . '/images/icon.png';
    }

    /**
     * Defines the IDs of required inputs.
     *
     * @since 1.0.2
     *
     * @return string[]
     */
    public function get_required_inputs_ids() {
        return array( '1', '2', '3' );
    }

    /**
     * Generates an array that contains aria-describedby attribute for each input.
     *
     * Depending on each input's validation state, aria-describedby takes the value of the validation message container ID, the description only or nothing.
     *
     * @since 1.0.2
     *
     * @param array        $required_inputs_ids IDs of required field inputs.
     * @param array|string $values              Inputs values.
     *
     * @return array
     */
    public function get_inputs_describedby_attributes( $required_inputs_ids, $values ) {

        $describedby_attributes = array();
        $warning_container_id   = ! GFCommon::is_ssl() && ! ( $this->is_form_editor() || $this->is_entry_detail() ) ? "field_{$this->formId}_{$this->id}_ach_warning_message" : '';

        foreach ( $this->inputs as $input ) {
            $input_id    = str_replace( $this->id . '.', '', $input['id'] );
            $input_value = GFForms::get( $input['id'], $values );
            if ( ! empty( $_POST[ 'is_submit_' . $this->formId ] ) && $this->isRequired && in_array( $input_id, $required_inputs_ids ) &&  empty( $input_value ) ) {
                $describedby_attributes[ $input_id ] = "aria-describedby='validation_message_{$this->formId}_{$this->id} {$warning_container_id}'";
            } else {
                $describedby_attributes[ $input_id ] = empty( $warning_container_id ) ? '' : "aria-describedby='{$warning_container_id}'";
            }
        }

        return $describedby_attributes;
    }

	function get_form_editor_field_settings() {
		return array(
			'conditional_logic_field_setting',
			'force_ssl_field_setting',
			'error_message_setting',
			'label_setting',
			'label_placement_setting',
			'sub_labels_setting',
			'sub_label_placement_setting',
			'label_placement_setting',
			'admin_label_setting',
			'rules_setting',
			'description_setting',
			'css_class_setting',
			'input_placeholders_setting',
		);
	}

	public function get_form_editor_button() {
		return array(
			'group' => 'pricing_fields',
			'text'  => $this->get_form_editor_field_title()
		);
	}

	public function is_value_submission_empty( $form_id ) {
		return false;
	}

	public function validate( $value, $form ) {
		$account_number 	= rgpost( 'input_' . $this->id . '_1' );
		$routing_number		= rgpost( 'input_' . $this->id . '_2' );
		$account_type		= rgpost( 'input_' . $this->id . '_3' );

		if ( $this->isRequired && ( empty( $account_number ) || empty( $routing_number ) || empty( $account_type ) ) ) {
			$this->failed_validation  = true;
			$this->validation_message = empty( $this->errorMessage ) ? esc_html__( 'Please enter your bank account information.', 'gf-ach-field' ) : $this->errorMessage;
		} elseif ( ! empty( $account_number ) ) {

			if ( empty( $routing_number ) ) {
				$this->failed_validation  = true;
				$this->validation_message = esc_html__( "Please enter your bank account's routing number.", 'gf-ach-field' );
			}
			if ( empty( $account_type ) ) {
				$this->failed_validation  = true;
				$this->validation_message = esc_html__( "Please select the account type.", 'gf-ach-field' );
			}
		}
	}

    /**
     * Returns the HTML tag for the field container.
     *
     * @since 1.1.2
     *
     * @param array $form The current Form object.
     *
     * @return string
     */
    public function get_field_container_tag( $form ) {

        if ( GFCommon::is_legacy_markup_enabled( $form ) ) {
            return parent::get_field_container_tag( $form );
        }

        return 'fieldset';

    }

    /**
     * Displays an insecure page warning below the field content.
     *
     * @since 1.1.2
     *
     * @param string|array $value                The field value. From default/dynamic population, $_POST, or a resumed incomplete submission.
     * @param bool         $force_frontend_label Should the frontend label be displayed in the admin even if an admin label is configured.
     * @param array        $form                 The Form Object currently being processed.
     *
     * @return string
     */
    public function get_field_content( $value, $force_frontend_label, $form ) {

        $is_form_editor  = GFCommon::is_form_editor();
        $is_entry_detail = GFCommon::is_entry_detail();
        $is_admin        = $is_form_editor || $is_entry_detail;

        // Get existing field content.
        $field_content = parent::get_field_content( $value, $force_frontend_label, $form );

        // If SSL is not used, display warning message.
        if ( ! GFCommon::is_ssl() && ! $is_admin ) {
            $field_content = "<div class='gfield_creditcard_warning_message' id='field_{$form['id']}_{$this->id}_ach_warning_message'><span>" . esc_html__( 'This page is unsecured. Do not enter a real bank account details! Use this field only for testing purposes. ', 'gravityforms' ) . '</span></div>' . $field_content;
        }

        return $field_content;

    }

	public function get_value_submission( $field_values, $get_from_post_global_var = true ) {

		if ( $get_from_post_global_var ) {
			$value[ $this->id . '.1' ] = $this->get_input_value_submission( 'input_' . $this->id . '_1', rgar( $this->inputs[0], 'name' ), $field_values, true );
			$value[ $this->id . '.2' ] = $this->get_input_value_submission( 'input_' . $this->id . '_2', rgar( $this->inputs[1], 'name' ), $field_values, true );
			$value[ $this->id . '.3' ] = $this->get_input_value_submission( 'input_' . $this->id . '_3', rgar( $this->inputs[2], 'name' ), $field_values, true );
			$value[ $this->id . '.4' ] = $this->get_input_value_submission( 'input_' . $this->id . '_4', rgar( $this->inputs[3], 'name' ), $field_values, true );
		} else {
			$value = $this->get_input_value_submission( 'input_' . $this->id, $this->inputName, $field_values, $get_from_post_global_var );
		}

		return $value;
	}

	public function get_field_input( $form, $value = '', $entry = null ) {
		$is_entry_detail = $this->is_entry_detail();
		$is_form_editor  = $this->is_form_editor();

		$form_id  = $form['id'];
		$id       = intval( $this->id );
		$field_id = $is_entry_detail || $is_form_editor || $form_id == 0 ? "input_$id" : 'input_' . $form_id . "_$id";
		$form_id  = ( $is_entry_detail || $is_form_editor ) && empty( $form_id ) ? rgget( 'id' ) : $form_id;

		$disabled_text = $is_form_editor ? "disabled='disabled'" : '';
		$class_suffix  = $is_entry_detail ? '_admin' : '';

		$form_sub_label_placement  = rgar( $form, 'subLabelPlacement' );
		$field_sub_label_placement = $this->subLabelPlacement;
		$is_sub_label_above        = $field_sub_label_placement == 'above' || ( empty( $field_sub_label_placement ) && $form_sub_label_placement == 'above' );
		$sub_label_class_attribute = $field_sub_label_placement == 'hidden_label' ? "class='hidden_sub_label screen-reader-text'" : '';

		$account_number   = '';
		$routing_number   = '';
		$account_type	  = '';
		$autocomplete     = RGFormsModel::is_html5_enabled() ? "autocomplete='off'" : '';

		$onchange	= '';
		$onkeyup 	= '';

		if ( is_array( $value ) ) {
			$account_number     = esc_attr( rgget( $this->id . '.1', $value ) );
			$routing_number     = esc_attr( rgget( $this->id . '.2', $value ) );
			$account_type	    = esc_attr( rgget( $this->id . '.3', $value ) );
			$account_name       = esc_attr( rgget( $this->id . '.4', $value ) );
		}

		$payment_methods = apply_filters( 'gform_payment_methods', array(), $this, $form_id );
		$payment_options = '';
		if ( is_array( $payment_methods ) ) {
			foreach ( $payment_methods as $payment_method ) {
				$checked = rgpost( 'gform_payment_method' ) == $payment_method['key'] ? "checked='checked'" : '';
				$payment_options .= "<div class='gform_payment_option gform_payment_{$payment_method['key']}'><input type='radio' name='gform_payment_method' value='{$payment_method['key']}' id='gform_payment_method_{$payment_method['key']}' onclick='gformToggleACH();' onkeypress='gformToggleACH();' {$checked}/> {$payment_method['label']}</div>";
			}
		}
		$checked           = rgpost( 'gform_payment_method' ) == 'ach' || rgempty( 'gform_payment_method' ) ? "checked='checked'" : '';
		$card_radio_button = empty( $payment_options ) ? '' : "<input type='radio' name='gform_payment_method' id='gform_payment_method_ach' value='ach' onclick='gformToggleACH();' onkeypress='gformToggleACH();' {$checked}/>";

        // Aria attributes.
        $account_number_aria_attributes = $this->_has_settings_renderer ? $this->get_aria_attributes( $value, '1' ) : '';
        $routing_number_aria_attributes = $this->_has_settings_renderer ? $this->get_aria_attributes( $value, '2' ) : '';
        $account_type_aria_attributes = $this->_has_settings_renderer ? $this->get_aria_attributes( $value, '3' ) : '';
        $account_name_aria_attributes = $this->_has_settings_renderer ? $this->get_aria_attributes( $value, '4' ) : '';

        //account number fields
		$tabindex                = $this->get_tabindex();
		$account_number_field_input = GFFormsModel::get_input( $this, $this->id . '.1' );
		$html5_output            = ! is_admin() && GFFormsModel::is_html5_enabled() ? "pattern='[0-9]*' title='" . esc_attr__( 'Only digits are allowed', 'gf-ach-field' ) . "'" : '';
		$account_number_label    = rgar( $account_number_field_input, 'customLabel' ) != '' ? $account_number_field_input['customLabel'] : esc_html__( 'Account Number', 'gf-ach-field' );
		$account_number_label    = gf_apply_filters( array( 'gform_account_number', $form_id ), $account_number_label, $form_id );

		$account_number_placeholder = $this->get_input_placeholder_attribute( $account_number_field_input );
		if ( $is_sub_label_above ) {
			$account_field = "<span class='ginput_left{$class_suffix}' id='{$field_id}_1_container' >
									<label for='{$field_id}_1' id='{$field_id}_1_label' {$sub_label_class_attribute}>{$account_number_label}</label>
									<input type='text' name='input_{$id}.1' id='{$field_id}_1' value='{$account_number}' {$tabindex} {$disabled_text} {$onchange} {$onkeyup} {$autocomplete} {$html5_output} {$account_number_placeholder} {$account_number_aria_attributes}/>
								 </span>";
		} else {
			$account_field = "<span class='ginput_left{$class_suffix}' id='{$field_id}_1_container' >
									<input type='text' name='input_{$id}.1' id='{$field_id}_1' value='{$account_number}' {$tabindex} {$disabled_text} {$onchange} {$onkeyup} {$autocomplete} {$html5_output} {$account_number_placeholder} {$account_number_aria_attributes}/>
									<label for='{$field_id}_1' id='{$field_id}_1_label' {$sub_label_class_attribute}>{$account_number_label}</label>
								 </span>";
		}

		$tabindex                = $this->get_tabindex();
		$account_type_field_input = GFFormsModel::get_input( $this, $this->id . '.3' );
		$account_type_options = $this->get_account_type_options( $account_type );
		$account_type_label    = rgar( $account_type_field_input, 'customLabel' ) != '' ? $account_type_field_input['customLabel'] : esc_html__( 'Account Type', 'gf-ach-field' );
		$account_type_label    = gf_apply_filters( array( 'gform_account_type', $form_id ), $account_type_label, $form_id );
		if ( $is_sub_label_above ) {
			$account_type_field = "<span class='ginput_right{$class_suffix}' id='{$field_id}_3_container'>
                                    <label for='{$field_id}_3' id='{$field_id}_3_label' {$sub_label_class_attribute}>{$account_type_label}</label>
									<select name='input_{$id}.3' id='{$field_id}_3' {$tabindex} {$disabled_text} class='ginput_card_expiration' {$account_type_aria_attributes}>
										{$account_type_options}
									</select>
                                 </span>";
		} else {
			$account_type_field = "<span class='ginput_right{$class_suffix}' id='{$field_id}_3_container'>
                                    <select name='input_{$id}.3' id='{$field_id}_3' {$tabindex} {$disabled_text} class='ginput_card_expiration' {$account_type_aria_attributes}>
										{$account_type_options}
									</select>
                                    <label for='{$field_id}_3' id='{$field_id}_3_label' {$sub_label_class_attribute}>{$account_type_label}</label>
                                 </span>";
		}

		$tabindex                = $this->get_tabindex();
		$routing_number_field_input = GFFormsModel::get_input( $this, $this->id . '.2' );
		$html5_output            = ! is_admin() && GFFormsModel::is_html5_enabled() ? "pattern='[0-9]*' title='" . esc_attr__( 'Only digits are allowed', 'gf-ach-field' ) . "'" : '';
		$routing_number_label    = rgar( $routing_number_field_input, 'customLabel' ) != '' ? $routing_number_field_input['customLabel'] : esc_html__( 'Routing Number', 'gf-ach-field' );
		$routing_number_label    = gf_apply_filters( array( 'gform_routing_number', $form_id ), $routing_number_label, $form_id );

		$routing_number_placeholder = $this->get_input_placeholder_attribute( $routing_number_field_input );
		if ( $is_sub_label_above ) {
			$routing_field = "<span class='ginput_full{$class_suffix}' id='{$field_id}_2_container' >
                                    <label for='{$field_id}_2' id='{$field_id}_2_label' {$sub_label_class_attribute}>{$routing_number_label}</label>
                                    <input type='text' name='input_{$id}.2' id='{$field_id}_2' value='{$routing_number}' {$tabindex} {$disabled_text} {$onchange} {$onkeyup} {$autocomplete} {$html5_output} {$routing_number_placeholder} {$routing_number_aria_attributes}/>
                                 </span>";
		} else {
			$routing_field = "<span class='ginput_full{$class_suffix}' id='{$field_id}_2_container' >
                                    <input type='text' name='input_{$id}.2' id='{$field_id}_2' value='{$routing_number}' {$tabindex} {$disabled_text} {$onchange} {$onkeyup} {$autocomplete} {$html5_output} {$routing_number_placeholder} {$routing_number_aria_attributes}/>
                                    <label for='{$field_id}_2' id='{$field_id}_2_label' {$sub_label_class_attribute}>{$routing_number_label}</label>
                                 </span>";
		}

		$tabindex              = $this->get_tabindex();
		$account_name_field_input = GFFormsModel::get_input( $this, $this->id . '.4' );
		$account_name_label       = rgar( $account_name_field_input, 'customLabel' ) != '' ? $account_name_field_input['customLabel'] : esc_html__( 'Account Holder Name', 'gf-ach-field' );
		$account_name_label       = gf_apply_filters( array( 'gform_account_name', $form_id ), $account_name_label, $form_id );

		$account_name_placeholder = $this->get_input_placeholder_attribute( $account_name_field_input );
		if ( $is_sub_label_above ) {
			$account_name_field = "<span class='ginput_full{$class_suffix}' id='{$field_id}_4_container'>
                                            <label for='{$field_id}_4' id='{$field_id}_4_label' {$sub_label_class_attribute}>{$account_name_label}</label>
                                            <input type='text' name='input_{$id}.4' id='{$field_id}_4' value='{$account_name}' {$tabindex} {$disabled_text} {$account_name_placeholder} {$account_name_aria_attributes}/>
                                        </span>";
		} else {
			$account_name_field = "<span class='ginput_full{$class_suffix}' id='{$field_id}_4_container'>
                                            <input type='text' name='input_{$id}.4' id='{$field_id}_4' value='{$account_name}' {$tabindex} {$disabled_text} {$account_name_placeholder} {$account_name_aria_attributes}/>
                                            <label for='{$field_id}_4' id='{$field_id}_4_label' {$sub_label_class_attribute}>{$account_name_label}</label>
                                        </span>";
		}

		return "<div class='ginput_complex{$class_suffix} ginput_container ginput_container_ach' id='{$field_id}'>" . $account_field . $account_type_field . $routing_field . $account_name_field . ' </div>';

	}

	public function get_field_label_class(){
		return 'gfield_label gfield_label_before_complex';
	}

	public function get_value_entry_detail( $value, $currency = '', $use_text = false, $format = 'html', $media = 'screen' ) {
		if ( is_array( $value ) ) {
            $separator   = $format == 'html' ? '<br/>' : "\n";
			$account_number = esc_html__( 'Account Number', 'gf-ach-field' ) . ': ' . trim( rgget( $this->id . '.1', $value ) );
			$routing_number = rgget( $this->id . '.2', $value ) ? ( $separator . esc_html__( 'Routing Number', 'gf-ach-field' ) . ': ' . trim( rgget( $this->id . '.2', $value ) ) ) : '';
			$account_type   = trim( rgget( $this->id . '.3', $value ) );
            $options = $this->get_account_type_choices();
            $account_type = esc_html__( 'Account Type', 'gf-ach-field' ) . ': ' . ( rgar( $options, $account_type ) ? rgar( $options, $account_type ) : $account_type );

			return empty( $account_number ) ? '' : $account_type . $separator . $account_number . $routing_number;
		} else {
			return '';
		}
	}

	public function get_entry_inputs() {
		$inputs = array();
		// only store account type and number input values
		foreach ( $this->inputs as $input ) {
			if ( in_array( $input['id'], array( $this->id . '.1', $this->id . '.2', $this->id . '.3' ) ) ) {
				$inputs[] = $input;
			}
		}

		return $inputs;
	}

	public function get_value_save_entry( $value, $form, $input_name, $lead_id, $lead ) {

		//saving last 4 digits of account number
		list( $input_token, $field_id_token, $input_id ) = rgexplode( '_', $input_name, 3 );
		if ( $input_id == '1' || $input_id == '2' ) {
			$value              = str_replace( ' ', '', $value );
			$card_number_length = strlen( $value );
			$value              = substr( $value, - 4, 4 );
			$value              = str_pad( $value, $card_number_length, 'X', STR_PAD_LEFT );
		} elseif ( $input_id == '3' ) {

			$value = rgpost( "input_{$field_id_token}_3" );

		} else {
			$value = '';
		}

		return $this->sanitize_entry_value( $value, $form['id'] );
	}

	public function get_account_type_choices() {
		$options = apply_filters( 'gf_ach_account_type_choices', array(
			'S' => __( 'Savings', 'gf-ach-field' ),
			'C'	=> __( 'Checking', 'gf-ach-field' ),
		) );
		return $options;
	}

	private function get_account_type_options( $selected_value, $placeholder = '' ) {
		if ( empty( $placeholder ) ) {
			$placeholder = esc_html__( 'Select', 'gf-ach-field' );
		}
		$options = $this->get_account_type_choices();
		$str = "<option value=''>{$placeholder}</option>";
		foreach ( $options as $value => $label ) {
			$selected = $selected_value  == $value ? "selected='selected'" : '';
			$str .= "<option value='{$value}' {$selected}>{$label}</option>";
		}

		return $str;
	}

}

GF_Fields::register( new GF_Field_ACH() );

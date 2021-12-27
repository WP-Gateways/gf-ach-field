<?php
function gf_ach_js_set_default_values() {
	?>
	case "ach" :
		if (!field.label)
			field.label = <?php echo json_encode( esc_html__( 'ACH', 'gf-ach-field' ) ); ?>;
		var accNumber, accType, routingNumber, accName;

		accNumber = new Input(field.id + ".1", <?php echo json_encode( gf_apply_filters( array( 'gform_account_number', rgget( 'id' ) ), esc_html__( 'Account Number', 'gf-ach-field' ), rgget( 'id' ) ) ); ?>);
		routingNumber = new Input(field.id + ".2", <?php echo json_encode( gf_apply_filters( array( 'gform_routing_number', rgget( 'id' ) ), esc_html__( 'Routing Number', 'gf-ach-field' ), rgget( 'id' ) ) ); ?>);
		accType = new Input(field.id + ".3", <?php echo json_encode( gf_apply_filters( array( 'gform_account_type', rgget( 'id' ) ), esc_html__( 'Account Type', 'gf-ach-field' ), rgget( 'id' ) ) ); ?>);
		accName = new Input(field.id + ".4", <?php echo json_encode( gf_apply_filters( array( 'gform_account_name', rgget( 'id' ) ), esc_html__( 'Account Holder Name', 'gf-ach-field' ), rgget( 'id' ) ) ); ?>);
		field.inputs = [accNumber, routingNumber, accType, accName];
		break;
	<?php
}
add_action( 'gform_editor_js_set_default_values', 'gf_ach_js_set_default_values' );

function gf_ach_add_submission_data( $submission_data, $feed, $form, $entry ) {
	$fields = GFAPI::get_fields_by_type( $form, array( 'ach' ) );
	if( $fields ) {
		$ach_field = $fields[0];
		$submission_data['account_number']	 = rgpost( "input_{$ach_field->id}_1" );
		$submission_data['routing_number']	 = rgpost( "input_{$ach_field->id}_2" );
		$submission_data['account_type'] 	 = rgpost( "input_{$ach_field->id}_3" );
		$submission_data['account_name'] 	 = rgpost( "input_{$ach_field->id}_4" );
	}
	return $submission_data;
}
add_action( 'gform_submission_data_pre_process_payment', 'gf_ach_add_submission_data', 10, 4 );

function gf_ach_field_class( $css_class, $field, $form ) {
	if( GFFormsModel::get_input_type( $field ) == 'ach' && ! GFCommon::is_ssl() ) {
		$css_class .= ' gfield_creditcard_warning';
	}
	return $css_class;
}
add_filter( 'gform_field_css_class', 'gf_ach_field_class', 10, 3 );
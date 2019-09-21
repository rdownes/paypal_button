<?php
/**
 * @file Settings form for PayPal Button module.
 */

function paypal_button_config_form($form, $form_state) {
  $form['paypal_button_live_paypal_email'] = array(
    '#type' => 'textfield',
    '#title' => t('Email of the live PayPal account'),
    '#default_value' => variable_get('paypal_button_live_paypal_email', ''),
    '#size' => 50,
    '#maxlength' => 50,
    '#required' => TRUE,
    );
  $form['paypal_button_sandbox_paypal_email'] = array(
    '#type' => 'textfield',
    '#title' => t('Email of the sandbox PayPal account'),
    '#default_value' => variable_get('paypal_button_sandbox_paypal_email', ''),
    '#size' => 50,
    '#maxlength' => 50,
    '#required' => FALSE,
    );
  $form['paypal_button_live_or_sandbox'] = array(
    '#type' => 'radios',
    '#title' => t('Which PayPal system?'),
    '#default_value' => variable_get('paypal_button_live_or_sandbox', 0),
    '#description' => t('Switch between the live and sandbox PayPal systems'),
    '#options' => array(PAYPAL_BUTTON_LIVE => t('Live'), PAYPAL_BUTTON_SANDBOX => t('Sandbox'),),
    '#required' => TRUE,
    );
  $form['paypal_button_default_payment_completion_url'] = array(
    '#type' => 'textfield',
    '#title' => t('The site\'s default URL for payment completion'),
    '#description' => t('Relative to the site root. Enter \'/\' for the front page'),
    '#default_value' => variable_get('paypal_button_default_payment_completion_url', PAYPAL_DEFAULT_COMPLETION_URL),
    '#size' => 256,
    '#maxlength' => 256,
    '#required' => TRUE,
    );
  $form['paypal_button_default_payment_cancellation_url'] = array(
    '#type' => 'textfield',
    '#title' => t('The site\'s default URL for payment cancellation'),
    '#description' => t('Relative to the site root. Enter \'/\' for the front page'),
    '#default_value' => variable_get('paypal_button_default_payment_cancellation_url', PAYPAL_DEFAULT_CANCELLATION_URL),
    '#size' => 256,
    '#maxlength' => 256,
    '#required' => TRUE,
    );
  $form['paypal_button_default_continue_url'] = array(
    '#type' => 'textfield',
    '#title' => t('The site\'s default URL to continue shopping - if blank then return to current page'),
    '#description' => t('Relative to the site root. Enter \'/\' for the front page'),
    '#default_value' => variable_get('paypal_button_default_continue_url',''),
    '#size' => 256,
    '#maxlength' => 256,
    '#required' => FALSE,
    );
  $form['paypal_button_tax_option'] = array(
    '#type' => 'checkbox',
    '#title' => t('Allow tax rates to vary accross the site'),
    '#description' => t('If this is ticked then the site editor can set tax rates' . 
      ' for each button and the field below is a site wide default'),
    '#default_value' => variable_get('paypal_button_tax_option', FALSE),
    '#return_value' => TRUE,
    '#required' => FALSE,
    );
  $form['paypal_button_default_tax_rate'] = array(
    '#type' => 'textfield',
    '#title' => t('The default tax rate percentage'),
    '#description' => t('If the field above is ticked then this will be the default tax rate' .
      ' which the site editor can override for each field. If the field above is not ticked' .
      ' then this will be the rate used for all PayPal buttons on this site with no option' .
      ' the site editor to override'),
    '#default_value' => variable_get('paypal_button_default_tax_rate', ''),
    '#size' => 6,
    '#maxlength' => 6,
    '#required' => TRUE,
    );

  // Save configuration button
/*  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => 'Save configuration',
  );*/

//  return $form;
  return system_settings_form($form);
}

/**
 * Implements validation of the PayPal Button settings form.
 *
 * @param $form
 *   A structured array containing the elements and properties of the form.
 * @param $form_state
 *   An array that stores information about the form's current state
 *   during processing.
 */
function paypal_button_config_form_validate($form, &$form_state) {
  $live_email = $form_state['values']['paypal_button_live_paypal_email'];
  $sandbox_email = $form_state['values']['paypal_button_sandbox_paypal_email'];
  $live_or_sandbox = $form_state['values']['paypal_button_live_or_sandbox'];
  $payment_completion_url = $form_state['values']['paypal_button_default_payment_completion_url'];
  $payment_cancellation_url = $form_state['values']['paypal_button_default_payment_cancellation_url'];
  $continue_url = $form_state['values']['paypal_button_default_continue_url'];
  $default_tax_rate = $form_state['values']['paypal_button_default_tax_rate'];

  if (!valid_email_address(trim($live_email)) ) {
    form_set_error('paypal_button_live_paypal_email', t('Please enter a valid email address for the live PayPal account'));
  }
  if ($sandbox_email == '' && $live_or_sandbox == 1) {
    form_set_error('paypal_button_sandbox_paypal_email', t('Please enter the email address of the PayPal sandbox account if you wish to use the PayPal sandbox'));
  }
  if ($sandbox_email != '' && !valid_email_address(trim($sandbox_email))) {
    form_set_error('paypal_button_sandbox_paypal_email', t('Please enter a valid email address for the sandbox PayPal account'));
  }
  if (trim($payment_completion_url) == '<front>') {
    $payment_completion_url = '/';
  }
  if (!valid_url(trim($payment_completion_url))) {
    form_set_error('paypal_button_default_payment_completion_url',
      t('Please enter a valid payment completion URL'));
  }
  if (trim($payment_cancellation_url) == '<front>') {
    $payment_cancellation_url = '/';
  }
  if (!valid_url(trim($payment_cancellation_url))) {
    form_set_error('paypal_button_default_payment_cancellation_url',
      t('Please enter a valid payment cancellation URL'));
  }
  if (trim($continue_url) == '<front>') {
    $continue_url = '/';
  }
  if (!empty($continue_url) && !valid_url(trim($continue_url))) {
    form_set_error('paypal_button_default_continue_url',
      t('Please enter a valid \'Continue Shopping\' URL'));
  }
  if (!is_numeric($default_tax_rate) || (float) $default_tax_rate < 0) {
    form_set_error('paypal_button_default_tax_rate',
      t('Please enter a positive percentage number'));
  }
}


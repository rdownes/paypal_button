<?php
/**
 * Defined constants to cover the usual PayPal button variables.
 *
 * This is to make it easier to code to avoid the obscure PayPal variable names and values
 */

/* Order variables */

// Variable name to set up the website's address for receiving IPN 
define('INSTANT_PAYMENT_NOTIFICATION_ADDRESS', 'notify_url'); // Limit of 255 chars
// Varialbe name to define the currency to use - default if not specified is USD! 
define('CURRENCY', 'currency_code');
// Variable name to define the country to use
define('COUNTRY', 'country');
// Variable name to define email address for PayPal account to receive payment
define('PAYPAL_EMAIL', 'business');
// Variable name to define address PayPal returns to when customer clicks - Continue Shopping 
define('CONTINUE_SHOPPING_URL', 'shopping_url');
// Variable to ask the customer to add a note to the order 
define('PROMPT_FOR_NOTE', 'no_note');
// Values - default not requested
define('NOTE_REQUESTED', 0);
define('NOTE_NOT_REQUESTED', 1);
// Override the default prompt lable for note of 'Add special instructions to merchant'
define('NOTE_LABEL', 'cn');
/* Shipping address prompt */
define('PROMPT_FOR_SHIPPING_ADDRESS', 'no_shipping');
// Values - default is not required
define('SHIPPING_ADDRESS_NOT_REQUIRED', 0);
define('SHIPPING_ADDRESS_NOT_COLLECTED', 1);
define('SHIPPING_ADDRESS_REQUIRED', 2);
// Variable name to define the URL address to which PayPal returns on payment completion
define('PAYMENT_COMPLETION_URL', 'return');
// Variable name to define the method of PayPal passing back info to the website
define('RETURN_FORM_METHOD', 'rm');
// Values - default GET with all shopping cart transactions
define('GET_WITH_TRANSACTIONS', 0);
define('GET_WITHOUT_TRANSACTIONS', 1);
define('PUT_WITH_TRANSACTIONS', 2);
// Variable name to override the text on the 'Return to Merchant' button on the PayPal completed page
define('RETURN_BUTTON_LABEL', 'cbt');
// Varialbe name to define the URL address that PayPal will return to when the customer cancels the order
define('PAYMENT_CANCELLED_URL', 'cancel_return');

/* Line item variables */

// Variable name to define the price of the order line item
define('PRICE', 'amount');
// Variable name to define the product name for the order line item
// Following is optional for Buy Now, Donate, Subscribe and Add to Cart buttons. Not required for gift certificates
define('PRODUCT_NAME', 'item_name'); // If this is left blank customer can fill in text - up to 127 chars
// Variable name to define the quantity ordered for the order line item
define('ORDER_QUANTITY', 'quantity'); // Optional - defaults to 1
// Optional variables for the order line item
define('CUSTOM_VARIABLE_LABEL_ONE', 'on0'); // Limit 64 char
define('CUSTOM_VARIABLE_VALUE_ONE', 'os0'); // Limit 200 char
define('CUSTOM_VARIABLE_LABEL_TWO', 'on1');
define('CUSTOM_VARIABLE_VALUE_TWO', 'os1');
define('CUSTOM_VARIABLE_LABEL_THREE', 'on2');
define('CUSTOM_VARIABLE_VALUE_THREE', 'os2');
define('CUSTOM_VARIABLE_LABEL_FOUR', 'on3');
define('CUSTOM_VARIABLE_VALUE_FOUR', 'os3');
define('CUSTOM_VARIABLE_LABEL_FIVE', 'on4');
define('CUSTOM_VARIABLE_VALUE_FIVE', 'os4');
define('CUSTOM_VARIABLE_LABEL_SIX', 'on5');
define('CUSTOM_VARIABLE_VALUE_SIX', 'os5');
define('CUSTOM_VARIABLE_LABEL_SEVEN', 'on6');
define('CUSTOM_VARIABLE_VALUE_SEVEN', 'os6');

// Note the above do not support drop down options and priced options.

/* If the GoPayPal Class library is extended to allow one of the custom variables to be options with or with out Values then the 
 * below variables will be used.
 */
define('CUSTOM_VARIABLE_WITH_OPTION', 'option_index'); // Which of the variables above has options 0-6
define('CUSTOM_OPTION_LABLE_ONE', 'option_select0'); // 1st option value
define('CUSTOM_OPTION_AMOUNT_ONE', 'option_amount0'); // If priced this is 1st option value's amount
define('CUSTOM_OPTION_LABLE_TWO', 'option_select0'); // 1st option value
define('CUSTOM_OPTION_AMOUNT_TWO', 'option_amount0'); // If priced this is 1st option value's amount
define('CUSTOM_OPTION_LABLE_THREE', 'option_select0'); // 1st option value
define('CUSTOM_OPTION_AMOUNT_THREE', 'option_amount0'); // If priced this is 1st option value's amount
define('CUSTOM_OPTION_LABLE_FOUR', 'option_select0'); // 1st option value
define('CUSTOM_OPTION_AMOUNT_FOUR', 'option_amount0'); // If priced this is 1st option value's amount
define('CUSTOM_OPTION_LABLE_FIVE', 'option_select0'); // 1st option value
define('CUSTOM_OPTION_AMOUNT_FIVE', 'option_amount0'); // If priced this is 1st option value's amount
define('CUSTOM_OPTION_LABLE_SIX', 'option_select0'); // 1st option value
define('CUSTOM_OPTION_AMOUNT_SIX', 'option_amount0'); // If priced this is 1st option value's amount
define('CUSTOM_OPTION_LABLE_SEVEN', 'option_select0'); // 1st option value
define('CUSTOM_OPTION_AMOUNT_SEVEN', 'option_amount0'); // If priced this is 1st option value's amount
define('CUSTOM_OPTION_LABLE_EIGHT', 'option_select0'); // 1st option value
define('CUSTOM_OPTION_AMOUNT_EIGHT', 'option_amount0'); // If priced this is 1st option value's amount
define('CUSTOM_OPTION_LABLE_NINE', 'option_select0'); // 1st option value
define('CUSTOM_OPTION_AMOUNT_NINE', 'option_amount0'); // If priced this is 1st option value's amount
define('CUSTOM_OPTION_LABLE_TEN', 'option_select0'); // 1st option value
define('CUSTOM_OPTION_AMOUNT_TEN', 'option_amount0'); // If priced this is 1st option value's amount

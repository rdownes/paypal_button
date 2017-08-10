<?php
require('PaypalSimpleClass.php');
//change below to you paypal account name
PaypalSimpleClass::singleton("you@yourdomain.com","");
// turn on error reporting
PaypalSimpleClass::setErrorReporting(true);
// turn on sandbox mode
PaypalSimpleClass::setActionUrl("https://www.sandbox.paypal.com/cgi-bin/webscr");
// set return url
PaypalSimpleClass::setReturnUrl('http://www.yourdomain.com/sample.php'); 
// set a custom button image
//PaypalSimpleClass::setButtonImage('http://www.yourdomain.com/button.png');
// Set the currency code and symbol
PaypalSimpleClass::setCurrency('USD','$');
// create an item
PaypalSimpleClass::setItem(1, 'Test Item 1', '', 10.00, 1.00, '');
// create a product option
PaypalSimpleClass::opt1Name('Colour');
// assign options and price pairs
PaypalSimpleClass::opt1ArrayValue(array(array('Pink', 20.00),array('Red', 22.00)));
// create a product option
PaypalSimpleClass::opt2Name('Size');
// assign options
PaypalSimpleClass::opt2ArrayValue(array('Small','Medium','Large'));
// output form to screen
echo PaypalSimpleClass::getItem();
?>
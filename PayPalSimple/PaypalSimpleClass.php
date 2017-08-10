<?php
/**
 * NOTE from Rory Downes - owner of the Drupal PayPal Button module that uses this file.
 * This class file comes from phpclasses.org. It has not been maintained since 2014
 * and contained some bugs. I have included this version with bug fixes for this project.
 */
/**
 * Program			: PaypalSimpleClass
 * Version			: 1.02
 * Author			: Robert Ireland
 * Abstract			: Simple Paypal API helper
 *		(1)
 *		Generate simple paypal buttons including buy now, add to cart and donate.
 * Note				: Always take care when handling transactions involving money.
 * Contact Email	: webmaster@eldasolutions.com
 
 Copyright (C) 2014  Robert Ireland
 
 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 any later version.
 
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 
 You should have received a copy of the GNU General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.

	1.01 Change log
		setCurrency($currency, $char = '')
			you can now set the currency character at the same time as changing the currency
			
		setCurrencyChar($char)
			you can set the currency character to be displayed

		getCurrencyChar
			you can retrive the current currency character
	1.02 Change Log
		__construct
			UTF-8 encodes currency char
		setItem
			re-initialise option variables to stop options duplicating on other buttons
		getItem
			outputs the currency char using htmentities using UTF-8 encoding
		setCurrencyChar
			now encodes string to UTF-8 format
		getCurrencyChar
			now return correct variable
		setDrawMode
			added function to allow either table or div drawing
		getDrawMode
			added function to return the current draw mode
		setBtnSize
			added function to set button size for large and small
		getBtnSize
			added function to return the current button size
			
			
			// note need to fix reset of sub options as they keep drawing to screen
	
**/
/**
 * Modifications made by Rory Downes.
 *
 * Fixes:
 *  setShoppingUrl and getShoppingUrl methods set private static variable 
 *    $item_return_url.
 *    Fix was to create private static vairable $item_shopping_url and changed 
 *     setShoppingUrl and getShoppingUrl to use and set this variable.
 *     also change getItem method to use this new variable.
 *  textValue method sets private static $text_field but the method getItem
 *    does not use it.
 *    Fix is to change getItem to set the PayPal variable 'cn'.
 *	Make sure price is set if there are options with out prices.
 *		getHTML method. Add to criteria so that if a price is set it is added to the HTML.
 *	Make sure prices are not showing on options if none are set.
 *		getHTML method. Test for option price of zero to avoid adding it to the option value
 *		and also avoid adding the separate setting of option_amount.
 *  Fix setTarget method.
 *    This was using strtoupper and then comparing to lowercase! Changed to strtolower.
 *    Also the test was to make sure it was either '_top' or 'paypal'. The logic for a fail
 *    tested that it was not '_top' OR not 'paypal' when the logic should be AND.
 *
 * Clearer code:
 *  Don't make users set the action URL - get them to pass in the TRUE/FALSE flag $sandbox.
 *   Add the method setSandboxFlag.
 *   This receives TRUE or FALSE and sets $item_action_url to the appropriate URL.
 *   Methods setActionUrl and getActionUrl are maintained for backwards compatibility.
 *  Added constant for default tax rate so that it can be refered to in calling code.
 *
 * Additions:
 *  created private static variable $item_cancel_url and methods setCancelUrl and getCancelUrl
 *   methods to set and get this variable. Also changed getItem method to use this variable.
 *
 * Notes:
 *  The original code sets language code to 'GB' (private static variable $language).
 *   This is passed in the PayPal 'lc' variable. However, PayPal's documentation implies that
 *   this is only revent to 'Subscribe' button which is not yet implemented. Also there are no
 *   methods to change this to another language and it overrides the default stored as a cookie
 *   by PayPal. I have removed the use of this variable from the method getItem to avoid issues.
 */

define('PAYPALSIMPLECLASS_DEFAULT_TAX_RATE', '20.000');

class PaypalSimpleClass
{
	private static $instance;
	private static $verno = "1.000a";
	private static $error_reporting = false;
	private static $account_id = '';
	private static $language = 'GB';
	private static $currency = 'GBP';
	private static $currency_chr = '£';
	private static $subtype = 'products';
	private static $notes = 0;
	private static $action_target = 'paypal';
	private static $action_method = 'post';
	private static $action_url = 'https://www.paypal.com/cgi-bin/webscr';
	private static $tax_rate = PAYPALSIMPLECLASS_DEFAULT_TAX_RATE;
	
	private static $draw_mode = 1; // 0 = table | 1 = div
	private static $btn_size = 1; //  0 = large | 1 = small
	
	private static $btn_types = array('_xclick','_cart','_donations');
	private static $btn_images = array('PP-BuyNowBF:btn_buynow_LG.gif:NonHostedGuest','PP-ShopCartBF:btn_cart_LG.gif:NonHostedGuest','PP-DonationsBF:btn_donate_LG.gif:NonHostedGuest');
	private static $btn_images_lrg_url = array('https://www.paypalobjects.com/en_GB/i/btn/btn_buynow_LG.gif','https://www.paypalobjects.com/en_GB/i/btn/btn_cart_LG.gif','https://www.paypalobjects.com/en_GB/i/btn/btn_donate_LG.gif');
	private static $btn_images_sml_url = array('https://www.paypalobjects.com/en_GB/i/btn/btn_buynow_SM.gif','https://www.paypalobjects.com/en_GB/i/btn/btn_cart_SM.gif','https://www.paypalobjects.com/en_GB/i/btn/btn_donate_SM.gif');
	private static $btn_custom_image = '';
	
	private static $allowed_currency_codes = array('AUD','BRL','CAD','CZK','DKK','EUR','HKD','HUF','ILS','JPY','MYR','MXN','NOK','NZD','PHP','PLN','GBP','SGD','SEK','CHF','TWD','THB','TRY','USD');
	private static $allowed_action_urls = array('https://www.sandbox.paypal.com/cgi-bin/webscr','https://www.paypal.com/cgi-bin/webscr');
	
	private static $item_type = 1;
	private static $item_name = '';
	private static $item_number = '';
	private static $item_cost = 0;
	private static $item_shipping = 0;
	private static $item_return_url = '';
	private static $item_shopping_url = '';
	private static $item_cancel_url = '';
	
	private static $option1_name = '';
	private static $option1 = array();
	private static $option2_name = '';
	private static $option2 = array();
	private static $text_field = '';

	private static $status = false;
	private static $last_item = '';
	
	private static $style_default = 'paypal';
	private static $style_cart_image = '';
	private static $style_cart_border_color = '';
	private static $style_header_image = '';
	private static $style_headerback_color = '';
	private static $style_headerborder_color = '';
	private static $style_cart_logo = '';
	private static $style_payflow_color = '';

    /**
    * constructor
	* Access	 : Private
    * @param1    : $accountID (string) - Your PayPal ID or an email address associated with your PayPal account. Email addresses must be confirmed. 
    * @param2    : $image (string) - custom button image
	* Note		 : Uses singleton factory do not use "new"
	* Detail   	 : Initiate static variables and create instance and send performace timer start if the performance class is available.
    */
	private function __construct($accountID, $image)
	{
		if (class_exists('PerformanceClass')) PerformanceClass::timer_start('PAYPALSIMPLE_CLASS');
		self::$account_id = $accountID;
		self::$btn_custom_image = $image;
		self::$option1 = array();
		self::$option2 = array();
		self::$currency_chr = utf8_encode('£');
	}

    /**
    * destructor
	* Access	 : Public
	* Detail   	 : Destroys Singleton instance and send performace timer end if the performance class is available.
    */
	final public function __destruct()
	{
		if (class_exists('PerformanceClass')) PerformanceClass::timer_end('PAYPALSIMPLE_CLASS');
		self::$instance = null;
	}

    /**
    * singleton
	* Access	 : Public
    * @param1    : $accountID (string) - Your PayPal ID or an email address associated with your PayPal account. Email addresses must be confirmed. 
    * @param2    : $image (string) - custom button image
	* Note		 : Uses singleton factory do not use "new"
	* Detail   	 : Start the Singleton class instance before calling other methods
	* Sample 1	 : PaypalSimpleClass::singleton("you@yourdomain.com","");
	* Sample 2	 : PaypalSimpleClass::singleton("you@yourdomain.com","http://www.yourdomain.com/yourbutton.jpg");
    */
	public static function singleton($accountID, $image = '') 
	{
		if (!isset(self::$instance))
		{
			$c = __CLASS__;
			self::$instance = new $c($accountID, $image);
		}
		return self::$instance;
	}

    /**
    * setItem
	* Access	 : Public
    * @param1    : $item_type (integer) - 0 - buy now / 1 - add to cart / 2 - donation. 
    * @param2    : $item_name (string) - product name
	* @param3    : $item_number (string) - product identification number
	* @param4    : $item_amount (float) - product price
	* @param5    : $item_shipping (float) - product shipping price
	* @param6    : $item_return_url (string) - return url for redirect from paypal site
	* Note		 : @param1 must be either 0,1,2 and @param2 is required.
	* Detail   	 : Initiate a button and set start values
	* Sample 1	 : PaypalSimpleClass::setItem(0, 'Sample Product', 'TT01', 10.00, 1.25, 'http://www.yourdomain.com/thisproduct.php');
	* Sample 2	 : PaypalSimpleClass::setItem(1, 'Sample Product', 'TT01', 10.00, 1.25, 'http://www.yourdomain.com/thisproduct.php');
	* Sample 3	 : PaypalSimpleClass::setItem(2, '£10 pound donation', '', 10.00);
    */
	public static function setItem($item_type = 1, $item_name, $item_number = '', $item_amount = 0.0, $item_shipping = 0.0, $item_return_url = '')
	{
		// $item_type must be either 0,1,2
		if (($item_type <= 0) && (2 <= $item_type))
		{
			if (self::$error_reporting == true) self::trigger_error(__CLASS__." ".__LINE__.": Type out of range.", E_USER_NOTICE);
			return false;
		}
		// $item_name is required.
		if (empty($item_name))
		{
			if (self::$error_reporting == true) self::trigger_error(__CLASS__." ".__LINE__.": No Item name.", E_USER_NOTICE);
			return false;
		}
		if (!is_float($item_amount))
		{
			if (self::$error_reporting == true) self::trigger_error(__CLASS__." ".__LINE__.": Amount is not float. ".gettype($item_amount)." passed.", E_USER_NOTICE);
			return false;
		}
		if (!is_float($item_shipping))
		{
			if (self::$error_reporting == true) self::trigger_error(__CLASS__." ".__LINE__.": Shipping is not float. ".gettype($item_amount)." passed.", E_USER_NOTICE);
			return false;
		}
		self::$item_type = $item_type;
		self::$item_name = $item_name;
		self::$item_number = $item_number;
		self::$item_cost = $item_amount;
		self::$item_shipping = $item_shipping;
		self::$item_return_url = $item_return_url;
		self::$option1_name = '';
		self::$option1 = array();
		self::$option2_name = '';
		self::$option2 = array();
//		self::$text_field = '';
		self::$status = true;
	}

    /**
    * getItem
	* Access	 : Public
	* Detail   	 : Return HTML form for paypal button
	* Sample 1	 : echo PaypalSimpleClass::getItem();
    */
	public static function getItem()
	{
		// Check that setItem has been called first
		if (self::$status == false)
		{
			if (self::$error_reporting == true) self::trigger_error(__CLASS__." ".__LINE__.": Item not initialised.", E_USER_NOTICE);
			return false;
		}
		$item = '<form action="'.self::$action_url.'" method="'.self::$action_method.'" target="'.self::$action_target.'" class="item_form">'.PHP_EOL;
		$item .= '<input type="hidden" name="cmd" value="'.self::$btn_types[self::$item_type].'"></input>'.PHP_EOL;
		$item .= '<input type="hidden" name="business" value="'.self::$account_id.'"></input>'.PHP_EOL;
//		$item .= '<input type="hidden" name="lc" value="'.self::$language.'"></input>'.PHP_EOL;
		$item .= '<input type="hidden" name="item_name" value="'.self::$item_name.'"></input>'.PHP_EOL;
		if (self::$item_type == 0 || self::$item_type == 1 && !empty(self::$item_number)) $item .= '<input type="hidden" name="item_number" value="'.self::$item_number.'"></input>'.PHP_EOL;
		if (empty(self::$option1) || self::$item_type == 2 || self::$item_cost<>0) $item .= '<input type="hidden" name="amount" value="'.self::$item_cost.'"></input>'.PHP_EOL;
		$item .= '<input type="hidden" name="currency_code" value="'.self::$currency.'"></input>'.PHP_EOL;
		if (self::$item_type == 0 || self::$item_type == 1) $item .= '<input type="hidden" name="button_subtype" value="services"></input>'.PHP_EOL;
		$item .= '<input type="hidden" name="no_note" value="'.self::$notes.'"></input>'.PHP_EOL;
		$item .= '<input type="hidden" name="cn" value="' . self::$text_field .
			'"></input>' . PHP_EOL;
		if (self::$item_type == 0 || self::$item_type == 1)
		{
			$item .= '<input type="hidden" name="tax_rate" value="'.self::$tax_rate.'"></input>'.PHP_EOL;
			$item .= '<input type="hidden" name="shipping" value="'.self::$item_shipping.'"></input>'.PHP_EOL;
		}
		if (self::$item_type == 1) $item .= '<input type="hidden" name="add" value="1"></input>'.PHP_EOL;
		if (empty(self::$btn_custom_image))
		{
			$item .= '<input type="hidden" name="bn" value="'.self::$btn_images[self::$item_type].'"></input>'.PHP_EOL;
		} else {
			$item .= '<input type="hidden" name="bn" value="PP-ShopCartBF:'.basename(self::$btn_custom_image).':NonHostedGuest"></input>'.PHP_EOL;
		}
		$subItems = '';
		if (self::$item_type == 0 || self::$item_type == 1 && !empty(self::$option1))
		{
			if (self::$draw_mode == 0)
			{
				$item .= '<table>'.PHP_EOL;
				$item .= '<tr><td><input type="hidden" name="on0" value="'.self::$option1_name.'"></input>'.self::$option1_name.'</td></tr>'.PHP_EOL;
				$item .= '<tr><td>'.PHP_EOL.'<select name="os0">'.PHP_EOL;
			} else {
				$item .= '<input type="hidden" name="on0" value="'.self::$option1_name.'"></input>'.PHP_EOL.'<div class="item_opt1_name">'.self::$option1_name.'</div>'.PHP_EOL;
				$item .= '<div class="item_opt1_select"><select name="os0">'.PHP_EOL;
			}
			$counter = 1;
			foreach(self::$option1 as $v)
			{
				if ($v["amount"] == 0) {
					$item .= '<option value="'.$v["text"].'">'.$v["text"].'</option>'.PHP_EOL;
				} else {
					$item .= '<option value="'.$v["text"].'">'.$v["text"].' - '.htmlentities(self::$currency_chr, ENT_QUOTES, "UTF-8").$v["amount"].'</option>'.PHP_EOL;
					$subItems .= '<input type="hidden" name="option_amount'.($counter - 1).'" value="'.$v["amount"].'"></input>'.PHP_EOL;
				}
				$subItems .= '<input type="hidden" name="option_select'.($counter - 1).'" value="'.$v["text"].'"></input>'.PHP_EOL;
				$counter ++;
			}
			if (self::$draw_mode == 0)
			{
				$item .= '</select></td></tr>'.PHP_EOL;
			} else {
				$item .= '</select></div>'.PHP_EOL;
			}
			if (!empty(self::$option2))
			{
				if (self::$draw_mode == 0)
				{
					$item .= '<tr><td><input type="hidden" name="on1" value="'.self::$option2_name.'"></input>'.self::$option2_name.'</td></tr><tr>'.PHP_EOL;
					$item .= '<tr><td>'.PHP_EOL.'<select name="os1">'.PHP_EOL;
				} else {
					$item .= '<input type="hidden" name="on1" value="'.self::$option2_name.'"></input>'.PHP_EOL.'<div class="item_opt2_name">'.self::$option2_name.'</div>'.PHP_EOL;
					$item .= '<div class="item_opt2_select"><select name="os1">'.PHP_EOL;
				}
				$counter = 1;
				foreach(self::$option2 as $v)
				{
					$item .= '<option value="'.$v["text"].'">'.$v["text"].'</option>'.PHP_EOL;
				}
				if (self::$draw_mode == 0)
				{
					$item .= '</select></td></tr>'.PHP_EOL;
				} else {
					$item .= '</select></div>'.PHP_EOL;
				}
			}
			if (self::$draw_mode == 0) $item .= '</table>'.PHP_EOL;
			$subItems .= '<input type="hidden" name="option_index" value="0"></input>'.PHP_EOL;
		}
		if (!empty($subItems)) $item .= $subItems;
		
		if (self::$item_type == 0 || self::$item_type == 1 && !empty(self::$item_shopping_url)) $item .= '<input type="hidden" name="shopping_url" value="'.self::$item_shopping_url.'" ></input>'.PHP_EOL;
		if (self::$item_type == 0 || self::$item_type == 1 && !empty(self::$item_return_url)) $item .= '<input type="hidden" name="return" value="'.self::$item_return_url.'" ></input>'.PHP_EOL;
		if (self::$item_type == 0 || self::$item_type == 1 && !empty(self::$item_cancel_url)) $item .= '<input type="hidden" name="cancel_return" value="'.self::$item_cancel_url.'" ></input>'.PHP_EOL;
		// check below
		if (!empty(self::$style_default)) $item .= '<input type="hidden" name="page_style" value="'.self::$style_default.'" ></input>'.PHP_EOL;
		if (!empty(self::$style_cart_image)) $item .= '<input type="hidden" name="image_url" value="'.self::$style_cart_image.'" ></input>'.PHP_EOL;
		if (!empty(self::$style_cart_border_color)) $item .= '<input type="hidden" name="cpp_cart_border_color" value="'.self::$style_cart_border_color.'" ></input>'.PHP_EOL;
		if (!empty(self::$style_header_image)) $item .= '<input type="hidden" name="cpp_header_image" value="'.self::$style_header_image.'" ></input>'.PHP_EOL;
		if (!empty(self::$style_headerback_color)) $item .= '<input type="hidden" name="cpp_headerback_color" value="'.self::$style_headerback_color.'" ></input>'.PHP_EOL;
		if (!empty(self::$style_headerborder_color)) $item .= '<input type="hidden" name="cpp_headerborder_color" value="'.self::$style_headerborder_color.'" ></input>'.PHP_EOL;
		if (!empty(self::$style_cart_logo)) $item .= '<input type="hidden" name="cpp_logo_image" value="'.self::$style_cart_logo.'" ></input>'.PHP_EOL;
		if (!empty(self::$style_payflow_color)) $item .= '<input type="hidden" name="cpp_payflow_color" value="'.self::$style_payflow_color.'" ></input>'.PHP_EOL;
		if (empty(self::$btn_custom_image))
		{
			if (self::$btn_size == 0)
			{
				$item .= '<input type="image" src="'.self::$btn_images_lrg_url[self::$item_type].'" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!"></input>'.PHP_EOL;
			} else {
				$item .= '<input type="image" src="'.self::$btn_images_sml_url[self::$item_type].'" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!"></input>'.PHP_EOL;
			}
		} else {
			$item .= '<input type="image" src="'.self::$btn_custom_image.'" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!"></input>'.PHP_EOL;
		}
		$item .= '<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1" />'.PHP_EOL;
		$item .= '</form>'.PHP_EOL;
		self::$status = false;
		self::$last_item = $item;
		return $item;
	}

    /**
    * opt1Name
	* Access	 : Public
    * @param1    : $name (string). 
	* Detail   	 : Set title for option group 1 i.e 'colour' or 'size'
	* Sample 1	 : PaypalSimpleClass::opt1Name('Colour');
    */
	public static function opt1Name($name)
	{
		self::$option1_name = $name;
	}

    /**
    * opt1Value
	* Access	 : Public
    * @param1    : $text (string) - option name i.e. 'red' or 'small'
	* @param2    : $amount (decimal) - product price
	* Detail   	 : add a single value pair for option group 1
	* Sample 1	 : PaypalSimpleClass::opt1Value('Red', '2.55');
    */
	public static function opt1Value($text, $amount = 0)
	{
		self::$option1[] = array("text" => $text, "amount" => $amount);
	}

    /**
    * opt1ArrayValue
	* Access	 : Public
    * @param1    : $array (array) - option array i.e. array('Pink', 20.52)
	* Detail   	 : add an array or value pairs for option group 1
	* Sample 1	 : PaypalSimpleClass::opt1ArrayValue(array(array('Pink', 20.52),array('Red', 22)));
    */
	public static function opt1ArrayValue($array)
	{
		if (!is_array($array) || empty($array))
		{
			if (self::$error_reporting == true) self::trigger_error(__CLASS__." ".__LINE__.": Opt1 Array not array or empty.", E_USER_NOTICE);
			return false;
		}
		foreach($array as $v)
		{
			self::$option1[] = array("text" => $v[0], "amount" => $v[1]);
		}
	}

    /**
    * opt2Name
	* Access	 : Public
    * @param1    : $name (string). 
	* Detail   	 : Set title for option group 2 i.e 'colour' or 'size'
	* Sample 1	 : PaypalSimpleClass::opt2Name('Colour');
    */
	public static function opt2Name($name)
	{
		self::$option2_name = $name;
	}

    /**
    * opt2Value
	* Access	 : Public
    * @param1    : $text (string) - option name i.e. 'red' or 'small'
	* Detail   	 : add a single value for option group 2
	* Sample 1	 : PaypalSimpleClass::opt2Value('Red');
	*/
	public static function opt2Value($text)
	{
		self::$option2[] = array("text" => $text);
	}

    /**
    * opt2ArrayValue
	* Access	 : Public
    * @param1    : $array (array) - option array i.e. array('Small','Medium','Large')
	* Detail   	 : add an array or values for option group 2
	* Sample 1	 : PaypalSimpleClass::opt2ArrayValue(array('Small','Medium','Large'));
	*/
	public static function opt2ArrayValue($array)
	{
		if (!is_array($array) || empty($array))
		{
			if (self::$error_reporting == true) self::trigger_error(__CLASS__." ".__LINE__.": Opt2 Array not array or empty.", E_USER_NOTICE);
			return false;
		}
		foreach($array as $v)
		{
			self::$option2[] = array("text" => $v);
		}
	}

    /**
    * setBtnSize
	* Access	 : Public
    * @param1    : $size (integer) - 
	* Detail   	 : Set the button size 0 = large / 1 = small
	* Sample 1	 : PaypalSimpleClass::setBtnSize(1);
	*/
	public static function setBtnSize($size)
	{
		self::$btn_size = $size;
	}

    /**
    * getBtnSize
	* Access	 : Public
	* Detail   	 : Returns the current button size.
	* Sample 1	 : PaypalSimpleClass::getBtnSize();
	*/
	public static function getBtnSize()
	{
		return self::$btn_size;
	}

    /**
    * setDrawMode
	* Access	 : Public
    * @param1    : $mode (integer) - 
	* Detail   	 : Set draw mode either table or div output
	* Sample 1	 : PaypalSimpleClass::setDrawMode(1);
	*/
	public static function setDrawMode($mode)
	{
		self::$draw_mode = $mode;
	}

    /**
    * getDrawMode
	* Access	 : Public
	* Detail   	 : Returns the draw mode.
	* Sample 1	 : PaypalSimpleClass::getDrawMode();
	*/
	public static function getDrawMode()
	{
		return self::$draw_mode;
	}

    /**
    * textValue
	* Access	 : Public
    * @param1    : $name (string) - 
	* Detail   	 : Set title for text field i.e 'Enter text to be printed' or 'Name to put on item'
	* Sample 1	 : PaypalSimpleClass::textValue('Enter text to be printed');
	*/
	public static function textValue($name)
	{
		self::$text_field = $name;
	}

    /**
    * setCurrencyChar
	* Access	 : Public
    * @param1    : $char (string) - 
	* Detail   	 : Set the currency character like £,$ etc, unless changed this will be applied to all buttons.
	* Sample 1	 : PaypalSimpleClass::setCurrencyChar('$');
	*/
	public static function setCurrencyChar($char = '')
	{
			self::$currency_chr = $char;
//			self::$currency_chr = utf8_encode($char);
	}

    /**
    * getCurrencyChar
	* Access	 : Public
	* Detail   	 : Returns the currency code, unless changed this will be applied to all buttons.
	* Sample 1	 : PaypalSimpleClass::getCurrencyChar();
	*/
	public static function getCurrencyChar()
	{
		return self::$currency_chr;
	}

    /**
    * setCurrency
	* Access	 : Public
    * @param1    : $currency (string) - 
	* Detail   	 : Set the currency code and or currency symbol, unless changed this will be applied to all buttons.
	* Sample 1	 : PaypalSimpleClass::setCurrency('usd');
	* Sample 2	 : PaypalSimpleClass::setCurrency('USD','$');
	*/
	public static function setCurrency($currency, $char = '')
	{
		$currency_upper = strtoupper($currency);
		if (!in_array($currency_upper, self::$allowed_currency_codes) || strlen($currency_upper) != 3)
		{
			if (self::$error_reporting == true) self::trigger_error(__CLASS__." ".__LINE__.": Currency code not found.", E_USER_NOTICE);
			return false;
		}
		if (!empty($char)) self::setCurrencyChar($char);
		self::$currency = $currency_upper;
	}

    /**
    * getCurrency
	* Access	 : Public
	* Detail   	 : Returns the currency code, unless changed this will be applied to all buttons.
	* Sample 1	 : PaypalSimpleClass::getCurrency();
	*/
	public static function getCurrency()
	{
		return self::$currency;
	}

    /**
    * setTaxrate
	* Access	 : Public
    * @param1    : $rate (float) - 
	* Detail   	 : Set the tax rate, unless changed this will be applied to all buttons.
	* Sample 1	 : PaypalSimpleClass::setCurrency('17.50');
	*/
	public static function setTaxrate($rate)
	{
		if (!is_numeric($rate) || !is_float($rate))
		{
			if (self::$error_reporting == true) self::trigger_error(__CLASS__." ".__LINE__.": Tax rate is not numeric.", E_USER_NOTICE);
			return false;
		}
		self::$tax_rate = $rate;
	}

    /**
    * getTaxrate
	* Access	 : Public
	* Detail   	 : Returns the current tax rate, unless changed this will be applied to all buttons.
	* Sample 1	 : PaypalSimpleClass::getTaxrate();
	*/
	public static function getTaxrate()
	{
		return self::$tax_rate;
	}

    /**
    * setTarget
	* Access	 : Public
	* @param1    : $target (string) - either '_top' or 'paypal'
	* Detail   	 : Set the target window for when the button is clicked, unless changed this will be applied to all buttons.
	* Sample 1	 : PaypalSimpleClass::setTarget('_top');
	*/
	public static function setTarget($target)
	{
		$target_lower = strtolower($target);
		if ($target_lower !== "_top" && $target_lower !== "paypal")
		{
			if (self::$error_reporting == true) self::trigger_error(__CLASS__." ".__LINE__.": Target must be _top or paypal.", E_USER_NOTICE);
			return false;
		}
		self::$action_target = $target_lower;
	}

    /**
    * getTarget
	* Access	 : Public
	* Detail   	 : Returns the current target for the button click, unless changed this will be applied to all buttons.
	* Sample 1	 : PaypalSimpleClass::getTaxrate();
	*/
	public static function getTarget()
	{
		return self::$action_target;
	}

    /**
    * setButtonImage
	* Access	 : Public
	* @param1    : $image (string) - the url of a custom button image
	* Detail   	 : Set the url for a custom button image, unless changed this will be applied to all buttons.
	* Sample 1	 : PaypalSimpleClass::setButtonImage('http://www.yourdomain.com/button.png');
	*/
	public static function setButtonImage($image)
	{
		if (!filter_var($image, FILTER_VALIDATE_URL))
		{
			if (self::$error_reporting == true) self::trigger_error(__CLASS__." ".__LINE__.": Invalid URL passed for image.", E_USER_NOTICE);
			return false;
		}
		self::$btn_custom_image = $image;
	}

    /**
    * getButtonImage
	* Access	 : Public
	* Detail   	 : Returns the current custom image for the button, unless changed this will be applied to all buttons.
	* Sample 1	 : PaypalSimpleClass::getButtonImage();
	*/
	public static function getButtonImage()
	{
		return self::$btn_custom_image;
	}

    /**
    * setShoppingUrl
	* Access	 : Public
	* @param1    : $url (string) - the url for return to site from paypal
	* Detail   	 : Set the url for return to site from paypal, unless changed this will be applied to all buttons.
	* Sample 1	 : PaypalSimpleClass::setButtonImage('http://www.yourdomain.com/youritem.php');
	*/
	public static function setShoppingUrl($url)
	{
		if (!filter_var($url, FILTER_VALIDATE_URL))
		{
			if (self::$error_reporting == true) self::trigger_error(__CLASS__." ".__LINE__.": Invalid URL passed for shopping return.", E_USER_NOTICE);
			return false;
		}
		self::$item_shopping_url = $url;
	}

    /**
    * getShoppingUrl
	* Access	 : Public
	* Detail   	 : Returns the current url for return to site from paypal, unless changed this will be applied to all buttons.
	* Sample 1	 : PaypalSimpleClass::getShoppingUrl();
	*/
	public static function getShoppingUrl()
	{
		return self::$item_shopping_url;
	}

	/**
	 * setSandbox
	 * Access 	: Public
	 * @param1 	: $sandbox (boolean) - TRUE if the PayPal Sandbox site is to be done.
	 * Detail 	: Set the url for the form to post to PayPal from the flag passed, unless changed
	 *            this will be applied to all buttons.
	 * Sample1 	: PaypalSimpleClass::setSandbox(TRUE);
	 * Sample2	: PaypalSimpleClass::setSandbox(FALSE);
	 */
	public static function setSandbox($sandbox = FALSE) {
		if ($sandbox) {
			self::$action_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
		} else {
			self::$action_url = 'https://www.paypal.com/cgi-bin/webscr';
		}
	}

    /**
    * setActionUrl
	* Access	 : Public
	* @param1    : $url (string) - the url for the form to post to, either normal paypal or sandbox
	* Detail   	 : Set the url for the form to post to paypal, unless changed this will be applied to all buttons.
	* Sample 1	 : PaypalSimpleClass::setActionUrl('https://www.sandbox.paypal.com/cgi-bin/webscr');
	*/
	public static function setActionUrl($url)
	{
		$action_lower = strtolower($url);
		if (!in_array($action_lower, self::$allowed_action_urls))
		{
			if (self::$error_reporting == true) self::trigger_error(__CLASS__." ".__LINE__.": Action URL not found.", E_USER_NOTICE);
			return false;
		}
		self::$action_url = $url;
	}

    /**
    * getActionUrl
	* Access	 : Public
	* Detail   	 : Returns the current url for the form to post to paypal, unless changed this will be applied to all buttons.
	* Sample 1	 : PaypalSimpleClass::getActionUrl();
	*/
	public static function getActionUrl()
	{
		return self::$action_url;
	}

    /**
    * setReturnUrl
	* Access	 : Public
	* @param1    : $url (string) - the url for return from checkout
	* Detail   	 : Sets the url for return from checkout, unless changed this will be applied to all buttons.
	* Sample 1	 : PaypalSimpleClass::setReturnUrl(http://www.yourdomain.com/thanks.php);
	*/
	public static function setReturnUrl($url)
	{
		if (!filter_var($url, FILTER_VALIDATE_URL) && !empty($url))
		{
			if (self::$error_reporting == true) self::trigger_error(__CLASS__." ".__LINE__.": Invalid URL passed for return.", E_USER_NOTICE);
			return false;
		}
		self::$item_return_url = $url;
	}

    /**
    * getReturnUrl
	* Access	 : Public
	* Detail   	 : Returns the curl for return from checkout, unless changed this will be applied to all buttons.
	* Sample 1	 : PaypalSimpleClass::getReturnUrl();
	*/
	public static function getReturnUrl()
	{
		return self::$item_return_url;
	}

  /**
    * setCancelUrl
	* Access	 : Public
	* @param1    : $url (string) - the url for cancel return from checkout
	* Detail   	 : Sets the url for cancelling from checkout, unless changed this will be applied to all buttons.
	* Sample 1	 : PaypalSimpleClass::setReturnUrl(http://www.yourdomain.com/thanks.php);
	*/
	public static function setCancelUrl($url)
	{
		if (!filter_var($url, FILTER_VALIDATE_URL) && !empty($url))
		{
			if (self::$error_reporting == true) self::trigger_error(__CLASS__." ".__LINE__.": Invalid URL passed for cancel return.", E_USER_NOTICE);
			return false;
		}
		self::$item_cancel_url = $url;
	}

    /**
    * getCancelUrl
	* Access	 : Public
	* Detail   	 : Returns the url for cancelling from checkout, unless changed this will be applied to all buttons.
	* Sample 1	 : PaypalSimpleClass::getCancelUrl();
	*/
	public static function getCancelUrl()
	{
		return self::$item_cancel_url;
	}

    /**
    * getLastItem
	* Access	 : Public
	* Detail   	 : Returns the last button html that was output.
	* Sample 1	 : PaypalSimpleClass::getLastItem();
	*/
	public static function getLastItem()
	{
		if (empty(self::$last_item)) return false;
		return self::$last_item;
	}

    /**
    * setStyle
	* Access	 : Public
	* @param1    : $style (string) - the name of a style stored in your paypal account, unless changed this will be applied to all buttons.
	* Detail   	 : Sets the name of a style stored in your paypal account, unless changed this will be applied to all buttons.
	* Sample 1	 : PaypalSimpleClass::setStyle('paypal');
	* Sample 2	 : PaypalSimpleClass::setStyle('primary');
	* Sample 3	 : PaypalSimpleClass::setStyle('mystoredstyle');
	*/
	public static function setStyle($style)
	{
		self::$style_default = $style;
	}

    /**
    * setStyleDetails
	* Access	 : Public
	* @param1    : $cartimage (string) - The URL of the 150x50-pixel image displayed as your logo in the upper left corner of the PayPal checkout pages.
	* @param2    : $bordercolour (string) - The HTML hex code for your principal identifying color.
	* @param3    : $headerimage (string) - The image at the top left of the checkout page. The image's maximum size is 750 pixels wide by 90 pixels high.
	* @param4    : $headercolour (string) - The background color for the header of the checkout page. Valid value is case-insensitive six-character, HTML hexadecimal color code in ASCII.
	* @param5    : $headerborder (string) - The border color around the header of the checkout page.
	* @param6    : $cartlogo (string) - A URL to your logo image. Use a valid graphics format, such as .gif, .jpg, or .png. Limit the image to 190 pixels wide by 60 pixels high. 
	* @param7    : $payflow (string) - The background color for the checkout page below the header. Valid value is case-insensitive six-character, HTML hexadecimal color code in ASCII.
	* Detail   	 : Set 1 or more style attributes for custom checkout apperance, unless changed this will be applied to all buttons.
	* Sample 1	 : PaypalSimpleClass::setStyleDetails('http://www.yourdomain.com/cartimage.png','FFCC00');
	* Sample 2	 : PaypalSimpleClass::setStyleDetails('','FFCC00');
	*/
	public static function setStyleDetails($cartimage = '', $bordercolour = '', $headerimage = '', $headercolour = '', $headerborder = '', $cartlogo = '', $payflow = '')
	{
		if (!empty($cartimage))
		{
			$image_data = getimagesize($cartimage);
			if ($image_data !== false)
			{
				if ($image_data[0] > 150 || $image_data[1] > 50) if (self::$error_reporting == true) self::trigger_error(__CLASS__." ".__LINE__.": Cart image size.", E_USER_NOTICE);
			}
			self::$style_cart_image = $cartimage;
		}
		if (!empty($bordercolour)) self::$style_cart_border_color = $bordercolour;
		if (!empty($headerimage))
		{
			$image_data = getimagesize($headerimage);
			if (image_data !== false)
			{
				if ($image_data[0] > 750 || $image_data[1] > 90) if (self::$error_reporting == true) self::trigger_error(__CLASS__." ".__LINE__.": Header image size.", E_USER_NOTICE);
			}
			self::$style_header_image = $headerimage;
		}
		if (!empty($headercolour)) self::$style_headerback_color = $headercolour;
		if (!empty($headerborder)) self::$style_headerborder_color = $headerborder;
		if (!empty($cartlogo))
		{
			$image_data = getimagesize($cartlogo);
			if (image_data !== false)
			{
				if ($image_data[0] > 190 || $image_data[1] > 60) if (self::$error_reporting == true) self::trigger_error(__CLASS__." ".__LINE__.": Cart logo image size.", E_USER_NOTICE);
			}
			self::$cart_logo = $cartlogo;
		}
		if (!empty($payflow)) self::$style_payflow_color = $payflow;
	}

    /**
    * setCartImage
	* Access	 : Public
	* @param1    : $cartimage (string) - The URL of the 150x50-pixel image displayed as your logo in the upper left corner of the PayPal checkout pages.
	* Detail   	 : Set the URL of the 150x50-pixel image displayed as your logo in the upper left corner of the PayPal checkout pages, unless changed this will be applied to all buttons.
	* Sample 1	 : PaypalSimpleClass::setCartImage('http://www.yourdomain.com/cartimage.png');
	*/
	public static function setCartImage($image)
	{
		self::$style_cart_image = $image;
	}

    /**
    * setBorder
	* Access	 : Public
	* @param1    : $colour (string) - The HTML hex code for your principal identifying color.
	* Detail   	 : Set the HTML hex code for your principal identifying color, unless changed this will be applied to all buttons.
	* Sample 1	 : PaypalSimpleClass::setCartImage('FFCC00');
	*/
	public static function setBorder($colour)
	{
		self::$style_cart_border_color = $colour;
	}

    /**
    * setHeaderImage
	* Access	 : Public
	* @param3    : $headerimage (string) - The image at the top left of the checkout page. The image's maximum size is 750 pixels wide by 90 pixels high.
	* Detail   	 : Set the image at the top left of the checkout page. The image's maximum size is 750 pixels wide by 90 pixels high, unless changed this will be applied to all buttons.
	* Sample 1	 : PaypalSimpleClass::setHeaderImage('http://www.yourdomain.com/cartimage.png');
	*/
	public static function setHeaderImage($image)
	{
		self::$style_header_image = $image;
	}

    /**
    * setHeaderColour
	* Access	 : Public
	* @param1    : $headercolour (string) - The background color for the header of the checkout page. Valid value is case-insensitive six-character, HTML hexadecimal color code in ASCII.
	* Detail   	 : Set the background color for the header of the checkout page, unless changed this will be applied to all buttons.
	* Sample 1	 : PaypalSimpleClass::setHeaderColour('FFCC00');
	*/
	public static function setHeaderColour($colour)
	{
		self::$style_headerback_color = $colour;
	}

    /**
    * setHeaderBorderColour
	* Access	 : Public
	* @param1    : $headerborder (string) - The border color around the header of the checkout page.
	* Detail   	 : Set the border color around the header of the checkout page or more style attributes for custom checkout apperance, unless changed this will be applied to all buttons.
	* Sample 1	 : PaypalSimpleClass::setHeaderBorderColour('FFCC00');
	*/
	public static function setHeaderBorderColour($colour)
	{
		self::$style_headerborder_color = $colour;
	}

    /**
    * setCartLogo
	* Access	 : Public
	* @param1    : $cartlogo (string) - A URL to your logo image. Use a valid graphics format, such as .gif, .jpg, or .png. Limit the image to 190 pixels wide by 60 pixels high. 
	* Detail   	 : Set a URL to your logo image. Use a valid graphics format, such as .gif, .jpg, or .png. Limit the image to 190 pixels wide by 60 pixels high, unless changed this will be applied to all buttons.
	* Sample 1	 : PaypalSimpleClass::setCartLogo('http://www.yourdomain.com/cartimage.png');
	*/
	public static function setCartLogo($url)
	{
		self::$cart_logo = $url;
	}

    /**
    * setPayflowColour
	* Access	 : Public
	* @param1    : $payflow (string) - The background color for the checkout page below the header. Valid value is case-insensitive six-character, HTML hexadecimal color code in ASCII.
	* Detail   	 : Set the background color for the checkout page below the header. Valid value is case-insensitive six-character, HTML hexadecimal color code in ASCII, unless changed this will be applied to all buttons.
	* Sample 1	 : PaypalSimpleClass::setPayflowColour('FFCC00');
	*/
	public static function setPayflowColour($colour)
	{
		self::$style_payflow_color = $colour;
	}

    /**
    * setErrorReporting
	* Access	 : Public
	* @param1    : $mode (boolean) - Turn on or off error reporting for debugging.
	* Detail   	 : Turn on or off error reporting for debugging, unless changed this will be applied to all buttons.
	* Sample 1	 : PaypalSimpleClass::setErrorReporting(true);
	*/
	public static function setErrorReporting($mode)
	{
		if ($mode !== true && $mode !== false) return false;
		self::$error_reporting = $mode;
		return true;
	}

    /**
    * trigger_error
	* Access	 : Private
	* @param1    : $message (string) - The error message to be sent.
	* @param2    : $type (constant) - The error type to be sent.
	* Detail   	 : Check error reporting mode and trigger error.
	* Sample 1	 : PaypalSimpleClass::trigger_error('Test Error',E_USER_NOTICE);
	*/
	final private static function trigger_error($message, $type)
	{
		
		if (class_exists('ErrorHandler'))
		{
			ErrorHandler::error_create($message, $type);
		} else {
			trigger_error($message, $type);
		}
	}

    /**
    * get_ver
	* Access	 : Public
	* Detail   	 : Returns the current script version.
	* Sample 1	 : PaypalSimpleClass::get_ver();
	*/
	final public static function get_ver()
	{
		return self::$verno;
	}

    /**
    * get_ver
	* Access	 : Public
	* Detail   	 : Returns "Not Allowed".
	*/
	final public function __toString()
	{
		return "Not Allowed";
	}

    /**
    * get_ver
	* Access	 : Public
	* Detail   	 : Triggers an error as clone object is not allowed with singleton factory method.
	*/

	final public function __clone()
	{
		self::trigger_error(__CLASS__." ".__LINE__.": Clone is not allowed.", E_USER_ERROR);
	}
}


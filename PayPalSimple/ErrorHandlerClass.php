<?php
/**
 * Create the error handler class for PayPalSimpleClass.
 */

class ErrorHandler {
  public function error_create($message, $type) {
    dpm($message);
  }
}

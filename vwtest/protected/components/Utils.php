<?php

register_shutdown_function( "shutdown" );

 function shutdown()
 {
   $error = error_get_last();
   if ($error['type'] === E_ERROR || $error['type'] === E_PARSE || $error['type'] === E_CORE_ERROR  || $error['type'] === E_COMPILE_ERROR) {
      ob_end_clean();
      NackResponseHandler::HandleRequest(null, 500, "Fatal Error!, " . $error['type']);
   }
 }

function dashesToCamelCase($string, $capitalizeFirstCharacter = false)
{

    $str = str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));

    if (!$capitalizeFirstCharacter) {
        $str[0] = strtolower($str[0]);
    }

    return $str;
}

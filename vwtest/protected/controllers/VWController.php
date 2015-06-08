<?php

/**
 * Main and only Controller used by the API, entirely coded for the Test (a-k-a not code used besides de MVC)
 */
class VWController extends Controller
{

  public function actionIndex()
  {
    return false;
  }

  /**
   * Unique access point for the entire API, Expects a POST XML
   * @return [type] [description]
   */
  public function actionAPI()
  {
    ob_start();

    // this could be done with the FWK, but done here for the purpose of this test
    if ($_SERVER['REQUEST_METHOD'] != "POST") {
      NackResponseHandler::HandleRequest(null, 405, "Method Not Allowed");
    }

    try {

      // the 5 lines below find the appropiate class for the request whitout reading the XML as a XMLObject
      // NOTE: there is a proper XML reading a XSD validation later
      // This is done this way for compatibility reasons, to allow the specific methods for ping & Reserve to work (since the specific method were made first)
      $rawData = trim((file_get_contents('php://input')));
      $begin = (strpos(($rawData), "type", true) + 5);
      $upTo = (strpos(($rawData), "/type", true) - 1);
      $rawRequestName = substr(($rawData), $begin, $upTo - $begin);
      $className = trim(dashesToCamelCase($rawRequestName, true) . "Handler");

      if (@class_exists($className, true) && get_parent_class($className) == "AbstractXMLHandler") {
        $result = $className::HandleRequest($rawData);  // read POST raw data
        header('Content-type: application/xml; charset=utf-8');     // set chatset and content-type
        echo $result->saveXML(); // print XML to the response, along with a 200.
      } else {
        NackResponseHandler::HandleRequest(null, 405, "Method Not Allowed");
      }

    } catch (VWException $e) {
      $this->sendResponse(CJSON::decode($e->getMessage()));   // catch the exception, print the result xml
    } catch (Exception $e) {
      $this->sendResponse(array(501, $e->getMessage())); // catch the exception, print the result xml
    }

  }

  /**
   * Action made for the API ping method, Expects a POST XML
   * @return [type] [description]
   */
  public function actionPing()
  {
    ob_start();

    // this could be done with the FWK, but done here for the purpose of this test
    if ($_SERVER['REQUEST_METHOD'] != "POST") {
      NackResponseHandler::HandleRequest(null, 405, "Method Not Allowed");
    }

    try {

      $result = PingRequestHandler::HandleRequest(trim((file_get_contents('php://input'))));  // read POST raw data
      header('Content-type: application/xml; charset=utf-8');     // set chatset and content-type
      echo $result->saveXML(); // print XML to the response, along with a 200.

    } catch (VWException $e) {
      $this->sendResponse(CJSON::decode($e->getMessage()));   // catch the exception, print the result xml
    } catch (Exception $e) {
      $this->sendResponse(array(501, $e->getMessage())); // catch the exception, print the result xml
    }

  }

  /**
   * Action made for the API reverse method, Expects a POST XML
   * @return [type] [description]
   */
  public function actionReverse()
  {
    ob_start();

    // this could be done with the FWK, but done here for the purpose of this test
    if ($_SERVER['REQUEST_METHOD'] != "POST") {
      NackResponseHandler::HandleRequest(null, 405, "Method Not Allowed");
    }

    try {

      $result = ReverseRequestHandler::HandleRequest(trim((file_get_contents('php://input')))); // read POST raw data
      header('Content-type: application/xml; charset=utf-8');     // set chatset and content-type
      echo $result->saveXML(); // print XML to the response, along with a 200.

    } catch (VWException $e) {
      $this->sendResponse(CJSON::decode($e->getMessage())); // catch the exception, print the result xml
    } catch (Exception $e) {
      $this->sendResponse(array(501, "Unknown server error")); // catch the exception, print the result xml
    }
  }

  /**
    * Sends a default response in case of client Errors or unexpected server errors
    * VERY BASIC (FOR THE SAKE OF TIME) error-output system
    * @param  array  $response An array containing these parameters
    * * embeded-param integer type     The Http code to be returned, default to Error
      * embeded-param integer code     The Http code to be returned, default to 500
      * embeded-param string response Http desc to be shown along the http status, default to "Internal Server Error"
    * @return [type]            Return the given array
  */
  private function sendResponse(array $response)
  {
    header('Content-type: application/xml; charset=utf-8');     // set chatset and content-type

    $response[0] = ($response[0]) ? $response[0] : "Error";     // force the defualt values for the 3 params
    $response[1] = ($response[1]) ? $response[1] : 500;
    $response[2] = ($response[2]) ? $response[2] : "Internal Server Error";

    http_response_code($response[1]);                           // set the appropiate http_status

    $aDomDocument = new DOMDocument('1.0', "UTF-8");            // hydrate the response

    // append the root node
    $rootNode = $aDomDocument->createElement("VWTestDefaultResponse");
    $rootNode = $aDomDocument->appendChild($rootNode);

    // append the "STATUS", just as "Error"
    $statusNode = $aDomDocument->createElement("Status", $response[0]);
    $insertedNode = $rootNode->appendChild($statusNode);

    // append the HTTP status, such as 401
    $HTTPStatusNode = $aDomDocument->createElement("HTTPStatusCode", $response[1]);
    $insertedNode = $rootNode->appendChild($HTTPStatusNode);

    // append the response text
    $descNode = $aDomDocument->createElement("Response", $response[2]);
    $insertedNode = $rootNode->appendChild($descNode);

    echo $aDomDocument->saveXML();
    die();
  }

}

<?php

/**
 * Main and only Controller used by the API, entirely coded for the Test (a-k-a not code used besides de MVC)
 */
class VWController extends Controller
{

  /**
   * Action made for the API ping method, Expects a POST XML
   * @return [type] [description]
   */
  public function actionPing()
  {
    ob_start();

    // this could be done with the FWK, but done here for the purpose of this test
    if ($_SERVER['REQUEST_METHOD'] != "POST") {
      $this->sendResponse(array("Method Not Allowed", 405, "Method Not Allowed"));
    }
    try {

      $result = PingRequestHandler::HandleRequest(trim((file_get_contents('php://input'))));  // read POST raw data

      if (is_array($result) && sizeof($result) == 3) {  // just check for an expected array of error

        $this->sendResponse($result); // return error, with info provided from within the app core
      } else {
        header('Content-type: application/xml; charset=utf-8');     // set chatset and content-type
        echo $result->saveXML(); // print XML to the response, along with a 200.
      }
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
      $this->sendResponse(array("Method Not Allowed", 405, "Method Not Allowed"));
    }
    try {

      $result = ReverseRequestHandler::HandleRequest(trim((file_get_contents('php://input')))); // read POST raw data

      if (is_array($result) && sizeof($result) == 3) { // just check for an expected array of error

        $this->sendResponse($result); // return error, with info provided from within the app core
      } else {
        header('Content-type: application/xml; charset=utf-8');     // set chatset and content-type
        echo $result->saveXML(); // print XML to the response, along with a 200.
      }
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

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

    } catch (Exception $e) {
      NackResponseHandler::HandleRequest(null, 405, $e->getMessage());
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

    } catch (Exception $e) {
      NackResponseHandler::HandleRequest(null, 405, $e->getMessage());
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

    } catch (Exception $e) {
      NackResponseHandler::HandleRequest(null, 405, $e->getMessage());
    }
  }
}

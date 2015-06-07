<?php

class VWController extends Controller
{
  /**
   * This is the default 'index' action that is invoked
   * when an action is not explicitly requested by users.
   */
  public function actionIndex()
  {

  }

  public function actionPing()
  {
    try {

      $result = PingRequestHandler::HandleRequest(trim((file_get_contents('php://input'))));

      if (is_array($result) && sizeof($result) == 3) {

        $this->sendResponse($result);
      } else {
        echo "<pre>" . htmlentities($result->saveXML()) . "</pre>";
      }
    } catch (VWException $e) {
      $this->sendResponse(CJSON::decode($e->getMessage()));
    } catch (Exception $e) {
      $this->sendResponse(array(501, "Unknown server error"));
    }

  }

  public function actionReverse()
  {
    try {

      $result = ReverseRequestHandler::HandleRequest(trim((file_get_contents('php://input'))));

      if (is_array($result) && sizeof($result) == 3) {

        $this->sendResponse($result);
      } else {
        echo "<pre>" . htmlentities($result->saveXML()) . "</pre>";
      }
    } catch (VWException $e) {
      $this->sendResponse(CJSON::decode($e->getMessage()));
    } catch (Exception $e) {
      $this->sendResponse(array(501, "Unknown server error"));
    }
  }

  /**
    * Sends a default response in case of Client Errors or unexpected server errors
    * @param  array  $response An array containing these parameters
      * embeded-param integer $code     The Http code to be returned, default to 500
      * embeded-param string $response Http desc to be shown along the http status, default to "Internal Server Errors"
    * @return [type]            Return the given array
  */
  private function sendResponse(array $response)
  {
    header('Content-type: application/xml; charset=utf-8');

    $aDomDocument = new DOMDocument('1.0', "UTF-8");

    // append the root node
    $rootNode = $aDomDocument->createElement("VWTestDefaultResponse");
    $rootNode = $aDomDocument->appendChild($rootNode);

    // append the HTTP code
    $statusNode = $aDomDocument->createElement("Status", $response[0]);
    $insertedNode = $rootNode->appendChild($statusNode);

    // append the HTTP code
    $HTTPStatusNode = $aDomDocument->createElement("HTTPStatusCode", $response[1]);
    $insertedNode = $rootNode->appendChild($HTTPStatusNode);

    // append the response text
    $descNode = $aDomDocument->createElement("Response", $response[2]);
    $insertedNode = $rootNode->appendChild($descNode);

    //die(CJSON::encode(array("result" => $response)));
    echo $aDomDocument->saveXML();
    die();
  }

}

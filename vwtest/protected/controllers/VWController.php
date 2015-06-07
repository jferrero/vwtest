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

  // /**
  //  * This is the action to handle external exceptions.
  //  */
  // public function actionError()
  // {
  //   if ($error=Yii::app()->errorHandler->error) {
  //     if(Yii::app()->request->isAjaxRequest)
  //       echo $error['message'];
  //     else
  //       $this->render('error', $error);
  //   }
  // }

  public function actionTest()
  {

    //$proveedorKey = Util::array_key_exists_nc('proveedor', $_POST);

    if (isset($_POST))
      var_dump($_POST);
    else
      echo "false";

  }

  public function actionPing()
  {
    // old request
    //$aPingDocument = $this->inputCheckNParsing();  // check data

    // new request
    $result = PingRequestHandler::HandleRequest(trim((file_get_contents('php://input'))));

    if (is_array($result) && sizeof($result) == 3) {

      $this->sendResponse($result);
    } else {
      echo "<pre>" . htmlentities($result->saveXML()) . "</pre>";
    }

  }

//---------------------
//
//

  private function inputCheckNParsing($xsdFilename = '')
  {
      $dataPOST = trim((file_get_contents('php://input'))); // load raw data in xml
      $aDomDocument = new DOMDocument();                    // create the DOM XML Element

      // ----- this is where the XML is validated first as as xml and then agains it's own xsd
      set_error_handler(array($this, 'handleXmlWrongFormat'));  // set a specific error handler for the loadXML function, to have control of the error
      // $aDomDocument = DOMDocument::loadXML($dataPOST, LIBXML_PARSEHUGE);    // load the xml
      $aDomDocument->loadXML($dataPOST, LIBXML_PARSEHUGE);    // load the xml

      $validation = 1;
      if ($xsdFilename) {
        $validation = $aDomDocument->schemaValidate ("protected/data/xsd/" . $xsdFilename);
      }
      restore_error_handler();  // return error handler no normal

      if ($validation) {
        return $aDomDocument;
      } else {
        $this->sendResponse(400, "Bad Request, not recognizable xml format");
      }
      // once assured it's an XML I should check the loaded XML against it's XSD
      return $aDomDocument;
  }

  public function handleXmlWrongFormat($errno, $errstr, $errfile, $errline)
  {
    //echo $errstr . "--" . $errfile;die;
    if ($errno==E_WARNING && (substr_count($errstr,"DOMDocument::loadXML()")>0)) {
        $this->sendResponse(400, "Bad Request, not a valid or recognizable xml format");
    } else {
      if ($errno==E_WARNING && (substr_count($errstr,"DOMDocument::schemaValidate(): Element")>0)) {
        $this->sendResponse(400, "Bad Request, this xml is not supported, please check the proper xsd format, request rejected");
      } else {
        if ($errno==E_WARNING) {
          return false;
        } else {
          //$this->sendResponse(400, $errstr . "--" . $errline);
          $this->sendResponse(400, $errstr . "--" . $errline);
        }
      }
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

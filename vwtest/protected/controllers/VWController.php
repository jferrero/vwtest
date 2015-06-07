<?php

class VWController extends Controller
{
  /**
   * Declares class-based actions.
   */
  public function actions()
  {
  }

  /**
   * This is the default 'index' action that is invoked
   * when an action is not explicitly requested by users.
   */
  public function actionIndex()
  {
    echo "index";
  }

  /**
   * This is the action to handle external exceptions.
   */
  public function actionError()
  {
    if ($error=Yii::app()->errorHandler->error) {
      if(Yii::app()->request->isAjaxRequest)
        echo $error['message'];
      else
        $this->render('error', $error);
    }
  }

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
    $aPingDocument = $this->inputCheckNParsing();  // check data

  }

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
   * @param  integer $code     The Http code to be returned, default to 500
   * @param  string  $response Http descrpition to be shown along the http status, default to "Internal Server Error"s
   * @return [type]            Return previous 2 parameters in XML 1.0 format
   */
  private function sendResponse($code = 500, $response = 'Internal Server Error')
  {
    header('Content-type: application/xml; charset=utf-8');

    $aDomDocument = new DOMDocument('1.0', "UTF-8");

    // append the root node
    $rootNode = $aDomDocument->createElement("VWTestDefaultResponse");
    $rootNode = $aDomDocument->appendChild($rootNode);

    // append the HTTP code
    $node = $aDomDocument->createElement("HTTPStatusCode", $code);
    $insertedNode = $rootNode->appendChild($node);

    // append the response text
    $node2 = $aDomDocument->createElement("Response", $response);
    $insertedNode = $rootNode->appendChild($node2);

    //die(CJSON::encode(array("result" => $response)));
    echo $aDomDocument->saveXML();
    die();
  }

}

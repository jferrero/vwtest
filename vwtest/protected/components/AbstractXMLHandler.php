<?php

abstract class AbstractXMLHandler
{

  protected $inputdata;
  protected $requestXmlObject;
  protected $responseXMLObject;

  protected $xsdRequestFilepath = 'protected/data/xsd/';
  protected $xsdRequestFilename = '';

  protected $xsdResponseFilepath = 'protected/data/xsd/';
  protected $xsdResponseFilename = '';

  protected $xmlResponseSampleFilepath = 'protected/data/samples/';
  protected $XmlResponseFilename = '';

  public static function HandleRequest($inputParams)
  {
  }

  protected function validateVsXsd()
  {
      // ----- this is where the XML is validated first as a xml and then against it's own xsd
      set_error_handler(array($this, 'CustomHandleErrors'));  // set a specific error handler for the loadXML function, to have control of the error
      $this->requestXmlObject = $aDomDocument = new DOMDocument;
      $aDomDocument->loadXML($this->inputdata, LIBXML_PARSEHUGE);    // load the xml, allowing big XMLs as well

      $validation = 1;
      if ($this->xsdRequestFilepath && $this->xsdRequestFilename) {
        // ToDo validation of directory
        //print_r("<pre>" .htmlentities($aDomDocument->saveXML()) . "</pre>");die;
        //print_r("<pre>" .$this->xsdRequestFilepath . $this->xsdRequestFilename. "</pre>");die;
        $validation = $aDomDocument->schemaValidate ($this->xsdRequestFilepath . $this->xsdRequestFilename);

      } else {
        return array("Error", "500", "Internal Xsd Data not properly defined, Contact Admin");
      }
      restore_error_handler();  // return error handler to default

      if ($validation) {
        return $aDomDocument;
      } else {
        return array("Error", 400, "Bad Request, not recognizable xml format");
      }
  }

  protected function handleHeader()
  {
    $this->responseXMLObject = $this->createDOMFromFile($this->xmlResponseSampleFilepath, $this->xmlResponseSampleFilename, $this->xsdResponseFilepath, $this->xmlResponseSampleFilename);

    $documentElement = $this->responseXMLObject->documentElement;
    $requestDocumentElement = $this->requestXmlObject->documentElement;

    if (($theTypeNode = $this->findFirstElementByTagName($documentElement, "type"))   == false) {
      return array("Error", "500", "Unknown Server Error, Errors while parsing the XML response object-1");
    }

    if (($theSenderNode = $this->findFirstElementByTagName($documentElement, "sender"))   == false) {
      return array("Error", "500", "Unknown Server Error, Errors while parsing the XML response object-2");
    }

    if (($theRecipientNode = $this->findFirstElementByTagName($documentElement, "recipient"))   == false) {
      return array("Error", "500", "Unknown Server Error, Errors while parsing the XML response object-3");
    }

    if (($theReferenceNode = $this->findFirstElementByTagName($documentElement, "reference"))   == false) {
      return array("Error", "500", "Unknown Server Error, Errors while parsing the XML response object-4");
    }

    if (($theTimestampNode = $this->findFirstElementByTagName($documentElement, "timestamp"))   == false) {
      return array("Error", "500", "Unknown Server Error, Errors while parsing the XML response object-5");
    }

    if (($theOldSenderNode = $this->findFirstElementByTagName($requestDocumentElement, "sender"))   == false) {
      return array("Error", "500", "Unknown Server Error, Errors while parsing the XML response object-6");
    }

    if (($theReferenceNode = $this->findFirstElementByTagName($requestDocumentElement, "reference"))   == false) {
      return array("Error", "500", "Unknown Server Error, Errors while parsing the XML response object-7");
    }
    $nowDate = new DateTime("now");
    $miliseconds = substr(microtime(),2,3);
    //$miliseconds = substr($nowDate->format("u"), 0, 3); // this should worked, but it didn't

    $theTypeNode->nodeValue = "ping_response";
    $theSenderNode->nodeValue = "DEMO";
    $theRecipientNode->nodeValue = $theOldSenderNode->nodeValue;
    $theReferenceNode->nodeValue = $theOldSenderNode->reference;
    $theTimestampNode->nodeValue = $nowDate->format("Y-m-d\TH:i:s." . $miliseconds . "P");

    return $this->responseXMLObject;
  }

  // been done public in order to be able to use set_error_handler, otherwise will be protected
  public function CustomHandleErrors($errno, $errstr, $errfile, $errline)
  {

    //echo $errstr . "--" . $errfile;die;
    if ($errno==E_WARNING && (substr_count($errstr,"DOMDocument::loadXML()")>0)) {
        throw new VWException(CJSON::encode(array("Error", 400, "Bad Request, not a valid or recognizable xml format",1)));
    } else {
      if ($errno==E_WARNING && (substr_count($errstr,"DOMDocument::schemaValidate(): Element")>0)) {
        throw new VWException(CJSON::encode(array("Error", 400, "Bad Request, the xml is not supported, please check the proper xsd format, request rejected",1)));
      } else {
        // EXCLUDED FROM THE TEST
        // Here some kind of Production/pre-production/development/local flag hsould be posted in order to print or no certain error, warnings and notices
        // normaly will not br printed out on PRO and be done better
        if ($errno==E_ERROR) {
          trigger_error ($errstr . "-- " . $errfile . " -- " . $errline . " , [$errno = E_USER_ERROR ] ");
        } else {
          trigger_error ($errstr . "-- " . $errfile . " -- " . $errline . " , [$errno = E_USER_ERROR ] ");  // NOTICES SHOULD BE ALSO TREATED IF NOT treated on php.ini or config at the FWK
        }
      }
    }
  }

  /**
   * Simple function to create a DomDocument from a XML example file, will also validate against the proper XSD JIC
   * @param  [type] $path     [description]
   * @param  [type] $fileName [description]
   * @return [type]           [description]
   */
  protected function createDOMFromFile($examplePath, $examplefileName, $xsdPath, $xsdFileName)
  {
    if (file_exists($examplePath . $examplefileName)) {
      $aResponseXMLObject = new DOMDocument;
      $aResponseXMLObject->load($examplePath . $examplefileName, LIBXML_PARSEHUGE);    // load the xml, allowing big XMLs as well

      return $aResponseXMLObject;
    } else {
      return array("Error", "500", "Oops, There is a internal error, cannot make an appropiate response");
    }
  }

  /**
   * Small wrapper to handle the search for a single (or at least the first) element by TagName within an XML, given the complexity of the exercise and to simplify just the first ocurrence will be returned
   * @return [type] [description]
   */
  protected function findFirstElementByTagName($aDomDocument, $tagName)
  {

    $nodes = $aDomDocument->getElementsByTagName($tagName);
    //print_r($nodes);die;
    $resultNode = $nodes->item(0);

     //print_r($resultNode);die;
    //   print_r($resultNode->hasAttribute("tagName"));die;
      //print_r($resultNode->getAttributeNode('type'));die;
    if ($resultNode instanceof DOMElement) {
      return $resultNode;
    } else {
      return false;
    }
  }

}

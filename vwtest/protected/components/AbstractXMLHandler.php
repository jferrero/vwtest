<?php

/**
 * Abstract class for handling all XML Handlers of the API, basically it's read, parse, compare against the proper XSD and
 * prepare the response headers, all custom behaviour should be included in the handler specific class
 */
abstract class AbstractXMLHandler
{

  protected $inputdata;              // RAW input data, as it comes
  protected $requestXmlObject;       // requestXMLObject, once properly loaded
  protected $responseXMLObject;      // responseXMLObject, loaded from an example and then modified until output is made

  protected $xsdRequestFilepath = 'protected/data/xsd/';  // path (not including the filename) of the XSD request files
  protected $xsdRequestFilename = null;                   // xsd request fileName (not hydrated, since this is an abstract class)

  protected $xsdResponseFilepath = 'protected/data/xsd/'; // path (not including the filename) of the XSD response files
  protected $xsdResponseFilename = null;                  // xsd response fileName (not hydrated, since this is an abstract class)

  protected $xmlResponseSampleFilepath = 'protected/data/samples/'; // path (not including the filename) of all sample files
  protected $XmlResponseFilename = null;                  // xsd response fileName (not hydrated, since this is an abstract class)

  public static $classIsPublished = true;                 // sets if the class can be called from the outside (to allow auto calling and allow generalization at the same time)

  /**
   * Typical constructor, made in order to be able to make one-line request at the constructor.
   * @param [type] $inputdata the raw POST input
   */
  public function __construct($inputdata)
  {
    $this->inputdata = $inputdata;

    return $this;
  }

  /**
   * This function load the XML and if possible compare it to the proper XSD file
   * @return the succesfully loaded and XSD-checked XML.
   */
  protected function validateVsXsd()
  {
      // ----- this is where the XML is validated first as a xml and then against it's own xsd
      set_error_handler(array($this, 'CustomHandleErrors'));  // set a specific error handler for the loadXML function, to have control of the error
      $this->requestXmlObject = $aDomDocument = new DOMDocument;
      $aDomDocument->loadXML($this->inputdata, LIBXML_PARSEHUGE);    // load the xml, allowing big XMLs as well

      $validation = 1;
      if ($this->xsdRequestFilepath && $this->xsdRequestFilename) {
        if (file_exists($this->xsdRequestFilepath . $this->xsdRequestFilename)) {
          $validation = $aDomDocument->schemaValidate ($this->xsdRequestFilepath . $this->xsdRequestFilename);
        } else {
          NackResponseHandler::HandleRequest($this->requestXmlObject, 500, "Internal Xsd Data not available, Contact Admin");
        }

      } else {
        NackResponseHandler::HandleRequest($this->requestXmlObject, 500, "Internal Xsd Data not properly defined, Contact Admin");
      }
      restore_error_handler();  // return error handler to default

      if ($validation) {
        return $aDomDocument;
      } else {
        NackResponseHandler::HandleRequest($this->requestXmlObject, 400, "Bad Request, not recognizable xml format");
      }
  }

  /**
   * Abstract method for handling the response, all the header construction/update it's abstract
   * @return [type] [description]
   */
  protected function handleResponse()
  {
    return $this->handleHeader();
  }

  /**
   * Once properly loaded and checked, the response XML must be built, this method build the header
   * @return [type] [description]
   */
  protected function handleHeader()
  {
    // load the reponseXML object from the examples
    $this->responseXMLObject = $this->createDOMFromFile($this->xmlResponseSampleFilepath, $this->xmlResponseSampleFilename);

    $documentElement = $this->responseXMLObject->documentElement;
    $requestDocumentElement = $this->requestXmlObject->documentElement;

    // access the diferent elements of the Response-XML header, to be changed later
    if (($theTypeNode = $this->findFirstElementByTagName($documentElement, "type"))   == false) {
      NackResponseHandler::HandleRequest($this->requestXmlObject, 500, "Error while parsing the XML response object-6");
    }

    if (($theSenderNode = $this->findFirstElementByTagName($documentElement, "sender"))   == false) {
      NackResponseHandler::HandleRequest($this->requestXmlObject, 500, "Error while parsing the XML response object-2");
    }

    if (($theRecipientNode = $this->findFirstElementByTagName($documentElement, "recipient"))   == false) {
      NackResponseHandler::HandleRequest($this->requestXmlObject, 500, "Error while parsing the XML response object-3");
    }

    if (($theReferenceNode = $this->findFirstElementByTagName($documentElement, "reference"))   == false) {
      NackResponseHandler::HandleRequest($this->requestXmlObject, 500, "Error while parsing the XML response object-4");
    }

    if (($theTimestampNode = $this->findFirstElementByTagName($documentElement, "timestamp"))   == false) {
      NackResponseHandler::HandleRequest($this->requestXmlObject, 500, "Error while parsing the XML response object-5");
    }

    if ($requestDocumentElement) {
      // access the diferent elements of the Request-XML header, to be used in the response Header
      if (($theOldSenderNode = $this->findFirstElementByTagName($this->requestXmlObject, "sender"))   == false) {
        NackResponseHandler::HandleRequest($this->requestXmlObject, 500, "Error while parsing the XML response object-6");
      }

      if (($theOldReferenceNode = $this->findFirstElementByTagName($this->requestXmlObject, "reference"))   == false) {
        NackResponseHandler::HandleRequest($this->requestXmlObject, 500, "Error while parsing the XML response object-7");
      }

      $theOldSenderNodeValue = $theOldSenderNode->nodeValue;
      $theOldReferenceNodeValue = $theOldReferenceNode->nodeValue;
    } else {
      $theOldSenderNodeValue = "Unknown";
      $theOldReferenceNodeValue = "Unknown";
    }

    $nowDate = new DateTime("now");
    $miliseconds = substr(microtime(),2,3);
    //$miliseconds = substr($nowDate->format("u"), 0, 3); // this should worked, but it didn't

    //$theTypeNode->nodeValue = "ping_response";
    $theSenderNode->nodeValue = "DEMO";
    $theRecipientNode->nodeValue = $theOldSenderNodeValue;
    $theReferenceNode->nodeValue = $theOldReferenceNodeValue;
    $theTimestampNode->nodeValue = $nowDate->format("Y-m-d\TH:i:s." . $miliseconds . "P");

    return $this->responseXMLObject;
  }

  /**
   * [CustomHandleErrors description]
   * NOTE: It has been made public in order to be able to use set_error_handler, otherwise should be protected
   * This method it's used since the XML library used does not allow to catch errors, normally I would select other library
   * but adds some more flavour to the php test.
   * @param [type] $errno   std error param
   * @param [type] $errstr  std error param
   * @param [type] $errfile std error param
   * @param [type] $errline std error param
   */
  public function CustomHandleErrors($errno, $errstr, $errfile, $errline)
  {

    //echo $errstr . "--" . $errfile;die;
    if ($errno==E_WARNING && (substr_count($errstr,"DOMDocument::loadXML()")>0)) {
        NackResponseHandler::HandleRequest($this->requestXmlObject, 400, "Bad Request, not a valid or recognizable xml format");
    } else {
      if ($errno==E_WARNING && (substr_count($errstr,"DOMDocument::schemaValidate(): Element")>0)) {
        NackResponseHandler::HandleRequest($this->requestXmlObject, 400, "Bad Request, the xml is not supported, please check the proper xsd format, request rejected");
      } else {
        // EXCLUDED FROM THE TEST
        // Here some kind of Production/pre-production/development/local flag hsould be posted in order to print or no certain error, warnings and notices
        // normaly will not be printed out on PRO and be done better
        if ($errno==E_ERROR) {
          trigger_error ($errstr . "-- " . $errfile . " -- " . $errline , E_USER_ERROR);
        } else {
          trigger_error ($errstr . "-- " . $errfile . " -- " . $errline , E_USER_ERROR);  // NOTICES SHOULD BE ALSO TREATED IF NOT treated on php.ini or config at the FWK
        }
      }
    }
  }

  /**
   * Simple function to create a DomDocument from a XML example file
   * @param  [type] $examplePath     [description]
   * @param  [type] $examplefileName [description]
   * @return [type]                  [description]
   */
  protected function createDOMFromFile($examplePath, $examplefileName)
  {
    if (file_exists($examplePath . $examplefileName)) {
      $aResponseXMLObject = new DOMDocument;
      $aResponseXMLObject->load($examplePath . $examplefileName, LIBXML_PARSEHUGE);    // load the xml, allowing big XMLs as well

      return $aResponseXMLObject;
    } else {
      NackResponseHandler::HandleRequest($this->requestXmlObject, 500, "Oops, Reponse XML Not found, cannot make an appropiate response");
    }
  }

  /**
   * Small wrapper to handle the search for a single (or at least the first) element by TagName within an XML, given the complexity of the exercise and to simplify just the first ocurrence will be returned
   * NOTE: Normally this function would be at a XML library
   * @return [type] [description]
   */
  protected function findFirstElementByTagName($aDomDocument, $tagName)
  {

    $nodes = $aDomDocument->getElementsByTagName($tagName);
    $resultNode = $nodes->item(0);

    if ($resultNode instanceof DOMElement) {
      return $resultNode;
    } else {
      return false;
    }
  }

}

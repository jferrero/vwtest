<?php

class PingRequestHandler extends AbstractXMLHandler
{

  protected $xsdRequestFilepath = 'protected/data/xsd/';
  protected $xsdRequestFilename = 'ping_request.xsd';

  protected $xsdResponseFilepath = 'protected/data/xsd/';
  protected $xsdResponseFilename = 'ping_response.xsd';

  protected $xmlResponseSampleFilepath = 'protected/data/samples/';
  protected $xmlResponseSampleFilename = 'ping_response.xml';

  public function __construct($inputdata)
  {
    $this->inputdata = $inputdata;

    return $this;
  }

  /**
   * Class Interface, creates a Object off this class and start the all method chain, returns an array-error or the XML to be returned if all it's sucessfull
   * @param [type] $input The text to be handled by the app core
   */
  public static function HandleRequest($input)
  {

    $aPingRequestHandler = new PingRequestHandler($input);                // create a PingRequest Object
    if (is_array($result = $aPingRequestHandler->validateVsXsd())) {      // check if it's an XML an validate against an xml

      return $result;                                                     // if it's an error, return it to the controller or to whoever it's calling
    } elseif (!($result instanceof DOMDocument)) {
      return array("Error", "500", "Unknown Server Error, XML could not be parsed");  // not suppose to happen, JIC anwser
    } else {

      // everything good to go
      return $aPingRequestHandler->handleResponse();                      // handle and return the reponse
    }

  }

  /**
   * Once ensured it's the proper XML, a reponse should be sended
   * @return [type] [description]
   */
  protected function handleResponse()
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
    $miliseconds = substr($nowDate->format("u"), 0, 3);

    $theTypeNode->nodeValue = "ping_response";
    $theSenderNode->nodeValue = "DEMO";
    $theRecipientNode->nodeValue = $theOldSenderNode->nodeValue;
    $theReferenceNode->nodeValue = $theOldSenderNode->reference;
    $theTimestampNode->nodeValue = $nowDate->format("Y-m-d\TH:i:s." . $miliseconds . "P");

    return $this->responseXMLObject;
  }

}

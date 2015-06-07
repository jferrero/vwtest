<?php

class ReverseRequestHandler extends AbstractXMLHandler
{

  protected $xsdRequestFilename = 'reverse_request.xsd';
  protected $xsdResponseFilename = 'reverse_response.xsd';
  protected $xmlResponseSampleFilename = 'reverse_response.xml';

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

    $aReverseRequestHandler = new ReverseRequestHandler($input);                // create a ReverseRequest Object
    if (is_array($result = $aReverseRequestHandler->validateVsXsd())) {      // check if it's an XML an validate against an xml

      return $result;                                                     // if it's an error, return it to the controller or to whoever it's calling
    } elseif (!($result instanceof DOMDocument)) {
      return array("Error", "500", "Unknown Server Error, XML could not be parsed");  // not suppose to happen, JIC anwser
    } else {

      // everything good to go
      return $aReverseRequestHandler->handleResponse();                      // handle and return the reponse
    }

  }

  /**
   * Once ensured it's the proper XML, a reponse should be sended
   * @return [type] [description]
   */
  protected function handleResponse()
  {
    parent::handleHeader();

    if (($theBodyNode = $this->findFirstElementByTagName($this->responseXMLObject->documentElement, "body"))   == false) {
      return array("Error", "500", "Unknown Server Error, Errors while parsing the XML response object-8");
    }

    if (($theOldBody = $this->findFirstElementByTagName($this->requestXmlObject->documentElement, "body"))   == false) {
      return array("Error", "500", "Unknown Server Error, Errors while parsing the XML response object-8");
    }

    $theBodyNode->nodeValue = strrev($theOldBody->nodeValue);

    return $this->responseXMLObject;
  }

}

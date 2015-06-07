<?php

/**
 * Specific class for handling the reverse requests
 */
class ReverseRequestHandler extends AbstractXMLHandler
{

  protected $xsdRequestFilename = 'reverse_request.xsd';          // filename of the proper request XSD
  protected $xsdResponseFilename = 'reverse_response.xsd';        // filename of the proper response XSD
  protected $xmlResponseSampleFilename = 'reverse_response.xml';  // filename of the example xml response

  /**
   * Class entry point, creates a Object off this class and start the all method chain, returns an array-error or the XML to be returned if all it's sucessfull
   * @param [type] $input The text to be handled by the app core
   */
  public static function HandleRequest($input)
  {

    $aReverseRequestHandler = new ReverseRequestHandler($input);                // create a ReverseRequest Object
    if (is_array($result = $aReverseRequestHandler->validateVsXsd())) {      // check if it's an XML an validate against an xml

      throw new VWException(CJSON::encode($result));
    } elseif (!($result instanceof DOMDocument)) {
      throw new VWException(CJSON::encode(array("Error", "500", "Unknown Server Error, XML could not be parsed")));
    } else {

      // check-up and good to go
      return $aReverseRequestHandler->handleResponse();                      // handle and return the reponse
    }

  }

  /**
   * Once ensured it's the proper XML, a reponse should be sended
   * @return [type] the response object
   */
  protected function handleResponse()
  {
    parent::handleResponse();   // made the standard changes to the response document

    if (($theBodyNode = $this->findFirstElementByTagName($this->responseXMLObject->documentElement, "body"))   == false) {
      throw new VWException(CJSON::encode(array("Error", "500", "Unknown Server Error, Errors while parsing the XML response object-R1")));
    }

    if (($theOldBody = $this->findFirstElementByTagName($this->requestXmlObject->documentElement, "body"))   == false) {
      throw new VWException(CJSON::encode(array("Error", "500", "Unknown Server Error, Errors while parsing the XML response object-R2")));
    }

    $theBodyNode->nodeValue = strrev($theOldBody->nodeValue); // THE reverse string line

    return $this->responseXMLObject;
  }

}

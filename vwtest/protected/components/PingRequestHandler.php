<?php

/**
 * Specific class for handling the ping requests
 */
class PingRequestHandler extends AbstractXMLHandler
{

  protected $xsdRequestFilename = 'ping_request.xsd';         // filename of the proper request XSD
  protected $xsdResponseFilename = 'ping_response.xsd';       // filename of the proper response XSD
  protected $xmlResponseSampleFilename = 'ping_response.xml'; // filename of the example xml response

  /**
   * Class entry point, creates a Object of this class and start all method's chain, returns the XML to be returned if all it's sucessfull
   * @param [type] $input The text to be handled by the app core, the post raw data
   */
  public static function HandleRequest($input)
  {
    $aPingRequestHandler = new PingRequestHandler($input);                // create a PingRequest Object
    if (!($result = $aPingRequestHandler->validateVsXsd()) instanceof DomDocument) {      // check if it's an XML an validate against an xml
      NackResponseHandler::HandleRequest(null, 500, "Unknown Server Error, Request could not be understood");
    } else {
      // check-up and good to go
      return $aPingRequestHandler->handleResponse();                      // handle and return the reponse
    }

  }

  /**
   * Once ensured it's the proper XML, a reponse should be sended
   * @return [type] the response object
   */
  protected function handleResponse()
  {
    parent::handleResponse();   // made the standard changes to the response document

    // copy the requestBody to the response Body
    if (($theBodyNode = $this->findFirstElementByTagName($this->responseXMLObject->documentElement, "body")) == false) {
      NackResponseHandler::HandleRequest($this->requestXmlObject, 500, "Unknown Server Error, Errors while parsing the XML response object-P1");
    }

    if (($theOldBody = $this->findFirstElementByTagName($this->requestXmlObject->documentElement, "body")) == false) {
      NackResponseHandler::HandleRequest($this->requestXmlObject, 500, "Unknown Server Error, Errors while parsing the XML response object-P2");
    }

    $theBodyNode->nodeValue = $theOldBody->nodeValue;   // copy the value (optional at the TEST PDF)

    return $this->responseXMLObject;
  }
}

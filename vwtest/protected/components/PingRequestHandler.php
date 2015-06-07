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
    if (is_array($result = $aPingRequestHandler->validateVsXsd())) {      // check if it's an XML an validate against an xml

      throw new VWException(CJSON::encode($result));
    } elseif (!($result instanceof DOMDocument)) {
      throw new VWException(CJSON::encode(array("Error", "500", "Unknown Server Error, XML could not be parsed")));
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

    if (($theBodyNode = $this->findFirstElementByTagName($this->responseXMLObject->documentElement, "body")) == false) {
      throw new VWException(CJSON::encode(array("Error", "500", "Unknown Server Error, Errors while parsing the XML response object-P1")));
    }

    if (($theOldBody = $this->findFirstElementByTagName($this->requestXmlObject->documentElement, "body")) == false) {
      throw new VWException(CJSON::encode(array("Error", "500", "Unknown Server Error, Errors while parsing the XML response object-P2")));
    }

    $theBodyNode->nodeValue = $theOldBody->nodeValue;   // copu the value (optional at the TEST PDF)

    return $this->responseXMLObject;
  }
}

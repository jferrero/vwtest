<?php

/**
 * Specific class for handling the reverse requests
 */
class NackResponseHandler extends AbstractXMLHandler
{

  protected $xsdRequestFilename = null;                           // filename of the proper request XSD
  protected $xsdResponseFilename = 'nack.xsd';                    // filename of the proper response XSD
  protected $xmlResponseSampleFilename = 'nack.xml';              // filename of the example xml response
  private $errorCode;
  private $errorMsg;
  public static $classIsPublished = false;                        // sets if the class can be called from the outside (to allow auto calling and allow generalization at the same time)

  /**
   * Class entry point, creates a Object off this class and start the all method chain, returns an array-error or the XML to be returned if all it's sucessfull
   * @param [type] $input The text to be handled by the app core
   */
  public static function HandleRequest($originalRequest, $errorCode, $errorMsg)
  {

    $aNackResponseHandler = new NackResponseHandler(null);                // create a PingRequest Object
    $aNackResponseHandler->requestXmlObject = $originalRequest;
    $aNackResponseHandler->errorCode = $errorCode;
    $aNackResponseHandler->errorMsg = $errorMsg;

    // check-up and good to go
    return $aNackResponseHandler->handleResponse();                        // handle and return the reponse

  }

  /**
   * Once ensured it's the proper XML, a response should be sended
   * @return [type] the response object
   */
  protected function handleResponse()
  {
    if (file_exists($this->xmlResponseSampleFilepath . $this->xmlResponseSampleFilename)) {
      $this->responseXMLObject = $aResponseXMLObject = new DOMDocument;
      $aResponseXMLObject->load($this->xmlResponseSampleFilepath . $this->xmlResponseSampleFilename);    // load the xml, allowing big XMLs as well
    }

    parent::handleResponse();   // made the standard changes to the response document

    if (($theBodyNode = $this->findFirstElementByTagName($this->responseXMLObject, "body"))   == false) {
      throw new VWException(CJSON::encode(array("Error", "500", "Unknown Server Error, Errors while parsing the XML response object-N1")));
    }

    if (($theCodeNode = $this->findFirstElementByTagName($theBodyNode, "code"))   == false) {
      throw new VWException(CJSON::encode(array("Error", "500", "Unknown Server Error, Errors while parsing the XML response object-N2")));
    }

    if (($theMessageNode = $this->findFirstElementByTagName($theBodyNode, "message"))   == false) {
      throw new VWException(CJSON::encode(array("Error", "500", "Unknown Server Error, Errors while parsing the XML response object-N3")));
    }

    $theCodeNode->nodeValue = $this->errorCode;
    $theMessageNode->nodeValue = $this->errorMsg;

    header('Content-type: application/xml; charset=utf-8');     // set chatset and content-type
    echo $this->responseXMLObject->saveXML();                   // print
    die();                                                      // force finish of execution
  }

}

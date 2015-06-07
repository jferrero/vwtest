<?php

abstract class AbstractXMLHandler
{

  protected $inputdata;
  protected $xmlObject;

  protected $xsdRequestFilepath = '';
  protected $xsdRequestFilename = '';

  protected $xsdResponseFilepath = '';
  protected $xsdResponseFilename = '';

  protected $XmlResponseFilepath = '';
  protected $XmlResponseFilename = '';

  public static function HandleRequest($inputParams)
  {
  }

  protected function inputCheckNParsing()
  {
  }

  protected function handleXmlWrongFormat()
  {
  }

  protected function validateVsXsd()
  {
      // ----- this is where the XML is validated first as a xml and then against it's own xsd
      set_error_handler(array(self, 'CustomHandleErrors'));  // set a specific error handler for the loadXML function, to have control of the error
      $aDomDocument = new DOMDocument;
      $aDomDocument->loadXML($this->inputdata, LIBXML_PARSEHUGE);    // load the xml, allowing big XMLs as well

      $validation = 1;
      if ($this->xsdRequestFilepath && $this->xsdRequestFilename) {
        // ToDo validation of directory
        //print_r("<pre>" .htmlentities($aDomDocument->saveXML()) . "</pre>");
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

  protected function CustomHandleErrors($errno, $errstr, $errfile, $errline)
  {
    //echo $errstr . "--" . $errfile;die;
    if ($errno==E_WARNING && (substr_count($errstr,"DOMDocument::loadXML()")>0)) {
        $this->sendResponse(400, "Bad Request, not a valid or recognizable xml format");
    } else {
      if ($errno==E_WARNING && (substr_count($errstr,"DOMDocument::schemaValidate(): Element")>0)) {
        $this->sendResponse(400, "Bad Request, this xml is not supported, please check the proper xsd format, request rejected");
      } else {
        if ($errno==E_WARNING) {
          $this->sendResponse(400, $errstr . "--" . $errline);
        } else {

          $this->sendResponse(400, $errstr . "--" . $errline);
        }
      }
    }
  }

  protected function processRequest()
  {
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

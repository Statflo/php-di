<?php

namespace Statflo\DI;

use Symfony\Component\DependencyInjection\Loader\XmlFileLoader as BaseXmlFileLoader;

class XMLFileLoader extends BaseXmlFileLoader
{
  public function validateSchema(\DOMDocument $dom)
  {
      return true;
  }
}

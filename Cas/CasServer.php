<?php
namespace PlFort\CasAuthBundle\Cas;

class CasServer implements CasServerInterface
{

    protected $id;

    protected $httpProtocol = "https";

    protected $host;

    protected $port;

    protected $protocolVersion = 2;

    protected $caCertFile;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getHost()
    {
        return $this->host;
    }

    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }

    public function getPort()
    {
        return $this->port;
    }

    public function setPort($port)
    {
        $this->port = $port;
        return $this;
    }

    public function getProtocolVersion()
    {
        return $this->protocolVersion;
    }

    public function setProtocolVersion($protocolVersion)
    {
        $this->protocolVersion = $protocolVersion;
        return $this;
    }

    public function getCaCertFile()
    {
        return $this->caCertFile;
    }

    public function setCaCertFile($caCertFile)
    {
        $this->caCertFile = $caCertFile;
        return $this;
    }

    public function getBaseUrl()
    {
        if($this->port){
            return sprintf("%s://%s:%s", $this->httpProtocol, $this->host, $this->port);
        }else{
            return sprintf("%s://%s", $this->httpProtocol, $this->host);
        }
        
    }

    public function getLoginUrl()
    {
        $protocol = $this->getProtocolVersion();
        
        switch ($protocol) {
            
            case 2:
                $path = CasProtocolV2::getLoginPath();
                break;
            default:
                throw new \RuntimeException("Unknown protocol $protocol");
        }
        
        return sprintf("%s%s", $this->getBaseUrl(), $path);
    }

    public function getLogoutuUrl()
    {
        $protocol = $this->getProtocolVersion();
        
        switch ($protocol) {
            
            case 2:
                $path = CasProtocolV2::getLogoutPath();
                break;
            default:
                throw new \RuntimeException("Unknown protocol $protocol");
        }
        
        return sprintf("%s%s", $this->getBaseUrl(), $path);
    }

    public function getValidateUrl()
    {
        $protocol = $this->getProtocolVersion();
        
        switch ($protocol) {
            
            case 2:
                $path = CasProtocolV2::getValidationPath();
                break;
            default:
                throw new \RuntimeException("Unknown protocol $protocol");
        }
        
        return sprintf("%s%s", $this->getBaseUrl(), $path);
    }

    public function getHttpProtocol()
    {
        return $this->httpProtocol;
    }

    public function setHttpProtocol($httpProtocol)
    {
        $this->httpProtocol = $httpProtocol;
        return $this;
    }
 
}
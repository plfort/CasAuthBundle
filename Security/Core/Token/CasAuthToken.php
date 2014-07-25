<?php
namespace PlFort\CasAuthBundle\Security\Core\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use PlFort\CasAuthBundle\Cas\CasServerInterface;

class CasAuthToken extends AbstractToken
{

    private $ticket;

    protected $casServerId;
    
    protected $serviceId;

    public function __construct($ticket,$casServerId,$serviceId,array $roles = array())
    {
        $this->casServerId = $casServerId;
        $this->ticket = $ticket;
        $this->serviceId = $serviceId;
        parent::__construct($roles);
    }

    public function getCredentials()
    {
        return '';
    }

    public function getTicket()
    {
        return $this->ticket;
    }

    public function getCasServerId()
    {
        return $this->casServerId;
    }
    
    public function getServiceId()
    {
        return $this->serviceId;
    }
 
    public function serialize()
    {
        return serialize(array($this->ticket,$this->casServerId,$this->serviceId, parent::serialize()));
    }
    
    public function unserialize($str)
    {
        list($this->ticket,$this->casServerId,$this->serviceId, $parentStr) = unserialize($str);
        parent::unserialize($parentStr);
    }
   
}
<?php
namespace PlFort\CasAuthBundle\Cas;

interface CasServerInterface
{

    public function getLoginUrl();
    
    public function getLogoutuUrl();
    
    public function getValidateUrl();
    
    public function getCaCertFile();
}
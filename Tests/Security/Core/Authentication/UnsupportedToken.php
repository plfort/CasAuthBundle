<?php
namespace PlFort\CasAuthBundle\Tests\Security\Core\Authentication;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class UnsupportedToken extends AbstractToken
{

    public function getCredentials()
    {
        return '';
    }
}
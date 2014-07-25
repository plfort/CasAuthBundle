<?php
namespace PlFort\CasAuthBundle\Cas;

/**
 * 
 * @author plfort
 *
 */
class CasProtocolV2 
{
    
    
    public static function getLoginPath()
    {
       return '/login'; 
    }
    
    public static function getValidationPath()
    {
        return '/serviceValidate';
    }
    
    public static function getLogoutPath()
    {
        return '/logout';
    }
    
}
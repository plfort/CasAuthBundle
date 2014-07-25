<?php
namespace PlFort\CasAuthBundle\Cas\ServerProvider;

interface CasServerProviderInterface
{
    
    /**
     * Return true if the server provider has the CasServer identified by $id
     * @param mixed $id
     */
    public function hasCasServer($id);
    
    /**
     * Return the CASServer identified by $id
     * @param mixed $id
     * @return CasServer|null
     */
    public function getCasServer($id);
    
    
}
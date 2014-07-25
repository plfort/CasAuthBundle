<?php
namespace PlFort\CasAuthBundle\Security\Http;

use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use PlFort\CasAuthBundle\Cas\ServerProvider\CasServerProviderInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class CasAuthEntryPoint implements AuthenticationEntryPointInterface
{

    const CAS_SERVER_ID_KEY = 'plf_cas_server';

    private $casServerProvider;
   
    private $config;
    


    public function __construct(CasServerProviderInterface $casServerProvider,$config)
    {
        $this->casServerProvider = $casServerProvider;
        $this->config = $config;


    }

    /**
     * (non-PHPdoc)
     *
     * @see \Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface::start()
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
           
        if(null === $casServerId = $request->query->get(self::CAS_SERVER_ID_KEY, null)) {
            throw new \RuntimeException("You must provide a CAS server id");
        }
        
        if (null === $casServer = $this->casServerProvider->getCasServer($casServerId)) {
            throw new \RuntimeException("CasServer $casServerId not found");
        }
        
        $queryParameters = array();
       
        $queryParameters['service']=  sprintf("%s?%s",$request->getUriForPath($this->config['check_path']),http_build_query($request->query->all()));
       
        return new RedirectResponse($casServer->getLoginUrl().'?'.http_build_query($queryParameters));
        
        
    }
}
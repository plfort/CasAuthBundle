<?php
namespace PlFort\CasAuthBundle\Security\Firewall;

use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Response;
use PlFort\CasAuthBundle\Security\Core\Token\CasAuthToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Firewall\AbstractPreAuthenticatedListener;
use Symfony\Component\Security\Http\Firewall\AbstractAuthenticationListener;
use PlFort\CasAuthBundle\Security\Http\CasAuthEntryPoint;

class CasAuthListener extends AbstractAuthenticationListener
{

    /**
     * (non-PHPdoc)
     * 
     * @see \Symfony\Component\Security\Http\Firewall\AbstractAuthenticationListener::attemptAuthentication()
     */
    protected function attemptAuthentication(Request $request)
    {
        if($request->query->has('ticket')) {
            $token = $this->createToken($request);
            
            return $this->authenticationManager->authenticate($token);
        }
      
      
    }

    private function createToken(Request $request)
    {
        // keep all query parameters
        $query = $request->query->all();
        unset($query['ticket']);
        $serviceId = sprintf("%s?%s",$request->getUriForPath($this->options['check_path']),http_build_query($query));
        
        return new CasAuthToken($request->query->get('ticket'),$request->query->get(CasAuthEntryPoint::CAS_SERVER_ID_KEY),$serviceId);
    }
}
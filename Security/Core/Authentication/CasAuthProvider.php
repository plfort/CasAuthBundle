<?php
namespace PlFort\CasAuthBundle\Security\Core\Authentication;

use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use PlFort\CasAuthBundle\Cas\ServerProvider\CasServerProviderInterface;
use PlFort\CasAuthBundle\Cas\CasAuthManager;
use PlFort\CasAuthBundle\Security\Core\Token\CasAuthToken;
use Symfony\Component\Security\Core\User\UserCheckerInterface;

class CasAuthProvider implements AuthenticationProviderInterface
{

    private $userProvider;
    
    private $casManager;
    
    private $casServerProvider;
    
    private $userChecker;

    
    public function __construct(CasAuthManager $casManager,UserCheckerInterface $userChecker,UserProviderInterface $userProvider, CasServerProviderInterface $casServerProvider)
    {
        
        $this->casManager = $casManager;
        $this->userProvider = $userProvider;
        $this->userChecker = $userChecker;
        $this->casServerProvider = $casServerProvider;
        

    }
    
    public function authenticate(TokenInterface $token)
    {
       
        if(!$this->supports($token)){
           
            return null;
        }
        
            
        if(false !== $username = $this->casManager->validateTicket($this->casServerProvider, $token)){
           $user =  $this->userProvider->loadUserByUsername($username);
            
           $this->userChecker->checkPreAuth($user);
           
           $this->userChecker->checkPostAuth($user);
           
           $token = new CasAuthToken($token->getTicket(),$token->getCasServerId(),$token->getServiceId(),$user->getRoles());
           
           $token->setUser($user);
           
           $token->setAuthenticated(true);
          
           return $token;
        }
       
            
        throw new AuthenticationException('The CAS authentication failed.');
    }

    public function supports(TokenInterface $token)
    {
       return $token instanceof CasAuthToken;
    }
}
<?php
namespace PlFort\CasAuthBundle\Tests\Security\Core\Authentication;

use PlFort\CasAuthBundle\Security\Core\Authentication\CasAuthProvider;
use PlFort\CasAuthBundle\Security\Core\Token\CasAuthToken;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
class CasAuthProviderTest extends \PHPUnit_Framework_TestCase
{

    private $casManager;


    private $userChecker;

    private $casServerProvider;

    private $userProvider;

    private $casAuthProvider;

    protected function setUp()
    {
        $this->casManager = $this->getMockCasManager();
        $this->userChecker = $this->getMockUserChecker();
        $this->casServerProvider = $this->getMockCasProvider();
        $this->userProvider = $this->getMockUserProvider();

        $this->casAuthProvider = $this->getCasAuthProvider();

    }

    protected function getCasAuthProvider()
    {
        return new CasAuthProvider($this->casManager, $this->userChecker, $this->userProvider, $this->casServerProvider);
    }

    protected function getMockCasManager()
    {
        return $this->getMockBuilder('PlFort\CasAuthBundle\Cas\CasAuthManager')
        ->disableOriginalConstructor()->getMock();
    }

    protected function getMockUserChecker()
    {
        return $this->getMock('Symfony\Component\Security\Core\User\UserCheckerInterface');
    }

    protected function getMockCasProvider()
    {
        return $this->getMock('PlFort\CasAuthBundle\Cas\ServerProvider\CasServerProviderInterface');
    }

    protected function getMockUserProvider()
    {
        return $this->getMock('Symfony\Component\Security\Core\User\UserProviderInterface');
    }


    public function test_unsupportedToken()
    {
        $token = new UnsupportedToken();
        $this->assertNull($this->casAuthProvider->authenticate($token));
    }

    protected function getMockUser()
    {
        return $this->getMock('Symfony\Component\Security\Core\User\UserInterface');

    }

    /**
     * @expectedException Symfony\Component\Security\Core\Exception\AuthenticationException
     */
    public function test_authenticationFail()
    {

        $token = new CasAuthToken('ticketId', 1, 'serviceId');
        $this->casManager->expects($this->once())
        ->method('validateTicket')
        ->with($this->casServerProvider,$token)
        ->will($this->returnValue(false));
        $this->casAuthProvider->authenticate($token);

    }

    /**
     * @expectedException Symfony\Component\Security\Core\Exception\UsernameNotFoundException
     */
    public function test_usernameNotFound()
    {
        $token = new CasAuthToken('ticketId', 1, 'serviceId');
        $this->casManager->expects($this->once())
        ->method('validateTicket')
        ->with($this->casServerProvider,$token)
        ->will($this->returnValue('toto'));

        $this->userProvider->expects($this->once())
        ->method('loaduserByUsername')
        ->with('toto')
        ->will($this->throwException(new UsernameNotFoundException()));

        $this->casAuthProvider->authenticate($token);
    }

    public function test_authenticationSuccess()
    {
        $token = new CasAuthToken('ticketId', 1, 'serviceId');
        $user = $this->getMockUser();
        $user->expects($this->any())
        ->method('getRoles')
        ->will($this->returnValue(array('ROLE_U1','ROLE_U2')));

        $user->expects($this->any())
        ->method('getUsername')
        ->will($this->returnValue('toto'));

        $this->casManager->expects($this->once())
        ->method('validateTicket')
        ->with($this->casServerProvider,$token)
        ->will($this->returnValue('toto'));

        $this->userProvider->expects($this->once())
        ->method('loaduserByUsername')
        ->with('toto')
        ->will($this->returnValue($user));

        $this->userChecker->expects($this->once())
        ->method('checkPreAuth')
        ->with($user);

        $this->userChecker->expects($this->once())
        ->method('checkPostAuth')
        ->with($user);

        $returnedToken = $this->casAuthProvider->authenticate($token);

        $this->assertSame($user, $returnedToken->getUser());
        $this->assertEquals('toto', $returnedToken->getUsername());
        $this->assertTrue($returnedToken->isAuthenticated());
    }


}
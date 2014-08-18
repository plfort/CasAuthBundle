<?php
namespace PlFort\CasAuthBundle\Tests\Cas;

use PlFort\CasAuthBundle\Cas\CasAuthManager;
use PlFort\CasAuthBundle\Security\Core\Token\CasAuthToken;
use PlFort\CasAuthBundle\Security\Core\Authentication\CasAuthProvider;
use PlFort\CasAuthBundle\Cas\CasServer;
use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStream;

class CasAuthManagerTest extends \PHPUnit_Framework_TestCase
{

    protected $httpClient;

    protected $casAuthManager;

    protected $fileLocator;

    protected function setUp()
    {
        $this->httpClient = $this->getMockHttpClient();
        $this->fileLocator = $this->getMockFileLocator();

        $this->casAuthManager = $this->getCasAuthManager();
    }

    protected function getMockFileLocator()
    {
        $fileLocator = $this->getMockBuilder('Symfony\Component\Config\FileLocatorInterface')
        ->getMock();
        $fileLocator->expects($this->any())
        ->method('locate')
        ->with('@PlFortCasAuthBundle/Cas/cas.xsd')
        ->will($this->returnValue(__DIR__.'/../../Cas/cas.xsd'));
        return $fileLocator;

    }

    protected function getMockHttpClient()
    {
        return $this->getMock('GuzzleHttp\ClientInterface');
    }

    protected function getMockCasServerProvider()
    {
        return $this->getMock('PlFort\CasAuthBundle\Cas\ServerProvider\CasServerProviderInterface');
    }

    protected function getCasAuthManager()
    {
        $casAuthManager = new CasAuthManager($this->fileLocator);
        $reflectionCasAuthManager = new \ReflectionClass('PlFort\CasAuthBundle\Cas\CasAuthManager');
        $httpClientProperty = $reflectionCasAuthManager->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($casAuthManager, $this->httpClient);
        return $casAuthManager;
    }

    protected function getMockCasServer()
    {
        $casServer = $this->getMock('PlFort\CasAuthBundle\Cas\CasServer');
        $casServer->expects($this->once())
            ->method('getValidateUrl')
            ->will($this->returnValue('http://cas.server.com/validate'));
        return $casServer;
    }

    public function test_validateTicketCasServerNotFound()
    {
        $token = new CasAuthToken('ticketid', 'casServerId', 'serviceid');
        $casServerProvider = $this->getMockCasServerProvider();
        $casServerProvider->expects($this->once())
            ->method('getCasServer')
            ->with('casServerId')
            ->will($this->returnValue(null));
        $this->assertNull($this->casAuthManager->validateTicket($casServerProvider, $token));
    }

    public function test_validateTicketVerifyCaCert()
    {
        vfsStreamWrapper::register();

        vfsStreamWrapper::setRoot(new vfsStreamDirectory('rootDir'));
        vfsStream::create(array(
            'cert' => array(
                'ca.crt' => 'CERTIFICATE'
            )
        ));

        $caCertFile = vfsStream::url('rootDir/cert/ca.crt');

        $casServer = $this->getMockCasServer();
        $casServer->expects($this->once())
            ->method('getCaCertFile')
            ->will($this->returnValue($caCertFile));

        $casServerProvider = $this->getMockCasServerProvider();

        $casServerProvider->expects($this->once())
            ->method('getCasServer')
            ->with('casServerId')
            ->will($this->returnValue($casServer));

        $token = new CasAuthToken('ticketid', 'casServerId', 'serviceid');

        $requestParam = array(
            'query' => array(
                'ticket' => 'ticketid',
                'service' => 'serviceid'
            ),
            'verify' => $caCertFile
        );

        $this->httpClient->expects($this->once())
            ->method('get')
            ->with('http://cas.server.com/validate', $requestParam);

        $this->casAuthManager->validateTicket($casServerProvider, $token);
    }

    public function test_notXmlResponse()
    {
        $casServer = $this->getMockCasServer();

        $casServerProvider = $this->getMockCasServerProvider();

        $casServerProvider->expects($this->once())
            ->method('getCasServer')
            ->with('casServerId')
            ->will($this->returnValue($casServer));
        $token = new CasAuthToken('ticketid', 'casServerId', 'serviceid');
        $requestParam = array(
            'query' => array(
                'ticket' => 'ticketid',
                'service' => 'serviceid'
            )
        );

        $response = $this->getMock('GuzzleHttp\Message\ResponseInterface');
        $response->expects($this->any())
            ->method('getBody')
            ->will($this->returnValue('This is not XML'));

        $this->httpClient->expects($this->once())
            ->method('get')
            ->with('http://cas.server.com/validate', $requestParam)
            ->will($this->returnValue($response));

        $this->assertFalse($this->casAuthManager->validateTicket($casServerProvider, $token));
    }

    public function test_NotValidXmlSwitchXsd()
    {
        $stringResponse = <<<EOT
                    <cas:serviceResponse xmlns:cas="http://www.yale.edu/tp/cas">
                <cas:BADTAG>
                <cas:user>username</cas:user>
                 <cas:proxyGrantingTicket>PGTIOU-84678-8a9d...</cas:proxyGrantingTicket>
                </cas:BADTAG>
            </cas:serviceResponse>
EOT;
        $casServer = $this->getMockCasServer();

        $casServerProvider = $this->getMockCasServerProvider();

        $casServerProvider->expects($this->once())
            ->method('getCasServer')
            ->with('casServerId')
            ->will($this->returnValue($casServer));
        $token = new CasAuthToken('ticketid', 'casServerId', 'serviceid');
        $requestParam = array(
            'query' => array(
                'ticket' => 'ticketid',
                'service' => 'serviceid'
            )
        );

        $response = $this->getMock('GuzzleHttp\Message\ResponseInterface');
        $response->expects($this->any())
            ->method('getBody')
            ->will($this->returnValue($stringResponse));

        $this->httpClient->expects($this->once())
            ->method('get')
            ->with('http://cas.server.com/validate', $requestParam)
            ->will($this->returnValue($response));

        $this->assertFalse($this->casAuthManager->validateTicket($casServerProvider, $token));
    }

    public function test_authenticationSuccess()
    {
        $stringResponse = <<<EOT
            <cas:serviceResponse xmlns:cas="http://www.yale.edu/tp/cas">
                <cas:authenticationSuccess>
                <cas:user>TheUsername</cas:user>
                 <cas:proxyGrantingTicket>PGTIOU-84678-8a9d...</cas:proxyGrantingTicket>
                </cas:authenticationSuccess>
            </cas:serviceResponse>
EOT;
        $casServer = $this->getMockCasServer();

        $casServerProvider = $this->getMockCasServerProvider();

        $casServerProvider->expects($this->once())
            ->method('getCasServer')
            ->with('casServerId')
            ->will($this->returnValue($casServer));
        $token = new CasAuthToken('ticketid', 'casServerId', 'serviceid');
        $requestParam = array(
            'query' => array(
                'ticket' => 'ticketid',
                'service' => 'serviceid'
            )
        );

        $response = $this->getMock('GuzzleHttp\Message\ResponseInterface');
        $response->expects($this->any())
            ->method('getBody')
            ->will($this->returnValue($stringResponse));

        $this->httpClient->expects($this->once())
            ->method('get')
            ->with('http://cas.server.com/validate', $requestParam)
            ->will($this->returnValue($response));

        $this->assertEquals('TheUsername', $this->casAuthManager->validateTicket($casServerProvider, $token));
    }
}
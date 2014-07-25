<?php
namespace PlFort\CasAuthBundle\Tests\Security\Http;

use PlFort\CasAuthBundle\Security\Http\CasAuthEntryPoint;
use Symfony\Component\HttpFoundation\Request;
use PlFort\CasAuthBundle\Cas\CasServer;
class CasAuthEntryPointTest extends \PHPUnit_Framework_TestCase
{

    /**
     * 
     * @expectedException \RuntimeException
     */
    public function testThatThrowExceptionWhenNoCasSevertIdIsProvided()
    {
        $request = Request::create('/','GET');
     
        $options = array('check_path' => '/check');
        
        $casServerProvider = $this->getMock('PlFort\CasAuthBundle\Cas\ServerProvider\CasServerProviderInterface');
        
        $casEntryPoint = new CasAuthEntryPoint($casServerProvider, $options);
        
        $casEntryPoint->start($request);
    }
    
    /**
     *
     * @expectedException \RuntimeException
     */
    public function testThatThrowExcpetionWhenCasServerIsNotFound()
    {
        $request = Request::create('/','GET',array(CasAuthEntryPoint::CAS_SERVER_ID_KEY=>1));
        $options = array('check_path' => '/check');
        
        $casServerProvider = $this->getMock('PlFort\CasAuthBundle\Cas\ServerProvider\CasServerProviderInterface');
        $casServerProvider->expects($this->once())
        ->method('getCasServer')
        ->will($this->returnValue(null));
        
        $casEntryPoint = new CasAuthEntryPoint($casServerProvider, $options);
        
        $casEntryPoint->start($request);
    }
    
    public function testThatReturnRedirectResponseWithCorrectUrl()
    {
        $casServer = $this->getMock('PlFort\CasAuthBundle\Cas\CasServer');
        $casServer->expects($this->once())
        ->method('getLoginUrl')
        ->will($this->returnValue('http://cas.server.com/login'));
        $request = Request::create('/','GET',array(CasAuthEntryPoint::CAS_SERVER_ID_KEY=>1));
        $options = array('check_path' => '/check');
   
        $casServerProvider = $this->getMock('PlFort\CasAuthBundle\Cas\ServerProvider\CasServerProviderInterface');
        $casServerProvider->expects($this->once())
        ->method('getCasServer')
        ->will($this->returnValue($casServer));
        $casEntryPoint = new CasAuthEntryPoint($casServerProvider, $options);
        
        $response = $casEntryPoint->start($request);
        $parsedResponse = parse_url($response->getTargetUrl());
        $this->assertEquals('http://cas.server.com/login',sprintf('%s://%s%s',$parsedResponse['scheme'],$parsedResponse['host'],$parsedResponse['path'] ));
        parse_str($parsedResponse['query']);

        $this->assertEquals('http://localhost/check?'.CasAuthEntryPoint::CAS_SERVER_ID_KEY.'=1', $service);
    }
}
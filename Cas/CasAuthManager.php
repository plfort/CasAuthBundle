<?php
namespace PlFort\CasAuthBundle\Cas;

use GuzzleHttp\Client;
use PlFort\CasAuthBundle\Security\Core\Token\CasAuthToken;
use PlFort\CasAuthBundle\Cas\ServerProvider\CasServerProviderInterface;
use Symfony\Component\Config\FileLocatorInterface;

/**
 *
 * @author plfort
 *
 */
class CasAuthManager
{

    protected $httpClient;

    protected $fileLocator;

    public function __construct(FileLocatorInterface $fileLocator)
    {
        $this->httpClient = new Client();
        $this->fileLocator = $fileLocator;
    }

    /**
     * Validate a service ticket
     * @param CasServerProviderInterface $casServerProvider
     * @param CasAuthToken $token
     * @return string|boolean The username from the CAS Server response or FALSE
     */
    public function validateTicket(CasServerProviderInterface $casServerProvider, CasAuthToken $token)
    {

        if (null == $casServer = $casServerProvider->getCasServer($token->getCasServerId())) {

            return null;
        }

        $requestParam = array();
        $requestParam['query'] = array(
            'ticket' => $token->getTicket(),
            'service' => $token->getServiceId()
        );

        if (null !== $caCert = $casServer->getCaCertFile()) {

            if (is_file($caCert)) {
                $requestParam['verify'] = $caCert;
            }
        }
        $response = $this->httpClient->get($casServer->getValidateUrl(), $requestParam);
        if ($response) {
            $dom = new \DOMDocument();

            if (@$dom->loadXML($response->getBody()) && @$dom->schemaValidate($this->getSchema())) {

                $tree = $dom->documentElement;

                if ($tree->getElementsByTagName("authenticationSuccess")->length != 0) {

                    $userTag = $tree->getElementsByTagName("authenticationSuccess")
                        ->item(0)
                        ->getElementsByTagName("user");
                    if ($userTag->length == 1) {

                        return $userTag->item(0)->nodeValue;
                    }
                }
            }
        }
        return false;
    }

    private function getSchema()
    {
        return $this->fileLocator->locate('@CasAuthBundle/Cas/cas.xsd');
    }

}
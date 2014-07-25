<?php
namespace PlFort\CasAuthBundle\Security\Factory;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AbstractFactory;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;

class CasAuthFactory extends AbstractFactory
{

    protected function createEntryPoint($container, $id, $config, $defaultEntryPointId)
    {
        $entryPointId = 'security.authentication.cas_auth_entry_point.' . $id;
        $container->setDefinition($entryPointId, new DefinitionDecorator('cas.security.authentication.cas_auth_entry_point'))
            ->replaceArgument(0, new Reference($config['cas_server_provider']))
            ->addArgument($config);
 
        return $entryPointId;
    }

  /*  protected function createListener($container, $id, $config, $userProvider)
    {
        $listenerId = 'security.authentication.listener.cas.' . $id;
        $container->setDefinition($listenerId, new DefinitionDecorator('cas.security.authentication.listener'));
        return $listenerId;
    }*/

    protected function getListenerId()
    {
        return 'cas.security.authentication.listener';
    }

    protected function createAuthProvider(ContainerBuilder $container, $id, $config, $userProviderId)
    {
        $providerId = 'security.authentication.provider.cas.' . $id;
        $container->setDefinition($providerId, new DefinitionDecorator('cas.security.authentication.provider'))
        ->replaceArgument(2, new Reference($userProviderId))
        ->replaceArgument(3, new Reference($config['cas_server_provider']));
        return $providerId;
    }
    
    public function addConfiguration(NodeDefinition $node)
    {
        
        parent::addConfiguration($node);
        $builder = $node->children();
    
        $builder
        ->scalarNode('cas_server_provider')->end();
        
    }

    
    
    public function getPosition()
    {
        return 'form';
    }

    public function getKey()
    {
        return 'cas';
    }
}
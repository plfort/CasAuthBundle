<?php

namespace PlFort\CasAuthBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use PlFort\CasAuthBundle\Security\Factory\CasAuthFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class PlFortCasAuthBundle extends Bundle
{
    
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
    
        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new CasAuthFactory());
    }
}

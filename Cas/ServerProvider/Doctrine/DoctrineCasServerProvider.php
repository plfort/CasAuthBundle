<?php
namespace PlFort\CasAuthBundle\Cas\ServerProvider\Doctrine;

use PlFort\CasAuthBundle\Cas\ServerProvider\CasServerProviderInterface;
use PlFort\CasAuthBundle\Cas\CasServer;
use Doctrine\Common\Persistence\ObjectRepository;

class DoctrineCasServerProvider implements CasServerProviderInterface
{

    protected $repository;
    
    public function __construct(ObjectRepository $repo)
    {
        $this->repository = $repo;
    }
    
    public function hasCasServer($id)
    {

        return $this->repository->find($id) != null;
    }

    public function getCasServer($id)
    {
        return $this->repository->find($id);
    }
}
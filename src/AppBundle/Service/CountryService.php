<?php
namespace AppBundle\Service;

use Doctrine\Bundle\DoctrineBundle\Registry;
use AppBundle\Entity\Country;
use Symfony\Component\DependencyInjection\Container;

/**
 * @package AppBundle\Service
 */
class CountryService
{
    /**
     * @var Container
     */
    protected $container;
    /**
     * @var Registry
     */
    protected $doctrine;

    /**
     * @param Container $container
     *
     * @throws \Exception
     */
    public function __construct(Container $container)
    {
        $this->container = $container;

        $this->doctrine = $this->container->get('doctrine');
    }

    /**
     * @return Registry
     */
    protected function getDoctrine()
    {
        return $this->doctrine;
    }

    /**
     * @return Container
     */
    protected function getContainer()
    {
        return $this->container;
    }

    /**
     * @param int $id
     *
     * @return Country
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getCountry($id)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->doctrine->getManager();

        /** @var \AppBundle\Repository\CountryRepository $repository */
        $repository = $em->getRepository('AppBundle:Country');

        return $repository->getCountry($id);
    }
}

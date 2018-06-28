<?php

namespace AppBundle\Service;

use AppBundle\Entity\CustomerCompany;
use Symfony\Component\DependencyInjection\Container;

class CustomerCompanyService
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var \Doctrine\Bundle\DoctrineBundle\Registry
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
        $this->doctrine = $this->getContainer()->get('doctrine');
    }

    /**
     * @return Container
     */
    protected function getContainer()
    {
        return $this->container;
    }

    /**
     * @return \Doctrine\Bundle\DoctrineBundle\Registry
     */
    public function getDoctrine()
    {
        return $this->doctrine;
    }

    /**
     * @param CustomerCompany $customerCompany
     *
     * @throws \Exception
     */
    public function saveCompany(CustomerCompany $customerCompany)
    {
        /** @var \Monolog\Logger $logger */
        $logger = $this->getContainer()->get('logger');

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $em->persist($customerCompany);

        /** @var CustomerService $customerService */
        $customerService = $this->getContainer()->get('customer.customer');
        if (null == $customerCompany->getCustomerContact()) {
            $message = $this->getContainer()->get('translator')->trans('api.company.no_contact');
            $logger->addInfo($message);
            throw new \Exception($message);
        }

        $customerService->updateCustomerAccount($customerCompany, $customerCompany->getCustomerContact(), true);

        /**
         * trigger save customer in SAP
         */
        /** @var \Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher $dispatcher */
        $dispatcher = $this->getContainer()->get('event_dispatcher');
        $dispatcher->dispatch('CUSTOMER_SYNC_EVENT', $customerService->getEvent());
    }
}

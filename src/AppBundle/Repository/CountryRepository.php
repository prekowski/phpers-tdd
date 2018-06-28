<?php
namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use AppBundle\Entity\Country;

class CountryRepository extends EntityRepository
{
    /**
     * @param int $id
     *
     * @return Country
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getCountry($id)
    {
        return $this->createQueryBuilder('c')
            ->where('c.status = :status')->setParameter('status', Country::STATUS_ENABLED)
            ->andWhere('c.id = :id')->setParameter('id', $id)
            ->setMaxResults(1)
            ->getQuery()
            ->useQueryCache(true)
            ->useResultCache(true)
            ->getOneOrNullResult();
    }
}

<?php
namespace AppBundle\Repository;

use AppBundle\Entity\Locality;
use Doctrine\ORM\EntityRepository;

class LocalityRepository extends EntityRepository
{
    /**
     * @param int $id
     * @return Locality
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getLocality($id)
    {
        return $this->createQueryBuilder('l')
            ->where('l.id = :id')->setParameter('id', $id)
            ->andWhere('l.status = :status')->setParameter('status', 1)
            ->setMaxResults(1)
            ->getQuery()
            ->useQueryCache(true)
            ->useResultCache(true)
            ->getOneOrNullResult();
    }
}

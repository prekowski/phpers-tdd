<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use AppBundle\Entity\Locale;

class LocaleRepository extends EntityRepository
{
    /**
     * @param int $id
     *
     * @return Locale
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getLocale($id)
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

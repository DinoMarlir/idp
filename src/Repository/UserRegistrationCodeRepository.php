<?php

namespace App\Repository;

use App\Entity\UserRegistrationCode;
use App\Entity\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

class UserRegistrationCodeRepository implements UserRegistrationCodeRepositoryInterface {

    private $em;

    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
    }

    private function createDefaultQueryBuilder(): QueryBuilder {
        return $this->em->createQueryBuilder()
            ->select(['c', 't'])
            ->from(UserRegistrationCode::class, 'c')
            ->leftJoin('c.type', 't');
    }

    public function findOneByCode(string $code): ?UserRegistrationCode {
        return $this->createDefaultQueryBuilder()
            ->where('c.code = :code')
            ->setParameter('code', $code)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneByToken(string $token): ?UserRegistrationCode {
        return $this->createDefaultQueryBuilder()
            ->where('c.token = :token')
            ->setParameter('token', $token)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAll() {
        return $this->createDefaultQueryBuilder()
            ->getQuery()
            ->getResult();
    }

    public function persist(UserRegistrationCode $code): void {
        $this->em->persist($code);
        $this->em->flush();
    }

    public function remove(UserRegistrationCode $code): void {
        $this->em->remove($code);
        $this->em->flush();;
    }

    public function beginTransaction() {
        $this->em->beginTransaction();
    }

    public function commit() {
        $this->em->commit();
    }

    public function rollBack() {
        $this->em->rollback();;
    }

    public function getPaginatedUsers(int $itemsPerPage, int &$page, UserType $type = null): Paginator {
        $qb = $this->createDefaultQueryBuilder();

        if($type !== null) {
            $qb->where('c.type = :type')
                ->setParameter('type', $type->getId());
        }

        if(!is_numeric($page) || $page < 1) {
            $page = 1;
        }

        $offset = ($page - 1) * $itemsPerPage;

        $paginator = new Paginator($qb);
        $paginator->getQuery()
            ->setMaxResults($itemsPerPage)
            ->setFirstResult($offset);

        return $paginator;
    }

    public function resetTokens(\DateTime $dateTime): void {
        $this->em->createQueryBuilder()
            ->update(UserRegistrationCode::class, 'u')
            ->set('u.token', ':null')
            ->set('u.tokenCreatedAt', ':null')
            ->where('u.tokenCreatedAt < :threshold')
            ->setParameter('threshold', $dateTime)
            ->setParameter('null', null)
            ->getQuery()
            ->execute();
    }
}
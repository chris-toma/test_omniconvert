<?php

namespace App\Repository;

use App\Entity\Transaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Transaction|null find($id, $lockMode = NULL, $lockVersion = NULL)
 * @method Transaction|null findOneBy(array $criteria, array $orderBy = NULL)
 * @method Transaction[]    findAll()
 * @method Transaction[]    findBy(array $criteria, array $orderBy = NULL, $limit = NULL, $offset = NULL)
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    /**
     * @param Transaction $transaction
     *
     * @return array
     */
    public function transform(Transaction $transaction)
    {
        return [
            'id'             => (int)$transaction->getId(),
            'user_id'        => (string)$transaction->getUserId(),
            'transaction_id' => (int)$transaction->getTransactionId(),
            'amount'         => (float)$transaction->getAmount(),
            'created_at'     => (string)$transaction->getCreatedAt(),
        ];
    }
}

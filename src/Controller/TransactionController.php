<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Helpers\DateHelper;
use App\Helpers\QueryHelper;
use App\Repository\TransactionRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use PDO;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Config\Tests\Util\Validator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class TransactionController
 *
 * @package App\Controller
 */
class TransactionController extends ApiController
{
    /**
     * @Route("/transaction", methods="GET")
     * @param Request $request
     * @param TransactionRepository $transactionRepository
     * @param EntityManagerInterface $em
     *
     * @param ValidatorInterface $validate
     *
     * @return JsonResponse
     */
    public function create(Request $request, TransactionRepository $transactionRepository, EntityManagerInterface $em, ValidatorInterface $validate)
    {
        // only get request allowed
        if (!$request->isMethod('get')) {
            // I would have gone for "405 method not allowed"
            return $this->respondUnauthorized(['The request needs to be get']);
        }

        // custom validation @todo move to model
        $data = $request->query->all();
        $validator = Validation::createValidator();
        $constraint = new Assert\Collection([
            // the keys correspond to the keys in the input array
            'user'        => [
                new Assert\Regex([
                    'pattern' => '/^[0-9]\d*$/',
                    'message' => 'User need\'s to be only positive numbers.',
                ]),
            ],
            'transaction' => new Assert\Regex([
                'pattern' => '/^[0-9]\d*$/',
                'message' => 'Transaction need\'s to be only positive numbers.',
            ]),
            'amount'      => [
                new Assert\Regex([
                    'pattern' => '/^[1-9]\d*(\.\d+)?$/',
                    'message' => 'Amount need\'s to be only positive float values',
                ]),
            ],
            'created_at'  => new Assert\Date(),

        ]);

        $violations = $validator->validate($data, $constraint);
        // Check for duplicates only if request data is valid
        if (0 === count($violations)) {
            // it would have been best to use the entity manager to fetch the record, using "findOneBy()" or "findBy()"
            // writing sql directly in the contreller is a no-no in Symfony
            // you can either fetch/update/delete using the entity manager, for simple cases
            // or use the Repository to write complex SQL/DQL queries
            $conn = $em-> getConnection();
            $stmt = $conn->prepare("SELECT id FROM transaction WHERE user_id = :user_id AND transaction_id = :transaction_id;");
            $stmt->execute([
                'user_id'        => $data['user'],
                'transaction_id' => $data['transaction'],
            ]);
            $exist = $stmt->fetchAll();

            // could be set as a constraint on the entity, which would add a unique constraint in the db
            if ($exist) {
                $violations[] = new ConstraintViolation('Duplicate entry', '', [], NULL, '', '');
            }
        }

        // I would have written the count($violations) > 0 case first, and what's inside this block would have come below
        // indented to the left; this makes the code easier to read in my oppinion
        if (0 === count($violations)) {
            // do the insert
            $transaction = new Transaction();
            $transaction->setUserId($data['user']);
            $transaction->setTransactionId($data['transaction']);
            $transaction->setAmount($data['amount']);
            $transaction->setCreatedAt($data['created_at']);
            $em->persist($transaction);
            $em->flush();
        }
        else {
            // a more elegant approach in Symfony would have been to throw an http exception, and create an exception listener
            // that would convert the message into a user-friendly response, in the needed format (json/html/etc)
            // @see https://symfony.com/doc/current/event_dispatcher.html
            return $this->respondValidationError($violations);
        }
        return $this->respondCreated($transactionRepository->transform($transaction));
    }

    /**
     * Deleting all transactions
     * @param EntityManagerInterface $em
     * @return RedirectResponse
     */
    public function deleteAll(EntityManagerInterface $em)
    {
        $conn = $em->getConnection();
        $stmt = $conn->prepare("DELETE FROM transaction WHERE id>0");
        $stmt->execute();

        return $this->redirectToRoute('report');
    }

    /**
     * Deleting all transactions and inserting dummy data.
     * @param EntityManagerInterface $em
     * @return RedirectResponse
     */
    public function dummyData(EntityManagerInterface $em)
    {
        // deleteng all data from DB;
        $conn = $em->getConnection();
        // maybe truncate? or did you think about permissions?
        $stmt = $conn->prepare("DELETE FROM transaction WHERE id>0");
        $stmt->execute();

        $users = [
            123,
            132,
            555,
            666,
            777,
            888,
            999,
        ];
        foreach ($users as $user) {
            for ($i = 0; $i < mt_rand(100,3000); $i++) {
                $transaction = new Transaction();
                $transaction->setUserId($user);
                $transaction->setTransactionId(mt_rand(1, 100) . $i . mt_rand(1, 999));
                $transaction->setAmount(mt_rand(1, 100));
                $transaction->setCreatedAt(DateHelper::randomDateInRange('2019-01-01', date('Y-m-d')));
                $em->persist($transaction);
            }
            $em->flush();
        }
        return $this->redirectToRoute('report');
    }
}

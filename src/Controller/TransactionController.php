<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Helpers\DateHelper;
use App\Repository\TransactionRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use PDO;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Config\Tests\Util\Validator;
use Symfony\Component\HttpFoundation\JsonResponse;
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
     * @Route("/transaction", name="transaction")
     * @return Response
     */
    public function index()
    {
        return $this->render('transaction/index.html.twig', [
            'controller_name' => 'TransactionController',
        ]);
    }

    /**
     * @Route("/transaction", methods="GET")
     * @param Request                $request
     * @param TransactionRepository  $transactionRepository
     * @param EntityManagerInterface $em
     *
     * @param ValidatorInterface     $validate
     *
     * @return JsonResponse
     */
    public function create(Request $request, TransactionRepository $transactionRepository, EntityManagerInterface $em, ValidatorInterface $validate)
    {
        // only get request allowed
        if (!$request->isMethod('get')) {
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
                ])
            ],
            'transaction' => new Assert\Regex([
                'pattern' => '/^[0-9]\d*$/',
                'message' => 'Transaction need\'s to be only positive numbers.',
            ]),
            'amount'      => [
                new Assert\Regex([
                    'pattern' => '/^[1-9]\d*(\.\d+)?$/',
                    'message' => 'Amount need\'s to be only positive float values',
                ])
            ],
            'created_at'  => new Assert\Date(),

        ]);

        $violations = $validator->validate($data, $constraint);
        $conn = $em->getConnection();
        $stmt = $conn->prepare("SELECT id FROM transaction WHERE user_id = :user_id AND transaction_id = :transaction_id;");
        $stmt->execute([
            'user_id'=>$data['user'],
            'transaction_id'=>$data['transaction'],
        ]);
        $exist = $stmt->fetchAll();

        if($exist){
            $violations[] = new ConstraintViolation('Duplicate entry','',[],NULL,'','');
        }

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
            return $this->respondValidationError($violations);
        }
        return $this->respondCreated($transactionRepository->transform($transaction));
    }
}

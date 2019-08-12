<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Helpers\DateHelper;
use App\Repository\TransactionRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;

/**
 * Class TransactionController
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
     * @param Request $request
     * @param TransactionRepository $transactionRepository
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    public function create(Request $request, TransactionRepository $transactionRepository, EntityManagerInterface $em)
    {
        // only get request allowed
        if(!$request->isMethod('get')){
            return $this->respondUnauthorized(['The request needs to be get']);
        }

        // custom validation @todo move to model
        $data = $request->query->all();
        $validator = Validation::createValidator();
        $constraint = new Assert\Collection([
            // the keys correspond to the keys in the input array
            'user' => new Assert\Regex([
                'pattern' => '/^[0-9]\d*$/',
                'message' => 'User need\'s to be only positive numbers.',
            ]),
            'transaction' =>new Assert\Regex([
                'pattern' => '/^[0-9]\d*$/',
                'message' => 'Transaction need\'s to be only positive numbers.',
            ]),
            'amount' => new Assert\Regex([
                'pattern' => '/^[1-9]\d*(\.\d+)?$/',
                'message' => 'Amount need\'s to be only positive float values',
            ]),

        ]);

        $violations = $validator->validate($data, $constraint);

        if (0 === count($violations)) {
          // do the insert
            $transaction = new Transaction();
            $transaction->setUserId($data['user']);
            $transaction->setTransactionId($data['transaction']);
            $transaction->setAmount($data['amount']);
            $transaction->setCreatedAt(DateHelper::randomDateInRange(date('Y-m-d',strtotime('2019-01-01')),date('Y-m-d')));

            $em->persist($transaction);
            $em->flush();

        }
        else {
            return $this->respondValidationError($violations);
        }
        return $this->respondCreated($transactionRepository->transform($transaction));
    }
}

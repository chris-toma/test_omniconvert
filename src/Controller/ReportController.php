<?php

namespace App\Controller;

use App\Helpers\QueryHelper;
use Symfony\Component\Form\Button;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Select;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DomCrawler\Field\TextareaFormField;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ReportController
 * @package App\Controller
 */
class ReportController extends AbstractController
{
    /**
     * @Route("/report", name="report")
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function index(EntityManagerInterface $em)
    {

        //the form
        // the form can also be created within its own class, in the Form namespace
        $form = $this->createFormBuilder(NULL, [
            'action' => '/report',
            'method' => 'GET',
        ])
            ->add('period', ChoiceType::class, [
                'label'=>'Select period',
                'choices' => [
                    'all time'   => QueryHelper::PERIOD_TYPE_ALL_TIME,
                    'last week'  => QueryHelper::PERIOD_TYPE_LAST_WEEK,
                    'last month' => QueryHelper::PERIOD_TYPE_LAST_MONTH,
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Search period',
                'attr'  => [
                    'class' => 'btn btn-primary',
                ],
            ])
            ->getForm();

        $request = Request::createFromGlobals();

        $form->handleRequest($request);
        // init empty conditions;
        $conditions = '';

        // if the period comes from the form we compose the date condition
        $formData = $form->getData();
        if ($formData) {
            // "in Symfony, everything should be a service"; so, this helper should be a service
            // and no static method calls are recommended (mainly due to unit testing overhead)
            $conditions = QueryHelper::dateIntervalConditionDiscerner($formData['period']);
        }

        $conn = $em->getConnection();

        // top 5 users by transaction
        // it would have been ideal to use placeholders and later on bind values - this way, the compiled query could
        // have been reused (which is the main performance advantage of using prepared statements)
        // @see TransactionController for the note on Repositories also
        $stmt = $conn->prepare('
                    SELECT user_id, count(transaction_id) AS transaction_count
                    FROM transaction ' . $conditions . '
                    GROUP BY user_id
                    ORDER BY transaction_count DESC
                    LIMIT 5;');
        $stmt->execute();
        $top5ByTransaction = $stmt->fetchAll();

        // Top 5 users, by transactions
        //
        $stmt = $conn->prepare('
                    SELECT user_id, SUM(amount) AS amount_sum
                    FROM transaction ' . $conditions . '
                    GROUP BY user_id
                    ORDER BY amount_sum DESC
                    LIMIT 5;');
        $stmt->execute();
        $top5ByAmount = $stmt->fetchAll();

        // Total amount evolution, per day (for all users summed up)
        $stmt = $conn->prepare('
                    SELECT created_at, sum(amount) AS amount_sum
                    FROM transaction ' . $conditions . '
                    GROUP BY created_at');
        $stmt->execute();
        $totalAmountEvolution = $stmt->fetchAll();


        return $this->render('report/index.html.twig', [
            'controller_name'      => 'ReportController',
            'top5ByTransaction'    => $top5ByTransaction,
            'top5ByAmount'         => $top5ByAmount,
            'totalAmountEvolution' => $totalAmountEvolution,
            'form'                 => $form->createView(),
        ]);
    }
}

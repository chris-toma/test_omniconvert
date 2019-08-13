<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ReportController extends AbstractController
{
    /**
     * @Route("/report", name="report")
     */
    public function index(EntityManagerInterface $em)
    {

//        $conn = $em->getConnection();
//        $stmt = $conn->prepare("SELECT id FROM transaction WHERE user_id = :user_id AND transaction_id = :transaction_id;");
//        $stmt->execute§([
//            'user_id'=>$data['user'],
//            'transaction_id'=>$data['transaction'],
//        ]);
//        $top5ByTransaction = $stmt->fetchAll();

        return $this->render('report/index.html.twig', [
            'controller_name' => 'ReportController',
        ]);
    }
}

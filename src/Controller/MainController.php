<?php
namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Service\Xdag;

class MainController extends Controller
{
    /**
     * @Route("/", name="index")
     */
    public function index(Xdag $xdag)
    {
		$stats = $xdag->getStats();

		return $this->render('index.html.twig', array(
			'blocks' => $xdag->getBlocks($stats),
			'main_blocks' => $xdag->getMainBlocks($stats),
			'supply' => $xdag->getSupply($stats),
			'hashrate' => $xdag->getHashrate($stats),
			'difficulty' => $xdag->getDifficulty($stats)
		));
    }

	/**
     * @Route(
     *     "/block/{address}",
     *     name="block",
     *     requirements={"address"="[a-zA-Z0-9\/+]{32}"}
     * )
     */
    public function block($address, Request $request, Xdag $xdag)
    {
		$block = $xdag->getBlock($address);

		$paginator = $this->get('knp_paginator');
		$pagination = $paginator->paginate($block['details'], $request->query->getInt('page', 1), 50);

		return $this->render('block.html.twig', array(
			'block' => $block,
			'pagination' => $pagination
		));
    }

	/**
     * @Route("/search", name="search", methods={"POST"})
     */
    public function search(Request $request)
    {
		$address = $request->request->get('address');
		return $this->redirectToRoute('block', ['address' => $address]);
    }

	/**
     * @Route("/misc/balance", name="balance")
     */
    public function balance(Request $request, Xdag $xdag)
    {
		$balance = '';

		$form = $this->createFormBuilder()
			->add('address', TextType::class)
			->add('send', SubmitType::class)
			->getForm();

		$form->handleRequest($request);

		if($form->isSubmitted() && $form->isValid()) {
			$balance = $xdag->getBalance($form->getData()['address']);
		}

		return $this->render('balance.html.twig', array(
			'form' => $form->createView(),
			'balance' => $balance
		));
    }

	/**
     * @Route("/mic/mining", name="mining")
     */
    public function mining(Request $request, Xdag $xdag)
    {
		$coins = '';

		$form = $this->createFormBuilder()
			->add('hashrate', TextType::class)
			->add('send', SubmitType::class)
			->getForm();

		$form->handleRequest($request);

		if($form->isSubmitted() && $form->isValid()) {
			$total_hashrate = $xdag->getHashrate();
			$coins_day = (3600*24)/64*1024;
			$user_hashrate = $form->getData()['hashrate'];
			$coins = $user_hashrate*$coins_day/$total_hashrate;
		}

		return $this->render('mining.html.twig', array(
			'form' => $form->createView(),
			'coins' => $coins
		));
    }

	/**
     * @Route("/mic/profit", name="profit")
     */
    public function profit(Request $request, Xdag $xdag)
    {
		$coins = '';

		$form = $this->createFormBuilder()
			->add('hashrate', TextType::class)
			->add('power', TextType::class)
			->add('cost_kwh', TextType::class)
			->add('pool_fee', TextType::class)
			->add('send', SubmitType::class)
			->getForm();

		$form->handleRequest($request);

		if($form->isSubmitted() && $form->isValid()) {

		}

		return $this->render('profit.html.twig', array(
			'form' => $form->createView()
		));
    }
}

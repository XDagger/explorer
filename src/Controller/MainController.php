<?php
namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
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
			'hashrate' => $xdag->getHashrate($stats)
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
}

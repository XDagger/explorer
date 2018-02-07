<?php
namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Service\Xdag;

class MainController extends Controller
{
    /**
     * @Route("/")
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
}

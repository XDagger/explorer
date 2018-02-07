<?php
namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Service\Xdag;

class MainController
{
    /**
     * @Route("/")
     */
    public function index(Xdag $xdag)
    {
		$stats = $xdag->getStats();

        return new Response(
			'Blocks' . $xdag->getBlocks($stats) .
			'<br>Main blocks' . $xdag->getMainBlocks($stats) .
			'<br>Supply' . $xdag->getSupply($stats) .
			'<br>Network hashrate' . $xdag->getHashrate($stats)
        );
    }
}

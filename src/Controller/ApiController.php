<?php
namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use App\Service\Xdag;

class ApiController extends Controller
{
	/**
     * @Route(
     *     "/api/block/{address}",
     *     name="api_block",
     *     requirements={"address"="[a-zA-Z0-9\/+]{32}"}
     * )
     */
    public function block($address, Xdag $xdag)
    {
		// The output is streamed directly to avoid out of memory errors
		$response = new StreamedResponse();
		$response->headers->set('Content-Type', 'application/json');

		echo "{";
		echo "\"block\": \"$address\",";
		$response->setCallback(function () use ($address, $xdag) {
			$lines = $xdag->commandStream("block $address", false);
			$i = 0;
			$flag_first = true;
			foreach($lines as $line) {
				switch($i) {
					case 0:
							if(preg_match("/Block is not found/i", $line)) {
								throw new \Exception('Block not found');
							} else if(preg_match("/Block as transaction: details/i", $line)) {
								$i++;
								echo "\"block_as_transaction\": [";
							} else if(preg_match("/\s*(.*): ([^\s]*)(\s*([0-9]*\.[0-9]*))?/i", $line, $matches)) {
								list($key, $value) = [str_replace(' ', '_', $matches[1]), $matches[2]];
								if($key == 'balance') $value = $matches[4];
								echo "\"$key\": \"$value\",";
							}
						break;
					case 1:
							if(preg_match("/block as address: details/i", $line)) {
									$i++;
									echo "],";
									echo "\"block_as_address\": [";
									$flag_first = true;
							} else if(preg_match("/\s*(fee|input|output|earning): ([a-zA-Z0-9\/+]{32})\s*([0-9]*\.[0-9]*)/i", $line, $matches)) {
								list(, $direction, $address, $amount) = $matches;
								if($flag_first)
								{
									$flag_first = false;
								} else {
									echo ",";
								}
								echo "{";
								echo "\"direction\": \"$direction\",";
								echo "\"address\": \"$address\",";
								echo "\"amount\": \"$amount\"";
								echo "}";
							}
						break;
					case 2:
						if(preg_match("/\s*(fee|input|output|earning): ([a-zA-Z0-9\/+]{32})\s*([0-9]*\.[0-9]*)\s*(.*)/i", $line, $matches)) {
								list(, $direction, $address, $amount, $time) = $matches;
								if($flag_first)
								{
									$flag_first = false;
								} else {
									echo ",";
								}
								echo "{";
								echo "\"direction\": \"$direction\",";
								echo "\"address\": \"$address\",";
								echo "\"amount\": \"$amount\",";
								echo "\"time\": \"$time\"";
								echo "}";
						}
						break;
				}
			}
			echo "]";
			echo "}";
		});

		return $response;
    }
}

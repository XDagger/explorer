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
		// that is why i "hardcode" the json directly instead of
		// put everything into an array and then use json_encode
		$response = new StreamedResponse();
		$response->headers->set('Content-Type', 'application/json');

		$response->setCallback(function () use ($address, $xdag) {
			$generator = $xdag->commandStream("block $address");

			echo "{";
			echo "\"block\": \"$address\",";

			while(true) {
				$line = $generator->current();
				$generator->next();

				if(preg_match("/Block is not found/i", $line)) {
					throw new \Exception('Block not found');
				} else if(preg_match("/Block as transaction: details/i", $line)) {
					// Jump to block as transaction
					break;
				} else if(preg_match("/\s*(.*): ([^\s]*)(\s*([0-9]*\.[0-9]*))?/i", $line, $matches)) {
					list($key, $value) = [str_replace(' ', '_', $matches[1]), $matches[2]];
					if($key == 'balance') $value = $matches[4];
					echo "\"$key\": \"$value\",";
				}
			}

			echo "\"block_as_transaction\": [";

			$first = true;
			while(true) {
				$line = $generator->current();
				$generator->next();

				if(preg_match("/block as address: details/i", $line)) {
						// Jump to block as address
						break;
				} else if(preg_match("/\s*(fee|input|output|earning): ([a-zA-Z0-9\/+]{32})\s*([0-9]*\.[0-9]*)/i", $line, $matches)) {
					list(, $direction, $address, $amount) = $matches;
					if(!$first) {
						echo ",";
					}
					$first = false;
					echo "{";
					echo "\"direction\": \"$direction\",";
					echo "\"address\": \"$address\",";
					echo "\"amount\": \"$amount\"";
					echo "}";
				}
			}

			echo "],";
			echo "\"block_as_address\": [";

			$first = true;
			while(true) {
				if(!$generator->valid()) {
					break;
				}

				$line = $generator->current();
				$generator->next();

				if(preg_match("/\s*(fee|input|output|earning): ([a-zA-Z0-9\/+]{32})\s*([0-9]*\.[0-9]*)\s*(.*)/i", $line, $matches)) {
						list(, $direction, $address, $amount, $time) = $matches;
						if(!$first) {
							echo ",";
						}
						$first = false;
						echo "{";
						echo "\"direction\": \"$direction\",";
						echo "\"address\": \"$address\",";
						echo "\"amount\": \"$amount\",";
						echo "\"time\": \"$time\"";
						echo "}";
				}
			}

			echo "]";
			echo "}";
		});

		return $response;
    }
}

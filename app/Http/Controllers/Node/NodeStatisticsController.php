<?php
namespace App\Http\Controllers\Node;

use App\Http\Controllers\Controller;
use App\Modules\NodeStatistic\NodeStatisticRepository;

class NodeStatisticsController extends Controller
{
	public function index(NodeStatisticRepository $repo)
	{
		if ((string) config('services.xdag.whitelist_path') === '') {
			$this->notify()->warning('Node statistics functionality is disabled for this Explorer instance.');
			return redirect()->route('home');
		}

		return view($this->resolveView('node.index', 'node.text-index'), [
			'nodes' => $repo->getPaginatedNodes(100, 'statistics'),
			'repo' => $repo,
			'lastCheckAt' => $repo->lastCheckAt(),
		]);
	}
}

<?php
namespace App\Xdag\Block\Pagination;

class Paginator
{
	/**
	 * @var int
	 */
	protected $perPage;

	/**
	 * @var string
	 */
	protected $pageName;

	/**
	 * @var bool
	 */
	protected $hasMorePages = false;

	/**
	 * @var int
	 */
	protected $totalNumberOfItems = 0;

	/**
	 * Paginator constructor.
	 *
	 * @param int	 $perPage
	 * @param string $pageName
	 */
	public function __construct($perPage = 10, $pageName = 'page')
	{
		$this->perPage	= $perPage;
		$this->pageName = $pageName;
	}

	/**
	 * @return int
	 */
	public function currentPage()
	{
		return ((int)request()->input($this->pageName, 1)) - 1;
	}

	/**
	 * @return int
	 */
	public function start()
	{
		$start = $this->currentPage() * $this->perPage;

		return $this->currentPage() > 0 ? $start + 1 : $start;
	}

	/**
	 * @return int
	 */
	public function end()
	{
		$end = $this->start() + $this->perPage;

		return $this->currentPage() > 0 ? $end - 1 : $end;
	}

	/**
	 * @return bool
	 */
	public function hasMorePages()
	{
		return $this->hasMorePages;
	}

	/**
	 * @param bool $value
	 *
	 * @return $this
	 */
	public function setHasMorePages($value)
	{
		$this->hasMorePages = (boolean) $value;

		return $this;
	}

	/**
	 * @param $totalNumberOfItems
	 *
	 * @return $this
	 */
	public function setTotalNumberOfItems($totalNumberOfItems)
	{
		$this->totalNumberOfItems = $totalNumberOfItems;

		return $this;
	}

	/**
	 * @return int
	 */
	public function totalNumberOfItems()
	{
		return $this->totalNumberOfItems;
	}

	/**
	 * @return bool
	 */
	public function isFirstPage()
	{
		return $this->isPage(1);
	}

	/**
	 * @return bool
	 */
	public function isLastPage()
	{
		return $this->isPage(
			$this->lastPage()
		);
	}

	/**
	 * @param $page
	 *
	 * @return bool
	 */
	public function isPage($page)
	{
		return $this->currentPage() == ($page - 1);
	}

	/**
	 * @return int
	 */
	public function lastPage()
	{
		return (int)ceil($this->totalNumberOfItems / $this->perPage);
	}

	/**
	 * @return null|string
	 */
	public function prevPageLink()
	{
		if ($this->isFirstPage()) {
			return null;
		}

		return $this->pageLink(
			$this->currentPage()
		);
	}

	/**
	 * @param $page
	 *
	 * @return string
	 */
	public function pageLink($page)
	{
		$request = request();

		$url = $request->fullUrlWithQuery([
			$this->pageName => $page,
		]);

		if ($address = $request->route('address_or_hash')) {
			$length = strlen($address);

			if ($length < 32) {
				$url = str_replace('?', str_repeat('/', 32 - $length) . '?', $url);
			}
		}

		return $url;
	}

	/**
	 * @return null|string
	 */
	public function nextPageLink()
	{
		if ($this->isLastPage() || $this->lastPage() === 0) {
			return null;
		}

		return $this->pageLink(
			$this->currentPage() + 2
		);
	}

	/**
	 * @return null|string
	 */
	public function firstPageLink()
	{
		return $this->pageLink(1);
	}

	/**
	 * @return null|string
	 */
	public function lastPageLink()
	{
		return $this->pageLink($this->lastPage());
	}

	/**
	 * @param int $limit
	 *
	 * @return int
	 */
	public function paginationLinksStart($limit = 5)
	{
		if ($this->isFirstPage() || $this->currentPage() < $limit) {
			return 1;
		}

		return $this->currentPage() - $limit + 2;
	}

	/**
	 * @param int $limit
	 *
	 * @return int|mixed
	 */
	public function paginationLinksEnd($limit = 5)
	{
		if ($this->currentPage() < $limit) {
			return min(($limit * 2) - 1, $this->lastPage());
		}

		if ($this->isLastPage()) {
			return $this->lastPage();
		}

		return min($this->currentPage() + $limit, $this->lastPage());
	}

	/**
	 * @param int $limit
	 *
	 * @return bool
	 */
	public function paginationLinksShowingEnd($limit = 5)
	{
		return $this->paginationLinksEnd($limit) == $this->lastPage();
	}

	/**
	 * @param int $limit
	 *
	 * @return bool
	 */
	public function paginationLinksShowingFirst($limit = 5)
	{
		return $this->paginationLinksStart($limit) == 1;
	}

	/**
	 * @return array
	 */
	public function toArray()
	{
		return [
			'current_page' => $this->currentPage(),
			'last_page'	   => $this->lastPage(),
			'total'		   => $this->totalNumberOfItems,
			'per_page'	   => $this->perPage,
			'links'		   => [
				'prev'	=> $this->prevPageLink(),
				'next'	=> $this->nextPageLink(),
				'first' => $this->firstPageLink(),
				'last'	=> $this->lastPageLink(),
			],
		];
	}
}

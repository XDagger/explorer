<div class="bg-grey-lighter text-grey w-full">
	<div class="container px-8 py-2">
		<div class="sm:flex items-center justify-between">
			<div class="text-center mb-2 sm:mb-0 sm:text-right text-sm">
				Copyright &copy; {{ date('Y') }}
			</div>

			<div class="sm:h-8 flex flex-col sm:flex-row items-center justify-center sm:justify-end">
				<a href="/text{{ request()->getPathInfo() }}" rel="nofollow" class="mb-4 sm:mb-0 sm:mr-4 text-grey-dark text-sm">
					Text view
				</a>

				<a href="{{ route('api docs') }}" class="mb-4 sm:mb-0 sm:mr-4 text-grey-dark text-sm">
					API Docs
				</a>

				<a href="https://xdag.io" target="_blank">
					<img src="/images/xdag.png" class="w-8 h-8">
				</a>
			</div>
		</div>
	</div>
</div>

<!-- This page took {{ number_format((microtime(true) - LARAVEL_START), 3) }} seconds to render -->

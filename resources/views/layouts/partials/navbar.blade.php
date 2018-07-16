<nav class="bg-white pt-2 shadow mb-4">
	<div class="container px-8">
		<div class="-mb-px flex justify-center sm:justify-start">
			<a href="{{ route('home') }}" class="no-underline border-b-2 {{ \App\Support\ActiveNavigationLink::checkRoute('home') ? 'text-blue-dark border-blue-dark' : 'text-grey-dark border-transparent' }} uppercase tracking-wide font-bold text-sm py-3 mr-8 flex items-center text-center">
				Home
			</a>

			<a href="{{ route('mining calculator') }}" class="no-underline border-b-2 {{ \App\Support\ActiveNavigationLink::checkRoute('mining calculator') ? 'text-blue-dark border-blue-dark' : 'text-grey-dark border-transparent' }} uppercase tracking-wide font-bold text-sm py-3 mr-8 flex items-center text-center">
				Mining calculator
			</a>

			<a href="{{ route('balance checker') }}" class="no-underline border-b-2 {{ \App\Support\ActiveNavigationLink::checkRoute('balance checker') ? 'text-blue-dark border-blue-dark' : 'text-grey-dark border-transparent' }} uppercase tracking-wide font-bold text-sm py-3 flex items-center text-center">
				Balance checker
			</a>
		</div>
	</div>
</nav>

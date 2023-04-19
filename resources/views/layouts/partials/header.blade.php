<toggle-menu inline-template>
	<div class="bg-blue-darkest w-full fixed z-50 pin-t overflow-hidden shadow-md" id="header">
		<div class="container px-8 py-3 lg:py-0 lg:h-16 relative z-10 flex flex-wrap items-center justify-between relative">
			<a href="{{ route('home') }}" class="w-full lg:w-auto no-underline flex items-center lg:justify-center text-white hover:text-blue-lighter scale transition mb-2 mr-8 tracking-wide">
				<img src="/images/xdag.png" class="w-8 h-8" style="transform: translateZ(0) scale(0.999999)">

				<h1 class="pl-4 text-xl">{{ $appName }}</h1>
			</a>

			<a href="{{ route('mining calculator') }}" :class="{ 'hidden lg:flex': !shown, 'flex': shown }" class="hidden lg:flex w-full lg:w-auto no-underline lg:border-b-2 {{ str_starts_with(request()->path(), 'mining-calculator') ? 'text-white border-blue' : 'text-grey-dark border-transparent' }} hover:text-white tracking-wide font-bold text-sm py-3 lg:mr-8 items-center justify-center">
				<span class="mr-2">
					@svg('calculator')
				</span>

				<span>Mining calculator</span>
			</a>

			<a href="{{ route('balance') }}" :class="{ 'hidden lg:flex': !shown, 'flex': shown }" class="hidden lg:flex w-full lg:w-auto no-underline lg:border-b-2 {{ str_starts_with(request()->path(), 'balance') ? 'text-white border-blue' : 'text-grey-dark border-transparent' }} hover:text-white tracking-wide font-bold text-sm py-3 items-center justify-center">
				<span class="mr-2">
					@svg('wallet')
				</span>

				<span>Balance checker</span>
			</a>

			<form :class="{ 'hidden lg:flex-1 lg:flex': !shown, 'flex w-full': shown }" class="hidden lg:flex-1 lg:flex justify-end" onsubmit="var v = document.getElementById('searchInput').value.trim(); if (v == '') return false; document.location.href = '/block/' + v; return false">
				<div class="flex w-full lg:w-auto xl:w-3/4">
					<input type="text" class="w-full text-md text-grey-darkest bg-white px-4 py-3 rounded-lg rounded-r-none outline-none" placeholder="Search address / block hash / height" id="searchInput">
					<button :class="{ 'small': shown }" type="submit" class="button primary text-md font-bold py-3 px-4 rounded-lg rounded-l-none">
						@svg('search', 'stroke-current w-5 h-5')
					</button>
				</div>
			</form>

			<div class="absolute z-10 pin-r pin-t h-16 px-4 mr-4 text-white lg:hidden flex items-center cursor-pointer" @click="toggleMenu">
				@svg('menu', 'stroke-current w-6 h-6')
			</div>
		</div>
	</div>
</toggle-menu>

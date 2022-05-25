@extends('layouts.app')

@section('body')
	<div class="container p-8">
		<div class="box">
			<h2 class="box-title">Balance checker</h2>

			<balance-checker inline-template>
				<div class="flex flex-wrap">
					<div class="w-full md:w-1/2 md:pr-6 mb-4 md:mb-0">
						<div class="form-group">
							<label for="input" class="form-label">Block</label>

							<input class="form-input" type="text" placeholder="Address / block hash / height" v-model="input">
						</div>
					</div>

					<div class="w-full md:w-1/2 md:pl-6">
						<div :class="{ 'bg-red-lightest border-red text-red-dark': hasError, 'bg-blue-lightest border-blue text-blue-dark': ! hasError }" class="border p-8 rounded shadow text-center" role="alert">

							<p class="font-bold" v-if="! input || (input && ! balance && ! loading && ! error)" v-cloak>Enter address, hash or main block height.</p>
							<p class="font-bold" v-if="input && ! balance && loading" v-cloak>Getting your balance...</p>

							<div v-if="! loading && error" v-cloak>
								<p class="font-bold">@{{ error }}</p>
							</div>

							<div v-if="input && balance && ! loading && ! error" v-cloak>
								<p>Balance on block <strong class="break-words">@{{ input }}</strong> is</p>

								<p class="mt-4 mb-8 text-lg"><strong>@{{ balance }}</strong> XDAG</p>

								<a :href="detailsLink" class="button primary flex items-center justify-center" rel="nofollow">
									<span class="w-4 h-4 mr-2">
										@svg('search', 'stroke-current')
									</span>

									<span>Show details</span>
								</a>
							</div>
						</div>
					</div>
				</div>
			</balance-checker>
		</div>
	</div>
@endsection

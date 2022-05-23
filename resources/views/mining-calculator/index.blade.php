@extends('layouts.app')

@section('body')
	<div class="container p-8">
		<div class="box">
			<h2 class="box-title">Mining calculator</h2>

			<mining-calculator inline-template :network-hashrate="{{ $hashrate }}" :reward="{{ intval($reward) }}">
				<div class="flex flex-wrap">
					<div class="w-full md:w-1/2 md:pr-6 mb-4 md:mb-0">
						<div class="form-group">
							<label for="hashrate" class="form-label">Your Hashrate (Kh/s)</label>

							<input class="form-input" type="text" placeholder="Enter your hashrate" name="hashrate" id="hashrate" v-model="hashrate">
						</div>
					</div>

					<div class="w-full md:w-1/2 md:pl-6">
						<div :class="{ 'bg-red-lightest border-red text-red-dark': hasError, 'bg-blue-lightest border-blue text-blue-dark': ! hasError }" class="border p-8 rounded shadow text-center" role="alert">
							<div v-if="error" v-cloak>
								<p class="font-bold">Invalid hashrate.</p>
							</div>

							<div v-if="! error" v-cloak>
								<p class="font-bold" v-if="! hashrate">Enter your hashrate to calculate mining estimation.</p>
								<p class="font-bold" v-if="hashrate && ! result">Calculating...</p>
								<p class="font-bold" v-if="hashrate && result">Estimated coins per day <strong>@{{ result }}</strong> XDAG</p>
							</div>
						</div>
					</div>
				</div>
			</mining-calculator>
		</div>
	</div>
@endsection

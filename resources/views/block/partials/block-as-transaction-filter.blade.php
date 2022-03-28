<transition name="fade">
	<div v-show="modal" @click.self="toggleModal" class="fixed z-50 pin overflow-auto bg-smoke-dark flex" style="display:none">
		<div class="fixed shadow-inner max-w-md max-h-screen overflow-auto md:relative pin-b pin-x align-top m-auto justify-end md:justify-center bg-white md:rounded w-full md:h-auto md:shadow flex flex-col">

			<form action="{{ request()->getPathInfo() }}#block-as-transaction" method="GET" class="overflow-auto">
				<input type="hidden" name="transaction-filter" value="1">

				<div class="p-8">
					<div class="form-group">
						<label for="transaction_address" class="form-label">Address</label>

						<input class="form-input {{ $transactionFiltersValidation->errors()->has('address') ? 'has-error' : '' }}" type="text" placeholder="Enter address" name="transaction_address" id="transaction_address" value="{{ $transactionFilters->address }}">

						@if ($transactionFiltersValidation->errors()->has('transaction_address'))
							<p class="error-text">{{ $transactionFiltersValidation->firstError('transaction_address') }}</p>
						@endif
					</div>

					<div class="flex flex-wrap">
						<div class="w-full md:w-1/2 md:pr-2">
							<div class="form-group">
								<label for="transaction_amount_from" class="form-label">Amount from</label>

								<input class="form-input {{ $transactionFiltersValidation->errors()->has('transaction_amount.from') ? 'has-error' : '' }}" type="text" placeholder="Enter amount" name="transaction_amount[from]" id="transaction_amount_from" value="{{ $transactionFilters->amountFrom }}">

								@if ($transactionFiltersValidation->errors()->has('transaction_amount.from'))
									<p class="error-text">{{ $transactionFiltersValidation->firstError('transaction_amount.from') }}</p>
								@endif
							</div>
						</div>

						<div class="w-full md:w-1/2 md:pl-2">
							<div class="form-group">
								<label for="transaction_amount_to" class="form-label">Amount to</label>

								<input class="form-input {{ $transactionFiltersValidation->errors()->has('transaction_amount.to') ? 'has-error' : '' }}" type="text" placeholder="Enter amount" name="transaction_amount[to]" id="transaction_amount_to" value="{{ $transactionFilters->amountTo }}">

								@if ($transactionFiltersValidation->errors()->has('transaction_amount.to'))
									<p class="error-text">{{ $transactionFiltersValidation->firstError('transaction_amount.to') }}</p>
								@endif
							</div>
						</div>
					</div>

					<div class="form-group">
						<div class="form-label">Direction</div>

						<div class="flex flex-wrap">
							<checkbox inline-template :checked="{{ in_array('fee', $transactionFilters->directions) ? 1 : 0 }}">
								<div class="mb-4 md:mb-0 w-full md:w-1/4">
									<input type="checkbox" class="checkbox-input hidden" name="transaction_directions[]" value="fee">

									<div class="cursor-pointer w-full flex items-center" @click="toggle()" :class="{ 'text-blue': isChecked }">
										<div class="form-checkbox flex items-center justify-center mr-2" :class="{ 'text-blue': isChecked, 'text-transparent': !isChecked }">
											@svg('check', 'stroke-current')
										</div>

										<div class="font-medium">Fee</div>
									</div>
								</div>
							</checkbox>

							<checkbox inline-template :checked="{{ in_array('input', $transactionFilters->directions) ? 1 : 0 }}">
								<div class="mb-4 md:mb-0 w-full md:w-1/4">
									<input type="checkbox" class="checkbox-input hidden" name="transaction_directions[]" value="input">

									<div class="cursor-pointer w-full flex items-center" @click="toggle()" :class="{ 'text-blue': isChecked }">
										<div class="form-checkbox flex items-center justify-center mr-2" :class="{ 'text-blue': isChecked, 'text-transparent': !isChecked }">
											@svg('check', 'stroke-current')
										</div>

										<div class="font-medium">Input</div>
									</div>
								</div>
							</checkbox>

							<checkbox inline-template :checked="{{ in_array('output', $transactionFilters->directions) ? 1 : 0 }}">
								<div class="w-full md:w-1/4">
									<input type="checkbox" class="checkbox-input hidden" name="transaction_directions[]" value="output">

									<div class="cursor-pointer w-full flex items-center" @click="toggle()" :class="{ 'text-blue': isChecked }">
										<div class="form-checkbox flex items-center justify-center mr-2" :class="{ 'text-blue': isChecked, 'text-transparent': !isChecked }">
											@svg('check', 'stroke-current')
										</div>

										<div class="font-medium">Output</div>
									</div>
								</div>
							</checkbox>
						</div>

						@if ($transactionFiltersValidation->errors()->has('transaction_directions'))
							<p class="error-text">{{ $transactionFiltersValidation->firstError('transaction_directions') }}</p>
						@endif
					</div>
				</div>

				<div class="bg-grey-lighter p-4 rounded-b">
					<div class="md:flex flex-wrap items-center justify-end w-full">
						<button type="submit" class="button primary block w-full md:w-auto text-base mb-2 md:mb-0">Apply filters</button>

						<a @click="toggleModal" class="button link text-base block w-full md:w-auto">Close</a>
					</div>
				</div>
			</form>

			<span @click="toggleModal" class="absolute pin-t pin-r pt-4 px-4 text-grey hover:text-grey-darkest cursor-pointer">
				@svg('x', 'w-6 h-6 fill-current')
			</span>
		</div>
	</div>
</transition>

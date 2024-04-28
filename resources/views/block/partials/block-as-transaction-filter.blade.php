<transition name="fade">
	<div v-show="modal" @click.self="toggleModal" class="fixed z-50 pin overflow-auto bg-smoke-dark flex" style="display:none">
		<div class="fixed shadow-inner max-w-md max-h-screen overflow-auto md:relative pin-b pin-x align-top m-auto justify-end md:justify-center bg-white md:rounded w-full md:h-auto md:shadow flex flex-col">

			<form action="{{ request()->getPathInfo() }}#block-as-transaction" method="GET" class="overflow-auto">
				<input type="hidden" name="r" value="{{ rand(1, 1000000) }}">

				<div class="p-8">
					<div class="form-group">
						<label for="transactions_address" class="form-label">Address</label>

						<input class="form-input {{ isset($errors['transactions_address']) ? 'has-error' : '' }}" type="text" placeholder="Enter address" name="transactions_address" id="transactions_address" value="{{ $filters['transactions_address']['value'] ?? '' }}">

						@if (isset($errors['transactions_address']))
							<p class="error-text">{{ $errors['transactions_address'] }}</p>
						@endif
					</div>

					<div class="flex flex-wrap">
						<div class="w-full md:w-1/2 md:pr-2">
							<div class="form-group">
								<label for="transactions_amount_from" class="form-label">Amount from</label>

								<input class="form-input {{ isset($errors['transactions_amount_from']) ? 'has-error' : '' }}" type="text" placeholder="Enter amount" name="transactions_amount_from" id="transactions_amount_from" value="{{ $filters['transactions_amount_from']['value'] ?? '' }}">

								@if (isset($errors['transactions_amount_from']))
									<p class="error-text">{{ $errors['transactions_amount_from'] }}</p>
								@endif
							</div>
						</div>

						<div class="w-full md:w-1/2 md:pl-2">
							<div class="form-group">
								<label for="transactions_amount_to" class="form-label">Amount to</label>

								<input class="form-input {{ isset($errors['transactions_amount_to']) ? 'has-error' : '' }}" type="text" placeholder="Enter amount" name="transactions_amount_to" id="transactions_amount_to" value="{{ $filters['transactions_amount_to']['value'] ?? '' }}">

								@if (isset($errors['transactions_amount_to']))
									<p class="error-text">{{ $errors['transactions_amount_to'] }}</p>
								@endif
							</div>
						</div>
					</div>

					<div class="form-group">
						<div class="form-label">Direction</div>

						<div class="flex flex-wrap">
							<checkbox inline-template :checked="{{ in_array('fee', $filters['transactions_directions']['value'] ?? []) ? 'true' : 'false' }}">
								<div class="mb-4 md:mb-0 w-full md:w-1/4">
									<input type="checkbox" class="checkbox-input hidden" name="transactions_directions[]" value="fee">

									<div class="cursor-pointer w-full flex items-center" @click="toggle()" :class="{ 'text-blue': isChecked }">
										<div class="form-checkbox flex items-center justify-center mr-2" :class="{ 'text-blue': isChecked, 'text-transparent': !isChecked }">
											@svg('check', 'stroke-current')
										</div>

										<div class="font-medium">Fee</div>
									</div>
								</div>
							</checkbox>

							<checkbox inline-template :checked="{{ in_array('input', $filters['transactions_directions']['value'] ?? []) ? 'true' : 'false' }}">
								<div class="mb-4 md:mb-0 w-full md:w-1/4">
									<input type="checkbox" class="checkbox-input hidden" name="transactions_directions[]" value="input">

									<div class="cursor-pointer w-full flex items-center" @click="toggle()" :class="{ 'text-blue': isChecked }">
										<div class="form-checkbox flex items-center justify-center mr-2" :class="{ 'text-blue': isChecked, 'text-transparent': !isChecked }">
											@svg('check', 'stroke-current')
										</div>

										<div class="font-medium">Input</div>
									</div>
								</div>
							</checkbox>

							<checkbox inline-template :checked="{{ in_array('output', $filters['transactions_directions']['value'] ?? []) ? 'true' : 'false' }}">
								<div class="w-full md:w-1/4">
									<input type="checkbox" class="checkbox-input hidden" name="transactions_directions[]" value="output">

									<div class="cursor-pointer w-full flex items-center" @click="toggle()" :class="{ 'text-blue': isChecked }">
										<div class="form-checkbox flex items-center justify-center mr-2" :class="{ 'text-blue': isChecked, 'text-transparent': !isChecked }">
											@svg('check', 'stroke-current')
										</div>

										<div class="font-medium">Output</div>
									</div>
								</div>
							</checkbox>
						</div>

						@if (isset($errors['transactions_directions']))
							<p class="error-text">{{ $errors['transactions_directions'] }}</p>
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

<transition name="fade">
	<div v-show="modal" @click.self="toggleModal" class="fixed z-50 pin overflow-auto bg-smoke-dark flex" style="display:none">
		<div class="fixed shadow-inner max-w-md max-h-screen md:relative pin-b pin-x align-top m-auto justify-end md:justify-center bg-white md:rounded w-full md:h-auto md:shadow flex flex-col overflow-auto md:overflow-visible">

			<form action="{{ request()->getPathInfo() }}#block-as-address" method="GET" class="overflow-auto md:overflow-visible">
				<input type="hidden" name="address-filter" value="1">

				<div class="p-8 pb-0">
					<div class="flex flex-wrap">
						<div class="w-full">
							<div class="form-group">
								<label for="address" class="form-label">Address</label>

								<input class="form-input {{ $addressFiltersValidation->errors()->has('address') ? 'has-error' : '' }}" type="text" placeholder="Enter address" name="address" id="address" value="{{ $addressFilters->address }}">

								@if ($addressFiltersValidation->errors()->has('address'))
									<p class="error-text">{{ $addressFiltersValidation->firstError('address') }}</p>
								@endif
							</div>
						</div>

						<date-range inline-template
									@if (! is_null($addressFilters->dateFrom))
									default-from="{{ $addressFilters->dateFrom }}"
									@endif

									@if (! is_null($addressFilters->dateTo))
									default-to="{{ $addressFilters->dateTo }}"
									@endif
						>
							<div class="w-full">
								<div class="flex flex-wrap">
									<div class="w-full md:w-1/2 md:pr-1">
										<div class="form-group">
											<label for="date_from" class="form-label">Date from</label>

											<calendar-dropdown inline-template :value="from" v-on:change="updateFrom">
												<div class="form-group relative" v-click-outside="hideCalendar" @click.stop>
													<input class="form-input {{ $addressFiltersValidation->errors()->has('date.from') ? 'has-error' : '' }}" type="text" placeholder="Enter date from" name="date[from]" id="date_from" v-model="inputValue" @focus="showCalendar">

													<calendar class="absolute mt-2" v-if="shown" :default="value" v-on:change="updateValue"></calendar>
												</div>
											</calendar-dropdown>

											@if ($addressFiltersValidation->errors()->has('date.from'))
												<p class="error-text">{{ $addressFiltersValidation->firstError('date.from') }}</p>
											@endif
										</div>
									</div>

									<div class="w-full md:w-1/2 pl-1">
										<div class="form-group">
											<label for="date_to" class="form-label">Date to</label>

											<calendar-dropdown inline-template :value="to" v-on:change="updateTo">
												<div class="form-group relative" v-click-outside="hideCalendar" @click.stop>
													<input class="form-input {{ $addressFiltersValidation->errors()->has('date.to') ? 'has-error' : '' }}" type="text" placeholder="Enter date to" name="date[to]" id="date_to" v-model="inputValue" @focus="showCalendar">

													<calendar class="absolute mt-2" v-if="shown" :default="value" v-on:change="updateValue"></calendar>
												</div>
											</calendar-dropdown>

											@if ($addressFiltersValidation->errors()->has('date.to'))
												<p class="error-text">{{ $addressFiltersValidation->firstError('date.to') }}</p>
											@endif
										</div>
									</div>
								</div>

								<div class="mb-4">
									<div class="flex flex-wrap -mx-2">
										<div class="w-full md:w-1/2 lg:w-1/4 p-2">
											<button type="button" class="button secondary small w-full" @click="lastDay">Last day</button>
										</div>
										<div class="w-full md:w-1/2 lg:w-1/4 p-2">
											<button type="button" class="button secondary small w-full" @click="lastWeek">Last week</button>
										</div>
										<div class="w-full md:w-1/2 lg:w-1/4 p-2">
											<button type="button" class="button secondary small w-full" @click="lastMonth">Last month</button>
										</div>
										<div class="w-full md:w-1/2 lg:w-1/4 p-2">
											<button type="button" class="button secondary small w-full" @click="lastYear">Last year</button>
										</div>
									</div>
								</div>
							</div>
						</date-range>

						<div class="w-full">
							<div class="flex flex-wrap">
								<div class="w-full md:w-1/2 md:pr-1">
									<div class="form-group">
										<label for="amount_from" class="form-label">Amount from</label>

										<input class="form-input {{ $addressFiltersValidation->errors()->has('amount.from') ? 'has-error' : '' }}" type="text" placeholder="Enter amount" name="amount[from]" id="amount_from" value="{{ $addressFilters->amountFrom }}">

										@if ($addressFiltersValidation->errors()->has('amount.from'))
											<p class="error-text">{{ $addressFiltersValidation->firstError('amount.from') }}</p>
										@endif
									</div>
								</div>

								<div class="w-full md:w-1/2 md:pl-1">
									<div class="form-group">
										<label for="amount_to" class="form-label">Amount to</label>

										<input class="form-input {{ $addressFiltersValidation->errors()->has('amount.to') ? 'has-error' : '' }}" type="text" placeholder="Enter amount" name="amount[to]" id="amount_to" value="{{ $addressFilters->amountTo }}">

										@if ($addressFiltersValidation->errors()->has('amount.to'))
											<p class="error-text">{{ $addressFiltersValidation->firstError('amount.to') }}</p>
										@endif
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="form-group">
						<div class="form-label">Direction</div>

						<div class="flex flex-wrap w-full">
							<checkbox inline-template :checked="{{ in_array('fee', $addressFilters->directions) ? 1 : 0 }}">
								<div class="mb-4 w-full sm:w-1/2 md:w-1/4">
									<input type="checkbox" class="checkbox-input hidden" name="directions[]" value="fee">

									<div class="cursor-pointer w-full flex items-center" @click="toggle()" :class="{ 'text-blue': isChecked }">
										<div class="form-checkbox flex items-center justify-center mr-2" :class="{ 'text-blue': isChecked, 'text-transparent': !isChecked }">
											@svg('check', 'stroke-current')
										</div>

										<div class="font-medium">Fee</div>
									</div>
								</div>
							</checkbox>

							<checkbox inline-template :checked="{{ in_array('input', $addressFilters->directions) ? 1 : 0 }}">
								<div class="mb-4 w-full sm:w-1/2 md:w-1/4">
									<input type="checkbox" class="checkbox-input hidden" name="directions[]" value="input">

									<div class="cursor-pointer w-full flex items-center" @click="toggle()" :class="{ 'text-blue': isChecked }">
										<div class="form-checkbox flex items-center justify-center mr-2" :class="{ 'text-blue': isChecked, 'text-transparent': !isChecked }">
											@svg('check', 'stroke-current')
										</div>

										<div class="font-medium">Input</div>
									</div>
								</div>
							</checkbox>

							<checkbox inline-template :checked="{{ in_array('output', $addressFilters->directions) ? 1 : 0 }}">
								<div class="mb-4 w-full sm:w-1/2 md:w-1/4">
									<input type="checkbox" class="checkbox-input hidden" name="directions[]" value="output">

									<div class="cursor-pointer w-full flex items-center" @click="toggle()" :class="{ 'text-blue': isChecked }">
										<div class="form-checkbox flex items-center justify-center mr-2" :class="{ 'text-blue': isChecked, 'text-transparent': !isChecked }">
											@svg('check', 'stroke-current')
										</div>

										<div class="font-medium">Output</div>
									</div>
								</div>
							</checkbox>

							<checkbox inline-template :checked="{{ in_array('earning', $addressFilters->directions) ? 1 : 0 }}">
								<div class="sm:mb-4 w-full sm:w-1/2 md:w-1/4">
									<input type="checkbox" class="checkbox-input hidden" name="directions[]" value="earning">

									<div class="cursor-pointer w-full flex items-center" @click="toggle()" :class="{ 'text-blue': isChecked }">
										<div class="form-checkbox flex items-center justify-center mr-2" :class="{ 'text-blue': isChecked, 'text-transparent': !isChecked }">
											@svg('check', 'stroke-current')
										</div>

										<div class="font-medium">Earning</div>
									</div>
								</div>
							</checkbox>
						</div>

						@if ($addressFiltersValidation->errors()->has('directions'))
							<p class="error-text">{{ $addressFiltersValidation->firstError('directions') }}</p>
						@endif

						<div class="w-full">
							<div class="form-group">
								<label for="address" class="form-label">Remark</label>

								<input class="form-input {{ $addressFiltersValidation->errors()->has('remark') ? 'has-error' : '' }}" type="text" placeholder="Remark" name="remark" id="remark" value="{{ $addressFilters->remark }}">

								@if ($addressFiltersValidation->errors()->has('remark'))
									<p class="error-text">{{ $addressFiltersValidation->firstError('remark') }}</p>
								@endif
							</div>
						</div>
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
				@svg('x', ['class' => 'w-6 h-6 fill-current'])
			</span>
		</div>
	</div>
</transition>

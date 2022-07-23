<transition name="fade">
	<div v-show="modal" @click.self="toggleModal" class="fixed z-50 pin overflow-auto bg-smoke-dark flex" style="display:none">
		<div class="fixed shadow-inner max-w-md max-h-screen md:relative pin-b pin-x align-top m-auto justify-end md:justify-center bg-white md:rounded w-full md:h-auto md:shadow flex flex-col overflow-auto md:overflow-visible">

			<form action="{{ request()->getPathInfo() }}#block-as-address" method="GET" class="overflow-auto md:overflow-visible">
				<input type="hidden" name="r" value="{{ rand(1, 1000000) }}">

				<div class="p-8 pb-0">
					<div class="flex flex-wrap">
						<div class="w-full">
							<div class="form-group">
								<label for="addresses_address" class="form-label">Address</label>

								<input class="form-input {{ isset($errors['addresses_address']) ? 'has-error' : '' }}" type="text" placeholder="Enter address" name="addresses_address" id="addresses_address" value="{{ $filters['addresses_address']['value'] ?? '' }}">

								@if (isset($errors['addresses_address']))
									<p class="error-text">{{ $errors['addresses_address'] }}</p>
								@endif
							</div>
						</div>

						<date-range inline-template
							@if (isset($filters['addresses_date_from']['value']))
								default-from="{{ $filters['addresses_date_from']['value'] }}"
							@endif

							@if (isset($filters['addresses_date_to']['value']))
								default-to="{{ $filters['addresses_date_to']['value'] }}"
							@endif
						>
							<div class="w-full">
								<div class="flex flex-wrap">
									<div class="w-full md:w-1/2 md:pr-1">
										<div class="form-group">
											<label for="addresses_date_from" class="form-label">Date from</label>

											<calendar-dropdown inline-template :value="from" v-on:change="updateFrom">
												<div class="form-group relative" v-click-outside="hideCalendar" @click.stop>
													<input class="form-input {{ isset($errors['addresses_date_from']) ? 'has-error' : '' }}" type="text" placeholder="Enter date from" name="addresses_date_from" id="addresses_date_from" v-model="inputValue" @focus="showCalendar">

													<calendar class="absolute mt-2" v-if="shown" :default="value" v-on:change="updateValue"></calendar>
												</div>
											</calendar-dropdown>

											@if (isset($errors['addresses_date_from']))
												<p class="error-text">{{ $errors['addresses_date_from'] }}</p>
											@endif
										</div>
									</div>

									<div class="w-full md:w-1/2 pl-1">
										<div class="form-group">
											<label for="addresses_date_to" class="form-label">Date to</label>

											<calendar-dropdown inline-template :value="to" v-on:change="updateTo">
												<div class="form-group relative" v-click-outside="hideCalendar" @click.stop>
													<input class="form-input {{ isset($errors['addresses_date_to']) ? 'has-error' : '' }}" type="text" placeholder="Enter date to" name="addresses_date_to" id="addresses_date_to" v-model="inputValue" @focus="showCalendar">

													<calendar class="absolute mt-2" v-if="shown" :default="value" v-on:change="updateValue"></calendar>
												</div>
											</calendar-dropdown>

											@if (isset($errors['addresses_date_to']))
												<p class="error-text">{{ $errors['addresses_date_to'] }}</p>
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
										<label for="addresses_amount_from" class="form-label">Amount from</label>

										<input class="form-input {{ isset($errors['addresses_amount_from']) ? 'has-error' : '' }}" type="text" placeholder="Enter amount" name="addresses_amount_from" id="addresses_amount_from" value="{{ $filters['addresses_amount_from']['value'] ?? '' }}">

										@if (isset($errors['addresses_amount_from']))
											<p class="error-text">{{ $errors['addresses_amount_from'] }}</p>
										@endif
									</div>
								</div>

								<div class="w-full md:w-1/2 md:pl-1">
									<div class="form-group">
										<label for="addresses_amount_to" class="form-label">Amount to</label>

										<input class="form-input {{ isset($errors['addresses_amount_to']) ? 'has-error' : '' }}" type="text" placeholder="Enter amount" name="addresses_amount_to" id="addresses_amount_to" value="{{ $filters['addresses_amount_to']['value'] ?? '' }}">

										@if (isset($errors['addresses_amount_to']))
											<p class="error-text">{{ $errors['addresses_amount_to'] }}</p>
										@endif
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="form-group">
						<div class="form-label">Direction</div>

						<div class="flex flex-wrap w-full">
							<checkbox inline-template :checked="{{ in_array('input', $filters['addresses_directions']['value'] ?? []) ? 'true' : 'false' }}">
								<div class="mb-4 w-full sm:w-1/2 md:w-1/4">
									<input type="checkbox" class="checkbox-input hidden" name="addresses_directions[]" value="input">

									<div class="cursor-pointer w-full flex items-center" @click="toggle()" :class="{ 'text-blue': isChecked }">
										<div class="form-checkbox flex items-center justify-center mr-2" :class="{ 'text-blue': isChecked, 'text-transparent': !isChecked }">
											@svg('check', 'stroke-current')
										</div>

										<div class="font-medium">Input</div>
									</div>
								</div>
							</checkbox>

							<checkbox inline-template :checked="{{ in_array('output', $filters['addresses_directions']['value'] ?? []) ? 'true' : 'false' }}">
								<div class="mb-4 w-full sm:w-1/2 md:w-1/4">
									<input type="checkbox" class="checkbox-input hidden" name="addresses_directions[]" value="output">

									<div class="cursor-pointer w-full flex items-center" @click="toggle()" :class="{ 'text-blue': isChecked }">
										<div class="form-checkbox flex items-center justify-center mr-2" :class="{ 'text-blue': isChecked, 'text-transparent': !isChecked }">
											@svg('check', 'stroke-current')
										</div>

										<div class="font-medium">Output</div>
									</div>
								</div>
							</checkbox>

							<checkbox inline-template :checked="{{ in_array('earning', $filters['addresses_directions']['value'] ?? []) ? 'true' : 'false' }}">
								<div class="sm:mb-4 w-full sm:w-1/2 md:w-1/4">
									<input type="checkbox" class="checkbox-input hidden" name="addresses_directions[]" value="earning">

									<div class="cursor-pointer w-full flex items-center" @click="toggle()" :class="{ 'text-blue': isChecked }">
										<div class="form-checkbox flex items-center justify-center mr-2" :class="{ 'text-blue': isChecked, 'text-transparent': !isChecked }">
											@svg('check', 'stroke-current')
										</div>

										<div class="font-medium">Earning</div>
									</div>
								</div>
							</checkbox>

							<checkbox inline-template :checked="{{ in_array('snapshot', $filters['addresses_directions']['value'] ?? []) ? 'true' : 'false' }}">
								<div class="sm:mb-4 w-full sm:w-1/2 md:w-1/4">
									<input type="checkbox" class="checkbox-input hidden" name="addresses_directions[]" value="snapshot">

									<div class="cursor-pointer w-full flex items-center" @click="toggle()" :class="{ 'text-blue': isChecked }">
										<div class="form-checkbox flex items-center justify-center mr-2" :class="{ 'text-blue': isChecked, 'text-transparent': !isChecked }">
											@svg('check', 'stroke-current')
										</div>

										<div class="font-medium">Snapshot</div>
									</div>
								</div>
							</checkbox>
						</div>

						@if (isset($errors['addresses_directions']))
							<p class="error-text">{{ $errors['addresses_directions'] }}</p>
						@endif

						<div class="w-full">
							<div class="form-group">
								<label for="addresses_remark" class="form-label">Remark</label>

								<input class="form-input {{ isset($errors['addresses_remark']) ? 'has-error' : '' }}" type="text" placeholder="Remark" name="addresses_remark" id="addresses_remark" value="{{ $filters['addresses_remark']['value'] ?? '' }}">

								@if (isset($errors['addresses_remark']))
									<p class="error-text">{{ $errors['addresses_remark'] }}</p>
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
				@svg('x', 'w-6 h-6 fill-current')
			</span>
		</div>
	</div>
</transition>

<transition name="fade">
	<div v-show="modal" @click.self="toggleModal" class="fixed z-50 pin overflow-auto bg-smoke-dark flex" style="display:none">
		<div class="fixed shadow-inner max-w-md relative pin-b pin-x align-top m-auto justify-end md:justify-center p-8 bg-white md:rounded w-full h-auto md:shadow flex flex-col">

			<div>
				<h2 class="text-black text-xl font-bold">Network hashrate</h2>
				<div class="text-grey-dark text-base font-normal mb-8">Last {{ \App\Modules\Network\Network::DAYS_LIMIT }} days</div>

				<single-line-chart chart-name="Network Hashrate Th/s"
					color="blue"
					:class="'w-full h-48'"
					:labels='{!! $hashrate_chart['days'] !!}'
					:chart-data='{!! $hashrate_chart['data'] !!}'>
				</single-line-chart>


				<h2 class="text-black text-xl font-bold mt-8">New blocks</h2>
				<div class="text-grey-dark text-base font-normal mb-8">Last hour</div>

				<single-bar-chart chart-name="New blocks"
					color="indigo"
					:class="'w-full h-48'"
					:labels='{!! $blocks_chart['hours'] !!}'
					:chart-data='{!! $blocks_chart['data'] !!}'>
				</single-bar-chart>
			</div>

			<span @click="toggleModal" class="absolute pin-t pin-r pt-4 px-4 text-grey hover:text-grey-darkest cursor-pointer">
				@svg('x', ['class' => 'w-6 h-6 fill-current'])
			</span>
		</div>
	</div>
</transition>

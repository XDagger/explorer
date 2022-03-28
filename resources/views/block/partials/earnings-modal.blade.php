<transition name="fade">
	<div v-show="modal" @click.self="toggleModal" class="fixed z-50 pin overflow-auto bg-smoke-dark flex" style="display:none">
		<div class="fixed shadow-inner max-w-md relative pin-b pin-x align-top m-auto justify-center p-8 bg-white rounded w-full h-auto shadow flex flex-col">

			<h2 class="text-black text-xl font-bold">Address earnings</h2>
			<div class="text-grey-dark text-base font-normal mb-8">Last week</div>

			<single-bar-chart
				chart-name="Earnings"
				color="green"
				:class="'w-full h-48'"
				:labels='{!! $block->getEarnings()->keys()->toJson(JSON_HEX_APOS) !!}'
				:chart-data='{!! $block->getEarnings()->values()->toJson(JSON_HEX_APOS) !!}'
				integers="true"
			>
			</single-bar-chart>

			<span @click="toggleModal" class="absolute pin-t pin-r pt-4 px-4 text-grey hover:text-grey-darkest cursor-pointer">
				@svg('x', 'w-6 h-6 fill-current')
			</span>
		</div>
	</div>
</transition>

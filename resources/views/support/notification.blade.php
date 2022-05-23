<notification inline-template>
	<transition name="fade">
		<div class="container p-8 pb-0" v-if="showNotification">
			<div class="notification {{ $type }}">
				<div class="w-4 h-4 mr-2">
					@if ($type == 'success')
						@svg('check', 'stroke-current')
					@elseif ($type == 'info')
						@svg('info', 'stroke-current')
					@else
						@svg('alert-triangle', 'stroke-current')
					@endif
				</div>

				<p class="m-0 font-medium mr-2">{!! $text !!}</p>

				<div class="ml-auto" @click="hideNotification">
					<span class="w-4 h-4 cursor-pointer">
						@svg('x', 'fill-current')
					</span>
				</div>
			</div>
		</div>
	</transition>
</notification>
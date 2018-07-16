<notification inline-template>
	<transition name="fade">
		<div class="container p-8 pb-0" v-if="showNotification">
			<div class="notification {{ $type }}">
				<div class="w-4 h-4 mr-2">
					{!! svg_image($icon, $iconClass ?? '')->toHtml() !!}
				</div>

				<p class="m-0 font-medium mr-2">{!! $text !!}</p>

				<div class="ml-auto">
					<span class="w-4 h-4 cursor-pointer" @click="hideNotification">
						@svg('x', 'fill-current')
					</span>
				</div>
			</div>
		</div>
	</transition>
</notification>
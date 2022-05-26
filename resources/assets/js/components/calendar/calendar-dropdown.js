export default {
	props: {
		format: {
			type: String,
			default: 'YYYY-MM-DD'
		},
		value: {
			default: () => null
		},
	},

	data() {
		return {
			shown: false,
			inputValue: null
		}
	},

	watch: {
		value(val) {
			this.inputValue = val ? val.format(this.format) : null
		}
	},

	mounted() {
		this.inputValue = this.value ? this.value.format(this.format) : null
	},

	methods: {
		toggleCalendar() {
			this.shown = !this.shown
		},

		showCalendar() {
			this.shown = true
		},

		hideCalendar() {
			this.shown = false
		},

		updateValue(value) {
			this.$emit('change', value)
		}
	},
}

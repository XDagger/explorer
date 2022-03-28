export default {
	props: {
		delay: {
			default: 5000
		}
	},
	data() {
		return {
			showNotification: true
		}
	},

	mounted() {
		_.delay(() => { this.hideNotification() }, this.delay)
	},

	methods: {
		hideNotification() {
			this.showNotification = false
		}
	}
}

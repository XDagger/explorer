export default {
	data() {
		return {
			input: null,
			balance: null,
			loading: false,
			error: false
		}
	},
	watch: {
		input: function () {
			this.balance = null
			this.error = null
			this.debouncedGetBlockBalance()
		}
	},
	created: function () {
		this.debouncedGetBlockBalance = _.debounce(this.getBalance, 500)
	},
	computed: {
		hasError() {
			return ! this.loading && this.error
		},

		detailsLink() {
			return '/block/' + this.input.trim()
		}
	},
	methods: {
		getBalance() {
			if (! this.input) {
				this.error = null
				this.loading = null

				return
			}

			this.loading = true

			axios.get('/api/balance/' + this.input)
				.then((response) => {
					this.balance = response.data.balance
					this.loading = false
				})
				.catch((error) => {
					this.error = error.response.data.message
					this.loading = false
				})
		}
	}
}

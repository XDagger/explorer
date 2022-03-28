const MAX_BLOCKS_PER_DAY_ON_NETWORK = (3600 * 24) / 64;

export default {
	props: {
		networkHashrate: {
			default: 1
		},
		reward: {
			default: 1024
		}
	},

	data() {
		return {
			hashrate: 5,
			result: null,
			error: false
		}
	},

	watch: {
		hashrate: function () {
			this.result = null
			this.debouncedGetResults()
		}
	},

	created: function () {
		this.calculateResult()
		this.debouncedGetResults = _.debounce(this.calculateResult, 500)
	},

	computed: {
		hashrateInHs() {
			return Math.abs(this.hashrate * 1024)
		},

		hasError() {
			return this.error
		}
	},

	methods: {
		calculateResult() {
			if (! this.hashrate) {
				this.error = false
				this.result = 0

				return
			}

			if (!this.isNumeric(this.hashrate)) {
				this.error = true
				this.result = 0
			} else {
				this.error = false
				this.result = (this.hashrateInHs * MAX_BLOCKS_PER_DAY_ON_NETWORK * this.reward / (this.networkHashrate + this.hashrateInHs)).toFixed(9)
			}
		},

		isNumeric(value) {
			return !isNaN(parseFloat(value)) && isFinite(value)
		}
	}
}

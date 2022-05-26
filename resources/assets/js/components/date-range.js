export default {
	props: {
		defaultFrom: {
			type: String,
			default: () => null
		},

		defaultTo: {
			type: String,
			default: () => null
		},

		format: {
			type: String,
			default: 'YYYY-MM-DD'
		}
	},

	data() {
		return {
			from: null,
			to: null
		}
	},

	created() {
		if (this.defaultFrom && this.defaultFrom.length) {
			this.from = moment.utc(this.defaultFrom, this.format)
		}

		if (this.defaultTo && this.defaultTo.length) {
			this.to = moment.utc(this.defaultTo, this.format)
		}
	},

	methods: {
		lastDay() {
			this.from = moment().utc()
			this.to = moment().utc()
		},

		lastWeek() {
			this.from = moment().utc().subtract(1, 'week')
			this.to = moment().utc()
		},

		lastMonth() {
			this.from = moment().utc().subtract(1, 'months')
			this.to = moment().utc()
		},

		lastYear() {
			this.from = moment().utc().subtract(1, 'years')
			this.to = moment().utc()
		},

		updateFrom(value) {
			this.from = value
		},

		updateTo(value) {
			this.to = value
		}
	}
}

<template>
	<div class="calendar">
		<div class="calendar-header">
			<div class="top">
				<div class="prev-month" @click="prevMonth">◄</div>
				<div class="calendar-title">
					{{ monthInYear().format('MMMM YYYY') }}
				</div>
				<div class="next-month" @click="nextMonth">►</div>
			</div>

			<table width="100%" cellspacing="0" cellpadding="0" border="0" class="day-names">
				<tbody>
				<tr>
					<td>{{ moment.weekdaysMin(1, true).toLowerCase() }}</td>
					<td>{{ moment.weekdaysMin(2, true).toLowerCase() }}</td>
					<td>{{ moment.weekdaysMin(3, true).toLowerCase() }}</td>
					<td>{{ moment.weekdaysMin(4, true).toLowerCase() }}</td>
					<td>{{ moment.weekdaysMin(5, true).toLowerCase() }}</td>
					<td>{{ moment.weekdaysMin(6, true).toLowerCase() }}</td>
					<td>{{ moment.weekdaysMin(7, true).toLowerCase() }}</td>
				</tr>
				</tbody>
			</table>
		</div>

		<table width="100%" cellspacing="0" cellpadding="0" border="0" class="days">
			<tbody>
			<tr v-for="week in weeks()">
				<td v-for="day in week" @click="selectDay(day)" class="calendar-day" :class="{ 'gray': day.month() != month, 'disabled': (dayIsDisabled(day) || day.diff(minDate, 'days') < 0), 'today': day.format('DD-MM-YYYY') == moment().utc().format('DD-MM-YYYY'), 'selected': (current && day.format('DD-MM-YYYY') == current.format('DD-MM-YYYY')) }">
					{{ day.format(dateStringFormat) }}
				</td>
			</tr>
			</tbody>
		</table>
	</div>
</template>

<script>
	export default {
		props: {
			disabledDays: {
				default: () => []
			},
			default: {
				default: () => moment().utc()
			},
			minDate: {
				default: null
			},
			dateStringFormat: {
				default: 'D'
			}
		},

		data() {
			let moment = this.default ? this.default : this.moment().utc()

			return {
				current: moment,
				year: moment.year(),
				month: moment.month()
			}
		},

		methods: {
			dayIsDisabled(day) {
				let disabled = _.find(this.disabledDays, (d) => {
					return d.format('DD-MM-YYYY') == day.format('DD-MM-YYYY')
				})

				return disabled !== undefined
			},

			monthInYear() {
				return moment.utc([this.year, this.month])
			},

			weeks() {
				let monthRange = moment.range(
					this.monthInYear().startOf('month'),
					this.monthInYear().endOf('month')
				)

				let weeks = Array.from(
					monthRange.by('weeks')
				)

				return _.map(weeks, (week, index) => {
					let weekStart = week.clone()

					// Switched years
					if (index > 0 && week < weeks[index - 1]) {
						weekStart = weekStart.add(1, 'year')
					}

					weekStart = weekStart.day(1)

					return Array.from(
						moment.range(weekStart, weekStart.clone().day(7)).by('days')
					)
				})
			},

			nextMonth() {
				if (this.month == 11) {
					this.month = 0;
					this.year = this.year + 1;
				} else {
					this.month = this.month + 1;
					this.year = this.year;
				}
			},

			prevMonth() {
				if (this.month == 0) {
					this.month = 11;
					this.year = this.year - 1;
				} else {
					this.month = this.month - 1;
					this.year = this.year;
				}
			},

			selectDay(day) {
				if (this.dayIsDisabled(day) || day.diff(this.minDate, 'days') < 0) {
					return;
				}

				this.updateCurrent(day)
			},

			updateCurrent(value) {
				this.current = value
				this.$emit('change', value)
			}
		}
	}
</script>
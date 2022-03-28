import { Line } from 'vue-chartjs'
import chartColors from '../config/chart-colors'

export default {
	extends: Line,

	props: ['chartName', 'labels', 'chartData', 'color'],

	mounted() {
		let chartColor = this.color ? chartColors[this.color] : chartColors.blue

		this.renderChart({
			labels: this.labels,
			datasets: [
				{
					label: this.chartName,
					lineTension: 0,
					backgroundColor: chartColor,
					backgroundColor: chartColor.replace(', 1)', ', .2)'),
					radius: 0,
					fill: 'origin',
					data: this.chartData,
				}
			]
		}, {
			responsive: true,
			maintainAspectRatio: false,
			title: {
				display: false,
			},
			tooltips: {
				mode: 'index',
				intersect: false,
			},
			hover: {
				mode: 'nearest',
				intersect: true
			},
			scales: {
				xAxes: [{
					display: true,
					scaleLabel: {
						display: false
					}
				}],
				yAxes: [{
					display: true,
					scaleLabel: {
						display: false
					},
					ticks: {
						beginAtZero: true
					}
				}]
			}
		})
	}
}

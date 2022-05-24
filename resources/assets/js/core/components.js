import ToggleMenu from '../components/toggle-menu'
import SingleLineChart from '../components/single-line-chart'
import SingleBarChart from '../components/single-bar-chart'
import Checkbox from '../components/checkbox'
import Modal from '../components/modal'
import Calendar from '../components/calendar/calendar'
import CalendarDropdown from '../components/calendar/calendar-dropdown'
import DateRange from '../components/date-range'
import MiningCalculator from '../components/mining-calculator'
import BalanceChecker from '../components/balance-checker'
import Notification from '../components/notification'

export default {
	install(Vue) {
		Vue.component('toggle-menu', ToggleMenu)
		Vue.component('single-line-chart', SingleLineChart)
		Vue.component('single-bar-chart', SingleBarChart)
		Vue.component('checkbox', Checkbox)
		Vue.component('modal', Modal)
		Vue.component('calendar', Calendar)
		Vue.component('calendar-dropdown', CalendarDropdown)
		Vue.component('date-range', DateRange)
		Vue.component('mining-calculator', MiningCalculator)
		Vue.component('balance-checker', BalanceChecker)
		Vue.component('notification', Notification)
	}
}

import AnchorScroll from '../utilities/anchor-scroll'

export default {
	install(Vue) {
		// Disable vuejs console log tips for production
		Vue.config.productionTip = false

		// Creates a new promise that automatically resolves after some timeout
		Promise.delay = function (time) {
			return new Promise((resolve, reject) => {
				setTimeout(resolve, time)
			})
		}

		// Throttle this promise to resolve no faster than the specified time
		// Useful for loader icons so it wont looks like shit when resolved too fast
		Promise.prototype.takeAtLeast = function (time) {
			return new Promise((resolve, reject) => {
				Promise.all([this, Promise.delay(time)]).then(([result]) => {
					resolve(result)
				}, reject)
			})
		}

		AnchorScroll.initialize()
	}
}
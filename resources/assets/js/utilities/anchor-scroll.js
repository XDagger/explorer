import lodash from 'lodash'

export default class {
	static initialize() {
		new this()
	}

	constructor() {
		window.addEventListener('scroll', lodash.once(this.scrollToTarget.bind(this)))

		if ('onhashchange' in window) {
			window.addEventListener('hashchange', this.scrollToTarget.bind(this))
		}

		lodash.delay(() => window.scrollTo(0, 0), 1)

		window.onload = () => {
			if (window.location.hash.length >= 2) {
				this.getTarget().scrollIntoView(false);

				this.scrollToTarget()
			}
		}
	}

	headerHeight() {
		return document.getElementById('header').scrollHeight
	}

	getElementOffset(element) {
		let bodyRect = document.body.getBoundingClientRect(),
			elemRect = element.getBoundingClientRect()

		return elemRect.top - bodyRect.top
	}

	getTarget() {
		return document.getElementById(window.location.hash.slice(1))
	}

	scrollToTarget() {
		let hash = window.location.hash

		if (hash.length < 2) {
			return
		}

		let target = this.getTarget()

		if (target === null) {
			return
		}

		let position = this.getElementOffset(target) - (this.headerHeight() * 1.5)

		window.scrollTo(0, position)
	}
}

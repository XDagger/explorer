import Vue from 'vue'
import Components from './core/components'
import Directives from './core/directives'
import Vendor from './core/vendor'
import Utilities from './core/utilities'

Vendor.install(Vue)
Directives.install(Vue)
Components.install(Vue)
Utilities.install(Vue)

const app = new Vue({
	el: '#app'
})

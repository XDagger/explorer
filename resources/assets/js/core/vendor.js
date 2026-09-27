import VueTippy from 'vue-tippy'

import lodash from 'lodash'
import axios from 'axios'

const Moment = require('moment')
const MomentRange = require('moment-range')

export default {
    install(Vue) {
        Vue.use(VueTippy)

        window._ = Vue.prototype._ = lodash
        window.axios = Vue.prototype.axios = axios

        const moment = MomentRange.extendMoment(Moment)

        // Set Monday as start of week
        moment.updateLocale('en', {
            week: {
                dow: 1
            }
        });

        window.moment = Vue.prototype.moment = moment

        require('es6-promise').polyfill()
    }
}

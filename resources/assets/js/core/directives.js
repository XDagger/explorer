import ClickOutside from '../directives/click-outside'

export default {
    install(Vue) {
        Vue.directive('click-outside', ClickOutside)
    }
}

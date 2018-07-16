export default {
    props: {
        checked: {
            default: false
        }
    },
    data() {
        return {
            isChecked: false
        }
    },
    mounted() {
        if (this.checked) {
            this.check()
        } else {
            this.uncheck()
        }
    },
    methods: {
        check() {
            this.isChecked = true
            this.$el.getElementsByClassName('checkbox-input')[0].checked = true
        },

        uncheck() {
            this.isChecked = false
            this.$el.getElementsByClassName('checkbox-input')[0].checked = false
        },

        toggle() {
            if (this.isChecked) {
                this.uncheck()
            } else {
                this.check()
            }
        }
    }
}

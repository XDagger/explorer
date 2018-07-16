export default {
    data() {
        return {
            showNotification: true
        }
    },

    mounted() {
        _.delay(() => { this.hideNotification() }, 5000)
    },

    methods: {
        hideNotification() {
            this.showNotification = false
        }
    }
}

export default {
    props: {
        default: {
            default: ''
        }
    },
    data() {
        return {
            current: ''
        }
    },
    mounted() {
        this.current = this.default
    },
    methods: {
        openTab(tab) {
            this.current = tab
        },

        toggleTab(tab) {
            this.openTab(
                this.current == tab ? '' : tab
            )
        }
    }
}

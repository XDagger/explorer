export default {
    props: {
        shown: {
            default: false
        }
    },

    data() {
        return {
            modal: false
        }
    },

    mounted() {
        if (this.shown) {
            this.toggleModal()
        }
    },

    methods: {
        toggleModal() {
            this.modal = ! this.modal

            document.body.classList.toggle('overflow-hidden')
        }
    }
}

export default {
    data() {
        return {
            wallet: null,
            balance: null,
            loading: false,
            error: false
        }
    },
    watch: {
        wallet: function () {
            this.balance = null
            this.error = null
            this.debouncedGetWalletBalance()
        }
    },
    created: function () {
        this.debouncedGetWalletBalance = _.debounce(this.getBalance, 500)
    },
    computed: {
        hasError() {
            return ! this.loading && this.error
        },

        addressLink() {
            return '/block/' + this.wallet.trim()
        }
    },
    methods: {
        getBalance() {
            if (! this.wallet) {
                this.error = null
                this.loading = null

                return
            }

            this.loading = true

            axios.get('/api/balance/' + this.wallet)
                .then((response) => {
                    this.balance = response.data.balance
                    this.loading = false
                })
                .catch((error) => {
                    this.error = error.response.data.message
                    this.loading = false
                })
        }
    }
}

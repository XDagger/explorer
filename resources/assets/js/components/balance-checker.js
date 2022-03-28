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
                this.error = false
                this.loading = false

                return
            }

            this.loading = true

            axios.post('/balance-checker', { address: this.wallet })
                .then((response) => {
                    this.balance = response.data.balance
                    this.loading = false
                })
                .catch((response) => {
                    this.error = true
                    this.loading = false
                })
        }
    }
}

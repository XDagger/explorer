# XDAG Block Explorer
This software provides a detailed view on the xdag network presented in a nice UI.

# Features
- responsive design
- network statistics on the home page, including a list of 20 latest main blocks, with creation time
- network graphs (network hashrate, number of blocks created each minute)
- balance checker
- mining estimation based on miner's hashrate
- block kind estimation (Main block, Transaction block, Wallet)
- extended block statistics (balance, earnings and spendings change in last 24 hours, balance, earnings and spendings graph for last 7 days, for transaction blocks, sum of inputs, fees and outputs)
- block listing filters, and pagination
- block listing summaries (earnings / inputs, spendings / outputs on each page)
- switching of block as address and block as transaction panels based on block kind
- extensive caching for best performance, blocks are cached for 3 minutes, balances and other commands are cached for 1 minute
- ability to handle very large blocks, data is never fully loaded into memory
- rich and documented API
- text mode for older / mobile browsers
- support for local development (without a real xdag daemon)
- support for testnet (differences in reported "normal" daemon state)

<?php
namespace App\Xdag\Block\Output;

class OutputStream
{
	protected $block_started = false;
	protected $transaction_number;
	protected $address_number;

	public function stream($message, $data)
	{
		if ($message == 'not_found') {
			echo '{"error":"block_not_found","message":"Block was not found."}';
			return;
		}

		if (!$this->block_started) {
			echo '{';
			$this->block_started = true;
		}

		if ($message == 'property') {
			foreach ($data as $key => $value) {
				if ($key == 'state')
					$value = ucfirst($value);
				echo "\"$key\":\"$value\",";
			}
		}

		if ($message == 'state') {
			if ($data == 'transactions')
				echo '"block_as_transaction":[';
			else if ($data == 'addresses')
				echo '],"block_as_address":[';
		}

		if ($message == 'transaction' || $message == 'address') {
			echo ($this->{$message . '_number'}  ? ',' : '') . '{';
			$num = 0;
			foreach ($data as $key => $value) {
				if ($num)
					echo ',';
				$num++;
				echo "\"$key\":\"$value\"";
			}
			echo '}';
			$this->{$message . '_number'}++;
		}

		if ($message == 'block') {
			echo ']';

			if ($data->isTransactionBlock()) {
				echo ',"total_fee":' . $data->getTransactions()->getTotalFee();
				echo ',"total_inputs":' . $data->getTransactions()->getInputsSum();
				echo ',"total_outputs":' . $data->getTransactions()->getOutputsSum();
			} else {
				echo ',"balances_last_week":[';
				$num = 0;
				foreach ($data->getEarnings() as $key => $value) {
					if ($num)
						echo ',';
					$num++;
					echo "{\"$key\":\"$value\"}";
				}
				echo ']';

				echo ',"earnings_last_week":[';
				$num = 0;
				foreach ($data->getEarnings() as $key => $value) {
					if ($num)
						echo ',';
					$num++;
					echo "{\"$key\":\"$value\"}";
				}
				echo ']';

				echo ',"spendings_last_week":[';
				$num = 0;
				foreach ($data->getSpendings() as $key => $value) {
					if ($num)
						echo ',';
					$num++;
					echo "{\"$key\":\"$value\"}";
				}
				echo ']';

				echo ',"balance_change_last_24_hours":' . $data->getBalanceChange()['valueChange'];
				echo ',"earnings_change_last_24_hours":' . $data->getEarningsChange()['valueChange'];
				echo ',"spendings_change_last_24_hours":' . $data->getSpendingsChange()['valueChange'];

				echo ',"total_earnings":' . $data->getTotalEarnings();
				echo ',"total_spendings":' . $data->getTotalSpendings();
			}

			echo ',"kind":"' . ($data->isMainBlock() ? 'Main block' : ($data->isTransactionBlock() ? 'Transaction block' : 'Wallet')) . '"';
			echo '}';
		}
	}
}

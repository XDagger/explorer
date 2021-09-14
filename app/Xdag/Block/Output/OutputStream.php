<?php
namespace App\Xdag\Block\Output;

class OutputStream
{
	protected $block_started = false;
	protected $transaction_number;
	protected $address_number;
	protected $extras = [];
	protected $block_not_found_called = false;
	protected $invalid_markup_called = false;

	public function stream($message, $data)
	{
		if ($message == 'not_found') {
			if (!$this->block_not_found_called)
				echo '{"error":"block_not_found","message":"Block was not found."}';

			$this->block_not_found_called = true;
			return;
		}

		if ($message == 'invalid_markup') {
			if (!$this->invalid_markup_called)
				echo '{"error":"not_ready","message":"Unable to retrieve block data at this time."}';

			$this->invalid_markup_called = true;
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
				echo ',"total_fee":' . $data->getTotalFees();
				echo ',"total_inputs":' . $data->getTotalInputs();
				echo ',"total_outputs":' . $data->getTotalOutputs();

				echo ',"page_fee_sum":' . $data->getFeesSum();
				echo ',"page_inputs_sum":' . $data->getInputsSum();
				echo ',"page_outputs_sum":' . $data->getOutputsSum();

				echo ',"filtered_fee_sum":' . $data->getFilteredFees();
				echo ',"filtered_inputs_sum":' . $data->getFilteredInputs();
				echo ',"filtered_outputs_sum":' . $data->getFilteredOutputs();
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

				echo ',"page_earnings_sum":' . $data->getEarningsSum();
				echo ',"page_spendings_sum":' . $data->getSpendingsSum();

				echo ',"filtered_earnings_sum":' . $data->getFilteredEarnings();
				echo ',"filtered_spendings_sum":' . $data->getFilteredSpendings();
			}

			echo ',"kind":"' . ($data->isMainBlock() ? 'Main block' : ($data->isTransactionBlock() ? 'Transaction block' : 'Wallet')) . '"';

			foreach ($this->extras as $key => $value) {
				echo ',"'.$key.'":' . json_encode($value);
			}

			echo '}';
		}

		if ($message == 'extras') {
			foreach ($data as $key => $value) {
				$this->extras[$key] = $value;
			}
		}
	}
}

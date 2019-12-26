<?php
namespace App\Xdag;

use Carbon\Carbon;
use App\Xdag\Block\Block;

class XdagLocal extends Xdag implements XdagInterface
{
	protected $version;

	public function __construct()
	{
		$this->version = env('XDAG_LOCAL_VERSION', '0.2.5');
		$this->socketFile = null;
	}

	public function getVersion()
	{
		return $this->simpleCachedCommand('version', 30, function ($file) {
			fwrite($file, $this->version);
		});
	}

	protected function commandStream($cmd, $read_lines)
	{
		$file = $this->getCommandFile($cmd);

		if ($read_lines) {
			while ($line = fgets($file, 1024)) {
				yield rtrim($line, "\n");
			}
		} else {
			while (!feof($file)) {
				yield fread($file, 16384);
			}
		}

		fclose($file);
	}

	protected function commandOutputFile($data)
	{
		// php://temp handler stores data in memory if they don't exceed predefined limit (usually 2MB).
		// data is then stored to a temp file on disk, and automatically discarded by PHP if script execution ends.
		$file = fopen('php://temp', 'w+');
		fwrite($file, $data);
		fseek($file, 0);

		return $file;
	}

	protected function getCommandFile($cmd)
	{
		$cmd = preg_split('/\s+/', trim($cmd));

		if ($cmd[0] == 'state')
			return $this->commandOutputFile('Synchronized with the main network. Normal operation.');

		if ($cmd[0] == 'stats') {
			$date = Carbon::parse('2018-06-14 19:34:23.999');
			$diff = $date->diffInSeconds(now());

			$new_main_blocks = floor($diff / 64);
			$main_blocks = 189734 + $new_main_blocks;
			$blocks = 58027510 + $new_main_blocks * 1000 + (int) rand(100, 800);
			$supply = $main_blocks * 1024; // for test data, this is enough
			$net_hash = round(75100856 + rand(1024, 8192) * 1024, 2);

			return $this->commandOutputFile("Statistics for ours and maximum known parameters:
			hosts: 456 of 456
		   blocks: $blocks of $blocks
	  main blocks: $main_blocks of $main_blocks
	orphan blocks: 133
 wait sync blocks: 260
 chain difficulty: 630fcc7af946716ccb8c40865da of 630fcc7af946716ccb8c40865da
	  XDAG supply: $supply.000000000 of $supply.000000000
4 hr hashrate MHs: 2357987.48 of $net_hash");
		}

		if ($cmd[0] == 'balance')
			return $this->commandOutputFile('Balance: 24542.435093494 XDAG');

		if ($cmd[0] == 'net' && isset($cmd[1]) && $cmd[1] == 'conn') {
			if ($this->versionGreaterThan('0.3.0'))
				return $this->commandOutputFile('---------------------------------------------------------------------------------------------------------
Connection list:
  0. 10.0.0.1:16775          398 sec, [in/out] - 18432/18944 bytes, 36/37 packets, 0/0 dropped
  1. 192.168.0.111:35234     334 sec, [in/out] - 13824/13824 bytes, 27/27 packets, 0/0 dropped
---------------------------------------------------------------------------------------------------------');

			return $this->commandOutputFile('Current connections:
  0. 1.2.3.4:55555		   183417 sec, 33962388992/6979446272 in/out bytes, 64507158/11558944 packets, 0/3 dropped
  1. 111.11.111.111:123	   148832 sec, 24856093184/5718993408 in/out bytes, 47044872/9426490 packets, 0/6 dropped
  2. 110.110.110.11:33333  124050 sec, 11723742208/8924076032 in/out bytes, 21416469/15970217 packets, 0/1 dropped
  3. 22.22.133.123:11111   118280 sec, 6187444224/16896978944 in/out bytes, 10839877/31606947 packets, 0/3 dropped');
		}

		if ($cmd[0] == 'block' && isset($cmd[1])) {
			$block_remark = '';
			$separator = '-------------------------------------------------------------------------------------------';
			$address_header = ' direction  transaction                                amount       time';
			$earning_1 = "\n   earning: ////3aEv+N8KHkA/CW7xOw+i5uLL////		1024.000000000	2018-06-14 19:34:23.999";
			$earning_2 = '';
			$remark_1 = '';
			$remark_2 = '';

			if ($this->versionGreaterThan('0.2.5')) {
				$block_remark = "\n    remark: test block remark";
				$separator = '-----------------------------------------------------------------------------------------------------------------------------';
				$address_header = ' direction  transaction                                amount       time                     remark                          ';
				$earning_1 = '';
				$earning_2 = "\n   earning: ////3aEv+N8KHkA/CW7xOw+i5uLL////		1024.000000000	2018-06-14 19:34:23.999";
				$remark_1 = '  test   remark   1';
				$remark_2 = '  test   remark   2';
			}

			// large wallet block
			if ($cmd[1] == 'WLYMhgmO01vA86yfdk7bEMX2lqzFxalj' || $cmd[1] == 'b458f488ad0ff62963a9c5c5ac96f6c510db4e769facf3c05bd38e09860cb658') {
				// > 0.2.5 simulation not supported on example large block
				return fopen(__DIR__ . '/ExampleData/largeblock.txt', 'r');
			}

			// tx block
			if ($cmd[1] == 'IxTmxt1HDfEN4H/AkzoVlfCezXb5eK+G' || $cmd[1] == '1377bfff49a19d0086af78f976cd9ef095153a93c07fe00df10d47ddc6e61423')
				return $this->commandOutputFile('      time: 2018-06-22 00:12:49.813
 timestamp: 16cb0fc0741
     flags: 1c
     state: Accepted
  file pos: 3400
      hash: 1377bfff49a19d0086af78f976cd9ef095153a93c07fe00df10d47ddc6e61423' . $block_remark . '
difficulty: 67afc768f709c3c51fe467dd6e3
   balance: IxTmxt1HDfEN4H/AkzoVlfCezXb5eK+G           0.000000000
' . $separator . '
                               block as transaction: details
 direction  address                                    amount
' . $separator . '
       fee: Pt9p+W1b1Ll1hnQnTvhnUVO1WBt/F05q           1.000000000
     input: esjB13qh5KTasesXNcNVEtxhtk7AK+7J          21.384352287
    output: jXUomq7J+E6ov4wWcfMIaqfl6C0wKbES           0.014877667
    output: YIHx9JHyKSLL+8zbrsZQ7l847dMZXHGm           2.450097091
    output: K3J2dUqEBo5i3inQ43/rEW3E61jyTh9V           1.205742958
    output: XtqgEgnlCchXrIOB1rIgyJJ7gS+XDcYF           0.757055319
    output: pqIiZ36TUDkYGviy3jqGBBKqDJrh3IhK          12.472868535
    output: NctTavrPnUeZVkmI1cmAeB9b6INYFRYA           0.225361744
    output: 4pnwt6f74A2jMDClYajQnlhAy+rH+P+G           0.798203981
    output: QjjaGsA5zWd9ILVxZgn1miopGGQF+n51           1.406786679
    output: DLW9zsi4Xjgz+pIhA05AG+SIuZhD8nwL           0.691224573
    output: Bhx07eBKNaunnC3zdDJ7AaIJyZDFlMnM           0.814736369
    output: gIr0tcVxGMqI1Qvvertdwj0hVCN5tkTE           0.547397367
' . $separator . '
                                 block as address: details
' . $address_header . '
' . $separator);

			// main block
			if (!$this->versionGreaterThan('0.2.4') && ($cmd[1] == '////3aEv+N8KHkA/CW7xOw+i5uLL////' || $cmd[1] == '0000000000000285cd1c19cbe2e6620d3bf16e092b401e0adf602fa1dda57b40'))
				return $this->commandOutputFile('      time: 2018-06-14 19:34:23.999
 timestamp: 16c26daffff
	 flags: 1f
	 state: Main
  file pos: f0e00
	  hash: 0000000000000285cd1c19cbe2e6620d3bf16e092b401e0adf602fa1dda57b40
difficulty: 630f2898a810cecc2a958835ea9
   balance: ////3aEv+N8KHkA/CW7xOw+i5uLL////		  10.240000519
-------------------------------------------------------------------------------------------
							   block as transaction: details
 direction	address									   amount
-------------------------------------------------------------------------------------------
	   fee: ////3aEv+N8KHkA/CW7xOw+i5uLL////		   1.000000000
	output: F81tjpr7ONhatIapuqU1LqLOP3q7gzhQ		   2.000000000
	output: rFYU3YXvzhZHFrq0HMn1W5AwIyjCASzb		   0.000000000
	output: EL5Nfmve021QCBB0yyLS69QdKwV9xH3r		   5.000000000
	output: t3sOkNEKp3TwQXEeThSxAWDFymAqJELs		   0.000000000
	output: ElecK6xIzLcVK8qYmDuOALOXl2SFx1N9		   7.000000000
	output: 2NmDFr0AJeAQxNn4MtpU4XRxB+/Og94/		   0.000000000
	output: tkyKSe3ajtG03eEZ9/zdSHQ6x9lAIjMy		   0.000000000
	output: QctB5ycsHBt0D7g1CRgdNQ9Y/kTHD/P3		   0.000000000
	output: ssafXksV6+10EWrROBGCbuCSY65SmiOD		   0.000000000
	output: g3bAGI/Oecez51hF679ZSXrgHTVObW1f		   0.000000000
	output: 9XFlMlg5G1xgptjFmwdOxUVte3HSwcWl		   0.000000000
	output: TQxa9lLYGTOb4spslbM0LnLaJ0ihl1SA		   0.000000000
-------------------------------------------------------------------------------------------
								 block as address: details
 direction	transaction								   amount		time
-------------------------------------------------------------------------------------------
   earning: ////3aEv+N8KHkA/CW7xOw+i5uLL////		1024.000000000	2018-06-14 19:34:23.999
	output: RYvemDqKltkC4VaxkdgT2nu84RmGCQfE		   1.175313073	2018-06-14 19:49:20.378
	output: QTTm45K/Reju1/3FIU/fD4D4gv+ojkbQ		   2.659098001	2018-06-14 19:49:20.381
	output: GcO1gv2BKKugmumKMiufQ0Ue3pI5Ogtm		   2.552766561	2018-06-14 19:49:20.384
	output: 2UpSicD5xv1NA/PTN04+d/QuWoH1MCYm		   2.044502267	2018-06-14 19:49:20.387
	 input: AKoaDXKGUjoJKlNK2iP3CltkdKYfhUp7		   2.118406890	2018-06-14 19:49:20.390
	output: crqPIxsR7TAq1PMpahTDwImUUlnMIAJ1		   2.106789894	2018-06-14 19:49:20.393
	output: UTgjTbGi9kA9i30A7hml4Oqyw8W62AoG		   3.436523429	2018-06-14 19:49:20.395
	output: FbM4lXZ9SbV8+QtRfhfl/Pj+YpfYVnPO		   1.371980242	2018-06-14 19:49:20.398
	 input: U8I3EnAdmwSi88Izl3MVqExVFmqvVDLE		   3.222378291	2018-06-14 19:49:20.401
	output: aBxFYbuRwakAk37/4lDtauxRmQuIpfXz		   2.402931114	2018-06-14 19:49:20.404
	output: gZuElfoHtLZZC6Mx6u4zyxfxvKjoE9I0		   2.957635511	2018-05-26 19:49:20.406
	output: LllAtVKm5ynmJmUZCkq5WpU5wZTqIVls		   1.190855161	2018-05-26 19:49:20.408
	output: pyOsQ/VeifLg4rm+6yoD3SeA7F9aVKvo		   2.134227059	2018-05-26 19:49:20.409
	output: U8g1N3qEJJe1hzApe9+M5CWNa0hOp+SH		   1.953279468	2018-05-26 19:49:20.411
	output: vac/u6TDrpFx1pPNR/wTEmCbsfRson+H		   3.927251766	2018-05-26 19:49:20.413
	output: UGvSzT19pe79ZrsaA5EYnv8QPjFGcZVQ		   3.411284811	2018-05-26 19:49:20.415
	output: SQ7ExBE6YevZMeFd2QyinDMgKteDguRW		   1.633962966	2018-05-26 19:49:20.416
	output: w0co4RbccB3fpFXPh6tnaPR5qFeaY4PO		   2.788434455	2018-05-26 19:49:20.418
	output: UF+9tZsqiq4g0BDV8ALrkt65PjMEQxCT		   3.166316379	2018-05-26 19:49:20.420
	output: HFE1RadRGRe7uYyYSHbKtCfbTL59EF3z		   1.649405705	2018-05-26 19:49:20.422
	output: xmy08hSOqhLmpvPeuetVZxvLkdF+AbPq		   1.604814279	2018-05-26 19:49:20.423
	output: TUAA6vyHP7DzMHdUIwKathLJSKwoZDNc		   2.174398141	2018-05-26 19:49:20.425
	output: arUw0slFAUDgEpYqhWHDC4OJxL6Ljf+v		   2.320351982	2018-05-26 19:49:20.427
	output: 2yMTp6uSKoFDnxdZgYxzDovvQmAeEw0I		   5.078783295	2018-05-26 19:49:20.429
	output: PIHte4XeIAnrvovGgdhX7CVK1wKY2epX		   2.312242650	2018-05-26 19:49:20.431
	output: m69fkNhvLOFomcu1rtB2EqktaqhEsWWM		   1.622886480	2018-05-26 19:49:20.433
	output: 6jCb6yTbqxuvkMJVzkCWjF5IdL1Zdi/9		   1.408484532	2018-05-26 19:49:20.435
	output: Kp7drSWPXEL6UzR/Rkd98M9No42uruL/		   4.177521841	2018-05-26 19:49:20.436
	output: w59UzVTdNppZ8WXCAyq3s0yjet5b94L0		   2.575802759	2018-05-26 19:49:20.438
	output: 1gGrOnK5FIeWshkHn+sxngCT2fS3IcXZ		   6.842075808	2018-05-26 19:49:20.440
	output: Cnf1FJF3RV+r8PlrWB7RPrRi8Ne3T+Wb		   1.643286046	2018-05-26 19:49:20.442
	output: ELggwR3n8j9hUwK5Uark1x4pAV21UXJZ		   2.788789341	2018-05-26 19:49:20.445
	output: t95KBshQtvA8SuqrYp9uh7//tm85TGWA		   2.118827769	2018-05-26 19:49:20.447
	output: NvDM36BVt8vaiOzhUgT+uPQca3uzK9/i		   2.286361560	2018-05-26 19:49:20.450
	output: MwrVtcR3RiVCrb5dhZJoyDnlakjw1pEA		   3.613535850	2018-05-26 19:49:20.453
	output: SD1Mc5MCA1OGtn55icO1QZSpPcVJImc9		   2.205842319	2018-05-26 19:49:20.456
	output: C0H08hSSqs5D+cInHyfVNYJ+ETyxe/6d		   4.298511479	2018-05-26 19:49:20.458
	output: lctoKIcQHMM1ZiHlMfLa3n3kfTXrO77u		   3.487491512	2018-05-26 19:49:20.461
	output: sVKHLTp5SVOGQdUkqLsBnUzNUYzisHPJ		   3.086055621	2018-05-26 19:49:20.464
	output: RV1SJM21/D8zPyAgFq9sj5kIvHmjihig		   2.872226229	2018-05-26 19:49:20.467
	output: JmyAGAKpoArK2nVLPH+VxsZPQn1xsxYt		   1.171596845	2018-05-26 19:49:20.469
	output: UbiBSJKS5QTx7WncIITtaBCaoa/I6OFd		   1.396881366	2018-05-26 19:49:20.471
	output: W79E/fiKULIeC/nrz+0u74cgMiupSERE		   2.938314714	2018-05-26 19:49:20.473
	output: LsXGerjAKYSS8hQVjJ+vwPyB/S79+5xu		   2.517118782	2018-05-26 19:49:20.474
	output: 8yZCkDfWYduyzTzb8WvULTaVcvxauAWk		   1.457770769	2018-05-26 19:49:20.477
	output: S/bk8fvkQbrzpNOzIwVlAOLjN0N+/74I		   3.536872488	2018-05-26 19:49:20.478
	output: SiSv7Mi91PQ9dqZ0VTE5/uCXCzf6++je		   6.511757279	2018-05-26 19:49:20.480
	output: QiYMMfQLBJQeCYmdoKLLEwpvU/J5RVvT		   2.219086767	2018-05-26 19:49:20.482
	output: XkomkaKwP8snDQ/aO9XnXdO4pIc8+zBb		   5.641860170	2018-05-26 19:49:20.484
	output: I9bUrLA2KvRKvV99PcixUxo1jUjkRzdc		   2.176470024	2018-05-26 19:49:20.487
	output: F1wt8wN/HpB20CFYjXOT/CbH9bwNVOlg		   1.174620473	2018-05-26 19:49:20.489
	output: vWXPagUeKnGnY8DZ1Gk/ZCafxf3Cvdzw		   2.122415904	2018-05-26 19:49:20.491
	output: idusGhSfshNJwXGv7pnluSIUnqHDZDUT		   2.714063701	2018-05-26 19:49:20.493
	output: +uI01Bp1L8p2o8giiXPb4HdD7rWPqIIN		   3.039246088	2018-05-26 19:49:20.495
	output: rEsUBTuQprP58H7Z+skm/b17D1dj3oKM		   7.097577694	2018-05-26 19:49:20.497
	output: BPXs4ipc0yIV7nAivzqKF6KrgkaGhm+x		   2.996871154	2018-05-26 19:49:20.499
	output: 6Nr5weOwZXApVvnp9Z4JI2PcDoHcpXV+		   4.226045499	2018-05-26 19:49:20.500
	output: C8JMgIwmplHXqDdxAF1qo5utkTKk578O		   2.032990581	2018-05-26 19:49:20.501
	output: iCb5kE7X8/eSrKe0VDco+razTXEOAfzS		   2.263772011	2018-05-26 19:49:20.504
	output: iNjBFso6YN8dErAZ+zepBklq1l97Zqfa		   2.880792801	2018-05-26 19:49:20.507
	output: V+h/mpUgC3gzmtB3buIuoo3xoXjn/Hnd		   2.087891923	2018-05-26 19:49:20.510
	output: 1iscX37gW84t0qB7SoRv958Hw556xM6c		   2.154175620	2018-05-26 19:49:20.513
	output: uz8qTxmFqm6Kb4TTIcUwOnCOnEE4jv00		   1.615650051	2018-05-26 19:49:20.516
	output: GQ7TYH0I5hNQqHyxx0AfzdViv4tUPbvi		   2.571351016	2018-05-26 19:49:20.518
	output: QJLKRcQ8u445BbVoJer69sJS3pF8d+Fr		   1.785006833	2018-05-26 19:49:20.521
	output: rWCrXhw3+zKODk6/IMuGrrP59b6kmP97		   2.342019696	2018-05-26 19:49:20.524
	output: lvPYAY3pXCqR22J6L3bUw+qC1zrD3Z3B		   2.945573825	2018-05-26 19:49:20.527
	output: EI++oFY6Hr+bPP7vyzd9nla6J3au4SDM		   1.997621725	2018-05-26 19:49:20.529
	output: hOhWShcBo/FqlDGdJjsT/Jl9jktEOuIO		   3.986983092	2018-05-26 19:49:20.531
	output: RrN1rbtmm8p4NvdUmJsk79YCiBEdWwYq		   2.630149242	2018-05-26 19:49:20.533
	output: tYZYOKGwCNc9S/OnksTrZmTOyHUQ+72H		   2.717005287	2018-05-26 19:49:20.535
	output: Fq0ZbOZDhHzsGvc5+vpFse9UOXTEsefI		   1.118867415	2018-05-26 19:49:20.537
	output: eEkVf+vv1lNWXbHUrYQNVoPq85yyqDWf		   2.582741502	2018-05-26 19:49:20.539
	output: qchHmS7DdFouOMRLtcTYunW7aCnCpunC		   1.909493362	2018-05-26 19:49:20.541
	output: +qscaupAwMLLpBbzMeFyVTV02Xrth0it		   2.134392585	2018-05-26 19:49:20.542
	output: cCTy/ZkVBuHwaIuUVJeGpp1hq2Gakzji		   2.512344658	2018-05-26 19:49:20.544
	output: xyNdYrM+7h1A5Uugqz1t65gBRuc9kRiK		   0.754757083	2018-05-26 19:49:20.546
	output: CByyLFrRnAvFRxoDubUwRdovHYaJYT0T		   2.140102021	2018-05-26 19:49:20.547
	output: di1q916Km9NDrRVhvE3Gt1nw0kWx8QkA		   2.635609872	2018-05-26 19:49:20.549
	output: 2d5E6ELqqAz5vnVHf9jqLhcJ4Mos/t1p		   2.592236952	2018-05-26 19:49:20.551
	output: z3/IZSoYSAyOx9ZqXo8B6Yx5ki41M64v		   1.407104243	2018-05-26 19:49:20.553
	output: XtN5TkxBnTtt7+a7k67tIwdn/J9091h5		   3.462345721	2018-05-26 19:49:20.555
	output: 9G+EZBg64f0OtRlsgmK12FlpW/aN31ZK		   2.302229596	2018-05-26 19:49:20.557
	output: rtK4QjfzrKqZlHSlgdZ8BfcnpXPj/KX3		   2.070593482	2018-05-26 19:49:20.559
	output: OcnZnX+cFZ9EE5w1kwiXD4y1QFfwIr5E		   4.199781362	2018-05-26 19:49:20.561
	output: fxki2DAV8Gm9ePsmKFBsckA4Ts+i/+HI		   2.528092311	2018-05-26 19:49:20.563
	output: 4EjtDJ2tY2vkDKhVnjyeqi5Mf0DhWvie		   4.145633529	2018-05-26 19:49:20.565
	output: 5RML6ydswjKqMklAtm0vEhMUFjjWkZVW		   2.576823386	2018-05-26 19:49:20.568
	output: eLQUzO4XLIkt8IEKE5Za9mOp+rop3CXF		   2.625653411	2018-05-26 19:49:20.570
	output: nRZ4QFhCqJOq6peI/KjtS0R7yFCbkJlI		   2.014642573	2018-05-26 19:49:20.572
	output: HrG3QJiu6sFgEbFFexBtvnVb8iLWmmO4		   1.615538465	2018-05-26 19:49:20.574
	output: 83WOL5YcgqHa2YHKJ4a5n6zFJSsczA2U		   2.581412571	2018-05-26 19:49:20.577
	output: 7kL7QCsM2+8AFek2UIs1ftEe/KRZQDMq		   1.819982490	2018-05-26 19:49:20.579
	output: sCWzOL/0mtam9jlwlW58Degkn6ij2hxY		   1.698452064	2018-05-26 19:49:20.581
	output: CyAzfgJp0WsW87zj5IIwmhHZPcUeMyLE		   2.503207233	2018-05-26 19:49:20.583
	output: wok8ekl5rqqsmIog9Y/FnHaG1WINO2KM		   3.030346014	2018-05-26 19:49:20.585
	output: ODcdaqhYDo9AZJLfxwnjliPNYBTeROVu		   1.213292015	2018-05-26 19:49:20.587
	output: /qNiCjEQZuWL2k2zT/7tDDPTersFCvfU		  12.210986441	2018-05-26 19:49:20.589
	output: qFSxbBMPhGzxm0U7w/seiLTEo35Hi9A1		   1.377637999	2018-05-26 19:49:20.591
	output: DIHYhrfGTJ7yM0ibOBtFD4CDt/wJjnFE		   1.367365726	2018-05-26 19:49:20.593
	output: N1FfHbBAwr4CgR5WBp32ximUIhqH7nkt		   4.644077876	2018-05-26 19:49:20.595
	output: cOTojXFVAd6Xhtmub1zGjYnobqMctZ0s		   2.388101827	2018-05-26 19:49:20.597
	output: upijWXg5JC4PVsWH3qve6m0N2ESi5x1B		   2.085997056	2018-05-26 19:49:20.599
	output: t4DYeSNsjfrpWzSWLicKqX6rQvPHKEvD		   2.626166279	2018-05-26 19:49:20.601
	output: p60EY8vrpEW5zG7FlWKuNpRfT7LuiXA0		   1.653288993	2018-05-26 19:49:20.604
	output: jxf7I5mdawwTLf8dTC1PixhkdQNsMZWa		   3.433122304	2018-05-26 19:49:20.606
	output: K6bPoXJpDzqDvgJlQdI2/+82SIIRThq5		   2.328448587	2018-05-26 19:49:20.608
	output: 4/ZMfmEvOGxMXv+QycWSrIkPDi+fN/Zl		   2.167249079	2018-05-26 19:49:20.610
	output: genxl0sDgtXNyp4T9tviMqONk2vqC+tr		   2.915299144	2018-05-26 19:49:20.612
	output: 5u49RMv0WIKXfgxGp9VdC7uJLh+66xvN		   2.032339071	2018-05-26 19:49:20.614
	output: v2v1yMky/FAe+hrGLXkr/3RqrXh9Jnuc		   2.815660546	2018-05-26 19:49:20.616
	output: c9e8c7UUjd7saIxJY6HU5IFPqqPuUwzW		   1.458433551	2018-05-26 19:49:20.619
	output: +J9sEBuWdcHJNXbHYFHUFF9zHMruLXEg		   4.265582168	2018-05-26 19:49:20.621
	output: YdKxZKyv9qrnpkbJQohDV8wdZRArvpzl		   6.306492393	2018-05-26 19:49:20.623
	output: hOnNYA4bPcfDoGhv99HUEyFrTilu1EoN		   3.916382086	2018-05-26 19:49:20.625
	output: a2Xuhga4s5hLTsUetCACAHNQsdtFn1XX		   2.737747628	2018-05-26 19:49:20.626
	output: uBXQ2JsZ7GfEE1T75CvuR645HBQvrkNL		   1.535050415	2018-05-26 19:49:20.628
	output: //0TswAyQ63jGwQtlZfL8rtVH9z+pY0j		   2.653836063	2018-05-26 19:49:20.630
	output: hrSa4yIpSWbEFz8cCKN4VKBFEKP8y+nQ		   1.505073280	2018-05-26 19:49:20.632
	output: 2DUKnH3VhjtcBLs976IJxlVOAGQrYbSj		   2.197188818	2018-05-26 19:49:20.634
	output: srRkU2JdV+a2TLNwXsfMcx4Muq6Ijf8X		   2.053562387	2018-05-26 19:49:20.636
	output: c4IfjI9HwoP5tjy1gQNVttQndLG29uFB		   2.570698892	2018-05-26 19:49:20.638
	output: tl5HItGkJd3gOfVgMzgwFuBqN+zj71Nz		   1.882048785	2018-05-26 19:49:20.640
	output: Ly/7oZtnND1ddjMFTvm4UKqKGeX3kuM8		   1.979603625	2018-05-26 19:49:20.642
	output: HwbBpjVJhnjGRry4MKG5KH8YNAD5bwXq		   2.008633679	2018-05-26 19:49:20.644
	output: he2Y+R3X80AmxoVlLk9tTiuLXu3htrDq		   1.316417609	2018-05-26 19:49:20.646
	output: H8kiRfzlkzayruhBxUzBx+w/CKt8jip1		   1.254689489	2018-05-26 19:49:20.648
	output: UYfPK7vAxoqbUMiEkPsiCMJshQ5Dy1RB		   2.615476018	2018-05-26 19:49:20.650
	output: abDyIvZyfVK9PNboVcJcFt0g7/tAtHZM		   3.461435072	2018-05-26 19:49:20.652
	output: HCkpdnUiForbr9juCJ1CLwHXMLx7noNJ		   3.178744361	2018-05-26 19:49:20.654
	output: RYJs8eJrzCe25a1+0ikLM+jIsYC45rE7		   3.562017038	2018-05-26 19:49:20.656
	output: /Tl4/fyajP5JjmO+UJcXItjIMs93xOSs		   1.419016733	2018-05-26 19:49:20.658
	output: fMmSYx7OPRlVw0Ladx/K+p+S+acoSG1i		   2.482394498	2018-05-26 19:49:20.661
	output: xOEK9r2Khih1W2x4jm0vitSawqoMp87d		   1.633676141	2018-05-26 19:49:20.664
	output: 3lsvsktJ1KODGEbqBa80ZfAf1YsYCFHM		   3.211036354	2018-05-26 19:49:20.666
	output: zWBXec80Z8nU2WoVQu8bxbi+DJqiyrCj		   3.223353167	2018-05-26 19:49:20.669
	output: JNX5nCBzCfVESulHrzr/hDrzFF/u0ILP		   2.717892041	2018-05-26 19:49:20.672
	output: Tw7P6rcR0BIbmGxUXP/PwH0zBgTbhHka		   0.919780916	2018-05-26 19:49:20.675
	output: awisl03BQ+jce7oY1qRJCBG/0Gpw1NVK		   1.824600354	2018-05-26 19:49:20.678
	output: tbnQatBJzIOXeOykSXG3Dui6tsqWvx/3		   2.924099147	2018-05-26 19:49:20.680
	output: nmNqOsLdTP6ImizSTcOdjFFeakEvgLmR		   1.333210499	2018-05-26 19:49:20.682
	output: vpwTRnTpkufpAuiomMPRG/FEZTPDsFiv		   2.219258914	2018-05-26 19:49:20.684
	output: LO7YtS897Nuw+6cr3URPd33gaQjI4BiH		   1.515833306	2018-05-26 19:49:20.686
	output: 4/oVHluw3flxpIhaG0/9UwkrpxA3aZFN		   1.482826100	2018-05-26 19:49:20.688
	output: S3ndFznGBzUn3R8smRZs9da6IxFOFaSl		   2.765346352	2018-05-26 19:49:20.690
	output: Yk+3TmwYMxh5A8a5Qnrc9DJtxs3ZBIv6		   3.620585558	2018-05-26 19:49:20.692
	output: 4Xoc3gmLt7qo6NkWw6RhMJI0yQNapIL6		   2.656110161	2018-05-26 19:49:20.694
	output: e5P6ahRnykVUw8l07rSmpEuHg0ge9wX5		   2.600019593	2018-05-26 19:49:20.696
	output: U1fBBHNYKZ0evIUnuV0t48DAAGR6lImY		   2.687953135	2018-05-26 19:49:20.698
	output: p4/1qHkzl1SaqTdOzDAgtCGleaocfzbr		   1.281853221	2018-05-26 19:49:20.700
	output: 072MX2FR8xHkSD3Thf1UfQ/ITdJ2+TRz		   3.975474843	2018-05-26 19:49:20.701
	output: OfegCvmO11w6m+ROD0iR9sEkEQXXNo3g		   2.155539554	2018-05-26 19:49:20.703
	output: fV8KSDmQ4kvo2IO1z3KY0JEn/W0kyzbk		   0.849655264	2018-05-26 19:49:20.705
	output: d9dbIUslGzQQaF95j3jVaMHcZUM0z74c		   2.193398126	2018-05-26 19:49:20.707
	output: DGYfbiPBMJjG0fBtReaP0D8ulPV/RNEq		   1.099642422	2018-05-26 19:49:20.708
	output: jgeqM0Hr8uce1Fe6uadP84Dv9vjDcfgB		   2.149450345	2018-05-26 19:49:20.710
	output: dCCtGCVB5Q0rLG0/emSS+R7EYGb2l9WF		   1.878442364	2018-05-26 19:49:20.712
	output: KUbLf8sBuv2+NfzdDypCnUeXy7TXJyx9		   1.766689993	2018-05-26 19:49:20.714
	output: T2LYjhOpP3gt0CeT1aX2jJybiTUYVMEV		   3.992716081	2018-05-26 19:49:20.715
	output: 0ipan84LjgCdtQB8TMBb//k0YsdwnMHg		   1.543694613	2018-05-26 19:49:20.718
	output: PnvABKpUZOU9bxE95gQQV0ALZzkjDKkA		   2.068931587	2018-05-26 19:49:20.719
	output: 6Z2ASKk7Q9jZ72zZfzmB49SGjQ9CANGb		   2.231040205	2018-05-26 19:49:20.721
	output: H54ZimvaR9r3E++IsPcqNRkddfx/g8Z9		   0.802241051	2018-05-26 19:49:20.723
	output: TM/GGfzxX96DNCcNuK2Hn8tyGTN3tvHI		   1.492903194	2018-05-26 19:49:20.726
	output: 6CcTu+tZIfkOkUZog8bpobrLB3Q1/QSe		   1.371335119	2018-05-26 19:49:20.727
	output: gJHoofYlT7B48MzFJN+ehmoUvlwhk9Lj		   4.240264423	2018-05-26 19:49:20.730
	output: smz/cxiF+0PU195lGeabUl7Y+9zXSo0O		   1.674298642	2018-05-26 19:49:20.732
	output: 0dxkjsZIF7CSkhwaEIQhw2FxAPfTQKMK		   3.335432613	2018-05-26 19:49:20.734
	output: 8QnZ/npcEb3TpC7vu+KmnKuliB5Hm1/C		   2.207418063	2018-05-26 19:49:20.735
	output: svfKvZmRh0JzpccHexNWszzC89hxSJBk		   2.420571378	2018-05-26 19:49:20.737
	output: J2hkfEo/NjQk2qkRiTC1uIAqRn02us7R		   2.571103142	2018-05-26 19:49:20.739
	output: Ygb1kLy5U+GcdlNXchT4LOkqIBqeNASb		   2.745096648	2018-05-26 19:49:20.741
	output: DDsY4Twg8vLnHZBgBA/dkoZRERF2Ec42		   2.667773891	2018-05-26 19:49:20.743
	output: RehY2KcoFza6nqOnKfAND140sJ19TNzv		   1.843385302	2018-05-26 19:49:20.745
	output: Tt/vJTpaN9xK8q7Uy7PC0XQBKWHMjqny		   2.595404806	2018-05-26 19:49:20.747
	output: gGscXKZkxLt6eXYzRc6XavB7R7RVtgWk		   3.146550384	2018-05-26 19:49:20.749
	output: OITRAMFodEqj+VEarWNKyNu1gAuz+PUg		   2.509774252	2018-05-26 19:49:20.750
	output: IzKR+z1BH3kl+w3owO2Dh1wSocybWFr3		   2.893416060	2018-05-26 19:49:20.752
	output: 5Y5C41gOx7JzVIyTZGl0vMIbujcBEEMc		   2.220732337	2018-05-26 19:49:20.754
	output: Jhta7c8SJV3o6uZv+9qtSwQrdNHqCGtv		   1.055110190	2018-05-26 19:49:20.756
	output: Y+boMjK5O1IA89WLw/X4QxDXUlU/VJMZ		   2.271472447	2018-05-26 19:49:20.758
	output: 1RbkXTjdoKfZZ7ZFaAJKZCAorYWRsGD5		   1.980498761	2018-05-26 19:49:20.759
	output: FBADMdIJefP5G8bmJgHSRR18T9iDn76n		   2.063634996	2018-05-26 19:49:20.761
	output: 312BVf2b+tnRr7a8M/zPyCr3KKXC87QN		   1.376562813	2018-05-26 19:49:20.763
	output: 9VO/K6nNVrKRAYA33D+oREa9WOhUXtFJ		   2.996642061	2018-05-26 19:49:20.765
	output: qOVp5naa5nB8ChlJoL4xIqA1KfH0yhkK		   3.707789120	2018-05-26 19:49:20.767
	output: MmU276CYtNW6AG55jBNOfBEL7E2X8yPC		   1.650011153	2018-05-26 19:49:20.769
	output: tvl4GpiRfnCmT1CCnyl/XZPD0MZMw6gP		   2.401287591	2018-05-26 19:49:20.771
	output: UWdBONPLeK0lAeuYcAIrtnc/+RXTqN2D		   4.560579251	2018-05-26 19:49:20.773
	output: dPnE7GYbj/zozLm8kzSOAC0OpcxedVtL		   2.911505880	2018-05-26 19:49:20.775
	output: 27B1bydg9PMYW5HApiaFiudTc43Rf8Z2		   2.544043265	2018-05-26 19:49:20.777
	output: DYPLizr6qxuKiTDt3OiPN4kzwofXGX9x		   2.600081193	2018-05-26 19:49:20.779
	output: Oj9tLOowd+XwGPXfqyvmmdFa5BZpucXm		   2.015463729	2018-05-26 19:49:20.781
	output: tHftcCf8i4MYvUKTFBOWxaJotQpm8WCv		   3.106038727	2018-05-26 19:49:20.783
	output: 0BQvpl1A4QsNDK7z5JPCX3nbR73NLVkR		   1.612389828	2018-05-26 19:49:20.786
	output: TRxWRXaNPBJyTlNdWSx8v2dn9//0WOM3		   3.380619372	2018-05-26 19:49:20.788
	output: m1y90btw39AyOvYzv8i1QILfc9zufhdY		   3.270448243	2018-05-26 19:49:20.790
	output: yi9V5lCFqbg+nTEKXr8RdzrF7FaRTEph		   2.031948110	2018-05-26 19:49:20.791
	output: EG2/cY6vUB8G9dMta7P/sSrMYwVgD12Y		   2.799381823	2018-05-26 19:49:20.792
	output: li/4Si79+e3YWbrPjowryEhO9HsHMoWB		   3.979146824	2018-05-26 19:49:20.794
	output: YzRLpYG7qZ6yYkZumffvm/pygMAhGWPi		   1.829002441	2018-05-26 19:49:20.797
	output: cR9G5AUdYCGHBRSwlt1vWjPzrPmPTrEi		   2.057887439	2018-05-26 19:49:20.799
	output: FmQzEEvPgLpS2iElcGZaOogCTTWzSS29		   4.448069736	2018-05-26 19:49:20.801
	output: qzDvdUmJFr2OWC9sFhwC1FDwOFaNStgO		   2.005542846	2018-05-26 19:49:20.803
	output: ngZtA/wjnFbz2/SqHudsXOzZzP8u7Vu/		   2.209646253	2018-05-26 19:49:20.805
	output: ZdvAHjNsk3wW8vaQJx2URo58p0lamLH1		   3.074597788	2018-05-26 19:49:20.807
	output: d+Tx9Ff01qY5dSekfVMzDipLzVf/7H2L		   2.057458531	2018-05-26 19:49:20.809
	output: KXzHvHdFe4ofwYgDibt+afcswRrtlre6		   3.535271495	2018-05-26 19:49:20.811
	output: X55RfT76hyNUUGI7piNOq/7EqIbj6i1b		   2.246130186	2018-05-26 19:49:20.814
	output: HsEM8qtJxE1NBBoo48D9IXFlwotUqdfm		   1.065312918	2018-05-26 19:49:20.817
	output: 8QGgI75S9OPuCTrq4n9VIfcEn8HKwx8y		   2.843917751	2018-05-26 19:49:20.820
	output: 72EYoIiY0dCsbFTWvUU9RP4fvsQFuq7s		   2.049953267	2018-05-26 19:49:20.823
	output: NUPp3WthzRSRCLrcpdJOMjhlHTsDlYSc		   4.205742598	2018-05-26 19:49:20.826
	output: /zYKyaQDaUOuUcORIFF+lnaX4T+Gy9YS		   2.684411043	2018-05-26 19:49:20.829
	output: euLEsrLMX3aQpoCIdl/d78ikgOO5n259		   1.701310919	2018-05-26 19:49:20.832
	output: XeMkiueHKWUh7tvKLDdGsZtv1ssuO2C8		   1.727076664	2018-05-26 19:49:20.834
	output: TBrWSki8hI1VtSzRsoSEI2FY+Q9b4mmh		   2.552319972	2018-05-26 19:49:20.837
	output: sGVMAdjptD6RZ1js7Quw3qHHanabyrBn		   3.259949352	2018-05-26 19:49:20.839
	output: yBw6439wN3uCSXzPu0umifb45y0GO/qH		   1.628290609	2018-05-26 19:49:20.841
	output: k2i7aHPLrzVkbwUAwEKWyx1YNFvejSjX		   3.207852371	2018-05-26 19:49:20.844
	output: xZZmX/oggNthO5bdntFkiClf1f13XVbY		   1.539975561	2018-05-26 19:49:20.846
	output: ir9HJ+Iaco6GUX9FEMniNX9iw6gBfiNs		   1.726300172	2018-05-26 19:49:20.848
	output: UcRUTQuv5q4Qyn7ahTRnfGCjhRvYfni1		   1.361617646	2018-05-26 19:49:20.850
	output: 5V/9f1N1mlm4PaGj7RiH3XzqkxBdhdqo		   1.749488192	2018-05-26 19:49:20.852
	output: UERiQhdrXIHRVwueqD/3aHQYIOFWEkTj		   3.913086969	2018-05-26 19:49:20.855
	output: LUqonZDupKfIEbrWE300gP25JtlezusW		   5.251649594	2018-05-26 19:49:20.857
	output: FaRO7VR0cTYKtBWu9WHwpV+lWJyO6H+J		   3.817874340	2018-05-26 19:49:20.860
	output: itr8xQuhOAhO5PLCF7GulakH2q52B0KO		   2.293788790	2018-05-26 19:49:20.863
	output: ycOGYRrqIRmNfB44GgPMDld3yxkmWgV3		   3.554927796	2018-05-26 19:49:20.866
	output: exYyaaX1qpj71hiLNgIRDywA4qCLV4Cx		   2.065850084	2018-05-26 19:49:20.869
	output: tvlOEDth5qNAt+Z7YNv+vOwc4pFf5UEq		   2.298133320	2018-05-26 19:49:20.872
	output: wnZQmUQl4ZvRTcRX3qdMCnkpTVTOmeYB		   3.812619967	2018-05-26 19:49:20.873
	output: z7ogC1tHpmM4Hn0d5c5boddNNyXJhEoZ		   2.640709698	2018-05-26 19:49:20.875
	output: qz4lJgrBvsi7CZrZsay2IZmUdTU5obLI		   2.916266230	2018-05-26 19:49:20.876
	output: ZbjZDwGOfZWXUsXorLMJ5og1CfDZE/qm		   2.201792291	2018-05-26 19:49:20.878
	output: WVeHKh+IVp4UHJZwvvQ3OfkpW8M4Xq5Y		   2.184742024	2018-05-26 19:49:20.880
	output: I1exNQohrmMV+H5dMYVsr2RjFKLABL6B		   2.013300542	2018-05-26 19:49:20.882
	output: ItqGSBR0QvPhiMdRgvBlvty4hkDOF1n1		   3.545807581	2018-05-26 19:49:20.884
	output: 5ZhVORPUVDSaHSFlvTs86dH0XNNzWEFM		   2.265666308	2018-05-26 19:49:20.886
	output: ef1zN8femcz6Q6jmYY2hqEQglwMs6FJw		   5.026009487	2018-05-26 19:49:20.888
	output: alQ0QJaeQMOoz+iGDnYSPFTUKjXy6AwB		   7.440831045	2018-05-26 19:49:20.890
	output: pAmJ6Cal2HPuNv6dJJaMGo5dVOXdUsEG		   1.749342385	2018-05-26 19:49:20.892
	output: muB08VbAvGx2xkXQLvX3Avby+JydoANH		   3.013914194	2018-05-26 19:49:20.894
	output: eOm/MYIs/WAz1rytObXVxL25+5/cj5HO		   3.397784726	2018-05-26 19:49:20.896
	output: tTx5qy/XLWq+pJ84wPO6WV7htBxhy6gX		   2.021192880	2018-05-26 19:49:20.898
	output: xPQ1pc6XZj+TETNGsIrdSaoOHs376nfK		   1.831849695	2018-05-26 19:49:20.900
	output: fGgdc6QEZtHPpgukTnBRytvaXW0BniJm		   2.072578295	2018-05-26 19:49:20.902
	output: 4BLFAYDc5epss2NwI4Wd2FspsTOvxEv+		   1.864325497	2018-05-26 19:49:20.904
	output: 0OlgUdHgKc8sebLTPT2rUMjuhPAvQRBk		   1.900306005	2018-05-26 19:49:20.906
	output: dPik9bvsezEqSYTnkjLcaVW8/bUpWANi		   3.800593021	2018-05-26 19:49:20.908
	output: pUYnYZt/Mhd8GeTxADOura2F1yP2kqcC		   3.929730087	2018-05-26 19:49:20.910
	output: iPxCOa2UEIzHmDj1HQdVscBpt5JUWmHb		   1.818629102	2018-05-26 19:49:20.912
	output: V1oWFQ7jIE9vTXgkxDKyanToUAF2EieU		   2.699562727	2018-05-26 19:49:20.914
	output: od2L1V2xsI8siFIirw3UOo2m/vigYxW1		   2.886990764	2018-05-26 19:49:20.916
	output: PRXfEEi3FMxRQ2LTs5dX/ZgZvtUcCHpx		   3.010785167	2018-05-26 19:49:20.917
	output: msUqOeNUFISmKu4zxWYT3DR3yGgQBxmA		   2.051231608	2018-05-26 19:49:20.919
	output: uyevgNfHpMJW/gmSQjaUJXsk/T8WQDDG		   2.818412452	2018-05-26 19:49:20.921
	output: mrkkC9E0Hp4pLOU/VVLlkHShuPeFUdQH		   1.659779806	2018-05-26 19:49:20.923
	output: 6JXmQeTMSl+PKwsgN3xZ/KAEBOB6Wy02		   2.348065199	2018-05-26 19:49:20.925
	output: uH8w4sT17nWiY7M6w7RmQ5QjUm+ccAK8		   2.609650728	2018-05-26 19:49:20.927
	output: p8lkP1/7qPeD38U2hfzI9WSaNldz3AW6		   3.928441998	2018-05-26 19:49:20.929
	output: NM7imAVqHJVZNZAaN3p6rBJjKYrym0w9		   1.086291691	2018-05-26 19:49:20.931
	output: k2TLCVOB9gLeVMPPiHhDLZ6Xo3/Gd6xN		   3.977664462	2018-05-26 19:49:20.933
	output: uuR4PryDicXY6LE6cVp+1mtc6kzr+fVn		   2.200123622	2018-05-26 19:49:20.936
	output: tdfq0NE1no0gvOmR3Hytpg8Q/eiiUnR1		   1.177611347	2018-05-26 19:49:20.938
	output: gZZNA/Y4y9RkpYncj1F83/Z9IAykxqnI		   1.865935632	2018-05-26 19:49:20.940
	output: Hh3ENMob1xqVtMFpw0vdrw2ncEVCNHhN		   3.914878146	2018-05-26 19:49:20.942
	output: 7SQ9TktqQfGaOOMnJo8yAcxmevaI4iFv		   2.589143302	2018-05-26 19:49:20.944
	output: nTQlFf3Xtwg0gjW5Rq6ljB6FLUcekAPX		   4.113838707	2018-05-26 19:49:20.946
	output: SILY5zYsdygf03m1gy2HThEB65t1BkSQ		   1.658248081	2018-05-26 19:49:20.948
	output: zZWF14xxkY2D80GGdlSh2v5pTfG/efl6		   2.235593684	2018-05-26 19:49:20.950
	output: sKz0m+mir1aQCmM4KenBV5cHmFAPrLow		   1.813300227	2018-05-26 19:49:20.952
	output: BORtt+K+6AOrXqeOqAZyhizXwAKouCDD		   4.490842329	2018-05-26 19:49:20.954
	output: C3LBsTLeSkujN2GLGlcFQHi5DBOS2WZW		   2.495678280	2018-05-26 19:49:20.955
	output: w7NkufOi4C4J1wHHMkkOTi2t6kdA55XP		   4.777126756	2018-05-26 19:49:20.957
	output: CyPMN2FoYETe9YA0QteOzIsKPtrLeVY+		   0.953997210	2018-05-26 19:49:20.958
	output: oyUq/H4qn8PDrcBftkx/JCNN1XpIP4Uv		   1.352127605	2018-05-26 19:49:20.960
	output: 8Nxxcf8miGLQat1Vus51kEjTcw2l8YPb		   3.335645260	2018-05-26 19:49:20.962
	output: 6Bq2drylm+edveTYcBDprm7SgNr20UWm		   1.795833675	2018-05-26 19:49:20.964
	output: pXXiIkyADDYarbt7xMWEVJp55MXf2ZLm		   2.411565895	2018-05-26 19:49:20.966
	output: mYLH1Y3ovBMbi46lOtFmHpY6vuwZdD9a		   2.625656505	2018-05-26 19:49:20.968
	output: HIUppTlLy7qALqSIR3Ce/rkI9vY5ewuz		   2.556238015	2018-05-26 19:49:20.970
	output: JtoqOD+lxQ7A/hqFHj968xWoJyia9Xoy		   1.654739190	2018-05-26 19:49:20.972
	output: aZUdksgheOPv/0mnnsfyJ28a3e+HmrLq		   2.960498157	2018-05-26 19:49:20.974
	output: r/6Sp6qA3P2jjqn/NwWwhJX7FV0At1tZ		   3.998029584	2018-05-26 19:49:20.976
	output: vpwZ7oPs7XAOYp3Ha+zJi9s5vb08I0g9		   1.414426853	2018-05-26 19:49:20.978
	output: rs5tvZv6ePWtTIZpvGXJWNtIfXp22SJA		   3.022049186	2018-05-26 19:49:20.980
	output: hrG4wyQccWafLZ528Iq07Okvy+Gv7BZX		   1.998626616	2018-05-26 19:49:20.982
	output: XJEL3aW+hnU3HeJR1A8IhL3eG3+/zz1Z		   1.835162358	2018-05-26 19:49:20.984
	output: v6oQe4MdPBUd7Z3lPIBenMTYwRjz4GsN		   2.696050485	2018-05-26 19:49:20.986
	output: pWQP+iPbFGy3a+mGuJrta+U3/jHT1TMM		   2.114342037	2018-05-26 19:49:20.988
	output: /a4ZSoI89Hl+usTB81apxpYYKk8l4EWf		   1.155875198	2018-05-26 19:49:20.990
	output: JPSdiyivo4k57Mp8th9hcjVrT7uSG9oS		   1.826864200	2018-05-26 19:49:20.992
	output: qDkRaa0hC9OMNZRRtO0/bh+a0XoliYVK		   1.973755144	2018-05-26 19:49:20.994
	output: A10gYgfDzFD6XHYg+Zm73UYUkCN2B7ZK		   1.656714005	2018-05-26 19:49:20.995
	output: 8IQ3aziOIn05UK0u3Q6/BFmqQ/YAgDDR		   1.323280764	2018-05-26 19:49:20.997
	output: H3eyD327E/Dd4gmmGSblwYuPBNBXwF4V		   2.961267439	2018-05-26 19:49:20.999
	output: oixM46kS+/d9C6z2Nm8v/V2BIgPmXzSr		   1.634208910	2018-05-26 19:49:21.000
	output: w4wreXCQ3HBazTrOAbjjgseiNIa2fPky		   1.528442905	2018-05-26 19:49:21.002
	output: 8ps/vlTqFWLxCgCuJ+ymjeeZ9Bjm63+2		   2.141503070	2018-05-26 19:49:21.004
	output: SZB8a9zv58/oU3rfsfugTxWK/Y0QspRh		   2.413570153	2018-05-26 19:49:21.006
	output: FXZ+zGIqucddqEPWEqTFZ1Ksen2m4DeD		   2.439004809	2018-05-26 19:49:21.008
	output: j6jdSezrOL8uipTdop7wpqio/TkO6rbk		   2.420212696	2018-05-26 19:49:21.010
	output: oXw81IUATYVL5y7qYMo4dbPMsUyvLZjC		   3.009716389	2018-05-26 19:49:21.012
	output: I40yobXMaDMrO4qPWY3S4Q4+DyBx6SkI		   2.886382385	2018-05-26 19:49:21.014
	output: i+Hy6yTFPAki96F/wBPcBSDnuKzxumSo		   2.041017468	2018-05-26 19:49:21.018
	output: vOiSHVzJ6MKjB24nGb0z4jWZ/jRo82j6		   1.300628346	2018-05-26 19:49:21.021
	output: rc4Kq3d1QVgSe0VuZwgDrg9cT0v7d4ix		   1.968508885	2018-05-26 19:49:21.025
	output: Mu2DoVELynS+pmZCvBNiO721QC+6fdT1		   0.878709556	2018-05-26 19:49:21.028
	output: LkvHMkfX/xFCv9oYn/P9W/4jXkhraYau		   1.426650108	2018-05-26 19:49:21.031
	output: q1VqU9uhtW5N3icnyeLio8iUfrC1x9B1		   1.307355278	2018-05-26 19:49:21.033
	output: tLwt8Z8+Dj0iYCUvhWhFHeoKTqTvYfBx		   2.460897157	2018-05-26 19:49:21.035
	output: AaClHDlpinIxPX8Q/WSFbAxhtbzaGyMk		   1.539234333	2018-05-26 19:49:21.037
	output: IvXZVNPsPf7GYzTg9OiSq13NEER9iYJC		   1.198354647	2018-05-26 19:49:21.039
	output: ARNgeYZm9Vy6r+BfczJXQLFL6M1gUEik		   2.970623527	2018-05-26 19:49:21.041
	output: lh0IYuk2Rhkhxw0X3ygyqQj26c3fq96t		   3.086815342	2018-05-26 19:49:21.043
	output: WObvC0faJpm5BrE88cYL/c/J+qtZd3gt		   1.782789361	2018-05-26 19:49:21.044
	output: bheoiujPSerYk/OXb2XGFCTsDgctLw4v		   4.339392891	2018-05-26 19:49:21.046
	output: 1ITsfRtP5/481MndKseho0URaR8PMNGJ		   1.342577285	2018-05-26 19:49:21.049
	output: UVuQO4lWWzRM8zIE6/GpcuUIP8Q3FTFK		   2.549177157	2018-05-26 19:49:21.050
	output: Jor7dCcMc7Lt9Xe0PMrI8vn+E9sPecBI		   1.590683860	2018-05-26 19:49:21.052
	output: Bm4bXCLnj77rkX0WeMRRcAgN2iwl/P+n		   2.379890469	2018-05-26 19:49:21.054
	output: PA5nHPjE3QdmjYOZ6rPzfWQb2ylm4l+t		   2.047786868	2018-05-26 19:49:21.057
	output: ZlqBCutZInQDfll1EXZOJflk26RIA37y		   2.114708561	2018-05-26 19:49:21.059
	output: MpcoeC9rzZeJiVEKbcFjcDYc3sCJRVC+		   4.419527972	2018-05-26 19:49:21.060
	output: fPzLUIvVfIXZnYp86J7/rvSpJhZqPm1q		   1.803446771	2018-05-26 19:49:21.062
	output: 6mWJJwQBu36QyXMV8buOwE/7FOorcrBE		   2.463824202	2018-05-26 19:49:21.064
	output: tdrurG+nWRRJrS2eDxxYZVHYmoJ/Q7Yd		   2.905308225	2018-05-26 19:49:21.066
	output: RE+MXyjZNfspSObAMh88QN85qqTolEd3		   1.912407174	2018-05-26 19:49:21.068
	output: zQmMOifiYOfUKo3QGt0nWXuelihkk8MZ		   1.596724271	2018-05-26 19:49:21.070
	output: Ci32KD5HQecKi1dRqb+vY/7qHn3Rskg3		   0.417902136	2018-05-26 19:49:21.072
	output: udLU7YrE30HTnwAkSGcHpk2i8sbqpvHe		   1.569774821	2018-05-26 19:49:21.074
	output: NIr3mU1T5eYIx8Jat+8lWrROiUp33gmN		   2.523196866	2018-05-26 19:49:21.076
	output: mkIuLwtihFp628WtaN2GPGi1+J6wq6U6		   2.116406416	2018-05-26 19:49:21.078
	output: hQqYVtLseJNr90Tf8LedT8cvnRaiOzhi		   1.514339143	2018-05-26 19:49:21.080
	output: 60riylzrp7+jZkDIcTeu8o9uV43B4Kvs		   2.767321380	2018-05-26 19:49:21.082
	output: O9RmK48x6lxdHl6Ij22fGaws3fTbyTKu		   3.171468276	2018-05-26 19:49:21.083
	output: O9a0bPQqrtDZ4KUjYhZjiJU262KRgAt7		   1.699996066	2018-05-26 19:49:21.085
	output: zGIDYsCu/noWL1kVZxtPNV+9gJF5iKyH		   1.143935885	2018-05-26 19:49:21.086
	output: Wx8SOJEj4UifBWeEjAI7sTRIMpmgUn4U		   3.258787049	2018-05-26 19:49:21.088
	output: gkxCsqWPz2BAz1U0n6d70+NkAa4KZ7qv		   1.084332250	2018-05-26 19:49:21.090
	output: oegXEj6pCqcjJoCCFVlTGTJG+MNUwApG		   1.583617066	2018-05-26 19:49:21.092
	output: tZtDWKdNYmVj51Fc4DL0PHYoBtUrM9gX		   3.736824217	2018-05-26 19:49:21.094
	output: 1Z8bfq0E0I10dGREyHpVmuaVagtRN3Lj		   1.673857729	2018-05-26 19:49:21.096
	output: Zlp4Hlni6/mGxHU8k0EnNdYcC6oz+tvb		   1.473249893	2018-05-26 19:49:21.098
	output: 7VZ5gBy7g2eBm1HCde60Hp2a5IAbOOK8		   1.233556914	2018-05-26 19:49:21.100
	output: KkUoG9TSwj5VzgNC96DQJ/YlgJoNvw20		   1.840359303	2018-05-26 19:49:21.102
	output: ogMOhBuYr73MEkJRKx5XW/iNVVz5onRb		   2.439120222	2018-05-26 19:49:21.104
	output: Q4Wi69ON3MOS8f1gjFoOYGBxC1cMs0DK		   1.943762653	2018-05-26 19:49:21.106
	output: 7928AYkCbW8QZMhs8O3V30oo9Tq8xZzn		   1.152305302	2018-05-26 19:49:21.108
	output: aqYX4ZBW2T7CKjopdLs7p/kilB6ZnMHk		   0.787538899	2018-05-26 19:49:21.110
	output: koP72wH0YrUuhavQniTaYB/xi7m+FUSJ		   1.384341381	2018-05-26 19:49:21.112
	output: 2Pg+hduwpkxy6sEAh1oM9jaa/DJ5zZ8o		   3.407944511	2018-05-26 19:49:21.114
	output: ejhGT6JzdN2eg6ks014c8DgvL3IOofkd		   2.388635677	2018-05-26 19:49:21.116
	output: YNSxE6T+PNXN4+rX4ua+yj9Aog6hJXXh		   2.084925866	2018-05-26 19:49:21.118
	output: iREvXXmobvW0ldHSQNPzPwqZt9eAdrzb		   3.185582737	2018-05-26 19:49:21.120
	output: ecsSQzGmrc0J2mTaQebZkRSETI21rEGP		   1.676784272	2018-05-26 19:49:21.122
	output: lQp4eNSvLo83li0pKKGahYVqtRmQ0LkB		   1.043287156	2018-05-26 19:49:21.124
	output: 4Y4qeRNMx9GusUnbluOsEalHaLUnK+u/		   1.295943590	2018-05-26 19:49:21.125
	output: DhJv+0B5ixfYhraKsXWNGmoeKJCt2xLW		   4.069795163	2018-05-26 19:49:21.127
	output: EgD2per1WNYr+as6gUGpTFrluOMK+yo8		   3.249663019	2018-05-26 19:49:21.129
	output: 83wkKbN9N1rWQhR9jyHwxwAxx4Gm8F0u		   0.912549415	2018-05-26 19:49:21.131
	output: DYIsBSrUQgUW5hJDIubdn8v94AjfrvNf		   1.585580505	2018-05-26 19:49:21.133
	output: gMrtcjY+/G8+2RiAh+KPP6i/b3Gyp1qm		   2.317411417	2018-05-26 19:49:21.134
	output: hfuNbW7Zmtm4rksmYUDK6Yp0g7/RLBlu		   4.014308833	2018-05-26 19:49:21.136
	output: 2OcesXy1Vi+EYdKVWFrsSZ1PjJ6252bR		   0.966530474	2018-05-26 19:49:21.138
	output: I5PrWdmWUwWbtJ/Fm3nD8SSWJYaHOT4q		   2.155795892	2018-05-26 19:49:21.140
	output: xZ7Q2rFfYn5f7ehx6LFkoBaRv2NTvcby		   3.010832007	2018-05-26 19:49:21.142
	output: /UfG65xGOutXFyFrZYAshT2eCWFyCit4		   1.595878324	2018-05-26 19:49:21.144
	output: C2F2D7wE8hfspyxG7SPlSlfjWDvJMFUs		   1.548233526	2018-05-26 19:49:21.146
	output: /Dssdf3jpLDbjduTziSabrpY7Pzzj0am		   3.352757961	2018-05-26 19:49:21.148
	output: YWfU774GFF7c24jRYOHhFwePZ/oCr67X		   1.947647050	2018-05-26 19:49:21.150
	output: 2uPe3sAHnpooznvfbyYpZVHy0VfV32f9		   2.684925480	2018-05-26 19:49:21.152
	output: Opb0H4m6X2eDAB1Ih/i54skkzPwgC7XI		   2.186845379	2018-05-26 19:49:21.154
	output: GZptLPk6MSMfNJ2DA401NYKhagkmFOnT		   0.915511960	2018-05-26 19:49:21.156
	output: L1ttbELYZsQKNluOT6qgZW8tq9kuLuim		   1.329773587	2018-05-26 19:49:21.157
	output: UBHx242iXf/7ULwiMhtM8sXYfniUu8nb		   1.097448498	2018-05-26 19:49:21.159
	output: 8aOfpRTtxFztmZxZ8387jKq3GXEWkLOk		   1.352462153	2018-05-26 19:49:21.161
	output: rZh5GItOfqWgJFmXsC3w1Ga1bG9YjTMR		   2.438775598	2018-05-26 19:49:21.163
	output: pPg5UQCijq4ZISlzK9dQNKZ/MvwO1opV		   2.216009252	2018-05-26 19:49:21.165
	output: D6pv8gbRIWXhwafRxadQW1LySNtG57e+		   2.573271659	2018-05-26 19:49:21.166
	output: ztMzMOpd7c0bMGMvnJGGzeBKG6mfUf4M		   2.358095234	2018-05-26 19:49:21.168
	output: jj5ZD/D7EjWsbmB3qMLQ3C0qsJETwIV1		   1.432860210	2018-05-26 19:49:21.169
	output: iV+oZmRX4eMWEWGw2I7XGheU3q/Ku03n		   4.321204221	2018-05-26 19:49:21.172
	output: xVvf6wEHQ9tjzzHC7ysfjHbT4Sgnn5r2		   1.793925677	2018-05-26 19:49:21.173
	output: VIeTSdJUNQJDbC6aIAW8gR3nuJv4h2OJ		   2.974168096	2018-05-26 19:49:21.175
	output: 2I6qgaVX3YXl3BfCdYLNex7wgFszbERc		   1.954104167	2018-05-26 19:49:21.177
	output: 1jGpyypuKIY7Kfc80CiZcLPa7sVbkRLa		   1.242570625	2018-05-26 19:49:21.179
	output: L6QQ9wOcDvLo+0GSCN953h2cWsH60/Z4		   2.866107579	2018-05-26 19:49:21.181
	output: wZ8X4RoD0FrNPCbmeS6IE3ODXw0CnhKe		   1.077541276	2018-05-26 19:49:21.182
	output: 0oVBvp2p1ipNQJhbi1YEUAREX7m8hlDH		   2.633927687	2018-05-26 19:49:21.184
	output: Ufp4iUoSXEzuwiMrtNtV7de0IILc7c53		   1.069579430	2018-05-26 19:49:21.186
	output: hrn26hG0NMCnieEFe+mMu00la1DVQJ3U		   1.942649338	2018-05-26 19:49:21.188
	output: HE8sAXu0I8BY4o4ZXI66UeiKGPXt9QYg		   4.044520010	2018-05-26 19:49:21.190
	output: 5NdB2SZiXx00h2Y0zyLt14soct5aU+RG		   0.553203563	2018-05-26 19:49:21.192
	output: 3HbRFg9G8mHAHNZbzuZwIADgM8joGgxK		   1.425130188	2018-05-26 19:49:21.194
	output: RYkr3TUkS3WDXXhE7OaUw2YjcO4OrySd		   2.687300150	2018-05-26 19:49:21.196
	output: 4sXpUG/EQmSKg1NHTG8uo8Y5TOK3SaMW		   0.975997569	2018-05-26 19:49:21.198
	output: 444JvcXprHvD5frdBlI2fQ80MNwnspzu		   2.233397630	2018-05-26 19:49:21.200
	output: Os435dj2LKurg0hLQiHCVKX3vIgW9XmU		   1.576440275	2018-05-26 19:49:21.202
	output: GuFxT05TqXxmLH6iYmx22C7r7Vyfy/fw		   0.656303866	2018-05-26 19:49:21.204
	output: bYC9PDQOXawlp75I7yZJtF8pE5/pflMS		   0.729044499	2018-05-26 19:49:21.206
	output: h/VP64038iIPknKd4owHf/QODnyo7m6a		   2.338533620	2018-05-26 19:49:21.208
	output: vyGhb4+UEiXUwm6K2p42VEQw5PEfJV9f		   0.616750372	2018-05-26 19:49:21.209
	output: MSgfD9NiYFxfnwnEN8rSqbeRSMpu/D6l		   0.592096773	2018-05-26 19:49:21.211
	output: lVMnxKVIBR/X0qPytaP0el4v3MP+Ks6j		   1.870216760	2018-05-26 19:49:21.213
	output: XX2aVxNzvKeOVIPenSwpCjL39G7syYBz		   1.023326142	2018-05-26 19:49:21.215
	output: 99p5hFDTtzq2k1LzbnxR/LkbjBZKsXQ9		   3.134266983	2018-05-26 19:49:21.217
	output: sKqjtAi9BJYdU5hKKyQQNkF6JY0BGBXN		   3.034347623	2018-05-26 19:49:21.219
	output: oO4hakcJ3vwpUn+s9HMV63mEHU9t/Qcy		   1.552188630	2018-05-26 19:49:21.221
	output: MEeOZ02CFU8VaEqDlD2mCY5oHbQu/Cqz		   0.379617618	2018-05-26 19:49:21.223
	output: RYbI5nbjU4N5gyHNqGrdPhvaaVbFld/b		   0.583310463	2018-05-26 19:49:21.225
	output: F/BdU+7GyHxOkMniwHpt5Tt0r0UFnfJZ		   0.082713423	2018-05-26 19:49:21.227
	output: sKjnlDvKEmZyHwKpOO5se8ugRMRgLsj+		   0.207872027	2018-05-26 19:49:21.228
	output: /VpR3JjbTgEoGrzlTCVJUqMn7N4nqCZu		   2.037629894	2018-05-26 19:49:21.230
	output: zYf6uRBMro4jzqCTRU20be/kc4wezHRq		   0.469957754	2018-05-26 19:49:21.232
	output: 2+KkEFsaan3yEZj2GNX0fXpDgCJmuTYy		   0.931297093	2018-05-26 19:49:21.234
	output: vEJfnfRVB8X53AVVlgBOZJxIM0PzZi12		   0.105292045	2018-05-26 19:49:21.236
	output: HgZ2aY+gJzioF/avzwbzgb0FKZv8MR1s		   0.908594982	2018-05-26 19:49:21.237
	output: HfFCVFq9Z2wEU50AOytH90F6sPR/6ePN		   0.285451259	2018-05-26 19:49:21.239
	output: i/lOsxToXt7hJrz3TWUj21ji87hO5VH4		   0.595436891	2018-05-26 19:49:21.241
	output: 39xEXjU8BQ7flPMLGq/9udplG9aaobxL		   0.585954342	2018-05-26 19:49:21.243
	output: 80CykzY4sdap2RPMexQdSt0DIchI4Guk		   0.115601075	2018-05-26 19:49:21.245
	output: QPdsPuLRYt6xfQfeq/tA6upbJgsmHY6B		   0.346154796	2018-05-26 19:49:21.247
	output: szhe4/brqyt1tjcG6R5Jnp1DOes+MTq6		   0.439448512	2018-05-26 19:49:21.249
	output: OUj48O7cUfqFeYDOQJzw+LA/b+A9FmXi		   0.073340981	2018-05-26 19:49:21.250
	output: NvfjiZMxBrtxjfp+sdZn1/EQTKQ0PDI4		   0.619787876	2018-05-26 19:49:21.252
	output: P3/4KceASRm2DY9uU6AMW+Hr48g9mCXA		  10.291589971	2018-05-26 19:49:21.254');

			if ($this->versionGreaterThan('0.2.4') && ($cmd[1] == '////3aEv+N8KHkA/CW7xOw+i5uLL////' || $cmd[1] == '0000000000000285cd1c19cbe2e6620d3bf16e092b401e0adf602fa1dda57b40'))
				return $this->commandOutputFile('      time: 2018-06-14 19:34:23.999
 timestamp: 16c26daffff
	 flags: 1f
	 state: Main
  file pos: f0e00
	  hash: 0000000000000285cd1c19cbe2e6620d3bf16e092b401e0adf602fa1dda57b40' . $block_remark . '
difficulty: 630f2898a810cecc2a958835ea9
   balance: ////3aEv+N8KHkA/CW7xOw+i5uLL////		  10.240000519
' . $separator . '
							   block as transaction: details
 direction	address									   amount
' . $separator . '
	   fee: ////3aEv+N8KHkA/CW7xOw+i5uLL////		   1.000000000
	output: F81tjpr7ONhatIapuqU1LqLOP3q7gzhQ		   2.000000000
	 input: rFYU3YXvzhZHFrq0HMn1W5AwIyjCASzb		   0.000000000
	output: EL5Nfmve021QCBB0yyLS69QdKwV9xH3r		   5.000000000
	output: t3sOkNEKp3TwQXEeThSxAWDFymAqJELs		   0.000000000
	output: ElecK6xIzLcVK8qYmDuOALOXl2SFx1N9		   7.000000000
	output: 2NmDFr0AJeAQxNn4MtpU4XRxB+/Og94/		   0.000000000
	output: tkyKSe3ajtG03eEZ9/zdSHQ6x9lAIjMy		   0.000000000
	output: QctB5ycsHBt0D7g1CRgdNQ9Y/kTHD/P3		   0.000000000
	output: ssafXksV6+10EWrROBGCbuCSY65SmiOD		   0.000000000
	output: g3bAGI/Oecez51hF679ZSXrgHTVObW1f		   0.000000000
	output: 9XFlMlg5G1xgptjFmwdOxUVte3HSwcWl		   0.000000000
	output: TQxa9lLYGTOb4spslbM0LnLaJ0ihl1SA		   0.000000000
' . $separator . '
								 block as address: details
' . $address_header . '
' . $separator . $earning_1 . '
	output: P3/4KceASRm2DY9uU6AMW+Hr48g9mCXA		  10.291589971	2018-05-26 19:49:21.254' . $remark_1 . '
	output: NvfjiZMxBrtxjfp+sdZn1/EQTKQ0PDI4		   0.619787876	2018-05-26 19:49:21.252' . $remark_1 . '
	output: OUj48O7cUfqFeYDOQJzw+LA/b+A9FmXi		   0.073340981	2018-05-26 19:49:21.250' . $remark_1 . '
	output: szhe4/brqyt1tjcG6R5Jnp1DOes+MTq6		   0.439448512	2018-05-26 19:49:21.249' . $remark_2 . '
	output: QPdsPuLRYt6xfQfeq/tA6upbJgsmHY6B		   0.346154796	2018-05-26 19:49:21.247' . $remark_2 . '
	output: 80CykzY4sdap2RPMexQdSt0DIchI4Guk		   0.115601075	2018-05-26 19:49:21.245' . $remark_2 . '
	output: 39xEXjU8BQ7flPMLGq/9udplG9aaobxL		   0.585954342	2018-05-26 19:49:21.243
	output: i/lOsxToXt7hJrz3TWUj21ji87hO5VH4		   0.595436891	2018-05-26 19:49:21.241
	output: HfFCVFq9Z2wEU50AOytH90F6sPR/6ePN		   0.285451259	2018-05-26 19:49:21.239
	output: HgZ2aY+gJzioF/avzwbzgb0FKZv8MR1s		   0.908594982	2018-05-26 19:49:21.237
	output: vEJfnfRVB8X53AVVlgBOZJxIM0PzZi12		   0.105292045	2018-05-26 19:49:21.236
	output: 2+KkEFsaan3yEZj2GNX0fXpDgCJmuTYy		   0.931297093	2018-05-26 19:49:21.234
	output: zYf6uRBMro4jzqCTRU20be/kc4wezHRq		   0.469957754	2018-05-26 19:49:21.232
	output: /VpR3JjbTgEoGrzlTCVJUqMn7N4nqCZu		   2.037629894	2018-05-26 19:49:21.230
	output: sKjnlDvKEmZyHwKpOO5se8ugRMRgLsj+		   0.207872027	2018-05-26 19:49:21.228
	output: F/BdU+7GyHxOkMniwHpt5Tt0r0UFnfJZ		   0.082713423	2018-05-26 19:49:21.227
	output: RYbI5nbjU4N5gyHNqGrdPhvaaVbFld/b		   0.583310463	2018-05-26 19:49:21.225
	output: MEeOZ02CFU8VaEqDlD2mCY5oHbQu/Cqz		   0.379617618	2018-05-26 19:49:21.223
	output: oO4hakcJ3vwpUn+s9HMV63mEHU9t/Qcy		   1.552188630	2018-05-26 19:49:21.221
	output: sKqjtAi9BJYdU5hKKyQQNkF6JY0BGBXN		   3.034347623	2018-05-26 19:49:21.219
	output: 99p5hFDTtzq2k1LzbnxR/LkbjBZKsXQ9		   3.134266983	2018-05-26 19:49:21.217
	output: XX2aVxNzvKeOVIPenSwpCjL39G7syYBz		   1.023326142	2018-05-26 19:49:21.215
	output: lVMnxKVIBR/X0qPytaP0el4v3MP+Ks6j		   1.870216760	2018-05-26 19:49:21.213
	output: MSgfD9NiYFxfnwnEN8rSqbeRSMpu/D6l		   0.592096773	2018-05-26 19:49:21.211
	output: vyGhb4+UEiXUwm6K2p42VEQw5PEfJV9f		   0.616750372	2018-05-26 19:49:21.209
	output: h/VP64038iIPknKd4owHf/QODnyo7m6a		   2.338533620	2018-05-26 19:49:21.208
	output: bYC9PDQOXawlp75I7yZJtF8pE5/pflMS		   0.729044499	2018-05-26 19:49:21.206
	output: GuFxT05TqXxmLH6iYmx22C7r7Vyfy/fw		   0.656303866	2018-05-26 19:49:21.204
	output: Os435dj2LKurg0hLQiHCVKX3vIgW9XmU		   1.576440275	2018-05-26 19:49:21.202
	output: 444JvcXprHvD5frdBlI2fQ80MNwnspzu		   2.233397630	2018-05-26 19:49:21.200
	output: 4sXpUG/EQmSKg1NHTG8uo8Y5TOK3SaMW		   0.975997569	2018-05-26 19:49:21.198
	output: RYkr3TUkS3WDXXhE7OaUw2YjcO4OrySd		   2.687300150	2018-05-26 19:49:21.196
	output: 3HbRFg9G8mHAHNZbzuZwIADgM8joGgxK		   1.425130188	2018-05-26 19:49:21.194
	output: 5NdB2SZiXx00h2Y0zyLt14soct5aU+RG		   0.553203563	2018-05-26 19:49:21.192
	output: HE8sAXu0I8BY4o4ZXI66UeiKGPXt9QYg		   4.044520010	2018-05-26 19:49:21.190
	output: hrn26hG0NMCnieEFe+mMu00la1DVQJ3U		   1.942649338	2018-05-26 19:49:21.188
	output: Ufp4iUoSXEzuwiMrtNtV7de0IILc7c53		   1.069579430	2018-05-26 19:49:21.186
	output: 0oVBvp2p1ipNQJhbi1YEUAREX7m8hlDH		   2.633927687	2018-05-26 19:49:21.184
	output: wZ8X4RoD0FrNPCbmeS6IE3ODXw0CnhKe		   1.077541276	2018-05-26 19:49:21.182
	output: L6QQ9wOcDvLo+0GSCN953h2cWsH60/Z4		   2.866107579	2018-05-26 19:49:21.181
	output: 1jGpyypuKIY7Kfc80CiZcLPa7sVbkRLa		   1.242570625	2018-05-26 19:49:21.179
	output: 2I6qgaVX3YXl3BfCdYLNex7wgFszbERc		   1.954104167	2018-05-26 19:49:21.177
	output: VIeTSdJUNQJDbC6aIAW8gR3nuJv4h2OJ		   2.974168096	2018-05-26 19:49:21.175
	output: xVvf6wEHQ9tjzzHC7ysfjHbT4Sgnn5r2		   1.793925677	2018-05-26 19:49:21.173
	output: iV+oZmRX4eMWEWGw2I7XGheU3q/Ku03n		   4.321204221	2018-05-26 19:49:21.172
	output: jj5ZD/D7EjWsbmB3qMLQ3C0qsJETwIV1		   1.432860210	2018-05-26 19:49:21.169
	output: ztMzMOpd7c0bMGMvnJGGzeBKG6mfUf4M		   2.358095234	2018-05-26 19:49:21.168
	output: D6pv8gbRIWXhwafRxadQW1LySNtG57e+		   2.573271659	2018-05-26 19:49:21.166
	output: pPg5UQCijq4ZISlzK9dQNKZ/MvwO1opV		   2.216009252	2018-05-26 19:49:21.165
	output: rZh5GItOfqWgJFmXsC3w1Ga1bG9YjTMR		   2.438775598	2018-05-26 19:49:21.163
	output: 8aOfpRTtxFztmZxZ8387jKq3GXEWkLOk		   1.352462153	2018-05-26 19:49:21.161
	output: UBHx242iXf/7ULwiMhtM8sXYfniUu8nb		   1.097448498	2018-05-26 19:49:21.159
	output: L1ttbELYZsQKNluOT6qgZW8tq9kuLuim		   1.329773587	2018-05-26 19:49:21.157
	output: GZptLPk6MSMfNJ2DA401NYKhagkmFOnT		   0.915511960	2018-05-26 19:49:21.156
	output: Opb0H4m6X2eDAB1Ih/i54skkzPwgC7XI		   2.186845379	2018-05-26 19:49:21.154
	output: 2uPe3sAHnpooznvfbyYpZVHy0VfV32f9		   2.684925480	2018-05-26 19:49:21.152
	output: YWfU774GFF7c24jRYOHhFwePZ/oCr67X		   1.947647050	2018-05-26 19:49:21.150
	output: /Dssdf3jpLDbjduTziSabrpY7Pzzj0am		   3.352757961	2018-05-26 19:49:21.148
	output: C2F2D7wE8hfspyxG7SPlSlfjWDvJMFUs		   1.548233526	2018-05-26 19:49:21.146
	output: /UfG65xGOutXFyFrZYAshT2eCWFyCit4		   1.595878324	2018-05-26 19:49:21.144
	output: xZ7Q2rFfYn5f7ehx6LFkoBaRv2NTvcby		   3.010832007	2018-05-26 19:49:21.142
	output: I5PrWdmWUwWbtJ/Fm3nD8SSWJYaHOT4q		   2.155795892	2018-05-26 19:49:21.140
	output: 2OcesXy1Vi+EYdKVWFrsSZ1PjJ6252bR		   0.966530474	2018-05-26 19:49:21.138
	output: hfuNbW7Zmtm4rksmYUDK6Yp0g7/RLBlu		   4.014308833	2018-05-26 19:49:21.136
	output: gMrtcjY+/G8+2RiAh+KPP6i/b3Gyp1qm		   2.317411417	2018-05-26 19:49:21.134
	output: DYIsBSrUQgUW5hJDIubdn8v94AjfrvNf		   1.585580505	2018-05-26 19:49:21.133
	output: 83wkKbN9N1rWQhR9jyHwxwAxx4Gm8F0u		   0.912549415	2018-05-26 19:49:21.131
	output: EgD2per1WNYr+as6gUGpTFrluOMK+yo8		   3.249663019	2018-05-26 19:49:21.129
	output: DhJv+0B5ixfYhraKsXWNGmoeKJCt2xLW		   4.069795163	2018-05-26 19:49:21.127
	output: 4Y4qeRNMx9GusUnbluOsEalHaLUnK+u/		   1.295943590	2018-05-26 19:49:21.125
	output: lQp4eNSvLo83li0pKKGahYVqtRmQ0LkB		   1.043287156	2018-05-26 19:49:21.124
	output: ecsSQzGmrc0J2mTaQebZkRSETI21rEGP		   1.676784272	2018-05-26 19:49:21.122
	output: iREvXXmobvW0ldHSQNPzPwqZt9eAdrzb		   3.185582737	2018-05-26 19:49:21.120
	output: YNSxE6T+PNXN4+rX4ua+yj9Aog6hJXXh		   2.084925866	2018-05-26 19:49:21.118
	output: ejhGT6JzdN2eg6ks014c8DgvL3IOofkd		   2.388635677	2018-05-26 19:49:21.116
	output: 2Pg+hduwpkxy6sEAh1oM9jaa/DJ5zZ8o		   3.407944511	2018-05-26 19:49:21.114
	output: koP72wH0YrUuhavQniTaYB/xi7m+FUSJ		   1.384341381	2018-05-26 19:49:21.112
	output: aqYX4ZBW2T7CKjopdLs7p/kilB6ZnMHk		   0.787538899	2018-05-26 19:49:21.110
	output: 7928AYkCbW8QZMhs8O3V30oo9Tq8xZzn		   1.152305302	2018-05-26 19:49:21.108
	output: Q4Wi69ON3MOS8f1gjFoOYGBxC1cMs0DK		   1.943762653	2018-05-26 19:49:21.106
	output: ogMOhBuYr73MEkJRKx5XW/iNVVz5onRb		   2.439120222	2018-05-26 19:49:21.104
	output: KkUoG9TSwj5VzgNC96DQJ/YlgJoNvw20		   1.840359303	2018-05-26 19:49:21.102
	output: 7VZ5gBy7g2eBm1HCde60Hp2a5IAbOOK8		   1.233556914	2018-05-26 19:49:21.100
	output: Zlp4Hlni6/mGxHU8k0EnNdYcC6oz+tvb		   1.473249893	2018-05-26 19:49:21.098
	output: 1Z8bfq0E0I10dGREyHpVmuaVagtRN3Lj		   1.673857729	2018-05-26 19:49:21.096
	output: tZtDWKdNYmVj51Fc4DL0PHYoBtUrM9gX		   3.736824217	2018-05-26 19:49:21.094
	output: oegXEj6pCqcjJoCCFVlTGTJG+MNUwApG		   1.583617066	2018-05-26 19:49:21.092
	output: gkxCsqWPz2BAz1U0n6d70+NkAa4KZ7qv		   1.084332250	2018-05-26 19:49:21.090
	output: Wx8SOJEj4UifBWeEjAI7sTRIMpmgUn4U		   3.258787049	2018-05-26 19:49:21.088
	output: zGIDYsCu/noWL1kVZxtPNV+9gJF5iKyH		   1.143935885	2018-05-26 19:49:21.086
	output: O9a0bPQqrtDZ4KUjYhZjiJU262KRgAt7		   1.699996066	2018-05-26 19:49:21.085
	output: O9RmK48x6lxdHl6Ij22fGaws3fTbyTKu		   3.171468276	2018-05-26 19:49:21.083
	output: 60riylzrp7+jZkDIcTeu8o9uV43B4Kvs		   2.767321380	2018-05-26 19:49:21.082
	output: hQqYVtLseJNr90Tf8LedT8cvnRaiOzhi		   1.514339143	2018-05-26 19:49:21.080
	output: mkIuLwtihFp628WtaN2GPGi1+J6wq6U6		   2.116406416	2018-05-26 19:49:21.078
	output: NIr3mU1T5eYIx8Jat+8lWrROiUp33gmN		   2.523196866	2018-05-26 19:49:21.076
	output: udLU7YrE30HTnwAkSGcHpk2i8sbqpvHe		   1.569774821	2018-05-26 19:49:21.074
	output: Ci32KD5HQecKi1dRqb+vY/7qHn3Rskg3		   0.417902136	2018-05-26 19:49:21.072
	output: zQmMOifiYOfUKo3QGt0nWXuelihkk8MZ		   1.596724271	2018-05-26 19:49:21.070
	output: RE+MXyjZNfspSObAMh88QN85qqTolEd3		   1.912407174	2018-05-26 19:49:21.068
	output: tdrurG+nWRRJrS2eDxxYZVHYmoJ/Q7Yd		   2.905308225	2018-05-26 19:49:21.066
	output: 6mWJJwQBu36QyXMV8buOwE/7FOorcrBE		   2.463824202	2018-05-26 19:49:21.064
	output: fPzLUIvVfIXZnYp86J7/rvSpJhZqPm1q		   1.803446771	2018-05-26 19:49:21.062
	output: MpcoeC9rzZeJiVEKbcFjcDYc3sCJRVC+		   4.419527972	2018-05-26 19:49:21.060
	output: ZlqBCutZInQDfll1EXZOJflk26RIA37y		   2.114708561	2018-05-26 19:49:21.059
	output: PA5nHPjE3QdmjYOZ6rPzfWQb2ylm4l+t		   2.047786868	2018-05-26 19:49:21.057
	output: Bm4bXCLnj77rkX0WeMRRcAgN2iwl/P+n		   2.379890469	2018-05-26 19:49:21.054
	output: Jor7dCcMc7Lt9Xe0PMrI8vn+E9sPecBI		   1.590683860	2018-05-26 19:49:21.052
	output: UVuQO4lWWzRM8zIE6/GpcuUIP8Q3FTFK		   2.549177157	2018-05-26 19:49:21.050
	output: 1ITsfRtP5/481MndKseho0URaR8PMNGJ		   1.342577285	2018-05-26 19:49:21.049
	output: bheoiujPSerYk/OXb2XGFCTsDgctLw4v		   4.339392891	2018-05-26 19:49:21.046
	output: WObvC0faJpm5BrE88cYL/c/J+qtZd3gt		   1.782789361	2018-05-26 19:49:21.044
	output: lh0IYuk2Rhkhxw0X3ygyqQj26c3fq96t		   3.086815342	2018-05-26 19:49:21.043
	output: ARNgeYZm9Vy6r+BfczJXQLFL6M1gUEik		   2.970623527	2018-05-26 19:49:21.041
	output: IvXZVNPsPf7GYzTg9OiSq13NEER9iYJC		   1.198354647	2018-05-26 19:49:21.039
	output: AaClHDlpinIxPX8Q/WSFbAxhtbzaGyMk		   1.539234333	2018-05-26 19:49:21.037
	output: tLwt8Z8+Dj0iYCUvhWhFHeoKTqTvYfBx		   2.460897157	2018-05-26 19:49:21.035
	output: q1VqU9uhtW5N3icnyeLio8iUfrC1x9B1		   1.307355278	2018-05-26 19:49:21.033
	output: LkvHMkfX/xFCv9oYn/P9W/4jXkhraYau		   1.426650108	2018-05-26 19:49:21.031
	output: Mu2DoVELynS+pmZCvBNiO721QC+6fdT1		   0.878709556	2018-05-26 19:49:21.028
	output: rc4Kq3d1QVgSe0VuZwgDrg9cT0v7d4ix		   1.968508885	2018-05-26 19:49:21.025
	output: vOiSHVzJ6MKjB24nGb0z4jWZ/jRo82j6		   1.300628346	2018-05-26 19:49:21.021
	output: i+Hy6yTFPAki96F/wBPcBSDnuKzxumSo		   2.041017468	2018-05-26 19:49:21.018
	output: I40yobXMaDMrO4qPWY3S4Q4+DyBx6SkI		   2.886382385	2018-05-26 19:49:21.014
	output: oXw81IUATYVL5y7qYMo4dbPMsUyvLZjC		   3.009716389	2018-05-26 19:49:21.012
	output: j6jdSezrOL8uipTdop7wpqio/TkO6rbk		   2.420212696	2018-05-26 19:49:21.010
	output: FXZ+zGIqucddqEPWEqTFZ1Ksen2m4DeD		   2.439004809	2018-05-26 19:49:21.008
	output: SZB8a9zv58/oU3rfsfugTxWK/Y0QspRh		   2.413570153	2018-05-26 19:49:21.006
	output: 8ps/vlTqFWLxCgCuJ+ymjeeZ9Bjm63+2		   2.141503070	2018-05-26 19:49:21.004
	output: w4wreXCQ3HBazTrOAbjjgseiNIa2fPky		   1.528442905	2018-05-26 19:49:21.002
	output: oixM46kS+/d9C6z2Nm8v/V2BIgPmXzSr		   1.634208910	2018-05-26 19:49:21.000
	output: H3eyD327E/Dd4gmmGSblwYuPBNBXwF4V		   2.961267439	2018-05-26 19:49:20.999
	output: 8IQ3aziOIn05UK0u3Q6/BFmqQ/YAgDDR		   1.323280764	2018-05-26 19:49:20.997
	output: A10gYgfDzFD6XHYg+Zm73UYUkCN2B7ZK		   1.656714005	2018-05-26 19:49:20.995
	output: qDkRaa0hC9OMNZRRtO0/bh+a0XoliYVK		   1.973755144	2018-05-26 19:49:20.994
	output: JPSdiyivo4k57Mp8th9hcjVrT7uSG9oS		   1.826864200	2018-05-26 19:49:20.992
	output: /a4ZSoI89Hl+usTB81apxpYYKk8l4EWf		   1.155875198	2018-05-26 19:49:20.990
	output: pWQP+iPbFGy3a+mGuJrta+U3/jHT1TMM		   2.114342037	2018-05-26 19:49:20.988
	output: v6oQe4MdPBUd7Z3lPIBenMTYwRjz4GsN		   2.696050485	2018-05-26 19:49:20.986
	output: XJEL3aW+hnU3HeJR1A8IhL3eG3+/zz1Z		   1.835162358	2018-05-26 19:49:20.984
	output: hrG4wyQccWafLZ528Iq07Okvy+Gv7BZX		   1.998626616	2018-05-26 19:49:20.982
	output: rs5tvZv6ePWtTIZpvGXJWNtIfXp22SJA		   3.022049186	2018-05-26 19:49:20.980
	output: vpwZ7oPs7XAOYp3Ha+zJi9s5vb08I0g9		   1.414426853	2018-05-26 19:49:20.978
	output: r/6Sp6qA3P2jjqn/NwWwhJX7FV0At1tZ		   3.998029584	2018-05-26 19:49:20.976
	output: aZUdksgheOPv/0mnnsfyJ28a3e+HmrLq		   2.960498157	2018-05-26 19:49:20.974
	output: JtoqOD+lxQ7A/hqFHj968xWoJyia9Xoy		   1.654739190	2018-05-26 19:49:20.972
	output: HIUppTlLy7qALqSIR3Ce/rkI9vY5ewuz		   2.556238015	2018-05-26 19:49:20.970
	output: mYLH1Y3ovBMbi46lOtFmHpY6vuwZdD9a		   2.625656505	2018-05-26 19:49:20.968
	output: pXXiIkyADDYarbt7xMWEVJp55MXf2ZLm		   2.411565895	2018-05-26 19:49:20.966
	output: 6Bq2drylm+edveTYcBDprm7SgNr20UWm		   1.795833675	2018-05-26 19:49:20.964
	output: 8Nxxcf8miGLQat1Vus51kEjTcw2l8YPb		   3.335645260	2018-05-26 19:49:20.962
	output: oyUq/H4qn8PDrcBftkx/JCNN1XpIP4Uv		   1.352127605	2018-05-26 19:49:20.960
	output: CyPMN2FoYETe9YA0QteOzIsKPtrLeVY+		   0.953997210	2018-05-26 19:49:20.958
	output: w7NkufOi4C4J1wHHMkkOTi2t6kdA55XP		   4.777126756	2018-05-26 19:49:20.957
	output: C3LBsTLeSkujN2GLGlcFQHi5DBOS2WZW		   2.495678280	2018-05-26 19:49:20.955
	output: BORtt+K+6AOrXqeOqAZyhizXwAKouCDD		   4.490842329	2018-05-26 19:49:20.954
	output: sKz0m+mir1aQCmM4KenBV5cHmFAPrLow		   1.813300227	2018-05-26 19:49:20.952
	output: zZWF14xxkY2D80GGdlSh2v5pTfG/efl6		   2.235593684	2018-05-26 19:49:20.950
	output: SILY5zYsdygf03m1gy2HThEB65t1BkSQ		   1.658248081	2018-05-26 19:49:20.948
	output: nTQlFf3Xtwg0gjW5Rq6ljB6FLUcekAPX		   4.113838707	2018-05-26 19:49:20.946
	output: 7SQ9TktqQfGaOOMnJo8yAcxmevaI4iFv		   2.589143302	2018-05-26 19:49:20.944
	output: Hh3ENMob1xqVtMFpw0vdrw2ncEVCNHhN		   3.914878146	2018-05-26 19:49:20.942
	output: gZZNA/Y4y9RkpYncj1F83/Z9IAykxqnI		   1.865935632	2018-05-26 19:49:20.940
	output: tdfq0NE1no0gvOmR3Hytpg8Q/eiiUnR1		   1.177611347	2018-05-26 19:49:20.938
	output: uuR4PryDicXY6LE6cVp+1mtc6kzr+fVn		   2.200123622	2018-05-26 19:49:20.936
	output: k2TLCVOB9gLeVMPPiHhDLZ6Xo3/Gd6xN		   3.977664462	2018-05-26 19:49:20.933
	output: NM7imAVqHJVZNZAaN3p6rBJjKYrym0w9		   1.086291691	2018-05-26 19:49:20.931
	output: p8lkP1/7qPeD38U2hfzI9WSaNldz3AW6		   3.928441998	2018-05-26 19:49:20.929
	output: uH8w4sT17nWiY7M6w7RmQ5QjUm+ccAK8		   2.609650728	2018-05-26 19:49:20.927
	output: 6JXmQeTMSl+PKwsgN3xZ/KAEBOB6Wy02		   2.348065199	2018-05-26 19:49:20.925
	output: mrkkC9E0Hp4pLOU/VVLlkHShuPeFUdQH		   1.659779806	2018-05-26 19:49:20.923
	output: uyevgNfHpMJW/gmSQjaUJXsk/T8WQDDG		   2.818412452	2018-05-26 19:49:20.921
	output: msUqOeNUFISmKu4zxWYT3DR3yGgQBxmA		   2.051231608	2018-05-26 19:49:20.919
	output: PRXfEEi3FMxRQ2LTs5dX/ZgZvtUcCHpx		   3.010785167	2018-05-26 19:49:20.917
	output: od2L1V2xsI8siFIirw3UOo2m/vigYxW1		   2.886990764	2018-05-26 19:49:20.916
	output: V1oWFQ7jIE9vTXgkxDKyanToUAF2EieU		   2.699562727	2018-05-26 19:49:20.914
	output: iPxCOa2UEIzHmDj1HQdVscBpt5JUWmHb		   1.818629102	2018-05-26 19:49:20.912
	output: pUYnYZt/Mhd8GeTxADOura2F1yP2kqcC		   3.929730087	2018-05-26 19:49:20.910
	output: dPik9bvsezEqSYTnkjLcaVW8/bUpWANi		   3.800593021	2018-05-26 19:49:20.908
	output: 0OlgUdHgKc8sebLTPT2rUMjuhPAvQRBk		   1.900306005	2018-05-26 19:49:20.906
	output: 4BLFAYDc5epss2NwI4Wd2FspsTOvxEv+		   1.864325497	2018-05-26 19:49:20.904
	output: fGgdc6QEZtHPpgukTnBRytvaXW0BniJm		   2.072578295	2018-05-26 19:49:20.902
	output: xPQ1pc6XZj+TETNGsIrdSaoOHs376nfK		   1.831849695	2018-05-26 19:49:20.900
	output: tTx5qy/XLWq+pJ84wPO6WV7htBxhy6gX		   2.021192880	2018-05-26 19:49:20.898
	output: eOm/MYIs/WAz1rytObXVxL25+5/cj5HO		   3.397784726	2018-05-26 19:49:20.896
	output: muB08VbAvGx2xkXQLvX3Avby+JydoANH		   3.013914194	2018-05-26 19:49:20.894
	output: pAmJ6Cal2HPuNv6dJJaMGo5dVOXdUsEG		   1.749342385	2018-05-26 19:49:20.892
	output: alQ0QJaeQMOoz+iGDnYSPFTUKjXy6AwB		   7.440831045	2018-05-26 19:49:20.890
	output: ef1zN8femcz6Q6jmYY2hqEQglwMs6FJw		   5.026009487	2018-05-26 19:49:20.888
	output: 5ZhVORPUVDSaHSFlvTs86dH0XNNzWEFM		   2.265666308	2018-05-26 19:49:20.886
	output: ItqGSBR0QvPhiMdRgvBlvty4hkDOF1n1		   3.545807581	2018-05-26 19:49:20.884
	output: I1exNQohrmMV+H5dMYVsr2RjFKLABL6B		   2.013300542	2018-05-26 19:49:20.882
	output: WVeHKh+IVp4UHJZwvvQ3OfkpW8M4Xq5Y		   2.184742024	2018-05-26 19:49:20.880
	output: ZbjZDwGOfZWXUsXorLMJ5og1CfDZE/qm		   2.201792291	2018-05-26 19:49:20.878
	output: qz4lJgrBvsi7CZrZsay2IZmUdTU5obLI		   2.916266230	2018-05-26 19:49:20.876
	output: z7ogC1tHpmM4Hn0d5c5boddNNyXJhEoZ		   2.640709698	2018-05-26 19:49:20.875
	output: wnZQmUQl4ZvRTcRX3qdMCnkpTVTOmeYB		   3.812619967	2018-05-26 19:49:20.873
	output: tvlOEDth5qNAt+Z7YNv+vOwc4pFf5UEq		   2.298133320	2018-05-26 19:49:20.872
	output: exYyaaX1qpj71hiLNgIRDywA4qCLV4Cx		   2.065850084	2018-05-26 19:49:20.869
	output: ycOGYRrqIRmNfB44GgPMDld3yxkmWgV3		   3.554927796	2018-05-26 19:49:20.866
	output: itr8xQuhOAhO5PLCF7GulakH2q52B0KO		   2.293788790	2018-05-26 19:49:20.863
	output: FaRO7VR0cTYKtBWu9WHwpV+lWJyO6H+J		   3.817874340	2018-05-26 19:49:20.860
	output: LUqonZDupKfIEbrWE300gP25JtlezusW		   5.251649594	2018-05-26 19:49:20.857
	output: UERiQhdrXIHRVwueqD/3aHQYIOFWEkTj		   3.913086969	2018-05-26 19:49:20.855
	output: 5V/9f1N1mlm4PaGj7RiH3XzqkxBdhdqo		   1.749488192	2018-05-26 19:49:20.852
	output: UcRUTQuv5q4Qyn7ahTRnfGCjhRvYfni1		   1.361617646	2018-05-26 19:49:20.850
	output: ir9HJ+Iaco6GUX9FEMniNX9iw6gBfiNs		   1.726300172	2018-05-26 19:49:20.848
	output: xZZmX/oggNthO5bdntFkiClf1f13XVbY		   1.539975561	2018-05-26 19:49:20.846
	output: k2i7aHPLrzVkbwUAwEKWyx1YNFvejSjX		   3.207852371	2018-05-26 19:49:20.844
	output: yBw6439wN3uCSXzPu0umifb45y0GO/qH		   1.628290609	2018-05-26 19:49:20.841
	output: sGVMAdjptD6RZ1js7Quw3qHHanabyrBn		   3.259949352	2018-05-26 19:49:20.839
	output: TBrWSki8hI1VtSzRsoSEI2FY+Q9b4mmh		   2.552319972	2018-05-26 19:49:20.837
	output: XeMkiueHKWUh7tvKLDdGsZtv1ssuO2C8		   1.727076664	2018-05-26 19:49:20.834
	output: euLEsrLMX3aQpoCIdl/d78ikgOO5n259		   1.701310919	2018-05-26 19:49:20.832
	output: /zYKyaQDaUOuUcORIFF+lnaX4T+Gy9YS		   2.684411043	2018-05-26 19:49:20.829
	output: NUPp3WthzRSRCLrcpdJOMjhlHTsDlYSc		   4.205742598	2018-05-26 19:49:20.826
	output: 72EYoIiY0dCsbFTWvUU9RP4fvsQFuq7s		   2.049953267	2018-05-26 19:49:20.823
	output: 8QGgI75S9OPuCTrq4n9VIfcEn8HKwx8y		   2.843917751	2018-05-26 19:49:20.820
	output: HsEM8qtJxE1NBBoo48D9IXFlwotUqdfm		   1.065312918	2018-05-26 19:49:20.817
	output: X55RfT76hyNUUGI7piNOq/7EqIbj6i1b		   2.246130186	2018-05-26 19:49:20.814
	output: KXzHvHdFe4ofwYgDibt+afcswRrtlre6		   3.535271495	2018-05-26 19:49:20.811
	output: d+Tx9Ff01qY5dSekfVMzDipLzVf/7H2L		   2.057458531	2018-05-26 19:49:20.809
	output: ZdvAHjNsk3wW8vaQJx2URo58p0lamLH1		   3.074597788	2018-05-26 19:49:20.807
	output: ngZtA/wjnFbz2/SqHudsXOzZzP8u7Vu/		   2.209646253	2018-05-26 19:49:20.805
	output: qzDvdUmJFr2OWC9sFhwC1FDwOFaNStgO		   2.005542846	2018-05-26 19:49:20.803
	output: FmQzEEvPgLpS2iElcGZaOogCTTWzSS29		   4.448069736	2018-05-26 19:49:20.801
	output: cR9G5AUdYCGHBRSwlt1vWjPzrPmPTrEi		   2.057887439	2018-05-26 19:49:20.799
	output: YzRLpYG7qZ6yYkZumffvm/pygMAhGWPi		   1.829002441	2018-05-26 19:49:20.797
	output: li/4Si79+e3YWbrPjowryEhO9HsHMoWB		   3.979146824	2018-05-26 19:49:20.794
	output: EG2/cY6vUB8G9dMta7P/sSrMYwVgD12Y		   2.799381823	2018-05-26 19:49:20.792
	output: yi9V5lCFqbg+nTEKXr8RdzrF7FaRTEph		   2.031948110	2018-05-26 19:49:20.791
	output: m1y90btw39AyOvYzv8i1QILfc9zufhdY		   3.270448243	2018-05-26 19:49:20.790
	output: TRxWRXaNPBJyTlNdWSx8v2dn9//0WOM3		   3.380619372	2018-05-26 19:49:20.788
	output: 0BQvpl1A4QsNDK7z5JPCX3nbR73NLVkR		   1.612389828	2018-05-26 19:49:20.786
	output: tHftcCf8i4MYvUKTFBOWxaJotQpm8WCv		   3.106038727	2018-05-26 19:49:20.783
	output: Oj9tLOowd+XwGPXfqyvmmdFa5BZpucXm		   2.015463729	2018-05-26 19:49:20.781
	output: DYPLizr6qxuKiTDt3OiPN4kzwofXGX9x		   2.600081193	2018-05-26 19:49:20.779
	output: 27B1bydg9PMYW5HApiaFiudTc43Rf8Z2		   2.544043265	2018-05-26 19:49:20.777
	output: dPnE7GYbj/zozLm8kzSOAC0OpcxedVtL		   2.911505880	2018-05-26 19:49:20.775
	output: UWdBONPLeK0lAeuYcAIrtnc/+RXTqN2D		   4.560579251	2018-05-26 19:49:20.773
	output: tvl4GpiRfnCmT1CCnyl/XZPD0MZMw6gP		   2.401287591	2018-05-26 19:49:20.771
	output: MmU276CYtNW6AG55jBNOfBEL7E2X8yPC		   1.650011153	2018-05-26 19:49:20.769
	output: qOVp5naa5nB8ChlJoL4xIqA1KfH0yhkK		   3.707789120	2018-05-26 19:49:20.767
	output: 9VO/K6nNVrKRAYA33D+oREa9WOhUXtFJ		   2.996642061	2018-05-26 19:49:20.765
	output: 312BVf2b+tnRr7a8M/zPyCr3KKXC87QN		   1.376562813	2018-05-26 19:49:20.763
	output: FBADMdIJefP5G8bmJgHSRR18T9iDn76n		   2.063634996	2018-05-26 19:49:20.761
	output: 1RbkXTjdoKfZZ7ZFaAJKZCAorYWRsGD5		   1.980498761	2018-05-26 19:49:20.759
	output: Y+boMjK5O1IA89WLw/X4QxDXUlU/VJMZ		   2.271472447	2018-05-26 19:49:20.758
	output: Jhta7c8SJV3o6uZv+9qtSwQrdNHqCGtv		   1.055110190	2018-05-26 19:49:20.756
	output: 5Y5C41gOx7JzVIyTZGl0vMIbujcBEEMc		   2.220732337	2018-05-26 19:49:20.754
	output: IzKR+z1BH3kl+w3owO2Dh1wSocybWFr3		   2.893416060	2018-05-26 19:49:20.752
	output: OITRAMFodEqj+VEarWNKyNu1gAuz+PUg		   2.509774252	2018-05-26 19:49:20.750
	output: gGscXKZkxLt6eXYzRc6XavB7R7RVtgWk		   3.146550384	2018-05-26 19:49:20.749
	output: Tt/vJTpaN9xK8q7Uy7PC0XQBKWHMjqny		   2.595404806	2018-05-26 19:49:20.747
	output: RehY2KcoFza6nqOnKfAND140sJ19TNzv		   1.843385302	2018-05-26 19:49:20.745
	output: DDsY4Twg8vLnHZBgBA/dkoZRERF2Ec42		   2.667773891	2018-05-26 19:49:20.743
	output: Ygb1kLy5U+GcdlNXchT4LOkqIBqeNASb		   2.745096648	2018-05-26 19:49:20.741
	output: J2hkfEo/NjQk2qkRiTC1uIAqRn02us7R		   2.571103142	2018-05-26 19:49:20.739
	output: svfKvZmRh0JzpccHexNWszzC89hxSJBk		   2.420571378	2018-05-26 19:49:20.737
	output: 8QnZ/npcEb3TpC7vu+KmnKuliB5Hm1/C		   2.207418063	2018-05-26 19:49:20.735
	output: 0dxkjsZIF7CSkhwaEIQhw2FxAPfTQKMK		   3.335432613	2018-05-26 19:49:20.734
	output: smz/cxiF+0PU195lGeabUl7Y+9zXSo0O		   1.674298642	2018-05-26 19:49:20.732
	output: gJHoofYlT7B48MzFJN+ehmoUvlwhk9Lj		   4.240264423	2018-05-26 19:49:20.730
	output: 6CcTu+tZIfkOkUZog8bpobrLB3Q1/QSe		   1.371335119	2018-05-26 19:49:20.727
	output: TM/GGfzxX96DNCcNuK2Hn8tyGTN3tvHI		   1.492903194	2018-05-26 19:49:20.726
	output: H54ZimvaR9r3E++IsPcqNRkddfx/g8Z9		   0.802241051	2018-05-26 19:49:20.723
	output: 6Z2ASKk7Q9jZ72zZfzmB49SGjQ9CANGb		   2.231040205	2018-05-26 19:49:20.721
	output: PnvABKpUZOU9bxE95gQQV0ALZzkjDKkA		   2.068931587	2018-05-26 19:49:20.719
	output: 0ipan84LjgCdtQB8TMBb//k0YsdwnMHg		   1.543694613	2018-05-26 19:49:20.718
	output: T2LYjhOpP3gt0CeT1aX2jJybiTUYVMEV		   3.992716081	2018-05-26 19:49:20.715
	output: KUbLf8sBuv2+NfzdDypCnUeXy7TXJyx9		   1.766689993	2018-05-26 19:49:20.714
	output: dCCtGCVB5Q0rLG0/emSS+R7EYGb2l9WF		   1.878442364	2018-05-26 19:49:20.712
	output: jgeqM0Hr8uce1Fe6uadP84Dv9vjDcfgB		   2.149450345	2018-05-26 19:49:20.710
	output: DGYfbiPBMJjG0fBtReaP0D8ulPV/RNEq		   1.099642422	2018-05-26 19:49:20.708
	output: d9dbIUslGzQQaF95j3jVaMHcZUM0z74c		   2.193398126	2018-05-26 19:49:20.707
	output: fV8KSDmQ4kvo2IO1z3KY0JEn/W0kyzbk		   0.849655264	2018-05-26 19:49:20.705
	output: OfegCvmO11w6m+ROD0iR9sEkEQXXNo3g		   2.155539554	2018-05-26 19:49:20.703
	output: 072MX2FR8xHkSD3Thf1UfQ/ITdJ2+TRz		   3.975474843	2018-05-26 19:49:20.701
	output: p4/1qHkzl1SaqTdOzDAgtCGleaocfzbr		   1.281853221	2018-05-26 19:49:20.700
	output: U1fBBHNYKZ0evIUnuV0t48DAAGR6lImY		   2.687953135	2018-05-26 19:49:20.698
	output: e5P6ahRnykVUw8l07rSmpEuHg0ge9wX5		   2.600019593	2018-05-26 19:49:20.696
	output: 4Xoc3gmLt7qo6NkWw6RhMJI0yQNapIL6		   2.656110161	2018-05-26 19:49:20.694
	output: Yk+3TmwYMxh5A8a5Qnrc9DJtxs3ZBIv6		   3.620585558	2018-05-26 19:49:20.692
	output: S3ndFznGBzUn3R8smRZs9da6IxFOFaSl		   2.765346352	2018-05-26 19:49:20.690
	output: 4/oVHluw3flxpIhaG0/9UwkrpxA3aZFN		   1.482826100	2018-05-26 19:49:20.688
	output: LO7YtS897Nuw+6cr3URPd33gaQjI4BiH		   1.515833306	2018-05-26 19:49:20.686
	output: vpwTRnTpkufpAuiomMPRG/FEZTPDsFiv		   2.219258914	2018-05-26 19:49:20.684
	output: nmNqOsLdTP6ImizSTcOdjFFeakEvgLmR		   1.333210499	2018-05-26 19:49:20.682
	output: tbnQatBJzIOXeOykSXG3Dui6tsqWvx/3		   2.924099147	2018-05-26 19:49:20.680
	output: awisl03BQ+jce7oY1qRJCBG/0Gpw1NVK		   1.824600354	2018-05-26 19:49:20.678
	output: Tw7P6rcR0BIbmGxUXP/PwH0zBgTbhHka		   0.919780916	2018-05-26 19:49:20.675
	output: JNX5nCBzCfVESulHrzr/hDrzFF/u0ILP		   2.717892041	2018-05-26 19:49:20.672
	output: zWBXec80Z8nU2WoVQu8bxbi+DJqiyrCj		   3.223353167	2018-05-26 19:49:20.669
	output: 3lsvsktJ1KODGEbqBa80ZfAf1YsYCFHM		   3.211036354	2018-05-26 19:49:20.666
	output: xOEK9r2Khih1W2x4jm0vitSawqoMp87d		   1.633676141	2018-05-26 19:49:20.664
	output: fMmSYx7OPRlVw0Ladx/K+p+S+acoSG1i		   2.482394498	2018-05-26 19:49:20.661
	output: /Tl4/fyajP5JjmO+UJcXItjIMs93xOSs		   1.419016733	2018-05-26 19:49:20.658
	output: RYJs8eJrzCe25a1+0ikLM+jIsYC45rE7		   3.562017038	2018-05-26 19:49:20.656
	output: HCkpdnUiForbr9juCJ1CLwHXMLx7noNJ		   3.178744361	2018-05-26 19:49:20.654
	output: abDyIvZyfVK9PNboVcJcFt0g7/tAtHZM		   3.461435072	2018-05-26 19:49:20.652
	output: UYfPK7vAxoqbUMiEkPsiCMJshQ5Dy1RB		   2.615476018	2018-05-26 19:49:20.650
	output: H8kiRfzlkzayruhBxUzBx+w/CKt8jip1		   1.254689489	2018-05-26 19:49:20.648
	output: he2Y+R3X80AmxoVlLk9tTiuLXu3htrDq		   1.316417609	2018-05-26 19:49:20.646
	output: HwbBpjVJhnjGRry4MKG5KH8YNAD5bwXq		   2.008633679	2018-05-26 19:49:20.644
	output: Ly/7oZtnND1ddjMFTvm4UKqKGeX3kuM8		   1.979603625	2018-05-26 19:49:20.642
	output: tl5HItGkJd3gOfVgMzgwFuBqN+zj71Nz		   1.882048785	2018-05-26 19:49:20.640
	output: c4IfjI9HwoP5tjy1gQNVttQndLG29uFB		   2.570698892	2018-05-26 19:49:20.638
	output: srRkU2JdV+a2TLNwXsfMcx4Muq6Ijf8X		   2.053562387	2018-05-26 19:49:20.636
	output: 2DUKnH3VhjtcBLs976IJxlVOAGQrYbSj		   2.197188818	2018-05-26 19:49:20.634
	output: hrSa4yIpSWbEFz8cCKN4VKBFEKP8y+nQ		   1.505073280	2018-05-26 19:49:20.632
	output: //0TswAyQ63jGwQtlZfL8rtVH9z+pY0j		   2.653836063	2018-05-26 19:49:20.630
	output: uBXQ2JsZ7GfEE1T75CvuR645HBQvrkNL		   1.535050415	2018-05-26 19:49:20.628
	output: a2Xuhga4s5hLTsUetCACAHNQsdtFn1XX		   2.737747628	2018-05-26 19:49:20.626
	output: hOnNYA4bPcfDoGhv99HUEyFrTilu1EoN		   3.916382086	2018-05-26 19:49:20.625
	output: YdKxZKyv9qrnpkbJQohDV8wdZRArvpzl		   6.306492393	2018-05-26 19:49:20.623
	output: +J9sEBuWdcHJNXbHYFHUFF9zHMruLXEg		   4.265582168	2018-05-26 19:49:20.621
	output: c9e8c7UUjd7saIxJY6HU5IFPqqPuUwzW		   1.458433551	2018-05-26 19:49:20.619
	output: v2v1yMky/FAe+hrGLXkr/3RqrXh9Jnuc		   2.815660546	2018-05-26 19:49:20.616
	output: 5u49RMv0WIKXfgxGp9VdC7uJLh+66xvN		   2.032339071	2018-05-26 19:49:20.614
	output: genxl0sDgtXNyp4T9tviMqONk2vqC+tr		   2.915299144	2018-05-26 19:49:20.612
	output: 4/ZMfmEvOGxMXv+QycWSrIkPDi+fN/Zl		   2.167249079	2018-05-26 19:49:20.610
	output: K6bPoXJpDzqDvgJlQdI2/+82SIIRThq5		   2.328448587	2018-05-26 19:49:20.608
	output: jxf7I5mdawwTLf8dTC1PixhkdQNsMZWa		   3.433122304	2018-05-26 19:49:20.606
	output: p60EY8vrpEW5zG7FlWKuNpRfT7LuiXA0		   1.653288993	2018-05-26 19:49:20.604
	output: t4DYeSNsjfrpWzSWLicKqX6rQvPHKEvD		   2.626166279	2018-05-26 19:49:20.601
	output: upijWXg5JC4PVsWH3qve6m0N2ESi5x1B		   2.085997056	2018-05-26 19:49:20.599
	output: cOTojXFVAd6Xhtmub1zGjYnobqMctZ0s		   2.388101827	2018-05-26 19:49:20.597
	output: N1FfHbBAwr4CgR5WBp32ximUIhqH7nkt		   4.644077876	2018-05-26 19:49:20.595
	output: DIHYhrfGTJ7yM0ibOBtFD4CDt/wJjnFE		   1.367365726	2018-05-26 19:49:20.593
	output: qFSxbBMPhGzxm0U7w/seiLTEo35Hi9A1		   1.377637999	2018-05-26 19:49:20.591
	output: /qNiCjEQZuWL2k2zT/7tDDPTersFCvfU		  12.210986441	2018-05-26 19:49:20.589
	output: ODcdaqhYDo9AZJLfxwnjliPNYBTeROVu		   1.213292015	2018-05-26 19:49:20.587
	output: wok8ekl5rqqsmIog9Y/FnHaG1WINO2KM		   3.030346014	2018-05-26 19:49:20.585
	output: CyAzfgJp0WsW87zj5IIwmhHZPcUeMyLE		   2.503207233	2018-05-26 19:49:20.583
	output: sCWzOL/0mtam9jlwlW58Degkn6ij2hxY		   1.698452064	2018-05-26 19:49:20.581
	output: 7kL7QCsM2+8AFek2UIs1ftEe/KRZQDMq		   1.819982490	2018-05-26 19:49:20.579
	output: 83WOL5YcgqHa2YHKJ4a5n6zFJSsczA2U		   2.581412571	2018-05-26 19:49:20.577
	output: HrG3QJiu6sFgEbFFexBtvnVb8iLWmmO4		   1.615538465	2018-05-26 19:49:20.574
	output: nRZ4QFhCqJOq6peI/KjtS0R7yFCbkJlI		   2.014642573	2018-05-26 19:49:20.572
	output: eLQUzO4XLIkt8IEKE5Za9mOp+rop3CXF		   2.625653411	2018-05-26 19:49:20.570
	output: 5RML6ydswjKqMklAtm0vEhMUFjjWkZVW		   2.576823386	2018-05-26 19:49:20.568
	output: 4EjtDJ2tY2vkDKhVnjyeqi5Mf0DhWvie		   4.145633529	2018-05-26 19:49:20.565
	output: fxki2DAV8Gm9ePsmKFBsckA4Ts+i/+HI		   2.528092311	2018-05-26 19:49:20.563
	output: OcnZnX+cFZ9EE5w1kwiXD4y1QFfwIr5E		   4.199781362	2018-05-26 19:49:20.561
	output: rtK4QjfzrKqZlHSlgdZ8BfcnpXPj/KX3		   2.070593482	2018-05-26 19:49:20.559
	output: 9G+EZBg64f0OtRlsgmK12FlpW/aN31ZK		   2.302229596	2018-05-26 19:49:20.557
	output: XtN5TkxBnTtt7+a7k67tIwdn/J9091h5		   3.462345721	2018-05-26 19:49:20.555
	output: z3/IZSoYSAyOx9ZqXo8B6Yx5ki41M64v		   1.407104243	2018-05-26 19:49:20.553
	output: 2d5E6ELqqAz5vnVHf9jqLhcJ4Mos/t1p		   2.592236952	2018-05-26 19:49:20.551
	output: di1q916Km9NDrRVhvE3Gt1nw0kWx8QkA		   2.635609872	2018-05-26 19:49:20.549
	output: CByyLFrRnAvFRxoDubUwRdovHYaJYT0T		   2.140102021	2018-05-26 19:49:20.547
	output: xyNdYrM+7h1A5Uugqz1t65gBRuc9kRiK		   0.754757083	2018-05-26 19:49:20.546
	output: cCTy/ZkVBuHwaIuUVJeGpp1hq2Gakzji		   2.512344658	2018-05-26 19:49:20.544
	output: +qscaupAwMLLpBbzMeFyVTV02Xrth0it		   2.134392585	2018-05-26 19:49:20.542
	output: qchHmS7DdFouOMRLtcTYunW7aCnCpunC		   1.909493362	2018-05-26 19:49:20.541
	output: eEkVf+vv1lNWXbHUrYQNVoPq85yyqDWf		   2.582741502	2018-05-26 19:49:20.539
	output: Fq0ZbOZDhHzsGvc5+vpFse9UOXTEsefI		   1.118867415	2018-05-26 19:49:20.537
	output: tYZYOKGwCNc9S/OnksTrZmTOyHUQ+72H		   2.717005287	2018-05-26 19:49:20.535
	output: RrN1rbtmm8p4NvdUmJsk79YCiBEdWwYq		   2.630149242	2018-05-26 19:49:20.533
	output: hOhWShcBo/FqlDGdJjsT/Jl9jktEOuIO		   3.986983092	2018-05-26 19:49:20.531
	output: EI++oFY6Hr+bPP7vyzd9nla6J3au4SDM		   1.997621725	2018-05-26 19:49:20.529
	output: lvPYAY3pXCqR22J6L3bUw+qC1zrD3Z3B		   2.945573825	2018-05-26 19:49:20.527
	output: rWCrXhw3+zKODk6/IMuGrrP59b6kmP97		   2.342019696	2018-05-26 19:49:20.524
	output: QJLKRcQ8u445BbVoJer69sJS3pF8d+Fr		   1.785006833	2018-05-26 19:49:20.521
	output: GQ7TYH0I5hNQqHyxx0AfzdViv4tUPbvi		   2.571351016	2018-05-26 19:49:20.518
	output: uz8qTxmFqm6Kb4TTIcUwOnCOnEE4jv00		   1.615650051	2018-05-26 19:49:20.516
	output: 1iscX37gW84t0qB7SoRv958Hw556xM6c		   2.154175620	2018-05-26 19:49:20.513
	output: V+h/mpUgC3gzmtB3buIuoo3xoXjn/Hnd		   2.087891923	2018-05-26 19:49:20.510
	output: iNjBFso6YN8dErAZ+zepBklq1l97Zqfa		   2.880792801	2018-05-26 19:49:20.507
	output: iCb5kE7X8/eSrKe0VDco+razTXEOAfzS		   2.263772011	2018-05-26 19:49:20.504
	output: C8JMgIwmplHXqDdxAF1qo5utkTKk578O		   2.032990581	2018-05-26 19:49:20.501
	output: 6Nr5weOwZXApVvnp9Z4JI2PcDoHcpXV+		   4.226045499	2018-05-26 19:49:20.500
	output: BPXs4ipc0yIV7nAivzqKF6KrgkaGhm+x		   2.996871154	2018-05-26 19:49:20.499
	output: rEsUBTuQprP58H7Z+skm/b17D1dj3oKM		   7.097577694	2018-05-26 19:49:20.497
	output: +uI01Bp1L8p2o8giiXPb4HdD7rWPqIIN		   3.039246088	2018-05-26 19:49:20.495
	output: idusGhSfshNJwXGv7pnluSIUnqHDZDUT		   2.714063701	2018-05-26 19:49:20.493
	output: vWXPagUeKnGnY8DZ1Gk/ZCafxf3Cvdzw		   2.122415904	2018-05-26 19:49:20.491
	output: F1wt8wN/HpB20CFYjXOT/CbH9bwNVOlg		   1.174620473	2018-05-26 19:49:20.489
	output: I9bUrLA2KvRKvV99PcixUxo1jUjkRzdc		   2.176470024	2018-05-26 19:49:20.487
	output: XkomkaKwP8snDQ/aO9XnXdO4pIc8+zBb		   5.641860170	2018-05-26 19:49:20.484
	output: QiYMMfQLBJQeCYmdoKLLEwpvU/J5RVvT		   2.219086767	2018-05-26 19:49:20.482
	output: SiSv7Mi91PQ9dqZ0VTE5/uCXCzf6++je		   6.511757279	2018-05-26 19:49:20.480
	output: S/bk8fvkQbrzpNOzIwVlAOLjN0N+/74I		   3.536872488	2018-05-26 19:49:20.478
	output: 8yZCkDfWYduyzTzb8WvULTaVcvxauAWk		   1.457770769	2018-05-26 19:49:20.477
	output: LsXGerjAKYSS8hQVjJ+vwPyB/S79+5xu		   2.517118782	2018-05-26 19:49:20.474
	output: W79E/fiKULIeC/nrz+0u74cgMiupSERE		   2.938314714	2018-05-26 19:49:20.473
	output: UbiBSJKS5QTx7WncIITtaBCaoa/I6OFd		   1.396881366	2018-05-26 19:49:20.471
	output: JmyAGAKpoArK2nVLPH+VxsZPQn1xsxYt		   1.171596845	2018-05-26 19:49:20.469
	output: RV1SJM21/D8zPyAgFq9sj5kIvHmjihig		   2.872226229	2018-05-26 19:49:20.467
	output: sVKHLTp5SVOGQdUkqLsBnUzNUYzisHPJ		   3.086055621	2018-05-26 19:49:20.464
	output: lctoKIcQHMM1ZiHlMfLa3n3kfTXrO77u		   3.487491512	2018-05-26 19:49:20.461
	output: C0H08hSSqs5D+cInHyfVNYJ+ETyxe/6d		   4.298511479	2018-05-26 19:49:20.458
	output: SD1Mc5MCA1OGtn55icO1QZSpPcVJImc9		   2.205842319	2018-05-26 19:49:20.456
	output: MwrVtcR3RiVCrb5dhZJoyDnlakjw1pEA		   3.613535850	2018-05-26 19:49:20.453
	output: NvDM36BVt8vaiOzhUgT+uPQca3uzK9/i		   2.286361560	2018-05-26 19:49:20.450
	output: t95KBshQtvA8SuqrYp9uh7//tm85TGWA		   2.118827769	2018-05-26 19:49:20.447
	output: ELggwR3n8j9hUwK5Uark1x4pAV21UXJZ		   2.788789341	2018-05-26 19:49:20.445
	output: Cnf1FJF3RV+r8PlrWB7RPrRi8Ne3T+Wb		   1.643286046	2018-05-26 19:49:20.442
	output: 1gGrOnK5FIeWshkHn+sxngCT2fS3IcXZ		   6.842075808	2018-05-26 19:49:20.440
	output: w59UzVTdNppZ8WXCAyq3s0yjet5b94L0		   2.575802759	2018-05-26 19:49:20.438
	output: Kp7drSWPXEL6UzR/Rkd98M9No42uruL/		   4.177521841	2018-05-26 19:49:20.436
	output: 6jCb6yTbqxuvkMJVzkCWjF5IdL1Zdi/9		   1.408484532	2018-05-26 19:49:20.435
	output: m69fkNhvLOFomcu1rtB2EqktaqhEsWWM		   1.622886480	2018-05-26 19:49:20.433
	output: PIHte4XeIAnrvovGgdhX7CVK1wKY2epX		   2.312242650	2018-05-26 19:49:20.431
	output: 2yMTp6uSKoFDnxdZgYxzDovvQmAeEw0I		   5.078783295	2018-05-26 19:49:20.429
	output: arUw0slFAUDgEpYqhWHDC4OJxL6Ljf+v		   2.320351982	2018-05-26 19:49:20.427
	output: TUAA6vyHP7DzMHdUIwKathLJSKwoZDNc		   2.174398141	2018-05-26 19:49:20.425
	output: xmy08hSOqhLmpvPeuetVZxvLkdF+AbPq		   1.604814279	2018-05-26 19:49:20.423
	output: HFE1RadRGRe7uYyYSHbKtCfbTL59EF3z		   1.649405705	2018-05-26 19:49:20.422
	output: UF+9tZsqiq4g0BDV8ALrkt65PjMEQxCT		   3.166316379	2018-05-26 19:49:20.420
	output: w0co4RbccB3fpFXPh6tnaPR5qFeaY4PO		   2.788434455	2018-05-26 19:49:20.418
	output: SQ7ExBE6YevZMeFd2QyinDMgKteDguRW		   1.633962966	2018-05-26 19:49:20.416
	output: UGvSzT19pe79ZrsaA5EYnv8QPjFGcZVQ		   3.411284811	2018-05-26 19:49:20.415
	output: vac/u6TDrpFx1pPNR/wTEmCbsfRson+H		   3.927251766	2018-05-26 19:49:20.413
	output: U8g1N3qEJJe1hzApe9+M5CWNa0hOp+SH		   1.953279468	2018-05-26 19:49:20.411
	output: pyOsQ/VeifLg4rm+6yoD3SeA7F9aVKvo		   2.134227059	2018-05-26 19:49:20.409
	output: LllAtVKm5ynmJmUZCkq5WpU5wZTqIVls		   1.190855161	2018-05-26 19:49:20.408
	output: gZuElfoHtLZZC6Mx6u4zyxfxvKjoE9I0		   2.957635511	2018-05-26 19:49:20.406
	output: aBxFYbuRwakAk37/4lDtauxRmQuIpfXz		   2.402931114	2018-06-14 19:49:20.404
	 input: U8I3EnAdmwSi88Izl3MVqExVFmqvVDLE		   3.222378291	2018-06-14 19:49:20.401
	output: FbM4lXZ9SbV8+QtRfhfl/Pj+YpfYVnPO		   1.371980242	2018-06-14 19:49:20.398
	output: UTgjTbGi9kA9i30A7hml4Oqyw8W62AoG		   3.436523429	2018-06-14 19:49:20.395
	output: crqPIxsR7TAq1PMpahTDwImUUlnMIAJ1		   2.106789894	2018-06-14 19:49:20.393
	 input: AKoaDXKGUjoJKlNK2iP3CltkdKYfhUp7		   2.118406890	2018-06-14 19:49:20.390
	output: 2UpSicD5xv1NA/PTN04+d/QuWoH1MCYm		   2.044502267	2018-06-14 19:49:20.387
	output: GcO1gv2BKKugmumKMiufQ0Ue3pI5Ogtm		   2.552766561	2018-06-14 19:49:20.384
	output: QTTm45K/Reju1/3FIU/fD4D4gv+ojkbQ		   2.659098001	2018-06-14 19:49:20.381
	output: RYvemDqKltkC4VaxkdgT2nu84RmGCQfE		   1.175313073	2018-06-14 19:49:20.378' . $earning_2);

			return $this->commandOutputFile('Block is not found');
		}

		if ($cmd[0] == 'lastblocks') {
			$blocks = '////3aEv+N8KHkA/CW7xOw+i5uLL////
IxTmxt1HDfEN4H/AkzoVlfCezXb5eK+G
JsH+GlSWOF5ctx8221AYio2GDnsrpZGC
b917aMjg0LGIP99NAp8hw25Z7vwUadtf
ZoDut9H09F9a4C4C5jPoqvLK3qSZOwq9
qnwOb9OGApYHY/tnnT+9SrwdCNWBjes5
mtkz/h5DZzv7wZsogdmO8mONV/B46Lyj
/NAJ9U8UDoq9K9kj1YBXmhIUGoa/7kIQ
LDro009JXqR76o2CXv33445Ajn2Ij//q
P087teZGZrhuckaRjmlOi5kc/1+MEUSY
jDzljCeKXiem5KztENpPhKSHCulGM1Y2
45ho1vnZtpxe0C7G+nWzLWIiDhuTpM5X
HvZjAlhyyQQN8j42flmDPo0gWzlUZLoN
uD4RyhT/ypODPNoW0FMl1c16uKtw6+7E
7rsMubsh6pVQxL9P3KcvcUjtQ2yPjgYj
xFyqCU75r9zACLgp5mmiw7yLB7A4aV70
BBvOON5qJW4OpW6N/7onNCdThiey4hMi
m1hq4E0dKDOj0+ww7liKAbpY0GBX3El3
Ui9dof5niOYQfcqJPc5gPGGst5RRL/R8
yljjLTsqhfp9QqUaiaKaapPHO8w+hc9H
thCbE4wkFDptYG1mbQA9Iog4hBFdjWN1
e8ewboUZLKwW6BzDEgDijqEib6w1Fgev
g5d/i+mZU3oRJerl265sTXri96asevHI
VAzMAs0tYNkS6ch7gJfS0H53x1paaV4T
WLYMhgmO01vA86yfdk7bEMX2lqzFxalj';

			return $this->commandOutputFile(collect(explode("\n", $blocks))->take($cmd[1] ?? 20)->implode("\n"));
		}

		if ($cmd[0] == 'mainblocks') {
			$remark_1 = '';
			$remark_2 = '';
			$separator = '-----------------------------------------------------------------------';
			$header = 'address                            time                      state';

			if ($this->versionGreaterThan('0.2.5')) {
				$remark_1 = '                                      ';
				$remark_2 = ' test   remark    3  ';
				$separator = '---------------------------------------------------------------------------------------------------------';
				$header = 'address                            time                      state     mined by                          ';
			}

			$blocks = $separator . '
' . $header . '
' . $separator . '
Jim9F5oqJbQA6dM68phqGZ2023UDdngs   2018-07-12 00:35:59.999   Main' . $remark_1 . '
erOISXoScyQ8TAVgX5OofnOgwy/hNYcN   2018-07-12 00:34:55.999   Main' . $remark_1 . '
GUImUst9bYFY8wR9I7eMxU1zsQ1hYIj9   2018-07-12 00:33:51.999   Main' . $remark_1 . '
LYXHuZwIOjy6/pUZ1SoUyS6bUSvo3lFR   2018-07-12 00:32:47.999   Main' . $remark_1 . '
ZAyhGaI3cRwB4IltNgY0HfW8ACsFdws+   2018-07-12 00:31:43.999   Main' . $remark_1 . '
ORak8Di3DtkTjeWEVn4lg1rk+ixP/MG9   2018-07-12 00:30:39.999   Main' . $remark_1 . '
1iToKTNdwFL54Nlhf4RNh3oJxwjfXDLy   2018-07-12 00:29:35.999   Main' . $remark_1 . '
PzUN6WUt9+ykkEZBsHkhRXlFhZ/gAtZ4   2018-07-12 00:28:31.999   Main' . $remark_1 . '
ECsF/JPS1KmhNBMewpIaLA/vPevLLusu   2018-07-12 00:27:27.999   Main' . $remark_1 . '
nBjQfO2A20wMJDscmxpRNk5/GuB/ghY6   2018-07-12 00:26:23.999   Main' . $remark_1 . '
PLA9HCrWooxjMVwjR5tKRuSrZcKu+5Pf   2018-07-12 00:25:19.999   Main' . $remark_1 . '
9xWFYp1GrTIWSRFZ0mhQPTp6Z17Gml9J   2018-07-12 00:24:15.999   Main' . $remark_2 . '
hJD/OBhexPzukGJROlw/b3ZNlJzthkVk   2018-07-12 00:23:11.999   Main' . $remark_1 . '
r9Ryt+CcPgaHEo9niW0VTMac4ZJBmPbA   2018-07-12 00:22:07.999   Main' . $remark_1 . '
tDg2IC19a+sBr2VcDkvVEqLXjWOcEtJy   2018-07-12 00:21:03.999   Main' . $remark_1 . '
E6IKRznvpoLYMDH9FwNami2gmuTQ6/VV   2018-07-12 00:19:59.999   Main' . $remark_2 . '
3bhRLUXh3kNfDGW61zuU0IFKmEQeJlga   2018-07-12 00:18:55.999   Main' . $remark_1 . '
cyCapIoGsagELQG2iZogX42q4mRu7i2D   2018-07-12 00:17:51.999   Main' . $remark_1 . '
IxTmxt1HDfEN4H/AkzoVlfCezXb5eK+G   2018-06-22 00:12:49.813   Main' . $remark_1 . '
////3aEv+N8KHkA/CW7xOw+i5uLL////   2018-06-14 19:34:23.999   Main' . $remark_1;

			return $this->commandOutputFile(collect(explode("\n", $blocks))->take(isset($cmd[1]) ? $cmd[1] + 3 : 23)->implode("\n"));
		}

		return $this->commandOutputFile('Illegal command.');
	}
}

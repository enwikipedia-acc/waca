<?php

namespace Waca\ConsoleTasks;

use Exception;
use PDOException;
use PDOStatement;
use Waca\Tasks\ConsoleTaskBase;

class RecreateTrustedIpTableTask extends ConsoleTaskBase
{
	public function execute()
	{

		echo "Fetching file...\n";

		$htmlfile = file($this->getSiteConfiguration()->getXffTrustedHostsFile(),
			FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

		$ip = array();
		$iprange = array();
		$dnsdomain = array();

		echo "Sorting file...\n";
		$this->readFile($htmlfile, $iprange, $ip, $dnsdomain);

		echo "Exploding CIDRs...\n";
		$this->explodeCidrs($iprange, $ip);

		echo "Resolving DNS...\n";
		$this->resolveDns($dnsdomain, $ip);

		echo "Uniq-ing array...\n";

		$ip = array_unique($ip);

		$database = $this->getDatabase();

		$database->exec('DELETE FROM xfftrustcache;');

		$insert = $database->prepare('INSERT INTO xfftrustcache (ip) VALUES (:ip);');

		$this->doInserts($ip, $insert);
	}

	/**
	 * @param $dnsdomain
	 * @param $ip
	 *
	 * @return array
	 */
	protected function resolveDns($dnsdomain, &$ip)
	{
		foreach ($dnsdomain as $d) {
			$ips = gethostbynamel($d);

			if ($ips === false) {
				echo "Invalid DNS name $d\n";
				continue;
			}

			foreach ($ips as $i) {
				$ip[] = $i;
			}

			// don't DoS
			usleep(10000);
		}
	}

	/**
	 * @param $iprange
	 * @param $ip
	 */
	protected function explodeCidrs($iprange, &$ip)
	{
		foreach ($iprange as $r) {
			$ips = $this->getXffTrustProvider()->explodeCidr($r);

			foreach ($ips as $i) {
				$ip[] = $i;
			}
		}
	}

	/**
	 * @param $htmlfile
	 * @param $iprange
	 * @param $ip
	 * @param $dnsdomain
	 */
	protected function readFile($htmlfile, &$iprange, &$ip, &$dnsdomain)
	{
		foreach ($htmlfile as $line_num => $rawline) {
			// remove the comments
			$hashPos = strpos($rawline, '#');
			if ($hashPos !== false) {
				$line = substr($rawline, 0, $hashPos);
			}
			else {
				$line = $rawline;
			}

			$line = trim($line);

			// this was a comment or empty line...
			if ($line == "") {
				continue;
			}

			// match a regex of an CIDR range:
			$ipcidr = "@(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)(?:/(?:32|3[01]|[0-2]?[0-9]))?@";
			if (preg_match($ipcidr, $line) === 1) {
				$iprange[] = $line;
				continue;
			}

			$ipnoncidr = "@(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)(?:/(?:32|3[01]|[0-2]?[0-9]))?@";
			if (preg_match($ipnoncidr, $line) === 1) {
				$ip[] = $line;
				continue;
			}

			// it's probably a DNS name.
			$dnsdomain[] = $line;
		}
	}

	/**
	 * @param array        $ip
	 * @param PDOStatement $insert
	 *
	 * @throws Exception
	 */
	protected function doInserts($ip, PDOStatement $insert)
	{
		$successful = true;

		foreach ($ip as $i) {
			if (count($i) > 15) {
				echo "Rejected $i\n";
				$successful = false;

				continue;
			}

			try {
				$insert->execute(array(':ip' => $i));
			}
			catch (PDOException $ex) {
				echo "Exception on $i :\n";
				echo $ex->getMessage();
				$successful = false;
				break;
			}
		}

		if (!$successful) {
			throw new Exception('Encountered errors during transaction processing');
		}
	}
}
<?php

namespace OPodSync;

class Utils
{
	/**
	 * Returns the short git commit hash the app is running from, or NULL if unavailable.
	 */
	static public function getGitCommit(): ?string
	{
		static $commit = false;

		if ($commit !== false) {
			return $commit;
		}

		$dir = dirname(ROOT) . '/.git';

		if (!is_dir($dir) || !is_readable($dir . '/HEAD')) {
			return $commit = null;
		}

		$head = trim((string) @file_get_contents($dir . '/HEAD'));

		// HEAD points to a ref (e.g. "ref: refs/heads/develop")
		if (str_starts_with($head, 'ref:')) {
			$ref = trim(substr($head, 4));
			$hash = null;

			if (is_readable($dir . '/' . $ref)) {
				$hash = trim((string) @file_get_contents($dir . '/' . $ref));
			}
			// Fall back to packed-refs
			elseif (is_readable($dir . '/packed-refs')) {
				foreach (file($dir . '/packed-refs', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
					if ($line[0] === '#' || $line[0] === '^') {
						continue;
					}

					[$h, $r] = explode(' ', $line, 2) + [null, null];

					if (trim((string) $r) === $ref) {
						$hash = trim((string) $h);
						break;
					}
				}
			}
		}
		else {
			// Detached HEAD: HEAD contains the hash directly
			$hash = $head;
		}

		if (empty($hash) || !preg_match('/^[0-9a-f]{40}$/', $hash)) {
			return $commit = null;
		}

		return $commit = substr($hash, 0, 7);
	}

	static public function format_description(string $str): string

	{
		$str = str_replace('</p>', "\n\n", $str);
		$str = preg_replace_callback('!<a[^>]*href=(".*?"|\'.*?\'|\S+)[^>]*>(.*?)</a>!i', function ($match) {
			$url = trim($match[1], '"\'');
			if ($url === $match[2]) {
				return $match[1];
			}
			else {
				return '[' . $match[2] . '](' . $url . ')';
			}
		}, $str);
		$str = htmlspecialchars(strip_tags($str));
		$str = preg_replace("!(?:\r?\n){3,}!", "\n\n", $str);
		$str = preg_replace('!\[([^\]]+)\]\(([^\)]+)\)!', '<a href="$2">$1</a>', $str);
		$str = preg_replace(';(?<!")https?://[^<\s]+(?!");', '<a href="$0">$0</a>', $str);
		$str = nl2br($str);
		return $str;
	}
}

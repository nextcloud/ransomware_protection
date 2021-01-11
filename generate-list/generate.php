<?php

declare(strict_types=1);

/**
 * Prepare the list of https://docs.google.com/spreadsheets/d/1TWS238xacAto-fLKh1n5uTsdijWdCEsGIM0Y0Hvmc5g/pubhtml
 * to work with plain string comparisons or regexes of php
 *
 * 1. Store the content of the "Extensions" column in extensions.txt
 * 2. Store the content of the "Extension Pattern" column in extension-patterns.txt
 * 3. Execute this file and commit the results
 */

$content = file_get_contents('extensions.txt');
$extensionsPerRW = explode("\n", $content);

$extensionIgnoreList = [
	'',
	'(encrypted)',
	'random',
	'.bin',
	'.css',
	'.dll',
	'.exe',
	'.EXE',
	'.html',
	'.mp3',
];

$extensions = [];
foreach ($extensionsPerRW as $exts) {
	$exts = trim($exts);
	if ($exts === '' || $exts === 'Sarah_G@ausi.com___') {
		continue;
	}

	if ($exts === '.crypt 4 random characters, e.g., .PzZs, .MKJL') {
		$extensions[] = '.crypt';
		continue;
	}

	if ($exts === '.кибер разветвитель') {
		$extensions[] = '.кибер разветвитель';
		continue;
	}

	if ($exts === '_crypt .id-_locked .id-_locked_by_krec .id-_locked_by_perfect .id-_x3m .id-_r9oj .id-_garryweber@protonmail.ch .id-_steaveiwalker@india.com_ .id-_julia.crown@india.com_ .id-_tom.cruz@india.com_ .id-_CarlosBoltehero@india.com_ .id-_maria.lopez1@india.com_') {
		$extensions[] = '(.*)_crypt\.(.*)-_locked$';
		$extensions[] = '(.*)_crypt\.(.*)-_locked_by_krec$';
		$extensions[] = '(.*)_crypt\.(.*)-_locked_by_perfect$';
		$extensions[] = '(.*)_crypt\.(.*)-_x3m$';
		$extensions[] = '(.*)_crypt\.(.*)-_r9oj$';
		$extensions[] = '(.*)_crypt\.(.*)-_(.*)@(.*)$';
		continue;
	}

	$extList = explode(' ', $exts);
	foreach ($extList as $ext) {
		$ext = trim($ext);

		if (in_array($ext, $extensionIgnoreList, true)) {
			continue;
		}

		if ($ext === '.locked-[XXX]') {
			$ext = '\.locked-(.*){3}$';
		} elseif ($ext === '.id-[victim_id]-maestro@pizzacrypts.info') {
			$ext = '\.id-(.*)-maestro@pizzacrypts\.info$';
		} elseif ($ext === '.cry_') {
			$ext = '.cry_*';
		} elseif ($ext === '.~') {
			$ext = '\.~$';
		} elseif ($ext === 'dummy_file.encrypted') {
			$ext = '\.encrypted\.(.*)$';
		} elseif ($ext === 'Lock.') {
			$ext = '^Lock\.';
		} elseif ($ext === '.31392E30362E32303136_[ID-KEY]_LSBJ1') {
			$ext = '\.([0-9A-Z]{20})_([0-9]{2})_([A-Z0-9]{4,5})$';
		} elseif ($ext === 'oor.') {
			$ext = '^oor\.';
		} elseif ($ext === '[KASISKI]') {
			$ext = 'KASISKI';
		} elseif ($ext === '!ENC') {
			$ext = '^!ENC';
		} elseif ($ext === '.[mia.kokers@aol.com]') {
			$ext = '.mia.kokers@aol.com';
		} elseif ($ext === '.[victim_id]_luck') {
			$ext = '\.[A-F0-9]{8}_luck$';
		} elseif ($ext === '.id-1235240425_help@decryptservice.info') {
			$ext = '\.id-(\d+)_(.*)$';
		}

		if (strpos($ext, '[random]') !== false) {
			$ext = '\\' . str_replace('[random]', '(.*)\\', $ext) . '$';
		}

		$extensions[] = $ext;
	}
}

$extensions[] = '.kk';
$extensions[] = '.ykcol';
$extensions[] = 'install_flash_player.exe';
$extensions[] = '.wkgdiba';
$extensions[] = '.NEXTCRY';

$extensions = array_unique($extensions);

file_put_contents('../resources/extensions.txt', implode("\n", $extensions));

$patternIgnoreList = [
	'',
	'random(x5)',
	'no filename change',
	'.id-%ID%_garryweber@protonmail.ch',
	'<6 random characters>',
	'name_crypt..extension',
	'grfg.wct.CRYPTOSHIELD',
	'Encrypt the extension using ROT-23',
	'removes extensions',
	'._[timestamp]_$[email]$.777 e.g. ._14-05-2016-11-59-36_$ninja.gaiver@aol.com$.777', // Covered by `.777` from extensions.txt
	'!___[EMAILADDRESS]_.crypt', // Covered by `.crypt` from extensions.txt
	'<file_hash>.locked', // Covered by `.locked` from extensions.txt
	'<ID>.locked, e.g., bill.!ID!8MMnF!ID!.locked', // Covered by `.locked` from extensions.txt
	'.id-[id].[email].bip', // Covered by `.bip` from extensions.txt
	'email-[params].cbf', // Covered by `.cbf` from extensions.txt
	'.id-########.decryptformoney@india.com.xtbl .[email_address].DHARMA', // Covered by `.xtbl` and `.DHARMA` from extensions.txt
	'.<email>.(dharma|wallet|zzzzz) .id-%ID%.[moneymaker2@india.com].wallet', // Covered by `.dharma`, `.wallet` and `.zzzzz` from extensions.txt
	'.id_(ID_MACHINE)_email_xoomx@dr.com_.code .id_*_email_zeta@dr.com .id_(ID_MACHINE)_email_anx@dr.com_.scl .email[supl0@post.com]id[\[[a-z0-9]{16}\]].lesli *filename*.email[*email*]_id[*id*].rdmk', // Covered by `.rdmk` from extensions.txt
	'([A-F0-9]{32}).locky ([A-F0-9]{32}).zepto ([A-F0-9]{32}).odin ([A-F0-9]{32}).shit ([A-F0-9]{32}).thor ([A-F0-9]{32}).aesir ([A-F0-9]{32}).zzzzz ([A-F0-9]{32}).osiris', // Covered by rules from extensions.txt
	'C-email-abennaki@india.com-(NOMBRE_ARCHIVO.ext).odcodc', // Covered by `.odcodc` from extensions.txt
	'file name[ID-000QQQ.hacker@AOL.com].phobos', // Covered by `.phobos` from extensions.txt
	'appending .abcde to the original file name (e.g., filename.txt.abcde)', // Covered by `.abcde` from extensions.txt
	'.([a-z]{6,7})',
	'[a-z]{4,6}',
	'.([a-zA-Z0-9]{4})',
	'[a-z]{4,6},[0-9]',
	'<random>.<random>, e.g., 27p9k967z.x1nep',
	'.<email>.<random> e.g.: .7076.docx.okean-1955@india.com.!dsvgdfvdDVGR3SsdvfEF75sddf#xbkNY45fg6}P{cg',
	'.coderksu@gmail_com_id[0-9]{2,3} .crypt@india.com.[\w]{4,12}',
	'[filename].ID-*8characters+countrycode[cryptservice@inbox.ru].[random7characters] *filename*.ID-[A-F0-9]{8}+countrycode[cryptcorp@inbox.ru].[a-z0-9]{13}',
	'.id-[ID]_[EMAIL_ADDRESS]',
	'id[_ID]email_xerx@usa.com.scl',
	'test.cry_jpg',
];

$content = file_get_contents('extension-patterns.txt');
$extensionPatterns = explode("\n", $content);

$patterns = [];
foreach ($extensionPatterns as $pattern) {
	$pattern = trim($pattern);

	if (in_array($pattern, $patternIgnoreList, true)) {
		continue;
	}

	if ($pattern === '._[timestamp]_$[email]$.777 e.g. ._14-05-2016-11-59-36_$ninja.gaiver@aol.com$.777') {
		$patterns[] = '\._([\d\-]+)_(.*)\.777$';
		continue;
	}

	if ($pattern === 'random.exotic') {
		$patterns[] = '\.exotic$';
		continue;
	}

	if ($pattern === '[base64].kraken') {
		$patterns[] = '\.kraken$';
		continue;
	}

	if ($pattern === '[A-F0-9]{8}_luck') {
		$patterns[] = '([A-F0-9]{8})_luck$';
		continue;
	}

	if ($pattern === 'hydracrypt_ID_[\w]{8}') {
		$patterns[] = 'hydracrypt_ID_([\w]{8})$';
		continue;
	}

	if ($pattern === '(.*).encoded.([A-Z0-9]{9})') {
		$patterns[] = '(.*)\.encoded\.([A-Z0-9]{9})$';
		continue;
	}

	if ($pattern === '[a-zA-Z0-9+_-]{1,}.[a-z0-9]{3,4}.locky') {
		$patterns[] = '([a-zA-Z0-9+_-]{1,})\.([a-z0-9]{3,4})\.locky$';
		continue;
	}

	if ($pattern === '.([0-9A-Z]{20})_([0-9]{2})_([A-Z0-9]{4,5})') {
		$patterns[] = '\.([0-9A-Z]{20})_([0-9]{2})_([A-Z0-9]{4,5})$';
		continue;
	}

	if ($pattern === 'umbrecrypt_ID_[VICTIMID]') {
		$patterns[] = '^umbrecrypt_';
		continue;
	}

	if ($pattern === '%random%.EnCrYpTeD') {
		$patterns[] = '\.EnCrYpTeD$';
		continue;
	}

	if ($pattern === 'locked-<original name>.[a-zA-Z]{4}') {
		$patterns[] = '^locked-(.*)\.([a-zA-Z]{4})$';
		continue;
	}

	if ($pattern === 'dummy_file.encrypted.[extension]') {
		$patterns[] = '^(.*).encrypted.(.*)$';
		continue;
	}

	if ($pattern === 'decipher_ne@outlook.com_[encrypted_filename] unCrypte@outlook.com_[encrypted_filename]') {
		$patterns[] = '^decipher_ne@outlook\.com_';
		$patterns[] = '^unCrypte@outlook\.com_';
		continue;
	}

	var_dump($pattern);
}

file_put_contents('../resources/extensions.txt', "\n" . implode("\n", $patterns) . "\n", FILE_APPEND);

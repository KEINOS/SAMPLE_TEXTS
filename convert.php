#!/usr/bin/env php
<?php

const LF="\n";
const CR="\r";
const CRLF="\r\n";

$path_file_control = './MASTER';
$text_master       = file_get_contents($path_file_control);
$encode_master     = mb_detect_encoding($text_master);
$eol_master        = detectEOL($text_master);

#print_r(mb_list_encodings());

$list_encode = [
    'UTF-8',
    'EUC-JP',
    'SJIS',
    'eucJP-win',
    'EUC-JP-2004',
    'SJIS-win',
    'SJIS-Mobile#DOCOMO',
    'SJIS-Mobile#KDDI',
    'SJIS-Mobile#SOFTBANK',
    'SJIS-mac',
    'SJIS-2004',
    'UTF-8-Mobile#DOCOMO',
    'UTF-8-Mobile#KDDI-A',
    'UTF-8-Mobile#KDDI-B',
    'UTF-8-Mobile#SOFTBANK',
    'CP932',
    'CP51932',
    'JIS',
    'ISO-2022-JP',
    'ISO-2022-JP-MS',
    'JIS-ms',
    'ISO-2022-JP-2004',
    'ISO-2022-JP-MOBILE#KDDI',
    'CP50220',
    'CP50220raw',
    'CP50221',
    'CP50222',
];

/* [Main] ============================================================================ */

$list_created = [];

foreach ($list_encode as $encode) {
    // LF
    $list_created[] = createFileConverted($text_master, LF, $encode, $encode_master);
    // CRLF
    $list_created[] = createFileConverted($text_master, CRLF, $encode, $encode_master);
}

$list_created_json = json_encode($list_created, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
if (false === file_put_contents('./list.json', $list_created_json)) {
    fputs(STDERR, 'Error while exporting JSON file.');
    exit(1);
}

echo 'DONE', PHP_EOL;
exit(0);

/* [Functions] ======================================================================= */

function createFileConverted($text_master, $code_eol, $encode_to, $encode_from)
{
    $text_uniformed = uniformEOL($text_master, $code_eol);
    $text_converted = mb_convert_encoding($text_uniformed, $encode_to, $encode_from);
    $name_file = createFileName($encode_to, $code_eol, '.txt');
    $path_file = './' . $name_file;
    $url_base  = 'https://KEINOS.github.io/SAMPLE_TEXTS/';

    if (false === file_put_contents($path_file, $text_converted)) {
        fputs(STDERR, 'Error while exporting text file.');
        exit(1);
    }
    return [
        'name_file' => $name_file,
        'name_encoding' => $encode_to,
        'code_eol' => getNameEOL($code_eol),
        'url_download' => $url_base . $name_file,
    ];
}

function createFileName($name_encode, $code_eol, $extension)
{
    $name_encode = uniformNameEncodeAsFileName($name_encode);
    $name_eol    = getNameEol($code_eol);
    $name_file   = $name_encode . '_' . $name_eol;
    $extension   = '.' . trim(str_replace('.', '', $extension));

    return $name_file . $extension;
}

function detectEOL($string)
{
    if (false !== strpos($string, CRLF)) {
        return CRLF;
    }
    if (false !== strpos($string, CR)) {
        return CR;
    }
    if (false !== strpos($string, LF)) {
        return LF;
    }

    fputs(STDERR, 'No valid EOL found.');
    exit(1);
}

function getNameEOL($code_eol)
{
    if ($code_eol === LF) {
        return 'LF';
    }
    if ($code_eol === CRLF) {
        return 'CRLF';
    }
    if ($code_eol === CR) {
        return 'CR';
    }
    fputs(STDERR, 'Unknown End Of File Code.');
    exit(1);
}

function uniformNameEncodeAsFileName($string)
{
    $string = str_replace('#', '-', $string);
    $string = str_replace('_', '-', $string);
    return trim($string);
}

function uniformEOL($string, $to = LF)
{
    return strtr($string, array_fill_keys(array(CRLF, CR, LF), $to));
}

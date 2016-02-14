<?php
ini_set('memory_limit', '512M');

$inputClilocs = [];
$directoryItarator = new DirectoryIterator('input');

foreach ($directoryItarator as $fileInfo)
{
    if (strpos(strtolower($fileInfo->getFilename()), 'cliloc') !== false)
    {
        array_push($inputClilocs, ['filePath' => $fileInfo->getPathname(), 'language' => strtoupper($fileInfo->getExtension())]);
    }
}

$allLanguages = [];

$useClilocEnuAsFallback = false;

foreach ($inputClilocs as $cliloc)
{
    array_push($allLanguages, $cliloc['language']);

    if ($cliloc['language'] == "ENU")
    {
        $useClilocEnuAsFallback = true;
    }
}

foreach ($inputClilocs as $cliloc)
{
    $outputFilePath = str_replace('input', 'output', $cliloc['filePath']);

    $json = json_decode(file_get_contents('json/Cliloc.json'), true);

    $fp = fopen($outputFilePath, 'wb');
    fwrite($fp, pack('C*', 2, 0, 0, 0, 1, 0));

    foreach ($json as $number => $languages)
    {
        $text = $languages[$cliloc['language']];

        if (empty($text) && $useClilocEnuAsFallback)
        {
            $text = $languages['ENU'];
        }

        fwrite($fp, pack('V', $number));
        fwrite($fp, pack('C', 0));
        fwrite($fp, pack('v', strlen($text)));
        fwrite($fp, $text);
    }

    fclose($fp);
}
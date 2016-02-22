<?php
ini_set('memory_limit', '512M');

$inputClilocs = [];
$directoryIterator = new DirectoryIterator('input');

foreach ($directoryIterator as $fileInfo)
    if (strpos(strtolower($fileInfo->getFilename()), 'cliloc') !== false)
        array_push($inputClilocs, ['filePath' => $fileInfo->getPathname(), 'language' => strtoupper($fileInfo->getExtension())]);

$allLanguages = [];

foreach ($inputClilocs as $cliloc)
    array_push($allLanguages, $cliloc['language']);

$output = [];

foreach ($inputClilocs as $cliloc)
{
    echo "Reading {$cliloc['filePath']}... ";

    $handle = fopen($cliloc['filePath'], 'rb');

    fseek($handle, 6);

    while (!feof($handle))
    {
        $numberBytes = fread($handle, 4);

        if (empty($numberBytes))
            break;

        fread($handle, 1);
        $lengthBytes = fread($handle, 2);

        $number = unpack('V', $numberBytes)[1];
        $length = unpack('v', $lengthBytes)[1];

        $text = $length > 0 ? fread($handle, $length) : '';

        foreach ($allLanguages as $language)
        {
            if (empty($output[$number]))
                $output[$number] = [];

            if (empty($output[$number][$language]))
                $output[$number][$language] = '';
        }

        $output[$number][$cliloc['language']] = $text;
    }

    fclose($handle);

    echo 'OK!' . PHP_EOL;
}

$saved = file_put_contents('json/Cliloc.json', json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo ($saved === false) ? 'Error! Cliloc.json could not be saved.' : 'Done! Cliloc.json has been saved in json folder. You can open and edit it using any text editor.';

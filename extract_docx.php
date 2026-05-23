<?php
$docx = 'd:/KULIAH/Skripsi/Panduan Instruksi Kerja Provillo Ai Agent.docx';
$zip = new ZipArchive();
$zip->open($docx);
$xml = $zip->getFromName('word/document.xml');
$zip->close();
$dom = new DOMDocument();
$dom->loadXML($xml);
$xpath = new DOMXPath($dom);
$xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
$paragraphs = $xpath->query('//w:p');
$lines = [];
foreach ($paragraphs as $p) {
    $texts = $xpath->query('.//w:t', $p);
    $text = '';
    foreach ($texts as $t) {
        $text .= $t->textContent;
    }
    if (trim($text)) {
        $lines[] = trim($text);
    }
}
$total = count($lines);
echo "Total lines: $total\n";
// Print lines 1000 to 1500
for ($i = 1000; $i < min(1500, $total); $i++) {
    echo ($i + 1) . ': ' . $lines[$i] . "\n";
}

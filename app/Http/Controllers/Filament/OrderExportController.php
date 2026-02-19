<?php

namespace App\Http\Controllers\Filament;

use App\Models\Order;
use Illuminate\Http\Response;
use ZipArchive;

class OrderExportController
{
    public function show(Order $order): Response
    {
        $documentXml = $this->buildDocumentXml($order);
        $content = $this->createDocx($documentXml);
        $filename = 'order-' . $order->id . '.docx';

        return response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    protected function buildDocumentXml(Order $order): string
    {
        $rows = [];

        $rows[] = $this->row('№ заявки', (string) $order->id, true);
        $rows[] = $this->row('Заявка на отгрузку', '', true, ['center' => true]);

        $agent = $order->agent;

        $rows[] = $this->row('Наименование юридического лица', optional($agent)->title);
        $rows[] = $this->row('Контактное лицо', optional($agent)->name);
        $rows[] = $this->row('Телефон', optional($agent)->phone);
        $rows[] = $this->row('Время, дата составления заявки', optional($order->created_at)->format('d.m.Y H:i:s'));
        $rows[] = $this->row('Дата доставки груза', optional($order->delivery_date)->format('d.m.Y'));
        $rows[] = $this->row('Адрес забора груза', $order->transfer_method_pick_address);
        $rows[] = $this->row('Количество коробов в поставке, шт', $this->numberOrEmpty($order->boxes_count));
        $rows[] = $this->row('Количество палет в поставке, шт.', $this->numberOrEmpty($order->pallets_count));
        $rows[] = $this->row('Кг\м³', $this->numberOrEmpty($order->boxes_weight));
        $rows[] = $this->row('Форма оплаты', $this->paymentMethod($order));
        $rows[] = $this->row('Склад МП', $order->warehouse_id);
        $rows[] = $this->row('Дата поставки на склад МП', optional($order->delivery_date)->format('d.m.Y'));

        $rows[] = $this->emptyRow();

        $rows[] = $this->row('Стоимость забора груза', $this->formatMoney($order->pick));
        $rows[] = $this->row('Стоимость перевозки', $this->formatMoney($order->delivery));
        $rows[] = $this->row('Палетирование', $this->formatMoney($order->additional));
        $rows[] = $this->row('Сумма к оплате', $this->formatMoney($order->total), false, ['bold' => true]);

        $rows[] = $this->emptyRow();

        $rows[] = $this->row('Груз сдал', '(дата,подпись представителя поставщика)');
        $rows[] = $this->emptyRow();
        $rows[] = $this->row('Груз принял', '(дата, подпись представителя ТК)');

        $rowsXml = implode('', $rows);

        return <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:document xmlns:wpc="http://schemas.microsoft.com/office/word/2010/wordprocessingCanvas" xmlns:mc="http://schemas.openxmlformats.org/markup-compatibility/2006" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:m="http://schemas.openxmlformats.org/officeDocument/2006/math" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:wp14="http://schemas.microsoft.com/office/word/2010/wordprocessingDrawing" xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing" xmlns:w10="urn:schemas-microsoft-com:office:word" xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" xmlns:w14="http://schemas.microsoft.com/office/word/2010/wordml" xmlns:wpg="http://schemas.microsoft.com/office/word/2010/wordprocessingGroup" xmlns:wpi="http://schemas.microsoft.com/office/word/2010/wordprocessingInk" xmlns:wne="http://schemas.microsoft.com/office/word/2006/wordml" xmlns:wps="http://schemas.microsoft.com/office/word/2010/wordprocessingShape" mc:Ignorable="w14 wp14">
  <w:body>
    <w:tbl>
      <w:tblPr>
        <w:tblW w:w="0" w:type="auto"/>
        <w:tblBorders>
          <w:top w:val="single" w:sz="4" w:space="0" w:color="000000"/>
          <w:left w:val="single" w:sz="4" w:space="0" w:color="000000"/>
          <w:bottom w:val="single" w:sz="4" w:space="0" w:color="000000"/>
          <w:right w:val="single" w:sz="4" w:space="0" w:color="000000"/>
          <w:insideH w:val="single" w:sz="4" w:space="0" w:color="000000"/>
          <w:insideV w:val="single" w:sz="4" w:space="0" w:color="000000"/>
        </w:tblBorders>
        <w:tblLayout w:type="fixed"/>
      </w:tblPr>
      <w:tblGrid>
        <w:gridCol w:w="4000"/>
        <w:gridCol w:w="8000"/>
      </w:tblGrid>
      {$rowsXml}
    </w:tbl>
    <w:sectPr>
      <w:pgSz w:w="11906" w:h="16838"/>
      <w:pgMar w:top="1440" w:right="1440" w:bottom="1440" w:left="1440" w:header="708" w:footer="708" w:gutter="0"/>
      <w:cols w:space="708"/>
      <w:docGrid w:type="lines" w:linePitch="360"/>
    </w:sectPr>
  </w:body>
</w:document>
XML;
    }

    protected function createDocx(string $documentXml): string
    {
        $contentTypes = <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>
  <Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>
  <Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>
</Types>
XML;

        $rels = <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>
  <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>
</Relationships>
XML;

        $core = <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
  <dc:title>Заявка на отгрузку</dc:title>
  <dc:creator>lk.tk82.ru</dc:creator>
  <cp:lastModifiedBy>lk.tk82.ru</cp:lastModifiedBy>
  <dcterms:created xsi:type="dcterms:W3CDTF">%s</dcterms:created>
  <dcterms:modified xsi:type="dcterms:W3CDTF">%s</dcterms:modified>
</cp:coreProperties>
XML;

        $now = now()->format('Y-m-d\TH:i:s\Z');
        $core = sprintf($core, $now, $now);

        $app = <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">
  <Application>lk.tk82.ru</Application>
</Properties>
XML;

        $tmp = tempnam(sys_get_temp_dir(), 'docx');
        $zip = new ZipArchive();
        $zip->open($tmp, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', $contentTypes);
        $zip->addFromString('_rels/.rels', $rels);
        $zip->addFromString('word/document.xml', $documentXml);
        $zip->addFromString('docProps/core.xml', $core);
        $zip->addFromString('docProps/app.xml', $app);
        $zip->close();

        $content = file_get_contents($tmp);
        unlink($tmp);

        return $content;
    }

    protected function row(string $label, ?string $value, bool $strongLabel = false, array $options = []): string
    {
        $labelCell = $this->cell($label, ['bold' => $strongLabel]);
        $valueCell = $this->cell($value, $options);

        return '<w:tr>' . $labelCell . $valueCell . '</w:tr>';
    }

    protected function cell(?string $text, array $options = []): string
    {
        $text = $text ?? '';
        $text = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $bold = !empty($options['bold']);
        $center = !empty($options['center']);

        $rPr = '';
        if ($bold || $center) {
            $props = [];
            if ($bold) {
                $props[] = '<w:b/>';
            }
            if ($center) {
                $props[] = '<w:jc w:val="center"/>';
            }
            $rPr = '<w:rPr>' . implode('', array_filter([
                $bold ? '<w:b/>' : null,
            ])) . '</w:rPr>';
        }

        $pPr = $center ? '<w:pPr><w:jc w:val="center"/></w:pPr>' : '';

        return '<w:tc><w:tcPr><w:tcW w:w="8000" w:type="dxa"/></w:tcPr>' . $pPr . '<w:p>' . $rPr . '<w:r><w:t xml:space="preserve">' . $text . '</w:t></w:r></w:p></w:tc>';
    }

    protected function emptyRow(): string
    {
        return '<w:tr>' . $this->cell('') . $this->cell('') . '</w:tr>';
    }

    protected function paymentMethod(Order $order): string
    {
        return match ($order->payment_method) {
            'cash' => 'Наличными при отправке',
            'bill' => 'По счету',
            default => (string) $order->payment_method,
        };
    }

    protected function formatMoney($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return number_format((float) $value, 2, ',', ' ') . ' руб.';
    }

    protected function numberOrEmpty($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return (string) $value;
    }
}

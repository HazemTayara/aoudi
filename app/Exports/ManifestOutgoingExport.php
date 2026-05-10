<?php

namespace App\Exports;

use App\Models\Menafest;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class ManifestOutgoingExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles, WithEvents
{
    protected $menafest;
    protected $count;
    protected $stats;

    public function __construct(Menafest $menafest)
    {
        $this->menafest = $menafest;
        $this->calculateStats();
    }

    /**
     * Calculate all statistics
     */
    private function calculateStats()
    {
        $orders = $this->menafest->orders;

        // Total counts
        $this->stats = [
            'total_orders' => $orders->count(),
            'total_count' => $orders->sum('count'),

            // Payment type counts
            'collection_count' => $orders->where('pay_type', 'تحصيل')->count(),
            'prepaid_count' => $orders->where('pay_type', 'مسبق')->count(),

            // Payment type amounts
            'collection_amount' => $orders->where('pay_type', 'تحصيل')->sum('amount'),
            'prepaid_amount' => $orders->where('pay_type', 'مسبق')->sum('amount'),

            // Total sum of all amounts (تحصيل + مسبق)
            'total_sum_amount' => $orders->sum('amount'),

            // Other financial totals
            'total_amount' => $orders->sum('amount'),
            'total_anti_charger' => $orders->sum('anti_charger'),
            'total_transmitted' => $orders->sum('transmitted'),
            'total_miscellaneous' => $orders->sum('miscellaneous'),
            'total_discount' => $orders->sum('discount'),
        ];

        // Calculate net total
        $this->stats['net_total'] = $this->stats['total_amount']
            + $this->stats['total_anti_charger']
            + $this->stats['total_transmitted']
            + $this->stats['total_miscellaneous']
            - $this->stats['total_discount'];
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->menafest->orders;
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'منفست ' . $this->menafest->manafest_code;
    }

    /**
     * @param mixed $order
     */
    public function map($order): array
    {
        return [
            $this->count++ + 1,
            $order->order_number,
            $order->content,
            $order->count,
            $order->sender,
            $order->recipient,
            $order->pay_type,
            format_number($order->amount),
            format_number($order->anti_charger),
            format_number($order->transmitted),
            format_number($order->miscellaneous),
            format_number($order->discount),
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            ['منفست - ' . $this->menafest->manafest_code],
            ['من مدينة: ' . $this->menafest->fromCity->name . ' | إلى مدينة: ' . $this->menafest->toCity->name],
            ['السائق: ' . $this->menafest->driver_name . ' | السيارة: ' . $this->menafest->car . ' | تاريخ الإنشاء: ' . now()->format('Y/m/d')],
            ['ملاحظات: ' . ($this->menafest->notes ?? '---')],
            [], // Empty row for spacing
            [
                '#',
                'رقم الطلب',
                'المحتوى',
                'العدد',
                'المرسل',
                'المرسل إليه',
                'الدفع',
                'المبلغ',
                'ضد الدفع',
                'محول',
                'متفرقات',
                'الخصم',
            ]
        ];
    }

    /**
     * Calculate the optimal width for a column based on its content
     */
    private function calculateOptimalWidth($data, $fontSize = 10, $extraPadding = 1)
    {
        $maxWidth = 0;

        foreach ($data as $value) {
            // Convert to string if not already
            $value = (string) $value;

            // Calculate width considering Arabic characters (they're wider)
            $length = mb_strlen($value, 'UTF-8');
            $arabicCount = preg_match_all('/[\x{0600}-\x{06FF}\x{0750}-\x{077F}]/u', $value);
            $nonArabicCount = $length - $arabicCount;

            // Arabic characters are approximately 1.5 times wider than Latin characters
            $effectiveLength = ($arabicCount * 1.5) + $nonArabicCount;

            // Calculate pixel width (approximate: each character at font size 10 is about 7 pixels)
            $width = ($effectiveLength * ($fontSize * 0.7)) / 7;

            $maxWidth = max($maxWidth, $width);
        }

        // Add padding
        return $maxWidth + $extraPadding;
    }

    /**
     * @param Worksheet $sheet
     */
    public function styles(Worksheet $sheet)
    {
        // Set right-to-left direction
        $sheet->setRightToLeft(true);

        // Set default font size to 10 for the entire sheet
        $sheet->getParent()->getDefaultStyle()->getFont()->setSize(10);

        // Style for main header (reduced from 16 to 14 for better A4 fit)
        $sheet->getStyle('A1:L1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1:L1')->getAlignment()->setHorizontal('center');

        // Style for info rows (reduced from 11 to 10)
        $sheet->getStyle('A2:L4')->getFont()->setSize(10);
        $sheet->getStyle('A2:L4')->getAlignment()->setHorizontal('center');

        // Style for column headers (row 6)
        $sheet->getStyle('A6:L6')->getFont()->setBold(true);
        $sheet->getStyle('A6:L6')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFdc3545');
        $sheet->getStyle('A6:L6')->getFont()->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A6:L6')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A6:L6')->getAlignment()->setVertical('center');
        $sheet->getStyle('A6:L6')->getAlignment()->setWrapText(true);

        return [];
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $spreadsheet = $sheet->getDelegate()->getParent();

                // Set page setup for A4
                $spreadsheet->getActiveSheet()
                    ->getPageSetup()
                    ->setPaperSize(PageSetup::PAPERSIZE_A4)
                    ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
                    ->setFitToWidth(1)
                    ->setFitToHeight(0)
                    ->setScale(85); // Slightly reduced scale to ensure fit
    
                // Set margins for better A4 fit (in inches)
                $spreadsheet->getActiveSheet()
                    ->getPageMargins()
                    ->setTop(0.4)
                    ->setBottom(0.4)
                    ->setLeft(0.3)
                    ->setRight(0.3)
                    ->setHeader(0.2)
                    ->setFooter(0.2);

                // Merge cells for header rows
                $sheet->mergeCells('A1:L1');
                $sheet->mergeCells('A2:L2');
                $sheet->mergeCells('A3:L3');
                $sheet->mergeCells('A4:L4');

                // Calculate optimal column widths based on content
                $columnHeaders = [
                    'A' => '#',
                    'B' => 'رقم الطلب',
                    'C' => 'المحتوى',
                    'D' => 'العدد',
                    'E' => 'المرسل',
                    'F' => 'المرسل إليه',
                    'G' => 'الدفع',
                    'H' => 'المبلغ',
                    'I' => 'ضد الدفع',
                    'J' => 'محول',
                    'K' => 'متفرقات',
                    'L' => 'الخصم',
                ];

                // Collect all data for width calculation
                $columnData = array_fill_keys(array_keys($columnHeaders), []);

                // Add header data
                foreach ($columnHeaders as $col => $header) {
                    $columnData[$col][] = $header;
                }

                // Add actual data
                $rowNum = 7; // Data starts from row 7
                foreach ($this->collection() as $order) {
                    $mappedData = [
                        'A' => (string) ($rowNum - 6), // Row number
                        'B' => (string) $order->order_number,
                        'C' => (string) $order->content,
                        'D' => (string) $order->count,
                        'E' => (string) $order->sender,
                        'F' => (string) $order->recipient,
                        'G' => (string) $order->pay_type,
                        'H' => (string) format_number($order->amount),
                        'I' => (string) format_number($order->anti_charger),
                        'J' => (string) format_number($order->transmitted),
                        'K' => (string) format_number($order->miscellaneous),
                        'L' => (string) format_number($order->discount),
                    ];

                    foreach ($mappedData as $col => $value) {
                        $columnData[$col][] = $value;
                    }

                    $rowNum++;
                }

                // Set optimal widths with minimum constraints
                $minWidths = [
                    'A' => 4,   // #
                    'B' => 8,   // رقم الطلب
                    'C' => 10,  // المحتوى
                    'D' => 6,   // العدد
                    'E' => 8,   // المرسل
                    'F' => 8,   // المرسل إليه
                    'G' => 6,   // الدفع
                    'H' => 8,   // المبلغ
                    'I' => 8,   // ضد الدفع
                    'J' => 6,   // محول
                    'K' => 8,   // متفرقات
                    'L' => 6,   // الخصم
                ];

                // Set column widths only for the data table area (not affecting stats section)
                foreach ($columnData as $col => $data) {
                    $optimalWidth = $this->calculateOptimalWidth($data, 10, 2);
                    $minWidth = $minWidths[$col] ?? 6;
                    $finalWidth = max($optimalWidth, $minWidth);

                    // Cap maximum width to prevent extremely wide columns
                    $finalWidth = min($finalWidth, 20);

                    $sheet->getColumnDimension($col)->setWidth($finalWidth);
                }

                // Get the last row of data
                $lastRow = $sheet->getHighestRow();

                // Center all cells in the data table and add borders
                if ($lastRow > 6) {
                    // Center align all data cells
                    $sheet->getStyle('A7:L' . $lastRow)->getAlignment()->setHorizontal('center');
                    $sheet->getStyle('A7:L' . $lastRow)->getAlignment()->setVertical('center');

                    // Add borders to data rows
                    $sheet->getStyle('A6:L' . $lastRow)->getBorders()->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN);
                }

                // Add statistics section as a compact centered widget
                $statsRow = $lastRow + 2;

                // Calculate the middle columns for centering the stats widget
                // Use columns D through I (6 columns) for the stats widget to keep it centered and compact
                $statsStartCol = 'D';
                $statsEndCol = 'I';
                $statsMergeRange = $statsStartCol . $statsRow . ':' . $statsEndCol . $statsRow;

                // Statistics Header
                $sheet->setCellValue($statsStartCol . $statsRow, 'إحصائيات المنفست');
                $sheet->mergeCells($statsMergeRange);
                $sheet->getStyle($statsMergeRange)->getFont()->setBold(true)->setSize(11);
                $sheet->getStyle($statsMergeRange)->getAlignment()->setHorizontal('center');
                $sheet->getStyle($statsMergeRange)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FF17a2b8');
                $sheet->getStyle($statsMergeRange)->getFont()->getColor()->setARGB('FFFFFFFF');
                $sheet->getStyle($statsMergeRange)->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                // General Stats
                $currentRow = $statsRow + 1;

                // إجمالي عدد الطلبات
                $labelRange = $statsStartCol . $currentRow . ':' . $this->incrementColumn($statsStartCol, 2) . $currentRow;
                $valueRange = $this->incrementColumn($statsStartCol, 3) . $currentRow . ':' . $statsEndCol . $currentRow;
                $fullRange = $statsStartCol . $currentRow . ':' . $statsEndCol . $currentRow;

                $sheet->mergeCells($labelRange);
                $sheet->mergeCells($valueRange);
                $sheet->setCellValue($statsStartCol . $currentRow, 'إجمالي عدد الطلبات:');
                $sheet->setCellValue($this->incrementColumn($statsStartCol, 3) . $currentRow, format_number($this->stats['total_orders']));
                $sheet->getStyle($labelRange)->getFont()->setBold(true);
                $sheet->getStyle($labelRange)->getAlignment()->setHorizontal('right');
                $sheet->getStyle($valueRange)->getAlignment()->setHorizontal('center');
                $sheet->getStyle($fullRange)->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                $currentRow++;
                // إجمالي عدد القطع
                $labelRange = $statsStartCol . $currentRow . ':' . $this->incrementColumn($statsStartCol, 2) . $currentRow;
                $valueRange = $this->incrementColumn($statsStartCol, 3) . $currentRow . ':' . $statsEndCol . $currentRow;
                $fullRange = $statsStartCol . $currentRow . ':' . $statsEndCol . $currentRow;

                $sheet->mergeCells($labelRange);
                $sheet->mergeCells($valueRange);
                $sheet->setCellValue($statsStartCol . $currentRow, 'إجمالي عدد القطع:');
                $sheet->setCellValue($this->incrementColumn($statsStartCol, 3) . $currentRow, format_number($this->stats['total_count']));
                $sheet->getStyle($labelRange)->getFont()->setBold(true);
                $sheet->getStyle($labelRange)->getAlignment()->setHorizontal('right');
                $sheet->getStyle($valueRange)->getAlignment()->setHorizontal('center');
                $sheet->getStyle($fullRange)->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                $currentRow++;
                // إجمالي المبلغ الكلي
                $labelRange = $statsStartCol . $currentRow . ':' . $this->incrementColumn($statsStartCol, 2) . $currentRow;
                $valueRange = $this->incrementColumn($statsStartCol, 3) . $currentRow . ':' . $statsEndCol . $currentRow;
                $fullRange = $statsStartCol . $currentRow . ':' . $statsEndCol . $currentRow;

                $sheet->mergeCells($labelRange);
                $sheet->mergeCells($valueRange);
                $sheet->setCellValue($statsStartCol . $currentRow, 'إجمالي المبلغ الكلي:');
                $sheet->setCellValue($this->incrementColumn($statsStartCol, 3) . $currentRow, format_number($this->stats['total_sum_amount']));
                $sheet->getStyle($labelRange)->getFont()->setBold(true);
                $sheet->getStyle($labelRange)->getAlignment()->setHorizontal('right');
                $sheet->getStyle($valueRange)->getAlignment()->setHorizontal('center');
                $sheet->getStyle($fullRange)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFe8f4f8');
                $sheet->getStyle($fullRange)->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                $currentRow += 1; // Add spacing
    
                // Payment Type Stats - compact table
                $paymentTitleRange = $statsStartCol . $currentRow . ':' . $statsEndCol . $currentRow;
                $sheet->setCellValue($statsStartCol . $currentRow, 'تفاصيل نوع الدفع');
                $sheet->mergeCells($paymentTitleRange);
                $sheet->getStyle($paymentTitleRange)->getFont()->setBold(true)->setUnderline(true);
                $sheet->getStyle($paymentTitleRange)->getAlignment()->setHorizontal('center');

                $currentRow++;
                // Payment stats header row
                $col1Range = $statsStartCol . $currentRow . ':' . $statsStartCol . $currentRow;
                $col2Range = $this->incrementColumn($statsStartCol, 1) . $currentRow . ':' . $this->incrementColumn($statsStartCol, 2) . $currentRow;
                $col3Range = $this->incrementColumn($statsStartCol, 3) . $currentRow . ':' . $statsEndCol . $currentRow;
                $fullHeaderRange = $statsStartCol . $currentRow . ':' . $statsEndCol . $currentRow;

                $sheet->mergeCells($col2Range);
                $sheet->mergeCells($col3Range);

                $sheet->setCellValue($statsStartCol . $currentRow, 'النوع');
                $sheet->setCellValue($this->incrementColumn($statsStartCol, 1) . $currentRow, 'العدد');
                $sheet->setCellValue($this->incrementColumn($statsStartCol, 3) . $currentRow, 'الإجمالي');

                $sheet->getStyle($fullHeaderRange)->getFont()->setBold(true);
                $sheet->getStyle($fullHeaderRange)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FF6c757d');
                $sheet->getStyle($fullHeaderRange)->getFont()->getColor()->setARGB('FFFFFFFF');
                $sheet->getStyle($statsStartCol . $currentRow)->getAlignment()->setHorizontal('center');
                $sheet->getStyle($col2Range)->getAlignment()->setHorizontal('center');
                $sheet->getStyle($col3Range)->getAlignment()->setHorizontal('center');
                $sheet->getStyle($fullHeaderRange)->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                $currentRow++;
                // تحصيل row
                $col1Range = $statsStartCol . $currentRow . ':' . $statsStartCol . $currentRow;
                $col2Range = $this->incrementColumn($statsStartCol, 1) . $currentRow . ':' . $this->incrementColumn($statsStartCol, 2) . $currentRow;
                $col3Range = $this->incrementColumn($statsStartCol, 3) . $currentRow . ':' . $statsEndCol . $currentRow;
                $fullRowRange = $statsStartCol . $currentRow . ':' . $statsEndCol . $currentRow;

                $sheet->mergeCells($col2Range);
                $sheet->mergeCells($col3Range);

                $sheet->setCellValue($statsStartCol . $currentRow, 'تحصيل');
                $sheet->setCellValue($this->incrementColumn($statsStartCol, 1) . $currentRow, format_number($this->stats['collection_count']));
                $sheet->setCellValue($this->incrementColumn($statsStartCol, 3) . $currentRow, format_number($this->stats['collection_amount']));
                $sheet->getStyle($statsStartCol . $currentRow)->getAlignment()->setHorizontal('center');
                $sheet->getStyle($col2Range)->getAlignment()->setHorizontal('center');
                $sheet->getStyle($col3Range)->getAlignment()->setHorizontal('center');
                $sheet->getStyle($fullRowRange)->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                $currentRow++;
                // مسبق row
                $col1Range = $statsStartCol . $currentRow . ':' . $statsStartCol . $currentRow;
                $col2Range = $this->incrementColumn($statsStartCol, 1) . $currentRow . ':' . $this->incrementColumn($statsStartCol, 2) . $currentRow;
                $col3Range = $this->incrementColumn($statsStartCol, 3) . $currentRow . ':' . $statsEndCol . $currentRow;
                $fullRowRange = $statsStartCol . $currentRow . ':' . $statsEndCol . $currentRow;

                $sheet->mergeCells($col2Range);
                $sheet->mergeCells($col3Range);

                $sheet->setCellValue($statsStartCol . $currentRow, 'مسبق');
                $sheet->setCellValue($this->incrementColumn($statsStartCol, 1) . $currentRow, format_number($this->stats['prepaid_count']));
                $sheet->setCellValue($this->incrementColumn($statsStartCol, 3) . $currentRow, format_number($this->stats['prepaid_amount']));
                $sheet->getStyle($statsStartCol . $currentRow)->getAlignment()->setHorizontal('center');
                $sheet->getStyle($col2Range)->getAlignment()->setHorizontal('center');
                $sheet->getStyle($col3Range)->getAlignment()->setHorizontal('center');
                $sheet->getStyle($fullRowRange)->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                $statsLastRow = $currentRow;

                // Set print area
                $spreadsheet->getActiveSheet()
                    ->getPageSetup()
                    ->setPrintArea('A1:L' . $statsLastRow);

                // Set repeat rows on each page (header rows)
                $spreadsheet->getActiveSheet()
                    ->getPageSetup()
                    ->setRowsToRepeatAtTopByStartAndEnd(1, 6);
            },
        ];
    }

    /**
     * Helper function to increment column letter
     */
    private function incrementColumn($column, $increment = 1)
    {
        $columnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($column);
        return \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex + $increment);
    }
}
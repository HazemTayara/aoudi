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
                'نوع الدفع',
                'المبلغ',
                'ضد الدفع',
                'محول',
                'متفرقات متنوعة',
                'الخصم',
            ]
        ];
    }

    /**
     * @param Worksheet $sheet
     */
    public function styles(Worksheet $sheet)
    {
        // Set right-to-left direction
        $sheet->setRightToLeft(true);

        // Style for main header
        $sheet->getStyle('A1:L1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1:L1')->getAlignment()->setHorizontal('center');

        // Style for info rows
        $sheet->getStyle('A2:L4')->getFont()->setSize(11);
        $sheet->getStyle('A2:L4')->getAlignment()->setHorizontal('center');

        // Style for column headers (row 6)
        $sheet->getStyle('A6:L6')->getFont()->setBold(true);
        $sheet->getStyle('A6:L6')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFdc3545');
        $sheet->getStyle('A6:L6')->getFont()->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A6:L6')->getAlignment()->setHorizontal('center');

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

                // Merge cells for header rows
                $sheet->mergeCells('A1:L1');
                $sheet->mergeCells('A2:L2');
                $sheet->mergeCells('A3:L3');
                $sheet->mergeCells('A4:L4');

                // Get the last row of data
                $lastRow = $sheet->getHighestRow();

                // Add border to data rows
                $sheet->getStyle('A6:L' . $lastRow)->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                // Add statistics section
                $statsRow = $lastRow + 2;

                // Statistics Header
                $sheet->setCellValue('A' . $statsRow, 'إحصائيات المنفست');
                $sheet->mergeCells('A' . $statsRow . ':L' . $statsRow);
                $sheet->getStyle('A' . $statsRow)->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A' . $statsRow)->getAlignment()->setHorizontal('center');
                $sheet->getStyle('A' . $statsRow)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FF17a2b8');
                $sheet->getStyle('A' . $statsRow)->getFont()->getColor()->setARGB('FFFFFFFF');

                // General Stats
                $currentRow = $statsRow + 2;

                $sheet->setCellValue('A' . $currentRow, 'إجمالي عدد الطلبات:');
                $sheet->setCellValue('B' . $currentRow, format_number($this->stats['total_orders']));
                $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true);

                $currentRow++;
                $sheet->setCellValue('A' . $currentRow, 'إجمالي عدد القطع:');
                $sheet->setCellValue('B' . $currentRow, format_number($this->stats['total_count']));
                $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true);

                $currentRow += 2; // Add spacing
    
                // Payment Type Stats
                $sheet->setCellValue('A' . $currentRow, 'إحصائيات نوع الدفع');
                $sheet->mergeCells('A' . $currentRow . ':C' . $currentRow);
                $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true)->setUnderline(true);

                $currentRow++;
                $sheet->setCellValue('A' . $currentRow, 'النوع');
                $sheet->setCellValue('B' . $currentRow, 'عدد الطلبات');
                $sheet->setCellValue('C' . $currentRow, 'الإجمالي');
                $sheet->getStyle('A' . $currentRow . ':C' . $currentRow)->getFont()->setBold(true);
                $sheet->getStyle('A' . $currentRow . ':C' . $currentRow)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FF6c757d');
                $sheet->getStyle('A' . $currentRow . ':C' . $currentRow)->getFont()->getColor()->setARGB('FFFFFFFF');

                $currentRow++;
                $sheet->setCellValue('A' . $currentRow, 'تحصيل');
                $sheet->setCellValue('B' . $currentRow, format_number($this->stats['collection_count']));
                $sheet->setCellValue('C' . $currentRow, format_number($this->stats['collection_amount']));

                $currentRow++;
                $sheet->setCellValue('A' . $currentRow, 'مسبق');
                $sheet->setCellValue('B' . $currentRow, format_number($this->stats['prepaid_count']));
                $sheet->setCellValue('C' . $currentRow, format_number($this->stats['prepaid_amount']));

                // $currentRow += 2; // Add spacing
    
                // Financial Summary
                // $sheet->setCellValue('A' . $currentRow, 'ملخص مالي');
                // $sheet->mergeCells('A' . $currentRow . ':C' . $currentRow);
                // $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true)->setUnderline(true);
    
                // $currentRow++;
                // $sheet->setCellValue('A' . $currentRow, 'البيان');
                // $sheet->setCellValue('B' . $currentRow, 'القيمة');
                // $sheet->getStyle('A' . $currentRow . ':B' . $currentRow)->getFont()->setBold(true);
                // $sheet->getStyle('A' . $currentRow . ':B' . $currentRow)->getFill()
                //     ->setFillType(Fill::FILL_SOLID)
                //     ->getStartColor()->setARGB('FF6c757d');
                // $sheet->getStyle('A' . $currentRow . ':B' . $currentRow)->getFont()->getColor()->setARGB('FFFFFFFF');
    
                $currentRow++;
                $sheet->setCellValue('A' . $currentRow, 'إجمالي المبالغ');
                $sheet->setCellValue('B' . $currentRow, format_number($this->stats['total_amount']));

                // $currentRow++;
                // $sheet->setCellValue('A' . $currentRow, 'إجمالي ضد الدفع');
                // $sheet->setCellValue('B' . $currentRow, $this->stats['total_anti_charger']);
    
                // $currentRow++;
                // $sheet->setCellValue('A' . $currentRow, 'إجمالي محول');
                // $sheet->setCellValue('B' . $currentRow, $this->stats['total_transmitted']);
    
                // $currentRow++;
                // $sheet->setCellValue('A' . $currentRow, 'إجمالي متنوعة');
                // $sheet->setCellValue('B' . $currentRow, $this->stats['total_miscellaneous']);
    
                // $currentRow++;
                // $sheet->setCellValue('A' . $currentRow, 'إجمالي الخصم');
                // $sheet->setCellValue('B' . $currentRow, $this->stats['total_discount']);
    
                // $currentRow += 2; // Add spacing
    
                // Net Total (highlighted)
                // $sheet->setCellValue('A' . $currentRow, 'الصافي الإجمالي');
                // $sheet->setCellValue('B' . $currentRow, $this->stats['net_total']);
                // $sheet->getStyle('A' . $currentRow . ':B' . $currentRow)->getFont()->setBold(true)->setSize(12);
                // $sheet->getStyle('A' . $currentRow . ':B' . $currentRow)->getFill()
                //     ->setFillType(Fill::FILL_SOLID)
                //     ->getStartColor()->setARGB('FF28a745');
                // $sheet->getStyle('A' . $currentRow . ':B' . $currentRow)->getFont()->getColor()->setARGB('FFFFFFFF');
    
                // Auto-size columns for the entire sheet
                foreach (range('A', 'L') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
            },
        ];
    }
}
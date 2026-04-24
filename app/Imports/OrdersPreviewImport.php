<?php

namespace App\Imports;

use App\Models\Menafest;
use Illuminate\Support\Collection;
use Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Validator;

class OrdersPreviewImport implements ToCollection, WithHeadingRow
{
    protected $errors = [];
    protected $menafest;

    public function __construct(Menafest $menafest)
    {
        $this->menafest = $menafest;
    }

    public function collection(Collection $rows)
    {
        $cityName = $this->menafest->fromCity->name;
        if ($cityName === 'حلب') {
            return $this->parseHalab($rows);
        }

        // Default: دمشق format
        return $this->parseDamascus($rows);
    }

    /**
     * Parse Excel rows in دمشق format
     */
    private function parseDamascus(Collection $rows)
    {
        $orders = [];

        foreach ($rows as $index => $row) {
            // Skip empty rows
            if (empty($row['alaysal']) && empty($row['almrsl_alyh'])) {
                continue;
            }

            // Determine pay_type and amount
            $collection = isset($row['althsyl']) ? floatval($row['althsyl']) : 0;
            $prepaid = isset($row['almdfoaa_msbka']) ? floatval($row['almdfoaa_msbka']) : 0;

            if ($collection > 0) {
                $pay_type = 'تحصيل';
                $amount = $collection;
            } elseif ($prepaid > 0) {
                $pay_type = 'مسبق';
                $amount = $prepaid;
            } else {
                $pay_type = 'مسبق';
                $amount = 0;
            }

            $order = [
                'order_number' => trim($row['alaysal'] ?? ''),
                'content' => trim($row['alnoaa'] ?? 'طرد'),
                'count' => is_numeric($row['alaadd'] ?? null) ? intval($row['alaadd']) : 1,
                'sender' => trim($row['asm_almrsl'] ?? ''),
                'recipient' => trim($row['almrsl_alyh'] ?? ''),
                'pay_type' => $pay_type,
                'amount' => $amount,
                'anti_charger' => is_numeric($row['dd_alshhn'] ?? null) ? floatval($row['dd_alshhn']) : 0,
                'transmitted' => is_numeric($row['almhol'] ?? null) ? floatval($row['almhol']) : 0,
                'miscellaneous' => is_numeric($row['mtfrkat_mtnoaa'] ?? null) ? floatval($row['mtfrkat_mtnoaa']) : 0,
                'discount' => is_numeric($row['alkhsm'] ?? null) ? floatval($row['alkhsm']) : 0,
                'is_paid' => false,
                'is_exist' => true,
                'notes' => '',
            ];

            // Validate row
            $validator = Validator::make($order, [
                'order_number' => 'required|max:255',
                'content' => 'nullable|string|max:255',
                'count' => 'required|integer|min:1',
                'sender' => 'required|string|max:255',
                'recipient' => 'required|string|max:255',
                'pay_type' => 'required|in:مسبق,تحصيل',
                'amount' => 'numeric|min:0',
                'anti_charger' => 'numeric|min:0',
                'transmitted' => 'numeric|min:0',
                'miscellaneous' => 'numeric|min:0',
                'discount' => 'numeric|min:0',
            ]);

            if ($validator->fails()) {
                $this->errors[] = "صف " . ($index + 2) . ": " . implode(', ', $validator->errors()->all());
            } else {
                $orders[] = $order;
            }
        }

        session(['import_orders' => $orders]);
        session(['import_errors' => $this->errors]);

        return collect($orders);
    }

    /**
     * Parse Excel rows in حلب format
     * TODO: Implement حلب-specific column mapping when the Excel format is known
     */
    private function parseHalab(Collection $rows)
    {
        $orders = [];

        foreach ($rows as $index => $row) {

            // Determine Order Number value based on available columns
            $orderNumber = isset($row['rkm_alashaaar']) ? $row['rkm_alashaaar'] : $row['almtslsl'] ?? '';

            $order = [
                'order_number' => intval(trim($orderNumber)),
                'content' => trim($row['noaa_altrd'] ?? 'طرد'),
                'count' => is_numeric($row['alkmy'] ?? null) ? intval($row['alkmy']) : 1,
                'sender' => trim($row['almrsl'] ?? ''),
                'recipient' => trim($row['almrsl_alyh'] ?? ''),
                'pay_type' => trim($row['aldfaa'] ?? 'مسبق'),
                'amount' => is_numeric($row['alsafy_lldfaa'] ?? null) ? floatval($row['alsafy_lldfaa']) : 0,
                'anti_charger' => is_numeric($row['dd_aldfaa'] ?? null) ? floatval($row['dd_aldfaa']) : 0,
                'transmitted' => is_numeric($row['almhol'] ?? null) ? floatval($row['almhol']) : 0,
                'miscellaneous' => is_numeric($row['tosyl'] ?? null) ? floatval($row['tosyl']) : 0,
                'discount' => is_numeric($row['alkhsm'] ?? null) ? floatval($row['alkhsm']) : 0,
                'is_paid' => false,
                'is_exist' => true,
                'notes' => '',
            ];

            // Validate row
            $validator = Validator::make($order, [
                'order_number' => 'required|max:255',
                'content' => 'nullable|string|max:255',
                'count' => 'required|integer|min:1',
                'sender' => 'required|string|max:255',
                'recipient' => 'required|string|max:255',
                'pay_type' => 'required|in:مسبق,تحصيل',
                'amount' => 'numeric|min:0',
                'anti_charger' => 'numeric|min:0',
                'transmitted' => 'numeric|min:0',
                'miscellaneous' => 'numeric|min:0',
                'discount' => 'numeric|min:0',
            ]);

            if ($validator->fails()) {
                $this->errors[] = "صف " . ($index + 2) . ": " . implode(', ', $validator->errors()->all());
            } else {
                $orders[] = $order;
            }
        }

        session(['import_orders' => $orders]);
        session(['import_errors' => $this->errors]);

        return collect($orders);

    }
}
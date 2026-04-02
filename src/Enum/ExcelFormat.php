<?php
namespace App\Enum;

enum ExcelFormat: string
{
    case CSV = 'csv';
    case ODS = 'ods';
    case XLSX = 'xlsx';

    public function contentType(): string
    {
        return match ($this) {
            self::CSV => 'text/csv',
            self::ODS => 'application/vnd.oasis.opendocument.spreadsheet',
            self::XLSX => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        };
    }
}


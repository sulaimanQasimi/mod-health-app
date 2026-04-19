<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Hekmatinasser\Verta\Facades\Verta;

class PhysiotherapyReportExport implements WithMultipleSheets
{
    protected $data;
    protected $startDate;
    protected $endDate;

    public function __construct($data, $startDate, $endDate)
    {
        $this->data = $data;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function sheets(): array
    {
        return [
            'Summary' => new SummarySheet($this->data, $this->startDate, $this->endDate),
            'Detailed' => new DetailedSheet($this->data, $this->startDate, $this->endDate),
            'By Type' => new ByTypeSheet($this->data, $this->startDate, $this->endDate),
            'By Physiotherapist' => new ByPhysiotherapistSheet($this->data, $this->startDate, $this->endDate),
        ];
    }
}

class SummarySheet implements FromArray, WithTitle, WithStyles, WithColumnWidths, ShouldAutoSize
{
    protected $data;
    protected $startDate;
    protected $endDate;

    public function __construct($data, $startDate, $endDate)
    {
        $this->data = $data;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function title(): string
    {
        return 'Summary';
    }

    public function array(): array
    {
        $summary = $this->data['summary'] ?? [];
        
        return [
            ['Physiotherapy Report - Summary'],
            [''],
            ['Report Information'],
            ['Start Date', verta($this->startDate)->format('Y-m-d')],
            ['End Date', verta($this->endDate)->format('Y-m-d')],
            ['Generated On', now()->format('Y-m-d H:i:s')],
            [''],
            ['Summary Statistics'],
            ['Metric', 'Value'],
            ['Total Procedures', $summary['total_procedures'] ?? 0],
            ['Completed Procedures', $summary['completed_procedures'] ?? 0],
            ['In Progress Procedures', $summary['in_progress_procedures'] ?? 0],
            ['Pending Procedures', $summary['pending_procedures'] ?? 0],
            ['Cancelled Procedures', $summary['cancelled_procedures'] ?? 0],
            ['Completion Rate', ($summary['completion_rate'] ?? 0) . '%'],
            ['Total Duration (minutes)', $summary['total_duration'] ?? 0],
            ['Average Duration (minutes)', $summary['average_duration'] ?? 0],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 16]],
            3 => ['font' => ['bold' => true, 'size' => 14]],
            8 => ['font' => ['bold' => true, 'size' => 14]],
            9 => ['font' => ['bold' => true]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,
            'B' => 20,
        ];
    }
}

class DetailedSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths, ShouldAutoSize
{
    protected $data;
    protected $startDate;
    protected $endDate;

    public function __construct($data, $startDate, $endDate)
    {
        $this->data = $data;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function title(): string
    {
        return 'Detailed';
    }

    public function array(): array
    {
        $detailed = $this->data['detailed'] ?? collect();
        
        $rows = [
            ['Physiotherapy Report - Detailed Procedures'],
            [''],
            ['ID', 'Patient', 'Type', 'Physiotherapist', 'Status', 'Progress', 'Start Date', 'Duration (min)'],
        ];

        foreach ($detailed->take(1000) as $procedure) {
            $rows[] = [
                $procedure->id,
                $procedure->appointment->patient->name ?? 'N/A',
                $procedure->physiotherapyType->name ?? 'N/A',
                $procedure->doctor->name ?? 'N/A',
                ucfirst(str_replace('_', ' ', $procedure->status)),
                $procedure->counter . '/' . $procedure->days_count,
                $procedure->start_date ? $procedure->start_date->format('Y-m-d') : 'N/A',
                $procedure->duration ?? 0,
            ];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 16]],
            3 => ['font' => ['bold' => true]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10,
            'B' => 25,
            'C' => 20,
            'D' => 25,
            'E' => 15,
            'F' => 15,
            'G' => 15,
            'H' => 15,
        ];
    }
}

class ByTypeSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths, ShouldAutoSize
{
    protected $data;
    protected $startDate;
    protected $endDate;

    public function __construct($data, $startDate, $endDate)
    {
        $this->data = $data;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function title(): string
    {
        return 'By Type';
    }

    public function array(): array
    {
        $byType = $this->data['by_type'] ?? collect();
        
        $rows = [
            ['Physiotherapy Report - By Type'],
            [''],
            ['Type', 'Total', 'Completed', 'In Progress', 'Pending', 'Completion Rate (%)', 'Avg Duration (min)'],
        ];

        foreach ($byType as $type) {
            $rows[] = [
                $type['type']->name ?? 'N/A',
                $type['total_procedures'] ?? 0,
                $type['completed_procedures'] ?? 0,
                $type['in_progress_procedures'] ?? 0,
                $type['pending_procedures'] ?? 0,
                number_format($type['completion_rate'] ?? 0, 1),
                $type['average_duration'] ?? 0,
            ];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 16]],
            3 => ['font' => ['bold' => true]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,
            'B' => 10,
            'C' => 12,
            'D' => 15,
            'E' => 12,
            'F' => 20,
            'G' => 20,
        ];
    }
}

class ByPhysiotherapistSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths, ShouldAutoSize
{
    protected $data;
    protected $startDate;
    protected $endDate;

    public function __construct($data, $startDate, $endDate)
    {
        $this->data = $data;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function title(): string
    {
        return 'By Physiotherapist';
    }

    public function array(): array
    {
        $byPhysiotherapist = $this->data['by_physiotherapist'] ?? [];
        $physiotherapists = $byPhysiotherapist['physiotherapists'] ?? collect();
        
        $rows = [
            ['Physiotherapy Report - By Physiotherapist'],
            [''],
            ['Physiotherapist', 'Email', 'Total', 'Completed', 'In Progress', 'Pending', 'Completion Rate (%)', 'Performance Score'],
        ];

        foreach ($physiotherapists as $physiotherapist) {
            $rows[] = [
                $physiotherapist['name'] ?? 'N/A',
                $physiotherapist['email'] ?? 'N/A',
                $physiotherapist['total_procedures'] ?? 0,
                $physiotherapist['completed_procedures'] ?? 0,
                $physiotherapist['in_progress_procedures'] ?? 0,
                $physiotherapist['pending_procedures'] ?? 0,
                number_format($physiotherapist['completion_rate'] ?? 0, 1),
                number_format($physiotherapist['performance_score'] ?? 0, 1),
            ];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 16]],
            3 => ['font' => ['bold' => true]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,
            'B' => 30,
            'C' => 10,
            'D' => 12,
            'E' => 15,
            'F' => 12,
            'G' => 20,
            'H' => 20,
        ];
    }
}

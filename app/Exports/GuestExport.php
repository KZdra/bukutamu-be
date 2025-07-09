<?php
namespace App\Exports;

use App\Models\Guest;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class GuestExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    ShouldAutoSize,
    WithEvents
{
    private int $iteration = 0;
    private $startDate;
    private $endDate;

    public function __construct($startDate = null, $endDate = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection(): Collection
    {
        $query = Guest::select('name', 'institution', 'purpose', 'created_at');

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('created_at', [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay(),
            ]);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama',
            'Instansi',
            'Keperluan',
            'Tanggal Kunjungan',
        ];
    }

    public function map($row): array
    {
        $this->iteration++;

        return [
            $this->iteration,
            $row->name,
            $row->institution,
            $row->purpose,
            Carbon::parse($row->created_at)->translatedFormat('l, d F Y H:i'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFD9E1F2']
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->freezePane('A2');
                $highestColumn = $sheet->getHighestColumn();
                $sheet->setAutoFilter("A1:{$highestColumn}1");
            },
        ];
    }
}

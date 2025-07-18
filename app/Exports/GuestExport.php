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
    private $statusId;
    private $unitId;


    public function __construct($startDate = null, $endDate = null, $statusId = null, $unitId = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->statusId = $statusId;
        $this->unitId = $unitId;
    }

    public function collection(): Collection
    {
        $query =  Guest::with('status', 'unit')->select('name', 'phone', 'institution_address', 'id_card_number', 'institution', 'purpose', 'status_id', 'unit_id', 'created_at', 'type');

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('created_at', [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay(),
            ]);
        }
        if ($this->statusId) {
            $query->where('status_id', $this->statusId);
        }
        if ($this->unitId) {
            $query->where('unit_id', $this->unitId);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'No Id Card Tamu',
            'Perorangan / Badan Usaha',
            'Nama',
            'No Telp',
            'Instansi',
            'Alamat Instansi',
            'Keperluan',
            'Unit Yang Dituju',
            'Tanggal Kunjungan',
            'Status'
        ];
    }

    public function map($row): array
    {
        $this->iteration++;

        return [
            $this->iteration,
            $row->id_card_number,
            ucwords(
                $row->type,
            ),
            $row->name,
            $row->phone,
            $row->institution,
            $row->institution_address,
            $row->purpose,
            optional($row->unit)->name ?? '-', // ← ini untuk ambil status
            Carbon::parse($row->created_at)->translatedFormat('l, d F Y H:i'),
            optional($row->status)->name ?? '-', // ← ini untuk ambil status
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

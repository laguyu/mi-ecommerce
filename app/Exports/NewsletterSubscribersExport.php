<?php

namespace App\Exports;

use App\Models\NewsletterSubscriber;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class NewsletterSubscribersExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(protected Collection $subscribers)
    {
    }

    public function collection(): Collection
    {
        return $this->subscribers;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Email',
            'Estado',
            'Suscrito desde',
            'Creado en',
            'Actualizado en',
        ];
    }

    public function map($row): array
    {
        /** @var NewsletterSubscriber $subscriber */
        $subscriber = $row;

        return [
            $subscriber->id,
            $subscriber->email,
            $subscriber->is_active ? 'Activo' : 'Inactivo',
            optional($subscriber->subscribed_at)->toDateTimeString(),
            optional($subscriber->created_at)->toDateTimeString(),
            optional($subscriber->updated_at)->toDateTimeString(),
        ];
    }
}

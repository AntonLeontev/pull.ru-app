<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use MoonShine\Components\FormBuilder;
use MoonShine\Components\MoonShineComponent;
use MoonShine\Fields\File;
use MoonShine\Pages\Page;

class CdekExpendsImport extends Page
{
    /**
     * @return array<string, string>
     */
    public function breadcrumbs(): array
    {
        return [
            '#' => $this->title(),
        ];
    }

    public function title(): string
    {
        return $this->title ?: 'Импорт расходов СДЭК ФФ';
    }

    /**
     * @return list<MoonShineComponent>
     */
    public function components(): array
    {
        return [
            FormBuilder::make(
                route('moonshine.cdek-expends-import.create'),
                fields: [
                    File::make('Файл', 'file'),
                ]
            )->submit('Импорт'),
        ];
    }
}

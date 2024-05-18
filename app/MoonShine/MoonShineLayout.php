<?php

namespace App\MoonShine;

use MoonShine\Components\Layout\Content;
use MoonShine\Components\Layout\Flash;
use MoonShine\Components\Layout\Footer;
use MoonShine\Components\Layout\Header;
use MoonShine\Components\Layout\LayoutBlock;
use MoonShine\Components\Layout\LayoutBuilder;
use MoonShine\Components\Layout\Menu;
use MoonShine\Components\Layout\Profile;
use MoonShine\Components\Layout\Search;
use MoonShine\Components\Layout\Sidebar;
use MoonShine\Components\When;
use MoonShine\Contracts\MoonShineLayoutContract;

class MoonshineLayout implements MoonShineLayoutContract
{
    public static function build(): LayoutBuilder
    {
        return LayoutBuilder::make([
            Sidebar::make([
                Menu::make()->customAttributes(['class' => 'mt-2']),
                When::make(
                    static fn () => config('moonshine.auth.enable', true),
                    static fn (): array => [Profile::make(withBorder: true)]
                ),
            ]),
            LayoutBlock::make([
                Flash::make(),
                Header::make([
                    Search::make(),
                ]),
                Content::make(),
                Footer::make(),
            ])->customAttributes(['class' => 'layout-page']),
        ])
            ->bodyClass('theme-minimalistic');
    }
}

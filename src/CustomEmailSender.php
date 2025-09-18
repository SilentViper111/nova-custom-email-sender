<?php

namespace Dniccum\CustomEmailSender;

use Illuminate\Http\Request;
use Laravel\Nova\Menu\MenuSection;
use Laravel\Nova\Tool;

class CustomEmailSender extends Tool
{
    /**
     * Build the menu that renders the navigation links for the tool.
     *
     * @param  \Illuminate\Http\Request $request
     * @return mixed
     */
    public function menu(Request $request)
    {
        return MenuSection::make(trans('custom-email-sender::navigation.sidebar-link'))
            ->path('/custom-email-sender')
            ->icon('paper-airplane');
    }
}

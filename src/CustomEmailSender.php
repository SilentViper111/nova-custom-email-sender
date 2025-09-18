<?php

namespace Dniccum\CustomEmailSender;

use Laravel\Nova\Nova;
use Laravel\Nova\Tool;

class CustomEmailSender extends Tool
{

    /**
     * Build the view that renders the navigation links for the tool.
     *
     * @return \Illuminate\View\View
     */
    public function renderNavigation()
    {
        return view('custom-email-sender::navigation');
    }
}

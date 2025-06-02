<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Http\RedirectResponse;

trait FlashesMessages
{
    /**
     * Redirect with a success message.
     */
    protected function redirectWithSuccess(string $route, string $message, array $parameters = []): RedirectResponse
    {
        return redirect()->route($route, $parameters)->with('success', $message);
    }

    /**
     * Redirect back with a success message.
     */
    protected function backWithSuccess(string $message): RedirectResponse
    {
        return back()->with('success', $message);
    }

    /**
     * Redirect with an error message.
     */
    protected function redirectWithError(string $route, string $message, array $parameters = []): RedirectResponse
    {
        return redirect()->route($route, $parameters)->with('error', $message);
    }

    /**
     * Redirect back with an error message.
     */
    protected function backWithError(string $message): RedirectResponse
    {
        return back()->with('error', $message);
    }

    /**
     * Redirect with a warning message.
     */
    protected function redirectWithWarning(string $route, string $message, array $parameters = []): RedirectResponse
    {
        return redirect()->route($route, $parameters)->with('warning', $message);
    }

    /**
     * Redirect back with a warning message.
     */
    protected function backWithWarning(string $message): RedirectResponse
    {
        return back()->with('warning', $message);
    }

    /**
     * Redirect with an info message.
     */
    protected function redirectWithInfo(string $route, string $message, array $parameters = []): RedirectResponse
    {
        return redirect()->route($route, $parameters)->with('info', $message);
    }

    /**
     * Redirect back with an info message.
     */
    protected function backWithInfo(string $message): RedirectResponse
    {
        return back()->with('info', $message);
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\User;

class UnsubscribeController extends Controller
{
    public function __invoke(User $user)
    {
        $user->forceFill(['email_opted_in' => false])->save();

        return view('pages.unsubscribed');
    }
}

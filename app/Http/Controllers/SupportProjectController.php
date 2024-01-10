<?php

namespace App\Http\Controllers;

use App\Mail\SupportProjectMail;
use App\Models\Catalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class SupportProjectController extends Controller
{
    public function sendMail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nome' => 'required',
            'cognome' => 'required',
            'azienda' => 'required',
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $request->all();

        $catalog = Catalog::find($data['catalog-id']);
        $emails = array_map('trim', explode(',', $catalog->designer_emails));

        try {
            Mail::to($data['email'])
                ->cc($emails)
                ->send(new SupportProjectMail($data));
        } catch (\Exception $e) {
            Log::error($e);
            throw new $e;
        }
    }
}

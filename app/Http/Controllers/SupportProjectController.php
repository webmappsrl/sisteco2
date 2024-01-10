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
        $data = $request->all();

        $validator = Validator::make($data, [
            'nome' => 'required',
            'cognome' => 'required',
            'azienda' => 'nullable',
            'email' => 'required|email',
            'note' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $catalog = Catalog::find($data['catalog-id']);
        $emails = array_map('trim', explode(',', $catalog->designer_emails));

        try {
            Mail::to($data['email'])
                ->cc($emails)
                ->send(new SupportProjectMail($data));

            return back()->with('Success', 'La tua richiesta è stata inviata con successo.');
        } catch (\Exception $e) {
            Log::error($e->getMessage() . ' - ' . $e->getTraceAsString() . ' - ' . $e->getFile() . ' - ' . $e->getLine());
            return back()->with('Error', 'Si è verificato un errore durante l\'invio della tua richiesta. Riprova più tardi.');
        }
    }
}

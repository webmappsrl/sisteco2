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

        $messages = [
            'nome.required' => 'Il campo Nome è obbligatorio.',
            'cognome.required' => 'Il campo Cognome è obbligatorio.',
            'email.required' => 'Il campo Email è obbligatorio.',
            'email.email' => 'Il campo Email deve essere un indirizzo email valido.',
            'telefono.numeric' => 'Il campo Telefono deve contenere solo numeri.',
        ];

        $validator = Validator::make($data, [
            'nome' => 'required',
            'cognome' => 'required',
            'azienda' => 'nullable',
            'email' => 'required|email',
            'telefono' => 'nullable|numeric',
            'note' => 'nullable|string'
        ], $messages);

        if ($validator->fails()) {
            return back()->with('Error', 'Errore: ' . $validator->errors()->first());
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

<?php

namespace App\Nova\Actions;

use Dompdf\Dompdf;
use Illuminate\Bus\Queueable;
use Laravel\Nova\Actions\Action;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Nova\Fields\ActionFields;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Laravel\Nova\Http\Requests\NovaRequest;

class PrintAreaPdf extends Action
{
    use InteractsWithQueue, Queueable;

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        foreach ($models as $model) {
            $sisteco = config('sisteco');
            $area = DB::table('catalog_areas')
                ->select(DB::raw('ST_Area(geometry) as area'))
                ->where('id', $model->id)
                ->value('area');
            $pdfName = 'catalog-area-' . $model->id . '.pdf';
            $pdfPath = 'pdf/' . $pdfName;

            $pdf = new Dompdf(['chroot' => public_path()]);
            $pdf->loadHtml(view('catalog-area', ['catalogArea' => $model, 'area' => $area, 'sisteco' => $sisteco])->render());
            $pdf->setPaper('A4', 'portrait');
            $pdf->render();

            if (!file_exists(storage_path('app/public/media/Pdf'))) {
                mkdir(storage_path('app/public/media/Pdf'), 0755, true);
            }

            if (!file_exists(storage_path('app/public/media/Pdf/' . $pdfName))) {
                try {
                    $output = $pdf->output();
                    file_put_contents(storage_path('app/public/media/Pdf/' . $pdfName), $output);
                } catch (\Exception $e) {
                    Log::debug("Error during PDF creation" . $e->getMessage());
                    return Action::danger('Errore durante la creazione del PDF');
                }
            }

            try {
                $model->addMedia(storage_path('app/public/media/Pdf/') . $pdfName)
                    ->toMediaCollection('documents');
            } catch (\Exception $e) {
                Log::debug("Errore durante il salvataggio del documento" . $e->getMessage());
                return Action::danger('Errore durante il salvataggio del documento');
            }
        }


        return Action::message('Documento salvato correttamente');
    }

    /**
     * Get the fields available on the action.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [];
    }
}

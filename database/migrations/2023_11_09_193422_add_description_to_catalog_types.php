<?php

use App\Models\CatalogType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('catalog_types', function (Blueprint $table) {
            $table->text('excerpt')->nullable();
            $table->text('description')->nullable();
        });

        // update Catalog_Type
        $values = [0,1,2,3,4,5];
        foreach ($values as $cod_int) {
            $types = CatalogType::where('cod_int',$cod_int)->get();
            if($types->count()>0){
                foreach($types as $t) {
                    $t->excerpt=$this->getCodeIntExcerpt($cod_int);
                    $t->description=$this->getCodeIntDescription($cod_int);
                    $t->save();
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('catalog_types', function (Blueprint $table) {
            $table->dropColumn('excerpt');
            $table->dropColumn('description');
        });
    }


    private function getCodeIntExcerpt($cod_int): string {
        switch ($cod_int) {
            // 0	Nessuna Lavorazione
            case '0':
                return 'Nessun intervento forestale previsto.';
                break;
            // 1	Diradamento
            case '1':
                return 'Diradamento fustaie di pino marittimo';
                break;
            // 2	Avviamento alto fusto
            case '2':
                return 'Avviamento a fustaia';
                break;
            // 3	Taglio Ceduo
            case '3':
                return 'Taglio ceduo, miglioramento e ricostituzione dei cedui invecchiati di castagno';
                break;
            // 4	Recupero post-incendio
            case '4':
                return 'Aree passate dal fuoco, recupero funzionale dell’ambiente forestale';
                break;
            // 5	Selvicutura ad albero
            case '5':
                return 'Selvicultura ad albero, aumento dell’indice di biodiversità potenziale';
                break;
            default:
                return 'Intervento non riconosciuto';
                break;
        }        
    }
    private function getCodeIntDescription($cod_int): string {
        switch ($cod_int) {
            // 0	Nessuna Lavorazione
            case '0':
                return 'Nessun intervento forestale previsto.';
                break;
            // 1	Diradamento
            case '1':
                return 'Gestione mirata a favorire l’evoluzione verso un bosco misto di latifoglie 
                Alleggerimento della copertura dell’intero popolamento di pino marittimo a favore degli individui più vigorosi e meglio conformati. Il taglio dei soggetti dovrà prediligere le piante deperienti, danneggiate, malformate, dominate e con evidenti attacchi di Matsococcus.
                L’intensità degli interventi colturali dovrà essere tanto maggiore quanto più il piano di rinnovazione è affermato, così da favorire la trasformazione del bosco di latifoglie.
                Sono rilasciate tutte le latifoglie sporadiche e di interesse ambientale e faunistico.
                ';
                break;
            // 2	Avviamento alto fusto
            case '2':
                return 'Taglio di avviamento con primo diradamento a carico dei polloni delle singole ceppaie con rilascio sulle ceppaie dei migliori allievi, con densità elevata (1.220 / 1.500 piante /ettaro).
                L’intervento deve garantire la permanenza delle specie meno rappresentate e delle specie importanti dal punto di vista faunistico (fruttifere) con particolare riferimento agli individui più vigorosi di corbezzolo e fillirea oltre alle altre specie poco diffuse come sughera, agrifoglio o alaterno. 
                Sono rilasciati tutti gli individui monumentali, gli individui cavi o con nidi e gli individui morti in piedi o atterrati di grandi dimensioni (almeno 2 piante morte o deperienti a ettaro, escludendo quelle con criticità di tipo fitosanitario) e almeno un individuo di grosse dimensioni ad ettaro ad invecchiamento indefinito.
                ';
                break;
            // 3	Taglio Ceduo
            case '3':
                return 'Asportazione dei polloni morti e di quelli deperienti.
                Nella maggior parte dei casi con taglio molto basso o addirittura tramarratura quando le ceppaie denotano una diminuzione della capacità rigenerativa. 
                Nelle ceppaie più vitali, con 3/4 polloni, selezione dei soggetti dalle migliori caratteristiche e asporto dei polloni deperienti o meno affermati in modo tale da prediligere i soggetti più promettenti. 
                ';
                break;
            // 4	Recupero post-incendio
            case '4':
                return 'Aree in cui è ancora presente la componente arborea: 
                taglio dei polloni morti e riceppatura bassa delle ceppaie ancora vitali; conservazione di tutte le piante portaseme vitali o parzialmente vitali, isolate o in gruppi; taglio dei pini morti in piedi. Sminuzzamento, trituratura o cippatura del materiale di risulta tagliato da restituire al suolo. Prediligere la rinnovazione di latifoglie rispetto al pino con particolare attenzione alle specie sporadiche come Arbutus Unedo, Ilex Aquifolium, Sorbus torminalis e Sorbus aucuparia, quercus suber e aceri.
                Aree in cui la componente vegetale appare compromessa e il suolo è scoperto:
                messa a dimora di nuove essenze alberi meglio se da seme di origine reperito direttamente in loco. (esempio: Q. suber, Q. pubescens, Q. ilex, Q. cerris, Ostrya carpinifolia, Acer monspessulanum, Acer opalus, Ulmus minor, Populus canescens, Popolus tremula, Salix caprea, Fraxinus ornus).
                ';
                break;
            // 5	Selvicutura ad albero
            case '5':
                return 'Individuazione all’interno del lotto boschivo di circa 60 piante/ha candidate (o piante obiettivo) tra le specie sporadiche presenti privilegiando le specie sporadiche (rare nel complesso regionale, nazionale, euorpeo e locale).
                Diradamento delle piante concorrenti così da assicurare alle piante prescelte una buona illuminazione dell’apparato fogliare, con intensità diversificata in funzione delle condizioni di sviluppo della pianta prescelta (più o meno vigoroso / più o meno modesto)
                ';
                break;
            default:
                return '';
                break;
        }
    }


};

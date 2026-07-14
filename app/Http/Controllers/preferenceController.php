<?php



namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ville;
use App\Models\Propriete;
use App\Models\Batiment;
use App\Models\Porte;
use App\Repositories\Agence\Interfaces\LotRepositoryInterface;



class preferenceController extends Controller
{
    protected  $lotRepo;

    public function __construct(
        LotRepositoryInterface $lotRepo

    ) {
        $this->lotRepo = $lotRepo;

    }
    public function getVille(Request $request)
    {
        $city = Ville::where('region_id',$request->parent_id)->get();

        $sql = '<option style="color: black" value=""  selected>---Sélectionnez une ville---</option>';
        foreach ($city as $row) {
            $sql .= '<option style="color: black" value="' . $row->id . '"  >' . $row->name .'</option>';
            // $sql.=' <option value="' . $row->nom_marque .' "></option>';
        }

        return response()->json([
            'select_tag' => $sql

        ]);
    }


    public function getlotByProprietaire(Request $request)
    {
        $agence =getInfoAgent()->users->agence_id;
        $lot = $this->lotRepo->getAllByProprietaire($request->parent_id,$agence);
        $sql = '<option style="color: black" value=""  selected>---Sélectionnez un lot---</option>';
        foreach ($lot as $row) {
            $sql .= '<option style="color: black" value="' . $row->propreietaire_lot_id . '"  >' . $row->name .'</option>';
            // $sql.=' <option value="' . $row->nom_marque .' "></option>';
        }

        return response()->json([
            'select_tag' => $sql

        ]);
    }

    public function getBatimentBylot(Request $request)
    {
        $agence =getInfoAgent()->users->agence_id;
        $propriete = Propriete::where(['lot_id'=> $request->parent_id , 'agence_id' => $agence])->first();
        $batiment          = Batiment::where(['propriete_id'=> $propriete->propriete_id , 'agence_id' => $agence])->get();
        $sql = '<option style="color: black" value=""  selected>---Sélectionnez un batiment ---</option>';
        foreach ($batiment as $row) {
            $sql .= '<option style="color: black" value="' . $row->batiment_id . '"  >' . $row->name .'</option>';
            // $sql.=' <option value="' . $row->nom_marque .' "></option>';
        }

        return response()->json([
            'select_tag' => $sql

        ]);
    }


    public function getPorteByBatiment(Request $request)
    {
        $agence =getInfoAgent()->users->agence_id;
        $batiment          = Porte::where(['batiment_id'=> $request->parent_id , 'agence_id' => $agence])->get();
        $sql = '<option style="color: black" value=""  selected>---Sélectionnez une porte---</option>';
        foreach ($batiment as $row) {
            $sql .= '<option style="color: black" value="' . $row->porte_id . '"  >' . $row->numero_porte .'</option>';
            // $sql.=' <option value="' . $row->nom_marque .' "></option>';
        }

        return response()->json([
            'select_tag' => $sql

        ]);
    }

}
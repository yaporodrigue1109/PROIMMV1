<?php

use Nette\Utils\Random;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Loyer;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;
/**
 * app/Helpers/SettingHelper.php
 *
 * Helper pour accéder facilement aux paramètres de configuration
 */





if (!function_exists('setting')) {
    /**
     * Obtenir une valeur de configuration
     *
     * @param string|null $key La clé à récupérer (null pour toute la configuration)
     * @param mixed $default Valeur par défaut
     * @return mixed
     *
     * Exemples :
     * setting() - Retourne l'objet Configuration complet
     * setting('name') - Retourne le nom commercial
     * setting('email1') - Retourne l'email principal
     * setting('contact1', '+225 07 00 00 00 00') - Retourne le téléphone ou la valeur par défaut
     */
    function setting(?string $key = null, mixed $default = null): mixed
    {
        $settingService = app(\App\Services\SettingService::class);

        if ($key === null) {
            return $settingService->getSetting();
        }

        try {
            $value = $settingService->getValue($key);
            return $value ?? $default;
        } catch (\Exception $e) {
            return $default;
        }
    }
}



if (!function_exists('company_name')) {
    /**
     * Obtenir le nom de l'entreprise
     */
    function company_name(): string
    {
        return setting('name', config('app.name'));
    }
}

if (!function_exists('company_email')) {
    /**
     * Obtenir l'email principal de l'entreprise
     */
    function company_email(): string
    {
        return setting('email1', config('mail.from.address'));
    }
}

if (!function_exists('company_phone')) {
    /**
     * Obtenir le téléphone principal de l'entreprise
     */
    function company_phone(): string
    {
        return setting('contact1', '');
    }
}

if (!function_exists('company_address')) {
    /**
     * Obtenir l'adresse de l'entreprise
     */
    function company_address(): string
    {
        return setting('adresse', '');
    }
}

if (!function_exists('company_logo')) {
    /**
     * Obtenir l'URL du logo
     */
    function company_logo(): ?string
    {
        return setting('logo') ? asset('storage/' . setting('logo')) : null;
    }
}

if (!function_exists('company_favicon')) {
    /**
     * Obtenir l'URL du favicon
     */
    function company_favicon(): ?string
    {
        return setting('flavicon') ? asset('storage/' . setting('flavicon')) : null;
    }
}

if (!function_exists('company_social')) {
    /**
     * Obtenir les réseaux sociaux de l'entreprise
     */
    function company_social(): array
    {
        return [
            'facebook' => setting('facebook'),
            'instagram' => setting('instagram'),
            'linkedin' => setting('linkedin'),
            'twitter' => setting('twitter'),
            'google' => setting('google'),
        ];
    }
}

if (!function_exists('upload')) {
function upload($dir, $format, $names, $image = null)
{
    $uploadedFile = null;

    if ($image instanceof \Illuminate\Http\UploadedFile) {
        $uploadedFile = $image;
    } elseif (is_array($image) && isset($image[$names]) && $image[$names] instanceof \Illuminate\Http\UploadedFile) {
        $uploadedFile = $image[$names];
    } elseif ($image instanceof \Illuminate\Http\Request && $image->hasFile($names)) {
        $uploadedFile = $image->file($names);
    }

    if ($uploadedFile instanceof \Illuminate\Http\UploadedFile)
    {
        $name = $uploadedFile->getClientOriginalName();
        $extension = strtolower($uploadedFile->getClientOriginalExtension() ?: $format);

        //Tableau des extensions que l'on accepte
        $extensions = ['jpg', 'png', 'jpeg', 'gif','pdf','docx','avif'];
        //Taille max que l'on accepte
        $maxSize = 10000000;
        $size = $uploadedFile->getSize();
        if(in_array($extension, $extensions) && $size <= $maxSize){

            $uniqueName = uniqid('', true);
            //uniqid génère quelque chose comme ca : 5f586bf96dcd38.73540086
            // $file = $uniqueName.".".$format;
            $file = $uniqueName.".".$extension;
            $file_path = 'admin/assets/images/'.$dir;
            if (!file_exists($file_path)) {
                // Create a new file or direcotry
                mkdir($file_path, 0777, true);
                //   CHMOD($file_path, 0777);
            }
            $uploadedFile->move($file_path, $file);
        }
        else{
            // echo "";
            return back()->withError('Mauvaise extension ou taille trop grande');
        }

        return rtrim(config('app.url'), '/').'/admin/assets/images/'.$dir.'/'.$file;
    }
}
}
if (!function_exists('update')) {
function update( $dir= null, $old_image= null,  $format= null, $image = null, $name= null)
{
    //  $file_path = 'assets/images/'.$dir; lorsque je n'enregistre pas le lien de l'image
    $file_path = 'admin/assets/images/';
    $uploadedFile = null;

    if ($image instanceof \Illuminate\Http\UploadedFile) {
        $uploadedFile = $image;
    } elseif (is_array($image) && isset($image[$name]) && $image[$name] instanceof \Illuminate\Http\UploadedFile) {
        $uploadedFile = $image[$name];
    } elseif ($image instanceof \Illuminate\Http\Request && $image->hasFile($name)) {
        $uploadedFile = $image->file($name);
    } elseif (isset($_FILES[$name]) && ($_FILES[$name]['name'] ?? '') !== '') {
        $uploadedFile = null;
    }

    if ($uploadedFile instanceof \Illuminate\Http\UploadedFile)
    {
        if(($old_image)){
            // dd(file_exists( $file_path."/".$old_image));
            /* if(file_exists( $file_path."/".$old_image)){
                 unlink($file_path."/".$old_image);

                 }*/
            if(file_exists( $old_image)){
                unlink($old_image);

            }
            //   unlink($old_image);
        }

        $imageName = upload($dir, $format, $name, $image);
        return $imageName;
    }
}
}
if (!function_exists('delete')) {
function delete( $dir, $old_image)
{
    //  $file_path = 'assets/images/'.$dir; lorsque je n'enregistre pas le lien de l'image
    $file_path = 'admin/assets/images/';
    if(($old_image)){
        // dd(file_exists( $file_path."/".$old_image));
        /* if(file_exists( $file_path."/".$old_image)){
             unlink($file_path."/".$old_image);

             }*/
        if(file_exists( $old_image)){
            unlink($old_image);

        }
    }
    return [
        'success' => 1,
        'message' => 'Removed successfully !'
    ];

}
}
if (!function_exists('doc_upload')) {
function doc_upload( $dir,  $format, $image = null)
{ //dd($image->getClientOriginalName());

    $test = explode('/',$dir);

    // dd($test);

    if ($image != null) {
        $tabExtension = explode('.', $image->getClientOriginalName());
        $extension = strtolower(end($tabExtension));
        //  dd( $extension);
        $imageName = Carbon::now()->toDateString() . "-" . uniqid() . "." . $extension;
        if (!Storage::disk('public')->exists($dir)) {
            Storage::disk('public')->makeDirectory($dir);
        }
        // dd($dir);
        if(count($test)>1)
        {
            $autre=$test[1];
            Storage::disk('public')->put($dir.'/'.$autre . $imageName, file_get_contents($image));
        }
        else{
            Storage::disk('public')->put($dir .'/'. $imageName, file_get_contents($image));
        }


    } else {
        $imageName = 'def.png';
    }

    return rtrim(config('app.url'), '/').'/'.$dir.'/'.$imageName;
}
}

if (!function_exists('doc_update')) {
function doc_update( $dir, $old_image,  $format, $image = null)
{
    foreach (json_decode($old_image) as $key => $value) {
        if (Storage::disk('public')->exists($dir . $value)) {
            Storage::disk('public')->delete($dir . $value);
        }

    }
    $imageName = doc_upload($dir, $format, $image);
    /*    if (Storage::disk('public')->exists($dir . $old_image)) {
            Storage::disk('public')->delete($dir . $old_image);
        }
        $imageName = PreferenceController::doc_upload($dir, $format, $image);*/
    return $imageName;
}
}
if (!function_exists('doc_delete')) {
function doc_delete($full_path)
{
    if (Storage::disk('public')->exists($full_path)) {
        Storage::disk('public')->delete($full_path);
    }

    return [
        'success' => 1,
        'message' => 'Removed successfully !'
    ];

}
}

if (!function_exists('getFileSizeFromUrl')) {
function getFileSizeFromUrl($url)
{
    try {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_NOBODY, TRUE); // Ne récupère que les headers
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE); // Suit les redirections
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);

        curl_exec($ch);
        $size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
        curl_close($ch);

        return $size > 0 ? $size : 0;
    } catch (\Exception $e) {
        return 0;
    }
}
}
if (!function_exists('generate')) {
function generate($table,$reference,$nbre,$string)
{

    $val=   Random::generate($nbre,$string);


    if (!empty(DB::table($table)->where($reference, $val)->first())) {
        // récursion sans $this
        return generate($table, $reference, $nbre, $string);
    }

    // dd($val);
    return $val;
}
}
if (!function_exists('getInfoAdmin')) {
function getInfoAdmin()
{
    $response ="";
    $data = [];
    //dd(Auth::guard('admin')->check());
    if(Auth::guard('admin')->check())
    {
        $data['admin'] = Auth::guard('admin')->user();
    }


    else{
        $data['admin'] = null;


        //  $data['systeme'] = SysConfiguration::first();

    }

    return (object) $data;
}
}

if (!function_exists('getInfoAgent')) {
function getInfoAgent()
{

    $response ="";
    $data = [];
    //dd(Auth::guard('admin')->check());
    if(Auth::guard('user')->check() )
    {
        $data['users'] = Auth::guard('user')->user();
    }


    else{
        $data['users'] = null;


        //  $data['systeme'] = SysConfiguration::first();

    }
   // dd($data );
    return (object) $data;
}

}

if (!function_exists('formatMoisAnnee')) {

 function formatMoisAnnee($mois, $annee)
{
    $mois = (int) $mois; // Force en entier
    $moisNom = [
        1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
        5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
        9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
    ];

    return $moisNom[$mois] . '-' . $annee;
}
}

if (!function_exists('getArrierePrecedentLocataire')) {

    function getArrierePrecedentLocataire(string $locataireId, string $porteId,string $proprietaireid, Carbon $target): float
    {
        // dd('hgjsdkl');
        $total = Loyer::where('locataire_id', $locataireId)
            ->where('porte_id',              $porteId)
            ->where('proprietaire_id',              $proprietaireid)
            ->where('montant_restant',       '>', 0)
            ->where(function ($query) use ($target) {
                // Tous les loyers dont le mois/année est strictement antérieur au mois cible
                $query->where('annee_paiement', '<', $target->year)
                    ->orWhere(function ($q) use ($target) {
                        $q->where('annee_paiement', $target->year)
                            ->where('mois_paiement',  '<', $target->month);
                    });
            })
            ->sum('montant_restant');

        return (float) $total;
    }

}


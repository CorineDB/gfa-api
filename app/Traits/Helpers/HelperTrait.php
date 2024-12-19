<?php

namespace App\Traits\Helpers;

use App\Models\Fichier;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Image;

trait HelperTrait
{

    protected $fileRepository;

    public static function changeEnvironmentVariable($key,$value)
    {
        $path = base_path('.env');

        if(is_bool(env($key)))
        {
            $old = env($key)? 'true' : 'false';
        }
        elseif(env($key)===null)
        {
            $old = 'null';
        }
        else
        {
            $old = env($key);
        }

        if (file_exists($path))
        {
            file_put_contents($path, str_replace(
            "$key=".$old, "$key=".$value, file_get_contents($path)));
        }
    }

    /**
     * Sauvegarder un fichier
     *
     * @param string document_path
     * @param Object model associé au fichier
     * @return Fichier
     */
    public function storeFile($file, $document_path, $model, $height, $description, $shared = null)
    {
            $filenameWithExt = $file->getClientOriginalName();
            $filename = strtolower(str_replace(' ', '-',time() . '-'. $filenameWithExt));
            $path ="{$document_path}/" . $filename;

            if(!$shared)
            {
                $fichier = Fichier::create([
                    'nom'                 => $filename,
                    'chemin'                 => "upload/".$path,
                    'fichiertable_type'    => $model ? get_class($model) : 'Autre',
                    'fichiertable_id'      => $model ? $model->id : 1,
                    'auteurId'             => Auth::user()->id,
                    'description'         => $description,
                    'programmeId'         => auth()->user()->programmeId
                  ]);

                if($description == "image" || $description == "logo" || $description == "photo")
                {
                    Storage::disk('public')->put( "upload/".$path, $file->getContent());
                }

                else
                {
                    Storage::disk('local')->put( "upload/".$path, $file->getContent());
                }

                //$path = $file->move("/storage/documents/{$document_path}/", $filename);

                /*if($height)
                {
                    $image = Image::make($path);
                    $image->resize($image->width(), $height);
                    $image->save($path);
                }*/
            }

            else
            {
                $fichier = Fichier::find($shared['fichierId']);

                Fichier::create([
                    'nom'                 => $fichier->nom,
                    'chemin'                 => "upload/".$fichier->chemin,
                    'fichiertable_type'    => $model ? get_class($model) : 'Autre',
                    'fichiertable_id'      => $model ? $model->id : 1,
                    'auteurId'             => Auth::user()->id,
                    'programmeId'         => auth()->user()->programmeId,
                    'description'         => $fichier->description,
                    'sharedId'           => $shared['userId']
                  ]);
            }

            return $fichier;

    }

    public function formatageNotification(Model $notification, User $user)
    {
        $note = [
            'id' => $notification->id,
            'texte' => $notification->data['texte'],
            'module' => $notification->data['module'],
            'module_id' => $notification->data['id']
        ];

        return [
            "notification" => $note,
            "notifiable_id" => $notification->notifiable_id,
            "unread" => $user->unreadNotifications->count()
        ];
    }

}

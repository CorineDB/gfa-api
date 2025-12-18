<?php

namespace App\Models;

use App\Traits\Helpers\Pta;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class ArchiveTache extends Model
{

    protected $table = 'archive_taches';

    public $timestamps = true;

    use HasSecureIds, HasFactory, Pta;

    protected $dates = ['deleted_at'];

    protected $fillable = array('nom', 'position', 'poids', 'parentId', 'activiteId', 'description', 'ptabScopeId', 'statut');

    public function activite()
    {
        return $this->belongsTo(ArchiveActivite::class, 'activiteId');
    }

    public function ptabScope()
    {
        return $this->belongsTo(PtabScope::class, 'ptabScopeId');
    }

    public function durees()
    {
        return $this->morphMany(Duree::class, 'dureeable');
    }

    public function suivis()
    {
        return $this->morphMany(Suivi::class, 'suivitable');
    }

    public function suivi()
    {
        return $this->suivis()->get()->last();
    }

    public function fichiers()
    {
        return $this->morphMany(Fichier::class, 'fichiertable');
    }

    public function commentaires()
    {
        return $this->morphMany(Commentaire::class, 'commentable');
    }

    public function statuts()
    {
        return $this->morphMany(Statut::class, 'statuttable');
    }

    public function getStatusAttribute()
    {
        $statut = $this->statuts->last();

        $statut = $statut ? $statut : $this->statuts()->create(['etat' => -1]);

        if($statut['etat'] > -2 && $this->position == 0)
        {
            $this->position = $this->position($this->activite, 'taches');
            $this->save();
        }

        return $statut ? $statut['etat'] : null;
    }

    public function getDureeAttribute()
    {
        $duree = $this->durees->first();
        $min = strtotime($duree->debut) - strtotime('1970-01-01');

        foreach($this->durees as $d)
        {
            $dif = strtotime(date('Y-m-d')) - strtotime($d->debut);

            if($dif <= $min)
            {
                $min = $dif;
                $duree = $d;
            }

        }

        return $duree;
    }

    public function getDebutAttribute()
    {
        if($this->duree)
            return $this->duree->debut;
    }

    public function getFinAttribute()
    {
        if($this->duree)
            return $this->duree->fin;
    }

    public function getTepAttribute()
    {
        $suivi = $this->suivis->last();

        if(!$suivi) return 0;

        return ( optional($suivi)->poidsActuel * 100) / $this->poids;
    }

    public function terminer()
    {
        $etat = ['etat' => 2];
        $suivi = ['poidsActuel' => $this->poids];

        $etats = $this->statuts()->create($etat);
        $suivi = $this->suivis()->create($suivi);
    }

    public function getCodePtaAttribute()
    {
        $activite = $this->activite;
        if($this->statut != -2 && $this->position == 0)
        {
            $this->position = max($this->activite->taches->pluck('position')->toArray()) + 1;
            $this->save();
        }
        return ''.optional($this->activite)->codePta.'.'.$this->position;
    }

    public function getBailleurAttribut()
    {
        return $this->activite->bailleur ;
    }

}

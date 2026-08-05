<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property int $militaire_id
 * @property string $message
 * @property \Illuminate\Support\Carbon $date_echeance
 * @property bool $est_vue
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $type_alerte
 * @property-read \App\Models\Militaire $militaire
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alerte newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alerte newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alerte query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alerte whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alerte whereDateEcheance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alerte whereEstVue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alerte whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alerte whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alerte whereMilitaireId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alerte whereTypeAlerte($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alerte whereUpdatedAt($value)
 */
	class Alerte extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $nom_certificat
 * @property string $niveau_certificat
 * @property string $grade_associe
 * @property array<array-key, mixed>|null $conditions
 * @property int|null $anciennete_requise
 * @property string|null $certificat_precedent
 * @property int|null $duree_certificat_precedent
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Certificat newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Certificat newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Certificat query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Certificat whereAncienneteRequise($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Certificat whereCertificatPrecedent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Certificat whereConditions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Certificat whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Certificat whereDureeCertificatPrecedent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Certificat whereGradeAssocie($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Certificat whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Certificat whereNiveauCertificat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Certificat whereNomCertificat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Certificat whereUpdatedAt($value)
 */
	class Certificat extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $militaire_id
 * @property \Illuminate\Support\Carbon $date_debut
 * @property \Illuminate\Support\Carbon|null $date_fin
 * @property \Illuminate\Support\Carbon|null $date_renouvellement
 * @property int $duree_annees
 * @property string $statut
 * @property string|null $observations
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Militaire $militaire
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contrat newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contrat newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contrat query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contrat whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contrat whereDateDebut($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contrat whereDateFin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contrat whereDateRenouvellement($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contrat whereDureeAnnees($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contrat whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contrat whereMilitaireId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contrat whereObservations($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contrat whereStatut($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contrat whereUpdatedAt($value)
 */
	class Contrat extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $militaire_id
 * @property string $type
 * @property string $cible
 * @property \Illuminate\Support\Carbon $date_eligibilite
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Militaire $militaire
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Eligibilite newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Eligibilite newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Eligibilite query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Eligibilite whereCible($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Eligibilite whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Eligibilite whereDateEligibilite($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Eligibilite whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Eligibilite whereMilitaireId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Eligibilite whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Eligibilite whereUpdatedAt($value)
 */
	class Eligibilite extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $nom_grade
 * @property string $code_grade
 * @property string $type_grade
 * @property int $ordre
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $retraite_obligatoire
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Militaire> $militaires
 * @property-read int|null $militaires_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade whereCodeGrade($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade whereNomGrade($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade whereOrdre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade whereRetraiteObligatoire($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade whereTypeGrade($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade whereUpdatedAt($value)
 */
	class Grade extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $matricule
 * @property string $nom
 * @property string $prenom
 * @property \Illuminate\Support\Carbon $date_naissance
 * @property \Illuminate\Support\Carbon $date_entree_service
 * @property string $grade_actuel
 * @property \Illuminate\Support\Carbon|null $date_derniere_promotion
 * @property string|null $specialite
 * @property string $statut
 * @property bool $a_permis_conduire
 * @property bool $a_fait_justice
 * @property bool $a_fait_discipline
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $position_actuelle
 * @property string|null $fonction_passee
 * @property string|null $fonction_actuelle
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Alerte> $alertes
 * @property-read int|null $alertes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Certificat> $certificats
 * @property-read int|null $certificats_count
 * @property-read \App\Models\Contrat|null $contratActif
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Contrat> $contrats
 * @property-read int|null $contrats_count
 * @property-read int $age
 * @property-read int $anciennete
 * @property-read string $anciennete_detaillee
 * @property-read int $anciennete_grade
 * @property-read string $anciennete_grade_detaillee
 * @property-read \App\Models\Contrat|null $contrat_actif
 * @property-read \App\Models\Grade|null $grade
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Militaire newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Militaire newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Militaire query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Militaire whereAFaitDiscipline($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Militaire whereAFaitJustice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Militaire whereAPermisConduire($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Militaire whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Militaire whereDateDernierePromotion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Militaire whereDateEntreeService($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Militaire whereDateNaissance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Militaire whereFonctionActuelle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Militaire whereFonctionPassee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Militaire whereGradeActuel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Militaire whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Militaire whereMatricule($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Militaire whereNom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Militaire wherePositionActuelle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Militaire wherePrenom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Militaire whereSpecialite($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Militaire whereStatut($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Militaire whereUpdatedAt($value)
 */
	class Militaire extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property int $is_admin
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 */
	class User extends \Eloquent {}
}


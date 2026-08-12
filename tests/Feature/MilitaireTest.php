<?php

namespace Tests\Feature;

use App\Models\Certificat;
use App\Models\CertificatDocument;
use App\Models\Grade;
use App\Models\Militaire;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MilitaireTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Grade::create([
            'nom_grade' => 'Soldat 2',
            'code_grade' => 'S2',
            'ordre' => 1,
            'type_grade' => 'militaire du rang',
        ]);

        Grade::create([
            'nom_grade' => 'Sergent',
            'code_grade' => 'SGT',
            'ordre' => 5,
            'type_grade' => 'sous-officier',
        ]);
    }

    public function test_can_create_a_militaire(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('militaires.store'), [
            'matricule' => 'MIL-10001',
            'nom' => 'Kouassi',
            'prenom' => 'Jean',
            'date_naissance' => '1995-05-15',
            'date_entree_service' => '2015-01-10',
            'grade_actuel' => 'Soldat 2',
            'statut' => 'actif',
            'telephone' => '+2250102030405',
            'sexe' => 'M',
            'groupe_sanguin' => 'O+',
        ]);

        $response->assertRedirect(route('militaires.index'));

        $this->assertDatabaseHas('militaires', [
            'matricule' => 'MIL-10001',
            'nom' => 'Kouassi',
            'prenom' => 'Jean',
            'statut' => 'actif',
        ]);
    }

    public function test_can_update_a_militaire_including_statut_formation_and_stage(): void
    {
        $user = User::factory()->create();

        $militaire = Militaire::create([
            'matricule' => 'MIL-10002',
            'nom' => 'Yao',
            'prenom' => 'Paul',
            'date_naissance' => '1990-08-20',
            'date_entree_service' => '2012-03-01',
            'grade_actuel' => 'Soldat 2',
            'statut' => 'actif',
        ]);

        $response = $this->actingAs($user)->put(route('militaires.update', $militaire->id), [
            'matricule' => 'MIL-10002',
            'nom' => 'Yao',
            'prenom' => 'Paul Henri',
            'date_naissance' => '1990-08-20',
            'date_entree_service' => '2012-03-01',
            'grade_actuel' => 'Sergent',
            'statut' => 'formation',
            'specialite' => 'Transmission',
            'sexe' => 'M',
            'groupe_sanguin' => 'A+',
        ]);

        $response->assertRedirect(route('militaires.show', $militaire->id));

        $this->assertDatabaseHas('militaires', [
            'id' => $militaire->id,
            'prenom' => 'Paul Henri',
            'grade_actuel' => 'Sergent',
            'statut' => 'formation',
            'specialite' => 'Transmission',
        ]);
    }

    public function test_syncing_certificats_preserves_existing_pivot_ids_and_documents(): void
    {
        $user = User::factory()->create();

        $militaire = Militaire::create([
            'matricule' => 'MIL-10003',
            'nom' => 'Konan',
            'prenom' => 'Yves',
            'date_naissance' => '1992-02-10',
            'date_entree_service' => '2014-04-01',
            'grade_actuel' => 'Soldat 2',
            'statut' => 'actif',
        ]);

        $certificat = Certificat::create([
            'nom_certificat' => 'CAT1',
            'niveau_certificat' => 'CAT1',
            'grade_associe' => 'Soldat 2',
            'conditions' => 'Aucune',
        ]);

        // Attacher le certificat initial
        $militaire->certificats()->attach($certificat->id, ['date_obtention' => '2020-01-01']);

        $pivotId = \DB::table('certificat_militaire')
            ->where('militaire_id', $militaire->id)
            ->where('certificat_id', $certificat->id)
            ->value('id');

        $doc = CertificatDocument::create([
            'militaire_certificat_id' => $pivotId,
            'nom_fichier' => 'test_certif.pdf',
            'chemin_fichier' => 'certificats_documents/test.pdf',
            'type_fichier' => 'application/pdf',
            'taille' => 1024,
        ]);

        // Mise à jour du militaire avec ré-envoi du certificat
        $response = $this->actingAs($user)->put(route('militaires.update', $militaire->id), [
            'matricule' => 'MIL-10003',
            'nom' => 'Konan',
            'prenom' => 'Yves Modified',
            'date_naissance' => '1992-02-10',
            'date_entree_service' => '2014-04-01',
            'grade_actuel' => 'Soldat 2',
            'statut' => 'actif',
            'certificats' => [
                $certificat->id => [
                    'obtenu' => '1',
                    'date_obtention' => '2020-01-01',
                ]
            ]
        ]);

        $response->assertRedirect(route('militaires.show', $militaire->id));

        // Vérifier que le document n'a PAS été supprimé
        $this->assertDatabaseHas('certificat_documents', [
            'id' => $doc->id,
            'militaire_certificat_id' => $pivotId,
        ]);
    }
}

<?php

namespace App\Imports;

use App\Models\Militaire;
use App\Models\Certificat;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MilitairesImport implements ToCollection, WithHeadingRow, WithValidation, SkipsEmptyRows, SkipsOnFailure, WithChunkReading
{
    use SkipsFailures;

    /**
     * Mapping des colonnes Excel vers les noms de certificats en base
     */
    protected const CERTIFICATE_MAPPING = [
        // Certificats sous-officiers
        'cat1'  => "Certificat d'Aptitude Technique Niveau 1",
        'cat2'  => "Certificat d'Aptitude Technique Niveau 2",
        'cia'   => "Certificat Interarmes (CIA)",
        'ba1'   => "Brevet d'Arme N1",
        'ba2'   => "Brevet d'Arme N2",
        'bmp1'  => "Brevet Militaire Professionnel N1",
        'bmp2'  => "Brevet Militaire Professionnel N2",
        'bs'    => "Brevet Supérieur",
        'ct2'   => "Certificat Technique N2",
        'be'    => "Brevet Élémentaire (BE)",
        'ct1'   => "Certificat Technique N1 (CT1)",
        // Formations officiers
        'apli'  => "Cour d'Application",
        'cfcu'  => "Cour des Capitaines / CFCU / CPO",
        'cpo'   => "Cour des Capitaines / CFCU / CPO",
        'certificat_etat_major' => "Certificat d'état-major",
        'ecole_guerre' => "École de guerre / Brevet Supérieur de Second Degré",
    ];

    protected string $duplicateAction;
    protected array $summary = [
        'created' => 0,
        'updated' => 0,
        'skipped' => 0,
        'errors' => 0
    ];
    protected array $duplicates = [];
    protected array $errorDetails = [];
    protected ?Collection $certificatsCache = null;

    public function __construct(string $duplicateAction = 'ignore')
    {
        $this->duplicateAction = $duplicateAction;
    }

    public function chunkSize(): int
    {
        return 500;
    }

    protected function getCertificatsCache(): Collection
    {
        if ($this->certificatsCache === null) {
            $this->certificatsCache = Certificat::all()->keyBy('nom');
        }
        return $this->certificatsCache;
    }

    public function collection(Collection $rows)
    {
        // Récupérer la liste des matricules valides du lot
        $matricules = $rows->pluck('matricule')
            ->filter(function ($m) {
                return $m !== null && trim((string)$m) !== '';
            })
            ->map(function ($m) {
                return trim((string)$m);
            })
            ->unique()
            ->toArray();

        // Récupération globale des militaires existants pour ce lot (1 seule requête SQL)
        $existingMilitaires = !empty($matricules)
            ? Militaire::whereIn('matricule', $matricules)->get()->keyBy('matricule')
            : collect();

        $pivotRowsToUpsert = [];
        $now = now()->toDateTimeString();

        DB::transaction(function () use ($rows, $existingMilitaires, &$pivotRowsToUpsert, $now) {
            foreach ($rows as $index => $row) {
                try {
                    $matricule = trim((string)($row['matricule'] ?? ''));
                    if (empty($matricule)) {
                        continue;
                    }

                    $existing = $existingMilitaires->get($matricule);

                    if ($existing) {
                        if ($this->duplicateAction === 'update') {
                            $militaire = $this->updateMilitaire($existing, $row->toArray());
                            $this->summary['updated']++;
                            $this->collectCertificatesForMilitaire($militaire, $row->toArray(), $pivotRowsToUpsert, $now);
                        } else {
                            $this->summary['skipped']++;
                            $this->duplicates[] = [
                                'matricule' => $matricule,
                                'nom' => $row['nom'] ?? '',
                                'prenom' => $row['prenom'] ?? '',
                                'action' => 'Ignoré'
                            ];
                        }
                        continue;
                    }

                    $militaire = $this->createMilitaire($row->toArray());
                    $this->summary['created']++;
                    $this->collectCertificatesForMilitaire($militaire, $row->toArray(), $pivotRowsToUpsert, $now);

                } catch (\Throwable $e) {
                    $this->summary['errors']++;
                    $this->errorDetails[] = [
                        'row' => $index + 2,
                        'matricule' => $row['matricule'] ?? 'N/A',
                        'message' => $e->getMessage()
                    ];
                    Log::error('Erreur import ligne ' . ($index + 2) . ': ' . $e->getMessage(), [
                        'row' => $row->toArray(),
                        'exception' => $e->getMessage()
                    ]);
                }
            }

            // Insertion / mise à jour globale ultra-rapide des certificats pivot pour le lot (1 seule requête SQL bulk upsert)
            if (!empty($pivotRowsToUpsert)) {
                // Dédupliquer par (militaire_id, certificat_id) dans le lot
                $uniquePivot = [];
                foreach ($pivotRowsToUpsert as $item) {
                    $key = $item['militaire_id'] . '_' . $item['certificat_id'];
                    $uniquePivot[$key] = $item;
                }

                DB::table('certificat_militaire')->upsert(
                    array_values($uniquePivot),
                    ['militaire_id', 'certificat_id'],
                    ['date_obtention', 'updated_at']
                );
            }
        });
    }

    /**
     * Parse une valeur de date, supportant :
     * - Les nombres sériels Excel (ex: 45292)
     * - Le format français JJ/MM/AAAA
     * - Le format standard AAAA-MM-JJ
     * - Le format JJ-MM-AAAA
     */
    protected function parseDate($value): ?Carbon
    {
        if (empty($value) || $value === '' || $value === null) {
            return null;
        }

        if (is_numeric($value)) {
            try {
                $excelBaseDate = Carbon::create(1899, 12, 30);
                return $excelBaseDate->addDays((int) $value);
            } catch (\Throwable $e) {
                return null;
            }
        }

        $value = trim((string) $value);

        try {
            if (preg_match('#^\d{1,2}/\d{1,2}/\d{4}$#', $value)) {
                return Carbon::createFromFormat('d/m/Y', $value)->startOfDay();
            }
        } catch (\Throwable $e) {}

        try {
            if (preg_match('#^\d{1,2}-\d{1,2}-\d{4}$#', $value)) {
                return Carbon::createFromFormat('d-m-Y', $value)->startOfDay();
            }
        } catch (\Throwable $e) {}

        try {
            return Carbon::parse($value)->startOfDay();
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Normalise la valeur du sexe
     */
    protected function parseSexe($value): ?string
    {
        if (empty($value)) return null;

        $value = mb_strtolower(trim((string)$value));

        if (in_array($value, ['m', 'masculin', 'homme', 'h', 'male'])) {
            return 'Masculin';
        }
        if (in_array($value, ['f', 'féminin', 'feminin', 'femme', 'female'])) {
            return 'Féminin';
        }

        return null;
    }

    /**
     * Normalise la valeur du statut
     */
    protected function parseStatut($value): string
    {
        if (empty($value)) return 'actif';

        $value = mb_strtolower(trim((string)$value));

        $statuts = ['actif', 'retraité', 'déserteur', 'décédé', 'formation', 'stage'];

        if (in_array($value, $statuts)) {
            return $value;
        }

        $mapping = [
            'retraite' => 'retraité',
            'deserteur' => 'déserteur',
            'decede' => 'décédé',
        ];

        return $mapping[$value] ?? 'actif';
    }

    /**
     * Parse une valeur booléenne (0/1, oui/non, vrai/faux, true/false)
     */
    protected function parseBool($value): bool
    {
        if (is_bool($value)) return $value;
        if (is_numeric($value)) return (bool) (int) $value;

        $value = mb_strtolower(trim((string) $value));

        return in_array($value, ['1', 'oui', 'vrai', 'true', 'yes', 'o', 'v']);
    }

    protected function createMilitaire(array $row): Militaire
    {
        $militaire = new Militaire();
        $militaire->matricule = trim((string)$row['matricule']);
        $militaire->nom = trim((string)($row['nom'] ?? ''));
        $militaire->prenom = trim((string)($row['prenom'] ?? ''));
        $militaire->date_naissance = $this->parseDate($row['date_naissance'] ?? null);
        $militaire->date_entree_service = $this->parseDate($row['date_entree_service'] ?? null);
        $militaire->grade_actuel = trim((string)($row['grade_actuel'] ?? ''));
        $militaire->date_derniere_promotion = $this->parseDate($row['date_derniere_promotion'] ?? null);
        $militaire->specialite = !empty($row['specialite']) ? trim((string)$row['specialite']) : null;
        $militaire->statut = $this->parseStatut($row['statut'] ?? null);
        $militaire->telephone = !empty($row['telephone']) ? trim((string)$row['telephone']) : null;
        $militaire->sexe = $this->parseSexe($row['sexe'] ?? null);
        $militaire->groupe_sanguin = !empty($row['groupe_sanguin']) ? trim((string)$row['groupe_sanguin']) : null;
        $militaire->personne_a_contacter = !empty($row['personne_a_contacter']) ? trim((string)$row['personne_a_contacter']) : null;
        $militaire->telephone_personne_contacter = !empty($row['telephone_personne_contacter']) ? trim((string)$row['telephone_personne_contacter']) : null;
        $militaire->position_actuelle = !empty($row['position_actuelle']) ? trim((string)$row['position_actuelle']) : null;
        $militaire->fonction_passee = !empty($row['fonction_passee']) ? trim((string)$row['fonction_passee']) : null;
        $militaire->fonction_actuelle = !empty($row['fonction_actuelle']) ? trim((string)$row['fonction_actuelle']) : null;
        $militaire->a_permis_conduire = $this->parseBool($row['a_permis_conduire'] ?? false);
        $militaire->a_fait_justice = $this->parseBool($row['a_fait_justice'] ?? false);
        $militaire->a_fait_discipline = $this->parseBool($row['a_fait_discipline'] ?? false);
        $militaire->save();

        return $militaire;
    }

    protected function updateMilitaire(Militaire $militaire, array $row): Militaire
    {
        if (!empty($row['nom'])) $militaire->nom = trim((string)$row['nom']);
        if (!empty($row['prenom'])) $militaire->prenom = trim((string)$row['prenom']);

        $dateNaissance = $this->parseDate($row['date_naissance'] ?? null);
        if ($dateNaissance) $militaire->date_naissance = $dateNaissance;

        $dateEntree = $this->parseDate($row['date_entree_service'] ?? null);
        if ($dateEntree) $militaire->date_entree_service = $dateEntree;

        if (!empty($row['grade_actuel'])) $militaire->grade_actuel = trim((string)$row['grade_actuel']);

        $datePromo = $this->parseDate($row['date_derniere_promotion'] ?? null);
        if ($datePromo) $militaire->date_derniere_promotion = $datePromo;

        if (!empty($row['specialite'])) $militaire->specialite = trim((string)$row['specialite']);
        if (!empty($row['statut'])) $militaire->statut = $this->parseStatut($row['statut']);
        if (!empty($row['telephone'])) $militaire->telephone = trim((string)$row['telephone']);
        if (!empty($row['sexe'])) $militaire->sexe = $this->parseSexe($row['sexe']);
        if (!empty($row['groupe_sanguin'])) $militaire->groupe_sanguin = trim((string)$row['groupe_sanguin']);
        if (!empty($row['personne_a_contacter'])) $militaire->personne_a_contacter = trim((string)$row['personne_a_contacter']);
        if (!empty($row['telephone_personne_contacter'])) $militaire->telephone_personne_contacter = trim((string)$row['telephone_personne_contacter']);
        if (!empty($row['position_actuelle'])) $militaire->position_actuelle = trim((string)$row['position_actuelle']);
        if (!empty($row['fonction_passee'])) $militaire->fonction_passee = trim((string)$row['fonction_passee']);
        if (!empty($row['fonction_actuelle'])) $militaire->fonction_actuelle = trim((string)$row['fonction_actuelle']);

        if (isset($row['a_permis_conduire'])) $militaire->a_permis_conduire = $this->parseBool($row['a_permis_conduire']);
        if (isset($row['a_fait_justice'])) $militaire->a_fait_justice = $this->parseBool($row['a_fait_justice']);
        if (isset($row['a_fait_discipline'])) $militaire->a_fait_discipline = $this->parseBool($row['a_fait_discipline']);

        if ($militaire->isDirty()) {
            $militaire->save();
        }

        return $militaire;
    }

    /**
     * Collecter les données de certificats pour une insertion groupée (bulk upsert)
     */
    protected function collectCertificatesForMilitaire(Militaire $militaire, array $row, array &$pivotRows, string $now): void
    {
        $certificatsCache = $this->getCertificatsCache();

        foreach (self::CERTIFICATE_MAPPING as $key => $nomCertificat) {
            $colFait = 'a_fait_' . $key;
            $colDate = 'date_obtention_' . $key;

            if (!isset($row[$colFait]) || !$this->parseBool($row[$colFait])) {
                continue;
            }

            $certificat = $certificatsCache->get($nomCertificat);
            if (!$certificat) {
                continue;
            }

            $dateObtention = $this->parseDate($row[$colDate] ?? null);

            $pivotRows[] = [
                'militaire_id' => $militaire->id,
                'certificat_id' => $certificat->id,
                'date_obtention' => $dateObtention ? $dateObtention->toDateString() : null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
    }

    public function rules(): array
    {
        return [
            'matricule' => 'required',
            'nom' => 'nullable',
            'prenom' => 'nullable',
            'date_naissance' => 'nullable',
            'date_entree_service' => 'nullable',
            'grade_actuel' => 'nullable',
            'date_derniere_promotion' => 'nullable',
            'specialite' => 'nullable',
            'statut' => 'nullable',
            'sexe' => 'nullable',
            'telephone' => 'nullable',
            'groupe_sanguin' => 'nullable',
            'personne_a_contacter' => 'nullable',
            'telephone_personne_contacter' => 'nullable',
            'position_actuelle' => 'nullable',
            'fonction_passee' => 'nullable',
            'fonction_actuelle' => 'nullable',
            'a_permis_conduire' => 'nullable',
            'a_fait_justice' => 'nullable',
            'a_fait_discipline' => 'nullable',
            // Certificats sous-officiers
            'a_fait_cat1' => 'nullable',
            'date_obtention_cat1' => 'nullable',
            'a_fait_cat2' => 'nullable',
            'date_obtention_cat2' => 'nullable',
            'a_fait_cia' => 'nullable',
            'date_obtention_cia' => 'nullable',
            'a_fait_ba1' => 'nullable',
            'date_obtention_ba1' => 'nullable',
            'a_fait_ba2' => 'nullable',
            'date_obtention_ba2' => 'nullable',
            'a_fait_bmp1' => 'nullable',
            'date_obtention_bmp1' => 'nullable',
            'a_fait_bmp2' => 'nullable',
            'date_obtention_bmp2' => 'nullable',
            'a_fait_bs' => 'nullable',
            'date_obtention_bs' => 'nullable',
            'a_fait_ct2' => 'nullable',
            'date_obtention_ct2' => 'nullable',
            // Formations officiers
            'a_fait_apli' => 'nullable',
            'date_obtention_apli' => 'nullable',
            'a_fait_cfcu' => 'nullable',
            'date_obtention_cfcu' => 'nullable',
            'a_fait_cpo' => 'nullable',
            'date_obtention_cpo' => 'nullable',
            'a_fait_cem' => 'nullable',
            'date_obtention_cem' => 'nullable',
            'a_fait_certificat_etat_major' => 'nullable',
            'date_obtention_certificat_etat_major' => 'nullable',
            'a_fait_ecole_guerre' => 'nullable',
            'date_obtention_ecole_guerre' => 'nullable',
        ];
    }

    public function getSummary(): array
    {
        return $this->summary;
    }

    public function getDuplicates(): array
    {
        return $this->duplicates;
    }

    public function getErrorDetails(): array
    {
        return $this->errorDetails;
    }

    public function getImportedCount(): int
    {
        return $this->summary['created'];
    }

    public function getSkippedCount(): int
    {
        return $this->summary['skipped'];
    }

    /**
     * Retourne les en-têtes de colonnes pour le modèle d'import
     */
    public static function getTemplateHeaders(): array
    {
        return [
            // Informations personnelles
            'matricule',
            'nom',
            'prenom',
            'date_naissance',
            'sexe',
            'telephone',
            'groupe_sanguin',
            'personne_a_contacter',
            'telephone_personne_contacter',
            // Informations professionnelles
            'date_entree_service',
            'grade_actuel',
            'date_derniere_promotion',
            'specialite',
            'statut',
            // Fonctions et positions
            'position_actuelle',
            'fonction_passee',
            'fonction_actuelle',
            // Permis et justice
            'a_permis_conduire',
            'a_fait_justice',
            'a_fait_discipline',
            // Certificats sous-officiers
            'a_fait_cat1',
            'date_obtention_cat1',
            'a_fait_cat2',
            'date_obtention_cat2',
            'a_fait_cia',
            'date_obtention_cia',
            'a_fait_ba1',
            'date_obtention_ba1',
            'a_fait_ba2',
            'date_obtention_ba2',
            'a_fait_bmp1',
            'date_obtention_bmp1',
            'a_fait_bmp2',
            'date_obtention_bmp2',
            'a_fait_bs',
            'date_obtention_bs',
            'a_fait_ct2',
            'date_obtention_ct2',
            // Formations officiers
            'a_fait_apli',
            'date_obtention_apli',
            'a_fait_cfcu',
            'date_obtention_cfcu',
            'a_fait_cpo',
            'date_obtention_cpo',
            'a_fait_cem',
            'date_obtention_cem',
            'a_fait_certificat_etat_major',
            'date_obtention_certificat_etat_major',
            'a_fait_ecole_guerre',
            'date_obtention_ecole_guerre',
        ];
    }

    /**
     * Retourne un exemple de données pour le modèle d'import
     */
    public static function getTemplateExampleRow(): array
    {
        return [
            // Informations personnelles
            'MAT-001',
            'DUPONT',
            'Jean',
            '15/03/1990',
            'Masculin',
            '77000000',
            'A+',
            'Marie Dupont',
            '77111111',
            // Informations professionnelles
            '01/09/2010',
            'Sergent',
            '15/06/2020',
            'Infanterie',
            'actif',
            // Fonctions et positions
            'Compagnie Alpha',
            'Chef de section',
            'Adjoint compagnie',
            // Permis et justice
            '1',
            '0',
            '0',
            // Certificats sous-officiers
            '1', '15/03/2015',  // cat1
            '1', '20/06/2017',  // cat2
            '0', '',            // cia
            '1', '10/01/2018',  // ba1
            '0', '',            // ba2
            '1', '05/09/2019',  // bmp1
            '0', '',            // bmp2
            '0', '',            // bs
            '0', '',            // ct2
            // Formations officiers
            '0', '',            // apli
            '0', '',            // cfcu
            '0', '',            // cpo
            '0', '',            // cem
            '0', '',            // certificat_etat_major
            '0', '',            // ecole_guerre
        ];
    }
}
